<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ResignationModel;
use App\Models\HandoverTaskModel;
use App\Models\ClearanceItemModel;
use App\Models\FnfSettlementModel;
use App\Models\FnfItemModel;
use App\Models\ResignationAuditLogModel;
use App\Models\UserModel;
use App\Services\AuthService;

class ResignationController extends BaseController
{
    private ResignationModel      $resignationModel;
    private HandoverTaskModel     $handoverModel;
    private ClearanceItemModel    $clearanceModel;
    private FnfSettlementModel    $fnfModel;
    private FnfItemModel          $fnfItemModel;
    private ResignationAuditLogModel $auditModel;
    private UserModel             $userModel;
    private AuthService           $authService;

    public function __construct()
    {
        $this->resignationModel = new ResignationModel();
        $this->handoverModel    = new HandoverTaskModel();
        $this->clearanceModel   = new ClearanceItemModel();
        $this->fnfModel         = new FnfSettlementModel();
        $this->fnfItemModel     = new FnfItemModel();
        $this->auditModel       = new ResignationAuditLogModel();
        $this->userModel        = new UserModel();
        $this->authService      = new AuthService(service('request'));
    }

    private function authUser()
    {
        return $this->authService->check();
    }

    private function getNoticeDays(int $userId): int
    {
        $db   = \Config\Database::connect();
        $info = $db->table('user_info')->where('user_id', $userId)->get()->getRowArray();
        return (int)($info['notice_period'] ?? 30);
    }

    private function json(array $data, int $code = 200)
    {
        return $this->response->setStatusCode($code)->setJSON($data);
    }

    // =========================================================================
    // EMPLOYEE VIEWS
    // =========================================================================

    /** GET /resignation — employee dashboard */
    public function index()
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/login');

        $existing = $this->resignationModel->getActiveForEmployee($user->sub);

        return view('resignation/employee/index', [
            'user'        => $user,
            'resignation' => $existing,
        ]);
    }

    /** POST /resignation/submit — employee submits */
    public function submit()
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/login');

        if ($this->resignationModel->getActiveForEmployee($user->sub)) {
            return redirect()->to('/resignation')->with('error', 'You already have an active resignation.');
        }

        $db      = \Config\Database::connect();
        $userRow = $db->table('users')->where('id', $user->sub)->get()->getRowArray();

        // Find reporting manager
        $managerId = $userRow['reporting_manager'] ?? null;
        if (!$managerId) {
            $mgr = $this->userModel->whereIn('role', ['hr', 'admin'])->first();
            $managerId = $mgr ? $mgr['id'] : $user->sub;
        }

        $noticeDays = $this->getNoticeDays($user->sub);

        $id = $this->resignationModel->insert([
            'employee_id'      => $user->sub,
            'resignation_date' => $this->request->getPost('resignation_date'),
            'reason'           => $this->request->getPost('reason'),
            'requested_lwd'    => $this->request->getPost('requested_lwd'),
            'notice_days'      => $noticeDays,
            'status'           => 'submitted',
            'manager_id'       => $managerId,
        ]);

        $this->auditModel->log($id, $user->sub, 'submitted', '', 'submitted', 'Resignation submitted');

        return redirect()->to('/resignation')->with('success', 'Resignation submitted. Awaiting manager approval.');
    }

    /** GET /resignation/detail/:id — employee resignation detail + timeline */
    public function myDetail(int $id)
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/login');

        $r = $this->resignationModel->getWithDetails($id);
        if (!$r || (int)$r['employee_id'] !== (int)$user->sub) {
            return redirect()->to('/resignation')->with('error', 'Not found.');
        }

        return view('resignation/employee/detail', [
            'user'        => $user,
            'resignation' => $r,
            'auditLogs'   => $this->auditModel->getByResignation($id),
            'handover'    => $this->handoverModel->getByResignation($id),
            'clearance'   => $this->clearanceModel->getByResignation($id),
        ]);
    }

    /** GET /resignation/withdraw/:id */
    public function withdraw(int $id)
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/login');

        $r = $this->resignationModel->find($id);
        if (!$r || (int)$r['employee_id'] !== (int)$user->sub) {
            return redirect()->to('/resignation')->with('error', 'Not found.');
        }
        if (!in_array($r['status'], ['submitted', 'manager_approved'])) {
            return redirect()->to('/resignation')->with('error', 'Cannot withdraw at this stage.');
        }

        $this->resignationModel->update($id, ['status' => 'withdrawn']);
        $this->auditModel->log($id, $user->sub, 'withdrawn', $r['status'], 'withdrawn', 'Employee withdrew resignation');

        return redirect()->to('/resignation')->with('success', 'Resignation withdrawn successfully.');
    }

    // =========================================================================
    // HANDOVER
    // =========================================================================

    /** GET /resignation/handover/:resignationId */
    public function handoverPage(int $resignationId)
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/login');

        $r = $this->resignationModel->find($resignationId);
        if (!$r) return redirect()->to('/resignation')->with('error', 'Not found.');

        // Employee sees their own; manager/hr can view all
        $isAdminHr = in_array($user->role, ['admin', 'hr', 'branch_admin', 'department_manager']);
        if (!$isAdminHr && (int)$r['employee_id'] !== (int)$user->sub) {
            return redirect()->to('/resignation')->with('error', 'Access denied.');
        }

        $employees = $this->userModel->where('is_deleted', 0)->findAll();

        return view('resignation/handover/index', [
            'user'        => $user,
            'resignation' => $r,
            'tasks'       => $this->handoverModel->getByResignation($resignationId),
            'employees'   => $employees,
        ]);
    }

    /** POST /api/resignation/handover/add */
    public function addHandoverTask()
    {
        $user = $this->authUser();
        if (!$user) return $this->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

        $resignationId = (int)$this->request->getPost('resignation_id');
        $r = $this->resignationModel->find($resignationId);
        if (!$r || (int)$r['employee_id'] !== (int)$user->sub) {
            return $this->json(['status' => 'error', 'message' => 'Not found'], 404);
        }

        $taskId = $this->handoverModel->insert([
            'resignation_id' => $resignationId,
            'task'           => $this->request->getPost('task'),
            'description'    => $this->request->getPost('description'),
            'handover_to'    => (int)$this->request->getPost('handover_to'),
            'due_date'       => $this->request->getPost('due_date'),
            'status'         => 'pending',
        ]);

        $this->auditModel->log($resignationId, $user->sub, 'handover_task_added', '', '', "Task ID #{$taskId} added");

        return $this->json(['status' => 'success', 'message' => 'Task added.', 'task_id' => $taskId]);
    }

    /** POST /api/resignation/handover/update/:taskId */
    public function updateHandoverTask(int $taskId)
    {
        $user = $this->authUser();
        if (!$user) return $this->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

        $task = $this->handoverModel->find($taskId);
        if (!$task) return $this->json(['status' => 'error', 'message' => 'Not found'], 404);

        $newStatus = $this->request->getPost('status');
        $remarks   = $this->request->getPost('remarks') ?? '';

        $update = ['status' => $newStatus, 'acceptor_remarks' => $remarks];
        if ($newStatus === 'accepted')  $update['accepted_at']  = date('Y-m-d H:i:s');
        if ($newStatus === 'completed') $update['completed_at'] = date('Y-m-d H:i:s');

        $this->handoverModel->update($taskId, $update);
        $this->auditModel->log($task['resignation_id'], $user->sub, "handover_task_{$newStatus}", '', '', $remarks);

        // All tasks done → move to clearance
        if ($newStatus === 'completed' && $this->handoverModel->allCompleted($task['resignation_id'])) {
            $this->resignationModel->update($task['resignation_id'], ['status' => 'clearance']);
            $this->clearanceModel->createDefaults($task['resignation_id']);
            $this->auditModel->log($task['resignation_id'], $user->sub, 'advanced_to_clearance', 'handover', 'clearance');
        }

        return $this->json(['status' => 'success', 'message' => 'Task updated.']);
    }

    /** GET /resignation/my-handover — tasks receiver needs to accept/complete */
    public function myHandoverTasks()
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/login');

        return view('resignation/handover/my_tasks', [
            'user'  => $user,
            'tasks' => $this->handoverModel->getPendingForUser($user->sub),
        ]);
    }

    // =========================================================================
    // MANAGER
    // =========================================================================

    /** GET /resignation/manager */
    public function managerList()
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/login');

        $db = \Config\Database::connect();
        $all = $db->table('resignations r')
            ->select('r.*, CONCAT(ui.firstname," ",ui.lastname) as employee_name, ui.employee_id as emp_code')
            ->join('user_info ui', 'ui.user_id = r.employee_id', 'left')
            ->where('r.manager_id', $user->sub)
            ->orderBy('r.created_at', 'DESC')
            ->get()->getResultArray();

        return view('resignation/manager/list', [
            'user' => $user,
            'all'  => $all,
        ]);
    }

    /** POST /resignation/manager/action/:id */
    public function managerAction(int $id)
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/resignation/manager');

        $r = $this->resignationModel->find($id);
        if (!$r || (int)$r['manager_id'] !== (int)$user->sub || $r['status'] !== 'submitted') {
            return redirect()->to('/resignation/manager')->with('error', 'Action not permitted.');
        }

        $action  = $this->request->getPost('action');
        $remarks = $this->request->getPost('remarks');

        if ($action === 'approve') {
            $newStatus = 'manager_approved';
            $msg = 'Resignation approved. Forwarded to HR.';
        } else {
            $newStatus = 'manager_rejected';
            $msg = 'Resignation rejected.';
        }

        $this->resignationModel->update($id, [
            'status'            => $newStatus,
            'manager_remarks'   => $remarks,
            'manager_action_at' => date('Y-m-d H:i:s'),
        ]);
        $this->auditModel->log($id, $user->sub, "manager_{$action}d", 'submitted', $newStatus, $remarks);

        return redirect()->to('/resignation/manager')->with('success', $msg);
    }

    // =========================================================================
    // HR
    // =========================================================================

    /** GET /resignation/hr */
    public function hrList()
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/login');

        return view('resignation/hr/list', [
            'user'        => $user,
            'resignations'=> $this->resignationModel->getAllWithEmployee(),
        ]);
    }

    /** GET /resignation/hr/detail/:id */
    public function hrDetail(int $id)
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/login');

        $r = $this->resignationModel->getWithDetails($id);
        if (!$r) return redirect()->to('/resignation/hr')->with('error', 'Not found.');

        $fnf      = $this->fnfModel->getByResignation($id);
        $employees = $this->userModel->where('is_deleted', 0)->findAll();

        return view('resignation/hr/detail', [
            'user'       => $user,
            'resignation'=> $r,
            'auditLogs'  => $this->auditModel->getByResignation($id),
            'handover'   => $this->handoverModel->getByResignation($id),
            'clearance'  => $this->clearanceModel->getByResignation($id),
            'fnf'        => $fnf,
            'fnfItems'   => $fnf ? $this->fnfItemModel->getByFnf((int)$fnf['id']) : [],
            'employees'  => $employees,
        ]);
    }

    /** POST /resignation/hr/action/:id */
    public function hrAction(int $id)
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/resignation/hr');

        $r = $this->resignationModel->find($id);
        if (!$r || $r['status'] !== 'manager_approved') {
            return redirect()->to("/resignation/hr/detail/{$id}")->with('error', 'Action not permitted.');
        }

        $action     = $this->request->getPost('action');
        $remarks    = $this->request->getPost('remarks');
        $finalLwd   = $this->request->getPost('final_lwd');
        $noticeDays = (int)$this->request->getPost('notice_days');

        if ($action === 'approve') {
            // Calculate shortfall: if employee's requested LWD is before final LWD
            $shortfall = 0;
            if ($r['requested_lwd'] && $finalLwd) {
                $diff = (strtotime($finalLwd) - strtotime($r['requested_lwd'])) / 86400;
                $shortfall = max(0, (int)$diff);
            }

            $this->resignationModel->update($id, [
                'status'                => 'notice_period',
                'hr_id'                 => $user->sub,
                'hr_remarks'            => $remarks,
                'hr_action_at'          => date('Y-m-d H:i:s'),
                'final_lwd'             => $finalLwd,
                'notice_days'           => $noticeDays,
                'notice_shortfall_days' => $shortfall,
            ]);
            $this->auditModel->log($id, $user->sub, 'hr_approved', 'manager_approved', 'notice_period', $remarks);
            $msg = 'Approved by HR. Notice period started.';
        } else {
            $this->resignationModel->update($id, [
                'status'       => 'hr_rejected',
                'hr_id'        => $user->sub,
                'hr_remarks'   => $remarks,
                'hr_action_at' => date('Y-m-d H:i:s'),
            ]);
            $this->auditModel->log($id, $user->sub, 'hr_rejected', 'manager_approved', 'hr_rejected', $remarks);
            $msg = 'Resignation rejected by HR.';
        }

        return redirect()->to("/resignation/hr/detail/{$id}")->with('success', $msg);
    }

    /** POST /resignation/hr/notice/:id — update notice period */
    public function updateNotice(int $id)
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/login');

        $this->resignationModel->update($id, [
            'final_lwd'    => $this->request->getPost('final_lwd'),
            'notice_days'  => (int)$this->request->getPost('notice_days'),
            'notice_waived'=> (int)($this->request->getPost('notice_waived') ?? 0),
        ]);
        $this->auditModel->log($id, $user->sub, 'notice_updated', '', '', 'Notice period updated by HR');

        return redirect()->to("/resignation/hr/detail/{$id}")->with('success', 'Notice period updated.');
    }

    /** POST /resignation/hr/advance-handover/:id */
    public function advanceHandover(int $id)
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/login');

        $r = $this->resignationModel->find($id);
        if (!$r || $r['status'] !== 'notice_period') {
            return redirect()->to("/resignation/hr/detail/{$id}")->with('error', 'Not in notice period.');
        }
        $this->resignationModel->update($id, ['status' => 'handover']);
        $this->auditModel->log($id, $user->sub, 'advanced_to_handover', 'notice_period', 'handover');

        return redirect()->to("/resignation/hr/detail/{$id}")->with('success', 'Advanced to Handover phase.');
    }

    // =========================================================================
    // CLEARANCE
    // =========================================================================

    /** GET /resignation/clearance */
    public function clearanceList()
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/login');

        $db = \Config\Database::connect();
        $items = $db->table('clearance_items c')
            ->select('c.*, r.id as resignation_id, r.final_lwd, CONCAT(ui.firstname," ",ui.lastname) as employee_name, ui.employee_id as emp_code')
            ->join('resignations r', 'r.id = c.resignation_id')
            ->join('user_info ui', 'ui.user_id = r.employee_id', 'left')
            ->where('c.status', 'pending')
            ->orderBy('c.created_at', 'ASC')
            ->get()->getResultArray();

        return view('resignation/clearance/list', ['user' => $user, 'items' => $items]);
    }

    /** POST /api/resignation/clearance/:itemId */
    public function clearanceAction(int $itemId)
    {
        $user = $this->authUser();
        if (!$user) return $this->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

        $item = $this->clearanceModel->find($itemId);
        if (!$item) return $this->json(['status' => 'error', 'message' => 'Not found'], 404);

        $action  = $this->request->getPost('action');
        $remarks = $this->request->getPost('remarks') ?? '';

        // IT: block if unreturned gadgets
        if ($item['department'] === 'IT' && $action === 'approve') {
            $db = \Config\Database::connect();
            if ($db->tableExists('gadget_issuances')) {
                $r = $this->resignationModel->find($item['resignation_id']);
                $unreturned = $db->table('gadget_issuances')
                    ->where('user_id', $r['employee_id'])
                    ->whereIn('status', ['Issued', 'Damaged'])
                    ->countAllResults();
                if ($unreturned > 0) {
                    return $this->json([
                        'status'  => 'error',
                        'message' => "Cannot approve: employee has {$unreturned} unreturned gadget(s). Mark them returned first.",
                    ], 422);
                }
            }
        }

        $this->clearanceModel->update($itemId, [
            'status'      => $action === 'approve' ? 'approved' : 'rejected',
            'approver_id' => $user->sub,
            'remarks'     => $remarks,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
        $this->auditModel->log($item['resignation_id'], $user->sub, "clearance_{$item['department']}_{$action}d", '', '', $remarks);

        // All approved → advance to F&F
        if ($action === 'approve' && $this->clearanceModel->allApproved($item['resignation_id'])) {
            $this->resignationModel->update($item['resignation_id'], ['status' => 'fnf']);
            $this->auditModel->log($item['resignation_id'], $user->sub, 'advanced_to_fnf', 'clearance', 'fnf');
        }

        return $this->json(['status' => 'success', 'message' => 'Clearance item updated.']);
    }

    // =========================================================================
    // F&F
    // =========================================================================

    /** POST /resignation/hr/fnf/prepare/:resignationId */
    public function fnfPrepare(int $resignationId)
    {
        $user = $this->authUser();
        if (!$user) return redirect()->to('/login');

        $r = $this->resignationModel->find($resignationId);
        if (!$r || $r['status'] !== 'fnf') {
            return redirect()->to("/resignation/hr/detail/{$resignationId}")->with('error', 'Not in F&F stage.');
        }

        $earnings    = $this->request->getPost('earnings')   ?? [];
        $deductions  = $this->request->getPost('deductions') ?? [];
        $totalE      = 0; $totalD = 0;
        foreach ($earnings   as $e) $totalE += (float)($e['amount'] ?? 0);
        foreach ($deductions as $d) $totalD += (float)($d['amount'] ?? 0);
        $net = $totalE - $totalD;

        $existing = $this->fnfModel->getByResignation($resignationId);

        $fnfData = [
            'resignation_id'   => $resignationId,
            'total_earnings'   => $totalE,
            'total_deductions' => $totalD,
            'net_payable'      => $net,
            'status'           => 'hr_prepared',
            'hr_prepared_by'   => $user->sub,
        ];

        if ($existing) {
            $this->fnfModel->update($existing['id'], $fnfData);
            $fnfId = (int)$existing['id'];
            $this->fnfItemModel->where('fnf_id', $fnfId)->delete();
        } else {
            $fnfId = (int)$this->fnfModel->insert($fnfData);
        }

        foreach ($earnings as $e) {
            if (!empty($e['title'])) {
                $this->fnfItemModel->insert(['fnf_id' => $fnfId, 'type' => 'earning',   'title' => $e['title'], 'amount' => (float)$e['amount'], 'created_at' => date('Y-m-d H:i:s')]);
            }
        }
        foreach ($deductions as $d) {
            if (!empty($d['title'])) {
                $this->fnfItemModel->insert(['fnf_id' => $fnfId, 'type' => 'deduction', 'title' => $d['title'], 'amount' => (float)$d['amount'], 'created_at' => date('Y-m-d H:i:s')]);
            }
        }

        $this->auditModel->log($resignationId, $user->sub, 'fnf_prepared', '', 'hr_prepared', "Net payable: {$net}");

        return redirect()->to("/resignation/hr/detail/{$resignationId}")->with('success', 'F&F prepared and sent to Finance for approval.');
    }

    /** POST /api/resignation/fnf/finance-approve/:fnfId */
    public function fnfFinanceApprove(int $fnfId)
    {
        $user = $this->authUser();
        if (!$user) return $this->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

        $fnf = $this->fnfModel->find($fnfId);
        if (!$fnf || $fnf['status'] !== 'hr_prepared') {
            return $this->json(['status' => 'error', 'message' => 'Cannot approve at this stage.'], 422);
        }

        $this->fnfModel->update($fnfId, [
            'status'              => 'finance_approved',
            'finance_approver_id' => $user->sub,
            'finance_remarks'     => $this->request->getPost('remarks'),
        ]);
        $this->auditModel->log((int)$fnf['resignation_id'], $user->sub, 'fnf_finance_approved', 'hr_prepared', 'finance_approved');

        return $this->json(['status' => 'success', 'message' => 'F&F approved by Finance.']);
    }

    /** POST /api/resignation/fnf/mark-paid/:fnfId */
    public function fnfMarkPaid(int $fnfId)
    {
        $user = $this->authUser();
        if (!$user) return $this->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

        $fnf = $this->fnfModel->find($fnfId);
        if (!$fnf || $fnf['status'] !== 'finance_approved') {
            return $this->json(['status' => 'error', 'message' => 'Cannot mark as paid at this stage.'], 422);
        }

        $this->fnfModel->update($fnfId, [
            'status'      => 'paid',
            'paid_date'   => $this->request->getPost('paid_date'),
            'payment_ref' => $this->request->getPost('payment_ref'),
        ]);

        // Relieve employee
        $r = $this->resignationModel->find((int)$fnf['resignation_id']);
        $this->resignationModel->update((int)$fnf['resignation_id'], ['status' => 'relieved']);
        $this->auditModel->log((int)$fnf['resignation_id'], $user->sub, 'relieved', 'fnf', 'relieved', 'F&F paid, employee relieved');

        // Mark employee inactive
        $db = \Config\Database::connect();
        $db->table('users')->where('id', (int)$r['employee_id'])->update(['status' => 'inactive']);
        $db->table('user_info')->where('user_id', (int)$r['employee_id'])
            ->update(['last_working_day' => $r['final_lwd']]);

        return $this->json(['status' => 'success', 'message' => 'F&F paid. Employee relieved and deactivated.']);
    }

    // =========================================================================
    // API
    // =========================================================================

    /** GET /api/resignation/:id */
    public function apiDetail(int $id)
    {
        $user = $this->authUser();
        if (!$user) return $this->json(['status' => 'error'], 401);

        $r = $this->resignationModel->getWithDetails($id);
        if (!$r) return $this->json(['status' => 'error', 'message' => 'Not found'], 404);

        $fnf = $this->fnfModel->getByResignation($id);
        return $this->json([
            'status' => 'success',
            'data'   => [
                'resignation' => $r,
                'audit'       => $this->auditModel->getByResignation($id),
                'handover'    => $this->handoverModel->getByResignation($id),
                'clearance'   => $this->clearanceModel->getByResignation($id),
                'fnf'         => $fnf,
                'fnf_items'   => $fnf ? $this->fnfItemModel->getByFnf((int)$fnf['id']) : [],
            ],
        ]);
    }
}
