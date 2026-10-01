<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\AuthService;
use App\Models\BranchModel;
use App\Models\BranchRulesModel;
use App\Models\UserModel;
use App\Models\UserInfoModel;
use App\Models\AuditLogModel;

/**
 * BranchController — Admin-only CRUD for branches.
 * All methods check for admin role server-side (AdminOnlyFilter also applied
 * at route level, but defence-in-depth here too).
 */
class BranchController extends ResourceController
{
    protected $authService;
    protected $branchModel;
    protected $branchRulesModel;
    protected $userModel;
    protected $userInfoModel;
    protected $auditLog;
    protected $db;

    public function __construct()
    {
        $this->db               = \Config\Database::connect();
        $this->authService      = new AuthService(service('request'));
        $this->branchModel      = new BranchModel();
        $this->branchRulesModel = new BranchRulesModel();
        $this->userModel        = new UserModel();
        $this->userInfoModel    = new UserInfoModel();
        $this->auditLog         = new AuditLogModel();
    }

    // -- Helper ----------------------------------------------------------------

    private function requireAdmin(): ?object
    {
        $user = $this->authService->check();
        if (!$user || $user->role !== 'admin') {
            return null;
        }
        return $user;
    }

    private function requireAdminOrHr(): ?object
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            return null;
        }
        return $user;
    }

    // -- Page Views ------------------------------------------------------------

    public function index()
    {
        if (!$this->requireAdminOrHr()) {
            return redirect()->to('/dashboard')->with('error', 'Admin or HR access required.');
        }
        return view('branches/index');
    }

    public function managersPage()
    {
        if (!$this->requireAdminOrHr()) {
            return redirect()->to('/dashboard')->with('error', 'Admin or HR access required.');
        }
        return view('branches/managers');
    }

    public function create()
    {
        if (!$this->requireAdmin()) {
            return redirect()->to('/dashboard');
        }
        return view('branches/form');
    }

    public function edit($id = null)
    {
        if (!$this->requireAdmin()) {
            return redirect()->to('/dashboard');
        }
        $branch = $this->branchModel->find($id);
        if (!$branch) {
            return redirect()->to('/branches')->with('error', 'Branch not found.');
        }

        // Fallback to branch_rules coordinates if branch coordinates are empty
        $rules = $this->branchRulesModel->where('branch_id', $id)->first();
        if ($rules) {
            if (empty($branch['latitude']) && !empty($rules['office_latitude'])) {
                $branch['latitude'] = $rules['office_latitude'];
            }
            if (empty($branch['longitude']) && !empty($rules['office_longitude'])) {
                $branch['longitude'] = $rules['office_longitude'];
            }
            if ((empty($branch['radius']) || $branch['radius'] == 100) && !empty($rules['office_radius'])) {
                $branch['radius'] = $rules['office_radius'];
            }
        }

        return view('branches/form', ['branch' => $branch]);
    }

    public function assignManagerPage($id = null)
    {
        if (!$this->requireAdminOrHr()) {
            return redirect()->to('/dashboard');
        }
        $branch = $this->branchModel->find($id);
        if (!$branch) {
            return redirect()->to('/branches')->with('error', 'Branch not found.');
        }

        // Current Branch Manager (branch_admin)
        $currentManager = $this->db->table('users u')
            ->select('u.id, ui.firstname, ui.lastname, u.email, ui.contact_number, ui.employee_id, ui.profile_image')
            ->join('user_info ui', 'ui.user_id = u.id', 'left')
            ->where('u.branch_id', $id)
            ->where('u.role', 'branch_admin')
            ->where('u.is_deleted', 0)
            ->get()->getRowArray();

        // Eligible candidates to be Branch Manager (employees, dept managers, or existing branch admins)
        $candidates = $this->db->table('users u')
            ->select('u.id, ui.firstname, ui.lastname, u.email, u.role, u.branch_id, b.name as branch_name, ui.employee_id, ui.contact_number')
            ->join('user_info ui', 'ui.user_id = u.id', 'left')
            ->join('branches b', 'b.id = u.branch_id', 'left')
            ->whereIn('u.role', ['employee', 'department_manager', 'branch_admin'])
            ->where('u.is_deleted', 0)
            ->orderBy('ui.firstname', 'ASC')
            ->get()->getResultArray();

        return view('branches/assign_manager', [
            'branch'         => $branch,
            'currentManager' => $currentManager,
            'candidates'     => $candidates,
        ]);
    }

    public function assignHrPage($id = null)
    {
        return $this->assignManagerPage($id);
    }

    // -- API Endpoints ---------------------------------------------------------

    /**
     * GET api/branches — list all branches (search + pagination) with Branch Manager info
     */
    public function list()
    {
        if (!$this->requireAdminOrHr()) {
            return $this->respond(['status' => 'error', 'message' => 'Admin or HR access required.'], 403);
        }

        $search  = $this->request->getGet('search') ?? '';
        $perPage = (int)($this->request->getGet('per_page') ?? 10);
        $page    = (int)($this->request->getGet('page') ?? 1);
        $offset  = ($page - 1) * $perPage;

        // Total count
        $totalBuilder = $this->db->table('branches b')->where('b.deleted_at IS NULL');
        if ($search) {
            $totalBuilder->groupStart()->like('b.name', $search)->orLike('b.code', $search)->groupEnd();
        }
        $total = $totalBuilder->countAllResults();

        // Fetch with stats & Branch Manager info
        $builder = $this->db->table('branches b')
            ->select('b.*, 
                (SELECT u.id FROM users u WHERE u.branch_id = b.id AND u.role = "branch_admin" AND u.is_deleted = 0 LIMIT 1) AS branch_admin_id,
                (SELECT COALESCE(CONCAT(ui.firstname, " ", ui.lastname), u.username) FROM users u LEFT JOIN user_info ui ON ui.user_id = u.id WHERE u.branch_id = b.id AND u.role = "branch_admin" AND u.is_deleted = 0 LIMIT 1) AS branch_admin_name,
                (SELECT u.email FROM users u WHERE u.branch_id = b.id AND u.role = "branch_admin" AND u.is_deleted = 0 LIMIT 1) AS branch_admin_email,
                (SELECT COUNT(*) FROM department d WHERE d.branch_id = b.id) AS dept_count,
                (SELECT COUNT(*) FROM users WHERE branch_id = b.id AND role = "employee" AND is_deleted = 0) AS staff_count')
            ->where('b.deleted_at IS NULL');

        if ($search) {
            $builder->groupStart()->like('b.name', $search)->orLike('b.code', $search)->groupEnd();
        }

        $branches = $builder->orderBy('b.created_at', 'DESC')
                            ->limit($perPage, $offset)
                            ->get()->getResultArray();

        return $this->respond([
            'status' => 'success',
            'data'   => $branches,
            'total'  => $total,
            'page'   => $page,
            'per_page' => $perPage,
        ]);
    }

    /**
     * POST api/branches/set-active
     * Sets the active branch context for admin users
     */
    public function setActiveBranch()
    {
        $user = $this->authService->check();
        if (!$user || $user->role !== 'admin') {
            return $this->respond(['status' => 'error', 'message' => 'Admin access required.'], 403);
        }

        $json = $this->request->getJSON();
        $branchId = isset($json->branch_id) ? $json->branch_id : '';
        
        session()->set('admin_active_branch', $branchId);
        
        return $this->respond(['status' => 'success', 'message' => 'Branch filter updated.']);
    }

    /**
     * GET api/branches/(:num) — single branch
     */
    public function show($id = null)
    {
        if (!$this->requireAdmin()) {
            return $this->respond(['status' => 'error', 'message' => 'Admin access required.'], 403);
        }

        $branch = $this->branchModel->find($id);
        if (!$branch) {
            return $this->respond(['status' => 'error', 'message' => 'Branch not found.'], 404);
        }

        $rules = $this->branchRulesModel->where('branch_id', $id)->first();

        // HR assigned to this branch
        $hrList = $this->db->table('users u')
            ->select('u.id, ui.firstname, ui.lastname, u.email, u.can_transfer_staff')
            ->join('user_info ui', 'ui.user_id = u.id', 'left')
            ->where('u.branch_id', $id)
            ->where('u.role', 'hr')
            ->where('u.is_deleted', 0)
            ->get()->getResultArray();

        return $this->respond([
            'status' => 'success',
            'data'   => $branch,
            'rules'  => $rules,
            'hr'     => $hrList,
        ]);
    }

    /**
     * POST api/branches — create branch
     */
    public function store()
    {
        $admin = $this->requireAdmin();
        if (!$admin) {
            return $this->respond(['status' => 'error', 'message' => 'Admin access required.'], 403);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        // Validation
        $rules = [
            'name' => 'required|min_length[2]|max_length[255]',
            'code' => 'required|min_length[2]|max_length[50]|is_unique[branches.code]',
        ];
        if (!$this->validate($rules)) {
            return $this->respond([
                'status' => 'error',
                'errors' => $this->validator->getErrors(),
            ], 422);
        }

        $insertData = [
            'name'    => trim($data['name']),
            'code'    => strtoupper(trim($data['code'])),
            'address' => $data['address'] ?? null,
            'city'      => $data['city'] ?? null,
            'phone'     => $data['phone'] ?? null,
            'status'    => $data['status'] ?? 'active',
            'latitude'  => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'radius'    => isset($data['radius']) ? (int)$data['radius'] : 100,
        ];

        $branchService = new \App\Services\BranchService();
        $branchId = $branchService->createBranch($insertData, $admin->sub);

        return $this->respond([
            'status'    => 'success',
            'message'   => 'Branch created successfully.',
            'branch_id' => $branchId,
        ]);
    }

    /**
     * PUT api/branches/(:num) — update branch
     */
    public function update($id = null)
    {
        $admin = $this->requireAdmin();
        if (!$admin) {
            return $this->respond(['status' => 'error', 'message' => 'Admin access required.'], 403);
        }

        $branch = $this->branchModel->find($id);
        if (!$branch) {
            return $this->respond(['status' => 'error', 'message' => 'Branch not found.'], 404);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getRawInput();

        $rules = [
            'name' => 'required|min_length[2]|max_length[255]',
            'code' => "required|min_length[2]|max_length[50]|is_unique[branches.code,id,{$id}]",
        ];
        if (!$this->validate($rules)) {
            return $this->respond([
                'status' => 'error',
                'errors' => $this->validator->getErrors(),
            ], 422);
        }

        $updateData = [
            'name'    => trim($data['name']),
            'code'    => strtoupper(trim($data['code'])),
            'address' => $data['address'] ?? $branch['address'],
            'city'      => $data['city'] ?? $branch['city'],
            'phone'     => $data['phone'] ?? $branch['phone'],
            'status'    => $data['status'] ?? $branch['status'],
            'latitude'  => $data['latitude'] ?? $branch['latitude'],
            'longitude' => $data['longitude'] ?? $branch['longitude'],
            'radius'    => isset($data['radius']) ? (int)$data['radius'] : ($branch['radius'] ?? 100),
        ];

        $this->branchModel->update($id, $updateData);
        $this->auditLog->log($admin->sub, 'branch.update', 'Branch', (int)$id, $branch, $updateData);

        // Bidirectional sync: synchronize geofence settings to branch_rules
        $ruleUpdate = [
            'office_latitude'  => !empty($updateData['latitude']) ? (string)$updateData['latitude'] : null,
            'office_longitude' => !empty($updateData['longitude']) ? (string)$updateData['longitude'] : null,
            'office_radius'    => isset($updateData['radius']) && $updateData['radius'] !== '' ? (int)$updateData['radius'] : 100,
        ];
        if (!empty($updateData['latitude']) && !empty($updateData['longitude'])) {
            $ruleUpdate['enable_geofencing'] = 1;
        }

        $existingRule = $this->branchRulesModel->where('branch_id', $id)->first();
        if ($existingRule) {
            $this->branchRulesModel->update($existingRule['id'], $ruleUpdate);
        } else {
            $defaultRules = $this->branchRulesModel->getDefaultRules();
            $mergedRules = array_merge($defaultRules, $ruleUpdate, ['branch_id' => (int)$id]);
            $this->branchRulesModel->insert($mergedRules);
        }

        return $this->respond(['status' => 'success', 'message' => 'Branch updated successfully.']);
    }

    /**
     * DELETE api/branches/(:num) — soft-delete a branch
     */
    public function delete($id = null)
    {
        $admin = $this->requireAdmin();
        if (!$admin) {
            return $this->respond(['status' => 'error', 'message' => 'Admin access required.'], 403);
        }

        $branch = $this->branchModel->find($id);
        if (!$branch) {
            return $this->respond(['status' => 'error', 'message' => 'Branch not found.'], 404);
        }

        $branchService = new \App\Services\BranchService();
        try {
            $branchService->deleteBranch((int)$id, $admin->sub);
            return $this->respond(['status' => 'success', 'message' => 'Branch deleted successfully.']);
        } catch (\Exception $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    /**
     * POST api/branches/assign-manager — assign a user as Branch Admin for a branch
     */
    public function assignManager()
    {
        $admin = $this->requireAdminOrHr();
        if (!$admin) {
            return $this->respond(['status' => 'error', 'message' => 'Admin or HR access required.'], 403);
        }

        $data     = $this->request->getJSON(true) ?? $this->request->getPost();
        $userId   = (int)($data['user_id'] ?? 0);
        $branchId = (int)($data['branch_id'] ?? 0);

        if (!$userId || !$branchId) {
            return $this->respond(['status' => 'error', 'message' => 'user_id and branch_id are required.'], 422);
        }

        $user = $this->userModel->where('id', $userId)->where('is_deleted', 0)->first();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        if (in_array($user['role'], ['admin', 'hr'])) {
            return $this->respond(['status' => 'error', 'message' => 'Super Admin and Global HR cannot be assigned as a Branch Admin.'], 400);
        }

        $branch = $this->branchModel->find($branchId);
        if (!$branch) {
            return $this->respond(['status' => 'error', 'message' => 'Branch not found.'], 404);
        }

        $oldRole = $user['role'];
        $oldBranchId = $user['branch_id'];

        // Update target user to role 'branch_admin' and set their branch_id
        $this->userModel->update($userId, [
            'role'      => 'branch_admin',
            'branch_id' => $branchId,
        ]);
        $this->userInfoModel->where('user_id', $userId)->set([
            'role' => 'branch_admin',
        ])->update();

        $this->auditLog->log($admin->sub, 'branch.assign_manager', 'User', $userId, 
            ['old_role' => $oldRole, 'old_branch' => $oldBranchId], 
            ['role' => 'branch_admin', 'branch_id' => $branchId]);

        return $this->respond(['status' => 'success', 'message' => 'Branch Manager assigned successfully.']);
    }

    /**
     * POST api/branches/assign-hr — backward compatibility
     */
    public function assignHr()
    {
        return $this->assignManager();
    }

    /**
     * GET api/branch-managers — get all branch managers
     */
    public function getBranchManagers()
    {
        if (!$this->requireAdminOrHr()) {
            return $this->respond(['status' => 'error', 'message' => 'Admin or HR access required.'], 403);
        }

        $branches = $this->db->table('branches b')
            ->select('b.id as branch_id, b.name as branch_name, b.code as branch_code, b.city as branch_city, b.phone as branch_phone, b.status as branch_status,
                      u.id as manager_id, u.email as manager_email, u.role as manager_role,
                      ui.firstname, ui.lastname, ui.contact_number, ui.profile_image, ui.employee_id,
                      (SELECT COUNT(*) FROM department d WHERE d.branch_id = b.id) as department_count,
                      (SELECT COUNT(*) FROM users emp WHERE emp.branch_id = b.id AND emp.role = "employee" AND emp.is_deleted = 0) as staff_count')
            ->join('users u', 'u.branch_id = b.id AND u.role = "branch_admin" AND u.is_deleted = 0', 'left')
            ->join('user_info ui', 'ui.user_id = u.id', 'left')
            ->where('b.deleted_at IS NULL')
            ->orderBy('b.name', 'ASC')
            ->get()->getResultArray();

        return $this->respond([
            'status' => 'success',
            'data'   => $branches,
        ]);
    }

    /**
     * POST api/branches/toggle-transfer-permission — toggle can_transfer_staff for an HR
     */
    public function toggleTransferPermission()
    {
        $admin = $this->requireAdmin();
        if (!$admin) {
            return $this->respond(['status' => 'error', 'message' => 'Admin access required.'], 403);
        }

        $data     = $this->request->getJSON(true) ?? $this->request->getPost();
        $hrUserId = (int)($data['user_id'] ?? 0);

        if (!$hrUserId) {
            return $this->respond(['status' => 'error', 'message' => 'user_id is required.'], 422);
        }

        $hrUser = $this->userModel->where('id', $hrUserId)->where('role', 'hr')->first();
        if (!$hrUser) {
            return $this->respond(['status' => 'error', 'message' => 'HR user not found.'], 404);
        }

        $newValue = $hrUser['can_transfer_staff'] ? 0 : 1;
        $this->userModel->update($hrUserId, ['can_transfer_staff' => $newValue]);
        $this->auditLog->log($admin->sub, 'hr.toggle_transfer_permission', 'User', $hrUserId,
            ['can_transfer_staff' => $hrUser['can_transfer_staff']],
            ['can_transfer_staff' => $newValue]);

        return $this->respond([
            'status'              => 'success',
            'can_transfer_staff'  => $newValue,
            'message'             => $newValue ? 'Transfer permission granted.' : 'Transfer permission revoked.',
        ]);
    }

    /**
     * GET api/branches/list-all — dropdown data (name + id) for all active branches
     */
    public function listAll()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $branches = $this->branchModel->getActiveBranches();

        return $this->respond(['status' => 'success', 'data' => $branches]);
    }
}

