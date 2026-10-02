<?php
namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\AuthService;
use App\Models\StaffTransferModel;
use App\Models\UserModel;
use App\Models\BranchModel;
use App\Models\AuditLogModel;

/**
 * StaffTransferController ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â transfers staff between branches.
 *
 * Permission rules:
 *   - Admin: can always transfer any staff.
 *   - HR with can_transfer_staff=1: can transfer staff from their own branch.
 *   - HR with can_transfer_staff=0: 403 on all transfer actions.
 */
class StaffTransferController extends ResourceController
{
    protected $authService;
    protected $transferModel;
    protected $userModel;
    protected $branchModel;
    protected $auditLog;

    public function __construct()
    {
        $this->authService   = new AuthService(service('request'));
        $this->transferModel = new StaffTransferModel();
        $this->userModel     = new UserModel();
        $this->branchModel   = new BranchModel();
        $this->auditLog      = new AuditLogModel();
    }

    // -- Helpers ---------------------------------------------------------------

    private function getAuthedUser(): ?object
    {
        return $this->authService->check() ?: null;
    }

    private function canTransfer(object $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role === 'hr') {
            $userId = $user->sub ?? $user->id ?? null;
            $row = $this->userModel->find($userId);
            return !empty($row['can_transfer_staff']) && $row['can_transfer_staff'] == 1;
        }
        return false;
    }

    // -- Page Views ------------------------------------------------------------

    public function page()
    {
        // Use session-based auth for page load (no JWT header on browser requests)
        $userId   = session()->get('user_id');
        $userRole = session()->get('role');

        if (!$userId || !$userRole) {
            return redirect()->to('/login');
        }

        // Build a simple user object from session for canTransfer check
        $user = (object)['role' => $userRole, 'id' => $userId, 'sub' => $userId];

        if (!$this->canTransfer($user)) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to transfer staff.');
        }

        $branches = $this->branchModel->getActiveBranches();

        // Load all employees server-side so the dropdown works without AJAX auth issues
        $db = \Config\Database::connect();
        $builder = $db->table('users u')
            ->select('u.id, ui.firstname, ui.lastname, u.email, u.branch_id, b.name AS branch_name')
            ->join('user_info ui', 'ui.user_id = u.id', 'left')
            ->join('branches b', 'b.id = u.branch_id', 'left')
            ->where('u.is_deleted', 0)
            ->where('u.role !=', 'admin');

        if ($userRole === 'hr') {
            $hrBranchId = $this->authService->getBranchId();
            $builder->where('u.branch_id', $hrBranchId);
        }

        $staffList = $builder->orderBy('ui.firstname')->get()->getResultArray();

        return view('staff_transfer/index', [
            'role'      => $userRole,
            'branches'  => $branches,
            'staffList' => $staffList,
        ]);
    }

    // -- API Endpoints ---------------------------------------------------------

    /**
     * POST api/staff-transfer/initiate ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â perform the transfer
     */
    public function initiate()
    {
        $user = $this->getAuthedUser();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        if (!$this->canTransfer($user)) {
            return $this->respond(['status' => 'error', 'message' => 'You do not have permission to transfer staff.'], 403);
        }

        $data         = $this->request->getJSON(true) ?? $this->request->getPost();
        $staffId      = (int)($data['user_id'] ?? 0);
        $toBranchId   = (int)($data['to_branch_id'] ?? 0);
        $effectiveDate = $data['effective_date'] ?? date('Y-m-d');
        $reason       = $data['reason'] ?? '';
        $actorId      = $user->sub ?? $user->id ?? null;

        if (!$staffId || !$toBranchId) {
            return $this->respond(['status' => 'error', 'message' => 'user_id and to_branch_id are required.'], 422);
        }

        $staff = $this->userModel->find($staffId);
        if (!$staff || $staff['is_deleted'] || !in_array($staff['role'], ['employee', 'hr'])) {
            return $this->respond(['status' => 'error', 'message' => 'Staff member not found.'], 404);
        }

        $fromBranchId = (int)($staff['branch_id'] ?? 0);

        // HR can only transfer staff from their own branch
        if ($user->role === 'hr') {
            $hrBranchId = $this->authService->getBranchId();
            if ($fromBranchId !== $hrBranchId) {
                return $this->respond([
                    'status'  => 'error',
                    'message' => 'You can only transfer staff from your own branch.',
                ], 403);
            }
        }

        // Cannot transfer to same branch
        if ($fromBranchId === $toBranchId) {
            return $this->respond(['status' => 'error', 'message' => 'Staff is already in the target branch.'], 422);
        }

        // Validate target branch exists
        $toBranch = $this->branchModel->find($toBranchId);
        if (!$toBranch) {
            return $this->respond(['status' => 'error', 'message' => 'Target branch not found.'], 404);
        }

        // Use a transaction for atomicity
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Update user's branch
            $this->userModel->update($staffId, ['branch_id' => $toBranchId]);

            // 2. Record transfer history
            $this->transferModel->insert([
                'user_id'        => $staffId,
                'from_branch_id' => $fromBranchId,
                'to_branch_id'   => $toBranchId,
                'transferred_by' => $actorId,
                'reason'         => $reason,
                'effective_date' => $effectiveDate,
                'created_at'     => date('Y-m-d H:i:s'),
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed.');
            }

            $this->auditLog->log($actorId, 'staff.transfer', 'StaffTransfer', $staffId,
                ['branch_id' => $fromBranchId],
                ['branch_id' => $toBranchId, 'effective_date' => $effectiveDate]);

            return $this->respond([
                'status'  => 'success',
                'message' => 'Staff transferred successfully. New branch rules apply from ' . $effectiveDate . '.',
            ]);

        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'StaffTransfer::initiate error: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Transfer failed. Please try again.'], 500);
        }
    }

    /**
     * GET api/staff-transfer/history?branch_id=X&user_id=Y
     */
    public function history()
    {
        $user = $this->getAuthedUser();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        if (!$this->canTransfer($user)) {
            return $this->respond(['status' => 'error', 'message' => 'Access denied.'], 403);
        }

        $userId   = (int)($this->request->getGet('user_id') ?? 0);
        $branchId = (int)($this->request->getGet('branch_id') ?? 0);
        
        $filters = [
            'search'           => $this->request->getGet('search') ?? '',
            'filter_branch_id' => $this->request->getGet('filter_branch_id') ?? '',
            'start_date'       => $this->request->getGet('start_date') ?? '',
            'end_date'         => $this->request->getGet('end_date') ?? '',
        ];
        
        $page = (int)($this->request->getGet('page') ?? 1);
        $limit = (int)($this->request->getGet('limit') ?? 0);
        if ($limit > 0) {
            $filters['limit'] = $limit;
            $filters['offset'] = ($page - 1) * $limit;
        }

        $total = 0;
        if ($userId) {
            $records = $this->transferModel->getHistoryForUser($userId);
            $total = count($records);
        } elseif ($user->role === 'admin') {
            $records = $this->transferModel->getAllHistory($branchId ?: null, $filters);
            $total = $this->transferModel->countAllHistory($branchId ?: null, $filters);
        } else {
            // HR sees transfers in their branch
            $hrBranchId = $this->authService->getBranchId();
            $records = $this->transferModel->getAllHistory($hrBranchId, $filters);
            $total = $this->transferModel->countAllHistory($hrBranchId, $filters);
        }

        return $this->respond([
            'status' => 'success', 
            'data' => $records,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    /**
     * GET api/staff-transfer/export
     */
    public function export()
    {
        $user = $this->getAuthedUser();
        if (!$user) {
            return $this->response->setStatusCode(401)->setBody('Unauthorized');
        }

        if (!$this->canTransfer($user)) {
            return $this->response->setStatusCode(403)->setBody('Access denied.');
        }

        $filters = [
            'search'           => $this->request->getGet('search') ?? '',
            'filter_branch_id' => $this->request->getGet('filter_branch_id') ?? '',
            'start_date'       => $this->request->getGet('start_date') ?? '',
            'end_date'         => $this->request->getGet('end_date') ?? '',
        ];

        if ($user->role === 'admin') {
            $records = $this->transferModel->getAllHistory(null, $filters);
        } else {
            $hrBranchId = $this->authService->getBranchId();
            $records = $this->transferModel->getAllHistory($hrBranchId, $filters);
        }

        $filename = 'Staff_Transfers_' . date('Ymd_His') . '.csv';

        $output = fopen('php://temp', 'w');
        
        // Add UTF-8 BOM
        fputs($output, "\xEF\xBB\xBF");
        
        // Header
        fputcsv($output, ['Employee ID', 'Employee Name', 'From Branch', 'To Branch', 'Effective Date', 'Reason', 'Transferred By', 'Transferred On']);

        foreach ($records as $r) {
            $empName = trim(($r['firstname'] ?? '-') . ' ' . ($r['lastname'] ?? ''));
            $byName = trim(($r['transferred_by_firstname'] ?? '-') . ' ' . ($r['transferred_by_lastname'] ?? ''));
            
            fputcsv($output, [
                $r['employee_id'] ?? '-',
                $empName,
                $r['from_branch_name'] ?? '-',
                $r['to_branch_name'] ?? '-',
                $r['effective_date'] ? date('d-m-Y', strtotime($r['effective_date'])) : '-',
                $r['reason'] ?? '',
                $byName,
                $r['created_at'] ?? '-'
            ]);
        }

        rewind($output);
        $csvData = stream_get_contents($output);
        fclose($output);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csvData);
    }

    /**
     * GET api/staff-transfer/eligible-staff ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â staff that can be transferred
     */
    public function eligibleStaff()
    {
        $user = $this->getAuthedUser();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        if (!$this->canTransfer($user)) {
            return $this->respond(['status' => 'error', 'message' => 'Access denied.'], 403);
        }

        $builder = $this->db->table('users u')
            ->select('u.id, ui.firstname, ui.lastname, u.email, u.branch_id, b.name AS branch_name')
            ->join('user_info ui', 'ui.user_id = u.id', 'left')
            ->join('branches b', 'b.id = u.branch_id', 'left')
            ->where('u.is_deleted', 0)
            ;

        if ($user->role === 'hr') {
            $hrBranchId = $this->authService->getBranchId();
            $builder->where('u.branch_id', $hrBranchId);
        }

        $staff = $builder->orderBy('ui.firstname')->get()->getResultArray();

        return $this->respond(['status' => 'success', 'data' => $staff]);
    }
}

