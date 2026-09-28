<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\DepartmentModel;
use App\Models\NotificationModel;
use App\Services\PushNotificationService;
use CodeIgniter\Database\BaseConnection;

/**
 * HierarchyService
 * Centralizes multi-level organizational hierarchy logic:
 *   Level 1: Admin (Super Admin - All Branches)
 *   Level 2: Global HR (Strictly ONE HR for ALL Branches)
 *   Level 3: Branch Admin (Oversees a specific branch)
 *   Level 4: Department Manager (Oversees a department in a branch)
 *   Level 5: Employee (Department staff)
 */
class HierarchyService
{
    private UserModel $userModel;
    private DepartmentModel $departmentModel;
    private NotificationModel $notificationModel;
    private PushNotificationService $pushNotificationService;
    private BaseConnection $db;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->departmentModel = new DepartmentModel();
        $this->notificationModel = new NotificationModel();
        $this->pushNotificationService = new PushNotificationService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Get user details with department and branch.
     */
    public function getUserDetails(int $userId): ?array
    {
        return $this->db->table('users u')
            ->select('u.id, u.username, u.email, u.role, u.branch_id, u.department_id, 
                      ui.firstname, ui.lastname, ui.department_id as ui_department_id,
                      b.name as branch_name, d.department_name')
            ->join('user_info ui', 'ui.user_id = u.id', 'left')
            ->join('branches b', 'b.id = u.branch_id', 'left')
            ->join('department d', 'd.id = COALESCE(u.department_id, ui.department_id)', 'left')
            ->where('u.id', $userId)
            ->where('u.is_deleted', 0)
            ->get()
            ->getRowArray();
    }

    /**
     * Find Department Manager for a given department.
     */
    public function getDepartmentManager(int $departmentId, ?int $branchId = null): ?array
    {
        if ($departmentId <= 0) {
            return null;
        }

        // 1. Check department.manager_id first
        $dept = $this->departmentModel->find($departmentId);
        if ($dept && !empty($dept['manager_id'])) {
            $manager = $this->userModel->where('id', $dept['manager_id'])
                ->where('is_deleted', 0)
                ->first();
            if ($manager) {
                return $manager;
            }
        }

        // 2. Query users with role 'department_manager' assigned to this department
        $builder = $this->db->table('users u')
            ->select('u.*')
            ->join('user_info ui', 'ui.user_id = u.id', 'left')
            ->where('u.role', 'department_manager')
            ->where('u.is_deleted', 0)
            ->groupStart()
                ->where('u.department_id', $departmentId)
                ->orWhere('ui.department_id', $departmentId)
            ->groupEnd();

        if ($branchId) {
            $builder->where('u.branch_id', $branchId);
        }

        return $builder->get()->getRowArray() ?: null;
    }

    /**
     * Find Branch Admin for a given branch.
     */
    public function getBranchAdmin(int $branchId): ?array
    {
        if ($branchId <= 0) {
            return null;
        }

        return $this->db->table('users')
            ->where('branch_id', $branchId)
            ->where('role', 'branch_admin')
            ->where('is_deleted', 0)
            ->get()
            ->getRowArray() ?: null;
    }

    /**
     * Get the single Global HR user.
     */
    public function getGlobalHr(): ?array
    {
        return $this->db->table('users')
            ->where('role', 'hr')
            ->where('is_deleted', 0)
            ->get()
            ->getRowArray() ?: null;
    }

    /**
     * Get all Super Admin user(s).
     */
    public function getSuperAdmins(): array
    {
        return $this->db->table('users')
            ->where('role', 'admin')
            ->where('is_deleted', 0)
            ->get()
            ->getResultArray();
    }

    /**
     * Ensure only ONE HR user exists in the system across all branches.
     */
    public function canCreateHr(?int $excludeUserId = null): bool
    {
        $builder = $this->db->table('users')
            ->where('role', 'hr')
            ->where('is_deleted', 0);

        if ($excludeUserId) {
            $builder->where('id !=', $excludeUserId);
        }

        return $builder->countAllResults() === 0;
    }

    /**
     * Core Check-In notification dispatcher:
     * - Employee checks in -> Department Manager notified
     * - Department Manager checks in -> Branch Admin notified
     * - Branch Admin checks in -> Global HR and Super Admin notified
     */
    public function dispatchCheckInNotification(int $userId, string $time, ?string $date = null): void
    {
        try {
            $user = $this->getUserDetails($userId);
            if (!$user) {
                return;
            }

            $date = $date ?: date('Y-m-d');
            $fullName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: $user['username'];
            $deptName = $user['department_name'] ?? 'Department';
            $branchName = $user['branch_name'] ?? 'Branch';
            $role = $user['role'];
            $deptId = (int)($user['department_id'] ?: $user['ui_department_id'] ?: 0);
            $branchId = (int)($user['branch_id'] ?? 0);

            if ($role === 'employee') {
                // Rule: If any employee checkin -> Department Manager get notifications
                $manager = $this->getDepartmentManager($deptId, $branchId);
                if ($manager && $manager['id'] != $userId) {
                    $this->sendDualNotification(
                        $userId,
                        (int)$manager['id'],
                        'Employee Check-In',
                        "{$fullName} ({$deptName}) has checked in at {$time}.",
                        [
                            'type'     => 'checkin',
                            'user_id'  => $userId,
                            'username' => $fullName,
                            'time'     => $time,
                            'date'     => $date,
                            'url'      => base_url('/attendence')
                        ]
                    );
                }
            } elseif ($role === 'department_manager') {
                // Rule: When Department Manager checkin -> Branch Admin will get notifications
                $branchAdmin = $this->getBranchAdmin($branchId);
                if ($branchAdmin && $branchAdmin['id'] != $userId) {
                    $this->sendDualNotification(
                        $userId,
                        (int)$branchAdmin['id'],
                        'Department Manager Check-In',
                        "Manager {$fullName} ({$deptName}, {$branchName}) has checked in at {$time}.",
                        [
                            'type'     => 'checkin',
                            'user_id'  => $userId,
                            'username' => $fullName,
                            'time'     => $time,
                            'date'     => $date,
                            'url'      => base_url('/attendence')
                        ]
                    );
                }
            } elseif ($role === 'branch_admin') {
                // Rule: When Branch Admin checkin -> Global HR and Super Admin get notifications
                $hr = $this->getGlobalHr();
                if ($hr && $hr['id'] != $userId) {
                    $this->sendDualNotification(
                        $userId,
                        (int)$hr['id'],
                        'Branch Admin Check-In',
                        "Branch Admin {$fullName} ({$branchName}) has checked in at {$time}.",
                        [
                            'type'     => 'checkin',
                            'user_id'  => $userId,
                            'username' => $fullName,
                            'time'     => $time,
                            'date'     => $date,
                            'url'      => base_url('/attendence')
                        ]
                    );
                }

                $admins = $this->getSuperAdmins();
                foreach ($admins as $adm) {
                    if ($adm['id'] != $userId && (!$hr || $hr['id'] != $adm['id'])) {
                        $this->sendDualNotification(
                            $userId,
                            (int)$adm['id'],
                            'Branch Admin Check-In',
                            "Branch Admin {$fullName} ({$branchName}) has checked in at {$time}.",
                            [
                                'type'     => 'checkin',
                                'user_id'  => $userId,
                                'username' => $fullName,
                                'time'     => $time,
                                'date'     => $date,
                                'url'      => base_url('/attendence')
                            ]
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'HierarchyService dispatchCheckInNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Core Leave Application notification dispatcher:
     * - Employee applies -> Department Manager notified
     * - Department Manager applies -> Branch Admin notified
     * - Branch Admin applies -> Global HR & Super Admin notified
     */
    public function dispatchLeaveNotification(int $leaveId, int $userId, array $leaveData): void
    {
        try {
            $user = $this->getUserDetails($userId);
            if (!$user) {
                return;
            }

            $fullName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: $user['username'];
            $role = $user['role'];
            $deptId = (int)($user['department_id'] ?: $user['ui_department_id'] ?: 0);
            $branchId = (int)($user['branch_id'] ?? 0);
            $leaveStart = $leaveData['start_date'] ?? date('Y-m-d');
            $leaveEnd = $leaveData['end_date'] ?? date('Y-m-d');
            $leaveTypeName = $leaveData['leave_type_name'] ?? 'Leave';
            $noOfDays = $leaveData['no_of_day'] ?? 1;

            $message = "{$fullName} has requested {$leaveTypeName} from {$leaveStart} to {$leaveEnd} ({$noOfDays} day(s)).";

            if ($role === 'employee') {
                $manager = $this->getDepartmentManager($deptId, $branchId);
                if ($manager && $manager['id'] != $userId) {
                    $this->sendDualNotification(
                        $userId,
                        (int)$manager['id'],
                        'New Leave Application',
                        $message,
                        [
                            'type'       => 'leave_request',
                            'leave_id'   => $leaveId,
                            'user_id'    => $userId,
                            'username'   => $fullName,
                            'leave_type' => $leaveTypeName,
                            'url'        => base_url('/leaveview')
                        ]
                    );
                } else {
                    // Fallback to Branch Admin if department has no manager
                    $branchAdmin = $this->getBranchAdmin($branchId);
                    if ($branchAdmin) {
                        $this->sendDualNotification(
                            $userId,
                            (int)$branchAdmin['id'],
                            'New Leave Application',
                            $message,
                            [
                                'type'       => 'leave_request',
                                'leave_id'   => $leaveId,
                                'user_id'    => $userId,
                                'username'   => $fullName,
                                'leave_type' => $leaveTypeName,
                                'url'        => base_url('/leaveview')
                            ]
                        );
                    }
                }
            } elseif ($role === 'department_manager') {
                $branchAdmin = $this->getBranchAdmin($branchId);
                if ($branchAdmin && $branchAdmin['id'] != $userId) {
                    $this->sendDualNotification(
                        $userId,
                        (int)$branchAdmin['id'],
                        'Manager Leave Application',
                        "Department Manager " . $message,
                        [
                            'type'       => 'leave_request',
                            'leave_id'   => $leaveId,
                            'user_id'    => $userId,
                            'username'   => $fullName,
                            'leave_type' => $leaveTypeName,
                            'url'        => base_url('/leaveview')
                        ]
                    );
                }
            } elseif ($role === 'branch_admin') {
                $hr = $this->getGlobalHr();
                if ($hr && $hr['id'] != $userId) {
                    $this->sendDualNotification(
                        $userId,
                        (int)$hr['id'],
                        'Branch Admin Leave Application',
                        "Branch Admin " . $message,
                        [
                            'type'       => 'leave_request',
                            'leave_id'   => $leaveId,
                            'user_id'    => $userId,
                            'username'   => $fullName,
                            'leave_type' => $leaveTypeName,
                            'url'        => base_url('/leaveview')
                        ]
                    );
                }

                $admins = $this->getSuperAdmins();
                foreach ($admins as $adm) {
                    if ($adm['id'] != $userId && (!$hr || $hr['id'] != $adm['id'])) {
                        $this->sendDualNotification(
                            $userId,
                            (int)$adm['id'],
                            'Branch Admin Leave Application',
                            "Branch Admin " . $message,
                            [
                                'type'       => 'leave_request',
                                'leave_id'   => $leaveId,
                                'user_id'    => $userId,
                                'username'   => $fullName,
                                'leave_type' => $leaveTypeName,
                                'url'        => base_url('/leaveview')
                            ]
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'HierarchyService dispatchLeaveNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Send both In-App Bell Notification and Web Push Notification to a recipient.
     */
    public function sendDualNotification(int $senderId, int $recipientId, string $title, string $message, array $data = []): void
    {
        // 1. In-App Notification Record
        $this->notificationModel->insert([
            'sender_id'    => $senderId,
            'recipient_id' => $recipientId,
            'data'         => json_encode(array_merge($data, [
                'title'   => $title,
                'message' => $message,
            ])),
            'is_read'      => 0,
        ]);

        // 2. Web Push Notification to recipient
        try {
            $this->pushNotificationService->notifyUser($recipientId, $title, $message, $data);
        } catch (\Throwable $e) {
            log_message('error', 'HierarchyService push notification failed: ' . $e->getMessage());
        }
    }
}
