<?php

namespace App\Controllers\Api;

use App\Models\LeaveModel;
use CodeIgniter\RESTful\ResourceController;
use App\Services\AuthService;
use App\Services\HierarchyService;
use App\Models\LeaveTypeModel;
use App\Models\UserModel;
use App\Models\UserInfoModel;
use App\Models\EmployeeLeaveModel;
use App\Models\NotificationSettingsModel;
use App\Services\PushNotificationService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LeaveController extends ResourceController
{
    private $leaveModel;
    protected $authService;
    protected $userModel;
    private $pushNotificationService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->leaveModel = new LeaveModel();
        $this->pushNotificationService = new PushNotificationService();
        $this->authService = new AuthService(service('request'));
    }

    public function getAll()
    {
        $authUser = $this->authService->user(); // Decode JWT to get user details
        if (!$authUser) {
            return $this->failUnauthorized('Unauthorized access');
        }

        $userModel = new UserInfoModel();
        $leaveModel = new LeaveModel();
        $usersModel = new UserModel();

        $requestedUserId = $this->request->getGet('user_id'); // Get user_id from query parameter

        $filterBranchId = $this->request->getGet('branch_id');
        if ($filterBranchId === null || $filterBranchId === '') {
            $filterBranchId = $this->authService->getBranchId();
        } else {
            $filterBranchId = (int)$filterBranchId;
        }

        if ($authUser->role === 'admin' || $authUser->role === 'hr') {
            // Super Admin or Global HR: Fetch leave records across company (filtered by active branch if selected)
            $leaveQuery = $leaveModel->select('leaves.*, user_info.firstname, leave_type.leave_type, users.username AS created_by_username')
                ->join('user_info', 'leaves.user_id = user_info.user_id', 'left')
                ->join('users', 'leaves.created_by = users.id', 'left')
                ->join('leave_type', 'leaves.leave_id = leave_type.id', 'left')
                ->join('users emp_u', 'leaves.user_id = emp_u.id', 'inner')
                ->where('emp_u.is_deleted', 0)
                ->where('emp_u.role !=', 'candidate');

            if (!empty($filterBranchId)) {
                $leaveQuery->where('emp_u.branch_id', (int)$filterBranchId);
            }
            if ($requestedUserId) {
                $leaveQuery->where('leaves.user_id', $requestedUserId);
            }

            $leaveRecords = $leaveQuery->findAll();

            $userQuery = $userModel->select('user_info.*, users.username, users.role')
                ->join('users', 'user_info.user_id = users.id')
                ->where('users.is_deleted', 0)
                ->where('users.role !=', 'candidate');
            if (!empty($filterBranchId)) {
                $userQuery->where('users.branch_id', (int)$filterBranchId);
            }
            if ($requestedUserId) {
                $userQuery->where('users.id', $requestedUserId);
            }
            $users = $userQuery->findAll();

        } elseif ($authUser->role === 'branch_admin') {
            // Branch Admin: Fetch leaves of employees in their branch
            $branchId = (int)$this->authService->getBranchId();
            $leaveQuery = $leaveModel->select('leaves.*, user_info.firstname, leave_type.leave_type, users.username AS created_by_username')
                ->join('user_info', 'leaves.user_id = user_info.user_id', 'left')
                ->join('users', 'leaves.created_by = users.id', 'left')
                ->join('leave_type', 'leaves.leave_id = leave_type.id', 'left')
                ->join('users emp_u', 'leaves.user_id = emp_u.id', 'inner')
                ->where('emp_u.is_deleted', 0)
                ->where('emp_u.branch_id', $branchId)
                ->groupStart()
                    ->where('leaves.user_id', $authUser->sub)
                    ->orWhereIn('emp_u.role', ['employee', 'department_manager'])
                ->groupEnd();

            if ($requestedUserId) {
                $leaveQuery->where('leaves.user_id', $requestedUserId);
            }
            $leaveRecords = $leaveQuery->findAll();

            $userQuery = $userModel->select('user_info.*, users.username, users.role')
                ->join('users', 'user_info.user_id = users.id')
                ->where('users.is_deleted', 0)
                ->where('users.branch_id', $branchId)
                ->groupStart()
                    ->where('users.id', $authUser->sub)
                    ->orWhereIn('users.role', ['employee', 'department_manager'])
                ->groupEnd();
            if ($requestedUserId) {
                $userQuery->where('users.id', $requestedUserId);
            }
            $users = $userQuery->findAll();

        } elseif ($authUser->role === 'department_manager') {
            // Department Manager: Fetch leaves in their department and branch (plus their own)
            $dmUser = $usersModel->find($authUser->sub);
            $dmBranchId = (int)($dmUser['branch_id'] ?? 0);
            $dmDeptId = (int)($dmUser['department_id'] ?? 0);

            $leaveQuery = $leaveModel->select('leaves.*, user_info.firstname, leave_type.leave_type, users.username AS created_by_username')
                ->join('user_info', 'leaves.user_id = user_info.user_id', 'left')
                ->join('users', 'leaves.created_by = users.id', 'left')
                ->join('leave_type', 'leaves.leave_id = leave_type.id', 'left')
                ->join('users emp_u', 'leaves.user_id = emp_u.id', 'inner')
                ->where('emp_u.is_deleted', 0)
                ->groupStart()
                    ->where('leaves.user_id', $authUser->sub)
                    ->orGroupStart()
                        ->where('emp_u.branch_id', $dmBranchId)
                        ->where('emp_u.department_id', $dmDeptId)
                        ->where('emp_u.role', 'employee')
                    ->groupEnd()
                ->groupEnd();

            if ($requestedUserId) {
                $leaveQuery->where('leaves.user_id', $requestedUserId);
            }
            $leaveRecords = $leaveQuery->findAll();

            $userQuery = $userModel->select('user_info.*, users.username, users.role')
                ->join('users', 'user_info.user_id = users.id')
                ->where('users.is_deleted', 0)
                ->groupStart()
                    ->where('users.id', $authUser->sub)
                    ->orGroupStart()
                        ->where('users.branch_id', $dmBranchId)
                        ->where('users.department_id', $dmDeptId)
                        ->where('users.role', 'employee')
                    ->groupEnd()
                ->groupEnd();
            if ($requestedUserId) {
                $userQuery->where('users.id', $requestedUserId);
            }
            $users = $userQuery->findAll();

        } else {
            // Employee: Fetch only their own leave records
            $users = $userModel->where('user_id', $authUser->sub)->findAll();
            $leaveRecords = $leaveModel->select('leaves.*, user_info.firstname, leave_type.leave_type, users.username AS created_by_username')
                ->join('user_info', 'leaves.user_id = user_info.user_id', 'left')
                ->join('users', 'leaves.created_by = users.id', 'left')
                ->join('leave_type', 'leaves.leave_id = leave_type.id', 'left')
                ->where('leaves.user_id', $authUser->sub)
                ->findAll();
        }

        // Map leave records to corresponding users
        $userLeaveData = [];
        foreach ($leaveRecords as $leave) {
            $userLeaveData[$leave['user_id']][] = $leave;
        }

        $leaveData = [];
        // Attach leave data to each user
        foreach ($users as &$user) {
            $user['leaves'] = $userLeaveData[$user['user_id']] ?? [];
            $leaveData[] = $user;
        }

        return $this->respond([
            'status' => 'success',
            'data' => $leaveData,
            'role' => $authUser->role,
            'current_user_id' => $authUser->sub,
        ]);
    }

    public function view()
    {
        return view('leave/leaveview');
    }

    public function display()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $userId = $user->sub;
        $role = $user->role;

        $leaveTypeModel = new LeaveTypeModel();
        $userModel = new UserModel();

        $leaveTypes = $leaveTypeModel->findAll();

        if (in_array($role, ['admin', 'hr'])) {
            // Admin and HR see all staff (filtered by active branch if selected)
            $usersQuery = $userModel->whereIn('role', ['admin', 'hr', 'branch_admin', 'department_manager', 'employee'])->where('is_deleted', 0);
            $filterBranchId = $this->authService->getBranchId();
            if (!empty($filterBranchId)) {
                $usersQuery->where('branch_id', (int)$filterBranchId);
            }
            $users = $usersQuery->findAll();
        } elseif ($role === 'branch_admin') {
            // Apply Leave is always for the logged-in manager. Team leave
            // review remains available through the separate review screen.
            $users = [$userModel->find($userId)];
        } elseif ($role === 'department_manager') {
            // A Department Manager is also an employee when applying leave.
            $users = [$userModel->find($userId)];
        } else {
            // Employee sees only themselves
            $users = [$userModel->find($userId)];
        }

        return view('leave/addleave', [
            'leaveTypes' => $leaveTypes,
            'users' => $users,
            'role' => $role,
            'currentUserId' => $userId,
        ]);
    }

    public function create()
    {
        // Check user authentication
        $user = $this->authService->check();

        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $userId = $user->sub;
        $role = $user->role;

        $data = $this->request->getJSON(true);
        $userModel = new UserModel();
        // Authorization check
        if ($role === 'admin' || $role === 'hr') {
            // Admin and Global HR can apply leave for any employee
        } elseif ($role === 'branch_admin') {
            $targetUser = $userModel->find($data['user_id']);
            $branchId = (int)$this->authService->getBranchId();
            if (!$targetUser || (int)($targetUser['branch_id'] ?? 0) !== $branchId) {
                return $this->failForbidden('Forbidden: Branch Admin can only add leave for staff in their branch.');
            }
        } elseif ($role === 'department_manager') {
            $dmUser = $userModel->find($userId);
            $targetUser = $userModel->find($data['user_id']);
            $dmBranchId = (int)($dmUser['branch_id'] ?? 0);
            $dmDeptId = (int)($dmUser['department_id'] ?? 0);
            if (!$targetUser || ($targetUser['id'] != $userId && ((int)($targetUser['branch_id'] ?? 0) !== $dmBranchId || (int)($targetUser['department_id'] ?? 0) !== $dmDeptId))) {
                return $this->failForbidden('Forbidden: Department Manager can only add leave for staff in their department.');
            }
        } else {
            if ($data['user_id'] != $userId) {
                return $this->failForbidden('Forbidden: Employees can only add leave for themselves.');
            }
        }

        // Calculate no_of_day
        if (isset($data['leave_duration']) && $data['leave_duration'] === 'half_day') {
            $data['no_of_day'] = '0.5';
            $data['end_date'] = $data['start_date'];
        } elseif (isset($data['start_date']) && isset($data['end_date'])) {
            $startDate = strtotime($data['start_date']);
            $endDate = strtotime($data['end_date']);
            if ($startDate && $endDate) {
                $data['no_of_day'] = (string) (($endDate - $startDate) / 86400 + 1);
            }
        }

        if (empty($data['leave_duration']) || $data['leave_duration'] !== 'half_day') {
            $data['leave_duration'] = 'full_day';
            $data['half_day_type'] = null;
        }

        // --- Sandwich Leave Logic ---
        $companyRulesModel = new \App\Models\CompanyRulesModel();
        $rules = $companyRulesModel->first();
        $isSandwichLeaveEnabled = $rules ? (bool) $rules['sandwich_leave'] : false;

        if ($isSandwichLeaveEnabled && isset($data['start_date']) && isset($data['end_date']) && (!isset($data['leave_duration']) || $data['leave_duration'] !== 'half_day')) {
            $startDayOfWeek = date('N', strtotime($data['start_date']));
            $endDayOfWeek = date('N', strtotime($data['end_date']));

            // If leave ends on Friday, automatically include Saturday and Sunday
            if ($endDayOfWeek == 5) {
                $data['end_date'] = date('Y-m-d', strtotime($data['end_date'] . ' + 2 days'));
            }
            // If leave starts on Monday, automatically include the preceding Saturday and Sunday
            if ($startDayOfWeek == 1) {
                $data['start_date'] = date('Y-m-d', strtotime($data['start_date'] . ' - 2 days'));
            }

            // Recalculate duration
            $start = strtotime($data['start_date']);
            $end = strtotime($data['end_date']);
            if ($start && $end) {
                $calc = ($end - $start) / 86400 + 1;
                $data['no_of_day'] = (string) max(1, $calc);
            }
        }

        // Default status if not set
        if (empty($data['status'])) {
            $data['status'] = 'Pending';
        }

        // Validation
        $validationRules = [
            'user_id' => 'required',
            'leave_id' => 'required',
            'start_date' => 'required|valid_date[Y-m-d]',
            'end_date' => 'required|valid_date[Y-m-d]|check_end_date[start_date]',
            'reason' => 'required',
            'no_of_day' => 'required|validate_no_of_day[start_date,end_date]',
        ];

        $validationMessages = [
            'user_id' => ['required' => 'User is required.'],
            'leave_id' => ['required' => 'Leave is required.'],
            'start_date' => [
                'required' => 'Start date is required.',
                'valid_date' => 'Start date must be in YYYY-MM-DD format.'
            ],
            'end_date' => [
                'required' => 'End date is required.',
                'valid_date' => 'End date must be in YYYY-MM-DD format.',
                'check_end_date' => 'End date must be after or equal to start date.'
            ],
            'reason' => ['required' => 'Reason is required.'],
            'no_of_day' => [
                'required' => 'Number of days is required.',
                'validate_no_of_day' => 'Number of days must be correct.'
            ],
        ];

        if (!$this->validateData($data, $validationRules, $validationMessages)) {
            log_message('error', 'Leave creation validation failed: ' . json_encode($this->validator->getErrors()));
            return $this->respond([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ], 400);
        }

        $data['created_by'] = $userId;

        if ($this->leaveModel->insert($data)) {
            $leaveId = $this->leaveModel->getInsertID();

            if (strtolower((string) ($data['status'] ?? '')) === 'approved') {
                try {
                    $data['id'] = $leaveId;
                    $this->syncLeaveBalanceForStatusChange(
                        array_merge($data, ['status' => 'pending']),
                        'approved'
                    );
                } catch (\Throwable $e) {
                    $this->leaveModel->delete($leaveId);
                    return $this->respond([
                        'status' => 'error',
                        'message' => 'Failed to sync leave balance after approval',
                    ], 500);
                }
            }

            // Send notifications (internal and push via HierarchyService)
            try {
                $hierarchyService = new HierarchyService();
                $hierarchyService->dispatchLeaveNotification($leaveId, (int)$data['user_id'], $data);
            } catch (\Exception $e) {
                log_message('error', 'Failed to trigger leave hierarchy notification: ' . $e->getMessage());
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Leave record added successfully',
                'id' => $leaveId
            ], 201);
        }

        return $this->respond([
            'status' => 'error',
            'message' => 'Failed to add leave record'
        ], 500);
    }


    public function getLeaveRequests()
    {
        if (!$this->authService->check()) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $user = $this->authService->user();

        $leaveModel = new LeaveModel();
        $leaveRequests = $leaveModel->where('user_id', $user->sub)->findAll();

        return $this->respond(['status' => 'success', 'data' => $leaveRequests]);
    }

    public function approveLeave($id)
    {
        if (!$this->authService->check()) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $leaveModel = new LeaveModel();
        $leave = $leaveModel->find($id);

        if ($leave) {
            $db = \Config\Database::connect();
            $db->transBegin();

            try {
                $this->syncLeaveBalanceForStatusChange($leave, 'approved');
                $leave['status'] = 'approved';
                $leaveModel->save($leave);

                if ($leaveModel->errors() || $db->transStatus() === false) {
                    throw new \RuntimeException('Failed to approve leave.');
                }
            } catch (\Throwable $e) {
                $db->transRollback();
                return $this->respond(['status' => 'error', 'message' => 'Failed to approve leave'], 500);
            }

            $db->transCommit();

            return $this->respond(['status' => 'success', 'message' => 'Leave approved successfully']);
        }

        return $this->fail('Leave request not found');
    }

    public function getLeaves()
    {
        $leaveModel = new \App\Models\LeaveModel();
        $userInfoModel = new \App\Models\UserInfoModel();

        $employeeId = $this->request->getGet('employee_id'); // Get employee_id from request

        $query = $leaveModel->join('user_info', 'leaves.user_id = user_info.user_id')
            ->select('leaves.*, user_info.firstname, user_info.lastname, user_info.profile_image')
            ->where('leaves.reason !=', 'Auto leave for full-day absence');

        if (!empty($employeeId)) {
            $query->where('leaves.user_id', $employeeId); // Filter by employee_id
        }

        $leaves = $query->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $leaves
        ]);
    }



    public function updateStatus($leaveId)
    {
        if (!$leaveId) {
            return $this->respond(['status' => 'error', 'message' => 'Missing leave ID'], 400);
        }

        // Check if user is authenticated
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $userRole = $user->role; // 'admin', 'hr', or 'employee'
        $userId = $user->sub; // Logged-in user ID

        // Employees cannot update leave status
        if ($userRole === 'employee') {
            return $this->failForbidden('Employees cannot update leave status');
        }

        // Validate status input
        $status = $this->request->getJSON()->status;
        if (!in_array($status, ['pending', 'approved', 'rejected'])) {
            return $this->respond(['status' => 'error', 'message' => 'Invalid status'], 400);
        }

        // Fetch leave request
        $leaveModel = new LeaveModel();
        $leave = $leaveModel->find($leaveId);

        if (!$leave) {
            return $this->respond(['status' => 'error', 'message' => 'Leave request not found'], 404);
        }

        // Hierarchy access check
        $userModel = new UserModel();
        $targetUser = $userModel->find($leave['user_id']);
        if (!$targetUser) {
            return $this->respond(['status' => 'error', 'message' => 'Employee not found'], 404);
        }

        // Managers are employees too. Their own leave must be approved by a
        // higher-level user, never by themselves.
        if ((int) $leave['user_id'] === (int) $userId) {
            return $this->failForbidden('You cannot update the status of your own leave request.');
        }

        if ($userRole === 'department_manager') {
            $dmUser = $userModel->find($userId);
            $dmBranchId = (int)($dmUser['branch_id'] ?? 0);
            $dmDeptId = (int)($dmUser['department_id'] ?? 0);
            if ((int)($targetUser['branch_id'] ?? 0) !== $dmBranchId || (int)($targetUser['department_id'] ?? 0) !== $dmDeptId) {
                return $this->failForbidden('Forbidden: You can only update leave requests in your department.');
            }
        } elseif ($userRole === 'branch_admin') {
            $branchId = (int)$this->authService->getBranchId();
            if ((int)($targetUser['branch_id'] ?? 0) !== $branchId) {
                return $this->failForbidden('Forbidden: You can only update leave requests in your branch.');
            }
        } elseif ($userRole === 'hr') {
            if ($targetUser['role'] === 'admin') {
                return $this->failForbidden('Forbidden: HR cannot update Super Admin leave requests.');
            }
        }

        // ─────────────────────────────────────────────────────────────
        // 🔔  SEND NOTIFICATION TO THE EMPLOYEE WHOSE LEAVE WAS UPDATED
        // ─────────────────────────────────────────────────────────────

        $notificationModel = new \App\Models\NotificationModel();
        $userModel2 = new UserModel();

        $employeeUser = $userModel2->find($leave['user_id']);
        $updatedBy = $userModel2->find($userId);
        $updaterName = $updatedBy['username'] ?? 'HR/Admin';

        // Human-readable status message
        $statusLabel = match (strtolower($status)) {
            'approved' => 'approved ✅',
            'rejected' => 'rejected ❌',
            default => 'moved to pending 🕐',
        };

        $notifMessage = 'Your leave request from ' . $leave['start_date'] . ' to ' . $leave['end_date']
            . ' has been ' . $statusLabel . ' by ' . $updaterName . '.';

        // 1. ── In-App (database) notification ──
        $notificationModel->insert([
            'sender_id' => $userId,
            'recipient_id' => $leave['user_id'],
            'data' => json_encode([
                'username' => $employeeUser['username'] ?? 'Employee',
                'type' => 'leave_status',
                'message' => $notifMessage,
                'status' => $status,
                'leave_id' => $leaveId,
                'start_date' => $leave['start_date'],
                'end_date' => $leave['end_date'],
                'url' => base_url('/leaveview'),
            ]),
            'is_read' => 0,
        ]);

        // 2. ── Web Push notification to the employee ──
        try {
            $pushTitle = 'Leave Request ' . ucfirst($status);
            $this->pushNotificationService->notifyUser(
                $leave['user_id'],
                $pushTitle,
                $notifMessage,
                [
                    'type' => 'leave_status',
                    'leave_id' => $leaveId,
                    'status' => $status,
                    'start_date' => $leave['start_date'],
                    'end_date' => $leave['end_date'],
                    'no_of_days' => $leave['no_of_day'] ?? 1,
                    'updated_by' => $updaterName,
                    'url' => base_url('/leaveview'),
                ]
            );
        } catch (\Exception $e) {
            log_message('error', '🔔 [updateStatus] Push notification failed: ' . $e->getMessage());
            // Do NOT abort the response — notification failure should not block the status update
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $this->syncLeaveBalanceForStatusChange($leave, $status);

            $leaveUpdateData = [
                'status' => $status,
                'created_by' => $userId,
                'approved_by' => $userId,
            ];
            if ($userRole === 'department_manager') {
                $leaveUpdateData['manager_approval_status'] = $status;
                $leaveUpdateData['manager_id'] = $userId;
            }
            $leaveModel->update($leaveId, $leaveUpdateData);

            if ($leaveModel->errors() || $db->transStatus() === false) {
                throw new \RuntimeException('Failed to update leave status.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->respond(['status' => 'error', 'message' => 'Failed to update leave status'], 500);
        }

        $db->transCommit();

        return $this->respond(['status' => 'success', 'message' => 'Leave status updated successfully']);
    }

    private function syncLeaveBalanceForStatusChange(array $leave, string $newStatus): void
    {
        $oldStatus = strtolower((string) ($leave['status'] ?? ''));
        $newStatus = strtolower($newStatus);

        $wasApproved = $oldStatus === 'approved';
        $isApproved = $newStatus === 'approved';

        if ($wasApproved === $isApproved) {
            return;
        }

        $balanceColumn = $this->resolveLeaveBalanceColumn((int) ($leave['leave_id'] ?? 0));
        if ($balanceColumn === null) {
            return;
        }

        $employeeLeaveModel = new EmployeeLeaveModel();
        $leaveBalance = $employeeLeaveModel
            ->where('employee_id', $leave['user_id'])
            ->first();

        $leaveDays = (float) ($leave['no_of_day'] ?? 0);
        $currentBalance = (float) ($leaveBalance[$balanceColumn] ?? 0);
        $updatedBalance = $isApproved
            ? max($currentBalance - $leaveDays, 0)
            : $currentBalance + $leaveDays;

        $leaveData = [
            'employee_id' => $leave['user_id'],
            $balanceColumn => $updatedBalance,
        ];

        if (!empty($leaveBalance['id'])) {
            $leaveData['id'] = $leaveBalance['id'];
        }

        $employeeLeaveModel->save($leaveData);

        if ($employeeLeaveModel->errors()) {
            throw new \RuntimeException('Failed to sync employee leave balance.');
        }
    }

    private function resolveLeaveBalanceColumn(int $leaveTypeId): ?string
    {
        if ($leaveTypeId === 1) {
            return 'paid_leave';
        }

        if ($leaveTypeId === 2) {
            return 'casual_leave';
        }

        $leaveTypeModel = new LeaveTypeModel();
        $leaveType = $leaveTypeModel->find($leaveTypeId);
        $leaveTypeName = strtolower(trim((string) ($leaveType['leave_type'] ?? '')));

        if (str_contains($leaveTypeName, 'paid')) {
            return 'paid_leave';
        }

        if (
            str_contains($leaveTypeName, 'casual') ||
            str_contains($leaveTypeName, 'sick')
        ) {
            return 'casual_leave';
        }

        return null;
    }

    /**
     * API: Employee cancels their own pending leave request.
     */
    public function cancelLeave($leaveId)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized();
        }

        $leave = $this->leaveModel->find($leaveId);
        if (!$leave) {
            return $this->failNotFound('Leave request not found');
        }

        // Must be their own leave
        if ($leave['user_id'] != $user->sub) {
            return $this->failForbidden('You can only cancel your own leave requests.');
        }

        // Must be pending
        if (strtolower($leave['status']) !== 'pending') {
            return $this->failForbidden('Only pending leave requests can be cancelled. Current status: ' . $leave['status']);
        }

        if ($this->leaveModel->update($leaveId, ['status' => 'cancelled'])) {
            return $this->respond(['status' => 'success', 'message' => 'Leave request cancelled successfully']);
        }

        return $this->fail('Failed to cancel leave.');
    }

    /**
     * API: Employee updates their own pending leave request dates.
     */
    public function updateDates($leaveId)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized();
        }

        $leave = $this->leaveModel->find($leaveId);
        if (!$leave) {
            return $this->failNotFound('Leave request not found');
        }

        // Must be their own leave
        if ($leave['user_id'] != $user->sub) {
            return $this->failForbidden('You can only update your own leave requests.');
        }

        // Must be pending
        if (strtolower($leave['status']) !== 'pending') {
            return $this->failForbidden('Strictly only pending leave requests can be updated.');
        }

        $data = $this->request->getJSON(true);
        $startDate = $data['start_date'] ?? null;
        $endDate = $data['end_date'] ?? null;

        if (!$startDate || !$endDate) {
            return $this->fail('Start date and end date are required');
        }

        // Recalculate duration
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        if ($start > $end) {
            return $this->fail('Start date cannot be after end date.');
        }

        // --- Sandwich Leave Logic ---
        $companyRulesModel = new \App\Models\CompanyRulesModel();
        $rules = $companyRulesModel->first();
        $isSandwichLeaveEnabled = $rules ? (bool) $rules['sandwich_leave'] : false;

        if ($isSandwichLeaveEnabled) {
            $startDayOfWeek = date('N', $start);
            $endDayOfWeek = date('N', $end);

            // If leave ends on Friday, automatically include Saturday and Sunday
            if ($endDayOfWeek == 5) {
                $endDate = date('Y-m-d', strtotime($endDate . ' + 2 days'));
                $end = strtotime($endDate);
            }
            // If leave starts on Monday, automatically include the preceding Saturday and Sunday
            if ($startDayOfWeek == 1) {
                $startDate = date('Y-m-d', strtotime($startDate . ' - 2 days'));
                $start = strtotime($startDate);
            }
        }

        $noOfDays = (string) max(0.5, (($end - $start) / 86400 + 1));
        
        if (isset($leave['leave_duration']) && $leave['leave_duration'] === 'half_day') {
            $noOfDays = '0.5';
            $updateData['end_date'] = $startDate;
        }

        $updateData = [
            'start_date' => $startDate,
            'end_date' => $updateData['end_date'] ?? $endDate,
            'no_of_day' => $noOfDays,
        ];

        if ($this->leaveModel->update($leaveId, $updateData)) {
            return $this->respond([
                'status' => 'success',
                'message' => 'Leave dates updated successfully',
                'no_of_day' => $noOfDays
            ]);
        }

        return $this->fail('Failed to update leave.');
    }


    public function add()
    {
        $validation = \Config\Services::validation();

        // Define validation rules
        $validation->setRules([
            'leave_type' => [
                'label' => 'Leave Type',
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Leave Type is required.',
                    'min_length' => 'Leave Type must be at least 3 characters long.',
                    'max_length' => 'Leave Type cannot exceed 255 characters.',
                ]
            ],
            'number_of_leaves' => [
                'label' => 'Number of Leaves',
                'rules' => 'required|integer|greater_than_equal_to[0]',
                'errors' => [
                    'required' => 'Number of Leaves is required.',
                    'integer' => 'Number of Leaves must be a valid number.',
                    'greater_than_equal_to' => 'Number of Leaves must be 0 or greater.',
                ]
            ]
        ]);

        // Run validation
        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $validation->getErrors()
            ]);
        }

        // Validation passed, proceed to insert
        $model = new LeaveTypeModel();

        // Prepare the data
        $data = [
            'leave_type' => $this->request->getVar('leave_type'),
            'number_of_leaves' => $this->request->getVar('number_of_leaves'),
            'created_by' => session()->get('user_id') // Assuming user_id is stored in session
        ];

        // Insert the data
        if ($model->insert($data)) {
            // Fetch the newly inserted record
            $leaveType = $model->find($model->insertID());

            return $this->response->setJSON([
                'success' => true,
                'leave_type' => $leaveType
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to add leave type.'
            ]);
        }
    }

    /**
     * Helper to send internal and push notifications for new leave requests
     */
    private function sendLeaveNotification($data, $leaveId)
    {
        $userModel = new \App\Models\UserModel();
        $notificationModel = new \App\Models\NotificationModel();

        $sender = $userModel->find($data['user_id']);
        $senderName = $sender['username'] ?? 'Employee';

        log_message('info', '🔔 [sendLeaveNotification] Starting notification process for leave #' . $leaveId . ' (User: ' . $senderName . ')');

        // 1. Internal Notifications (Database)
        $recipients = $userModel->whereIn('role', ['admin', 'hr'])->where('is_deleted', 0)->findAll();

        foreach ($recipients as $recipient) {
            $notificationModel->insert([
                'sender_id' => $data['user_id'],
                'recipient_id' => $recipient['id'],
                'data' => json_encode([
                    'username' => $senderName,
                    'type' => 'leave',
                    'leave_id' => $leaveId,
                    'user_id' => $data['user_id']
                ]),
                'is_read' => 0
            ]);
        }

        // 2. Push Notification to Admin/HR
        try {
            $notificationSettingsModel = new \App\Models\NotificationSettingsModel();

            // Safe check for settings
            $shouldNotify = true;
            try {
                $shouldNotify = $notificationSettingsModel->isLeaveNotificationsEnabled();
            } catch (\Exception $e) {
                log_message('warning', '🔔 [sendLeaveNotification] Could not check settings, defaulting to TRUE: ' . $e->getMessage());
            }

            if ($shouldNotify) {
                $leaveStart = $data['start_date'] ?? '';
                $leaveEnd = $data['end_date'] ?? '';
                $noOfDays = $data['no_of_day'] ?? 1;

                // Fetch leave type name
                $leaveTypeName = 'Leave';
                try {
                    $leaveTypeModel = new \App\Models\LeaveTypeModel();
                    $leaveType = $leaveTypeModel->find($data['leave_id']);
                    if ($leaveType) {
                        $leaveTypeName = $leaveType['leave_type'];
                    }
                } catch (\Exception $e) {
                    log_message('warning', '🔔 [sendLeaveNotification] Could not fetch leave type: ' . $e->getMessage());
                }

                log_message('info', '🔔 [sendLeaveNotification] Sending push notification to admins/HR');

                $this->pushNotificationService->notifyAdmins(
                    'Employee Leave Request (New Application)',
                    $senderName . ' has requested ' . $leaveTypeName . ' from ' . $leaveStart . ' to ' . $leaveEnd . ' (' . $noOfDays . ' day' . ($noOfDays > 1 ? 's' : '') . '). Reason: ' . ($data['reason'] ?? 'Not specified'),
                    [
                        'type' => 'leave_request',
                        'user_id' => $data['user_id'],
                        'username' => $senderName,
                        'leave_id' => $leaveId,
                        'leave_type' => $leaveTypeName,
                        'url' => base_url('/leaveview')
                    ]
                );
            }
        } catch (\Exception $e) {
            log_message('error', '🔔 [sendLeaveNotification] Push notification failed: ' . $e->getMessage());
        }
    }


    public function manage_index()
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['admin', 'hr', 'branch_admin', 'department_manager'])) {
            return redirect()->to('/login');
        }

        $filterBranchId = $this->authService->getBranchId();

        // Fetch active employees for the dropdown (branch-scoped / department-scoped)
        $employeeQuery = $this->userModel->where('is_deleted', 0);
        if ($user->role === 'admin' || $user->role === 'hr') {
            $employeeQuery->whereIn('role', ['employee', 'hr', 'branch_admin', 'department_manager']);
            if (!empty($filterBranchId)) {
                $employeeQuery->where('branch_id', (int)$filterBranchId);
            }
        } elseif ($user->role === 'branch_admin') {
            $employeeQuery->whereIn('role', ['employee', 'department_manager']);
            $employeeQuery->where('branch_id', (int)$filterBranchId);
        } elseif ($user->role === 'department_manager') {
            $dmUser = $this->userModel->find($user->sub);
            $employeeQuery->where('role', 'employee');
            $employeeQuery->where('branch_id', (int)($dmUser['branch_id'] ?? 0));
            $employeeQuery->where('department_id', (int)($dmUser['department_id'] ?? 0));
        }
        $employees = $employeeQuery->findAll();

        return view('leave/manage_leaves', [
            'employees' => $employees,
            'role' => $user->role
        ]);
    }

    public function delete($id = null)
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['admin', 'hr', 'branch_admin'])) {
            return $this->failUnauthorized();
        }

        if ($this->leaveModel->delete($id)) {
            return $this->respondDeleted(['status' => 'success', 'message' => 'Leave record deleted successfully']);
        }

        return $this->fail('Failed to delete leave record');
    }

    /**
     * Export Leaves to styled Excel (.xlsx)
     */
    public function exportExcel()
    {
        $authUser = $this->authService->user();
        if (!$authUser) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Unauthorized']);
        }

        $month        = $this->request->getGet('month');
        $year         = $this->request->getGet('year');
        $status       = $this->request->getGet('status');
        $departmentId = $this->request->getGet('department_id');
        $userId       = $this->request->getGet('user_id') ?: $this->request->getGet('employee_id');
        $leaveType    = $this->request->getGet('leave_type');
        $view         = $this->request->getGet('view');
        $search       = $this->request->getGet('search');

        $builder = $this->leaveModel->builder();
        $builder->select('leaves.*, ui.firstname, ui.lastname, ui.employee_id as emp_code, dep.department_name, lt.leave_type as leave_type_name, creator.username as created_by_username')
            ->join('user_info ui', 'ui.user_id = leaves.user_id', 'left')
            ->join('department dep', 'dep.id = ui.department_id', 'left')
            ->join('leave_type lt', 'lt.id = leaves.leave_id', 'left')
            ->join('users creator', 'creator.id = leaves.created_by', 'left');

        // Role scoping
        if (in_array($authUser->role, ['admin', 'hr'])) {
            if (!empty($userId)) {
                $builder->where('leaves.user_id', $userId);
            } else {
                $filterBranchId = $this->request->getGet('branch_id');
                if ($filterBranchId === null || $filterBranchId === '') {
                    $filterBranchId = $this->authService->getBranchId();
                } else {
                    $filterBranchId = (int)$filterBranchId;
                }
                if (!empty($filterBranchId)) {
                    $builder->join('users emp_b_u', 'emp_b_u.id = leaves.user_id', 'inner')
                            ->where('emp_b_u.branch_id', (int)$filterBranchId);
                }
            }
        } elseif ($authUser->role === 'branch_admin') {
            $branchId = (int)$this->authService->getBranchId();
            $builder->join('users emp_b_u', 'emp_b_u.id = leaves.user_id', 'inner')
                    ->where('emp_b_u.branch_id', $branchId);
            if (!empty($userId)) {
                $builder->where('leaves.user_id', $userId);
            }
        } elseif ($authUser->role === 'department_manager') {
            $dmUser = (new UserModel())->find($authUser->sub);
            $builder->join('users emp_b_u', 'emp_b_u.id = leaves.user_id', 'inner')
                    ->where('emp_b_u.branch_id', (int)($dmUser['branch_id'] ?? 0))
                    ->where('emp_b_u.department_id', (int)($dmUser['department_id'] ?? 0));
            if (!empty($userId)) {
                $builder->where('leaves.user_id', $userId);
            }
        } else {
            $builder->where('leaves.user_id', $authUser->sub);
        }

        if (!empty($departmentId)) {
            $builder->where('ui.department_id', $departmentId);
        }

        if (!empty($leaveType)) {
            if (is_numeric($leaveType)) {
                $builder->where('leaves.leave_id', $leaveType);
            } else {
                $builder->where('lt.leave_type', $leaveType);
            }
        }

        if (!empty($status)) {
            $builder->where('LOWER(leaves.status)', strtolower($status));
        } elseif ($view === 'cancelled') {
            $builder->where('LOWER(leaves.status)', 'cancelled');
        }

        if (!empty($year)) {
            $builder->where('YEAR(leaves.start_date)', $year);
        }

        if (!empty($month)) {
            $builder->where('MONTH(leaves.start_date)', (int)$month);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('ui.firstname', $search)
                ->orLike('ui.lastname', $search)
                ->orLike('ui.employee_id', $search)
                ->orLike('lt.leave_type', $search)
                ->orLike('leaves.reason', $search)
                ->groupEnd();
        }

        // Exclude automatic system-created absence leaves
        $builder->where('leaves.reason !=', LeaveModel::AUTO_ABSENCE_REASON);

        $leaves = $builder->orderBy('leaves.start_date', 'DESC')->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Leaves Report');

        $headers = [
            'A1' => 'S.No',
            'B1' => 'Emp ID',
            'C1' => 'Employee Name',
            'D1' => 'Department',
            'E1' => 'Leave Type',
            'F1' => 'Start Date',
            'G1' => 'End Date',
            'H1' => 'Duration (Days)',
            'I1' => 'Half Day Type',
            'J1' => 'Status',
            'K1' => 'Reason',
            'L1' => 'Applied On'
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E66136']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ];
        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $rowNum = 2;
        $sno = 1;
        foreach ($leaves as $item) {
            $fullName = trim(($item['firstname'] ?? '') . ' ' . ($item['lastname'] ?? ''));
            if (empty($fullName)) {
                $fullName = $item['created_by_username'] ?? 'Employee #' . ($item['user_id'] ?? '');
            }

            $rawCode = (string)($item['emp_code'] ?? '');
            $empCode = !empty($rawCode) && $rawCode !== '0'
                ? (str_starts_with($rawCode, 'EMP-') || str_starts_with($rawCode, 'EMP#') ? $rawCode : 'EMP-' . str_pad($rawCode, 3, '0', STR_PAD_LEFT))
                : ('EMP-' . ($item['user_id'] ?? ''));

            $halfDay = !empty($item['half_day_type']) ? ucfirst($item['half_day_type']) : '-';
            $statusVal = ucfirst(strtolower($item['status'] ?? 'Pending'));

            $sheet->setCellValue('A' . $rowNum, $sno++);
            $sheet->setCellValueExplicit('B' . $rowNum, (string)$empCode, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $rowNum, $fullName);
            $sheet->setCellValue('D' . $rowNum, $item['department_name'] ?? '-');
            $sheet->setCellValue('E' . $rowNum, $item['leave_type_name'] ?? ($item['leave_type'] ?? '-'));
            $sheet->setCellValue('F' . $rowNum, !empty($item['start_date']) ? date('Y-m-d', strtotime($item['start_date'])) : '-');
            $sheet->setCellValue('G' . $rowNum, !empty($item['end_date']) ? date('Y-m-d', strtotime($item['end_date'])) : '-');
            $sheet->setCellValue('H' . $rowNum, (float)($item['no_of_day'] ?? 1));
            $sheet->setCellValue('I' . $rowNum, $halfDay);
            $sheet->setCellValue('J' . $rowNum, $statusVal);
            $sheet->setCellValue('K' . $rowNum, $item['reason'] ?? '-');
            $sheet->setCellValue('L' . $rowNum, !empty($item['created_at']) ? date('Y-m-d H:i', strtotime($item['created_at'])) : '-');

            $rowNum++;
        }

        $lastRow = $rowNum - 1;
        if ($lastRow >= 2) {
            $dataStyle = [
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
            ];
            $sheet->getStyle("A2:L{$lastRow}")->applyFromArray($dataStyle);
            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B2:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I2:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Leaves_Export_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $excelData = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($excelData);
    }
}
