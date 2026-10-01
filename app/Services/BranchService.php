<?php

namespace App\Services;

use App\Models\BranchModel;
use App\Models\BranchRulesModel;
use App\Models\UserModel;
use App\Models\AuditLogModel;
use Config\Database;

class BranchService
{
    protected $branchModel;
    protected $branchRulesModel;
    protected $userModel;
    protected $auditLog;
    protected $db;

    public function __construct()
    {
        $this->branchModel = new BranchModel();
        $this->branchRulesModel = new BranchRulesModel();
        $this->userModel = new UserModel();
        $this->auditLog = new AuditLogModel();
        $this->db = Database::connect();
    }

    public function createBranch(array $data, int $adminId): int
    {
        $this->db->transStart();

        $branchId = $this->branchModel->insert($data);

        // Fetch global default rules to copy
        $globalRules = $this->db->table('company_rules')->orderBy('id', 'DESC')->get()->getRowArray() ?? [];

        // Create default rules for this branch
        $rulesData = [
            'branch_id' => $branchId,
            'payroll_type' => $globalRules['payroll_type'] ?? 'monthly',
            'working_hours_per_day' => $globalRules['working_hours_per_day'] ?? 8,
            'half_day_hours' => $globalRules['half_day_hours'] ?? 4,
            'sunday_off' => $globalRules['sunday_off'] ?? 1,
            'saturday_off_enabled' => $globalRules['saturday_off_enabled'] ?? 0,
            'saturday_working_hours' => $globalRules['saturday_working_hours'] ?? 4,
            'saturday_full_day_override' => $globalRules['saturday_full_day_override'] ?? 1,
            'grace_period' => $globalRules['grace_period'] ?? 10,
            'grace_minutes' => $globalRules['grace_minutes'] ?? $globalRules['grace_period'] ?? 10,
            'start_time' => $globalRules['start_time'] ?? '09:00:00',
            'end_time' => $globalRules['end_time'] ?? '18:00:00',
            'lunch_break' => $globalRules['lunch_break'] ?? '01:00:00',
            'enable_overtime' => $globalRules['enable_overtime'] ?? 0,
            'overtime_multiplier' => $globalRules['overtime_multiplier'] ?? 1.5,
            'min_overtime_count_in_minutes' => $globalRules['min_overtime_count_in_minutes'] ?? 30,
            'office_latitude' => !empty($data['latitude']) ? (string)$data['latitude'] : null,
            'office_longitude' => !empty($data['longitude']) ? (string)$data['longitude'] : null,
            'office_radius' => isset($data['radius']) && $data['radius'] !== '' ? (int)$data['radius'] : 100,
            'enable_geofencing' => (!empty($data['latitude']) && !empty($data['longitude'])) ? 1 : ($globalRules['enable_geofencing'] ?? 0),
            'enable_tax' => $globalRules['enable_tax'] ?? 0,
            'tax' => $globalRules['tax'] ?? 0,
            'sandwich_leave' => $globalRules['sandwich_leave'] ?? 0
        ];
        $this->branchRulesModel->insert($rulesData);

        // Audit Log
        $this->auditLog->insert([
            'user_id' => $adminId,
            'action' => 'Created Branch',
            'model' => 'BranchModel',
            'record_id' => $branchId,
            'new_value' => json_encode($data),
            'ip_address' => service('request')->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->transComplete();

        return $branchId;
    }

    public function deleteBranch(int $id, int $adminId): bool
    {
        // Check for active staff/HR in the branch
        $activeUsers = $this->userModel->where('branch_id', $id)
                                       ->where('is_deleted', 0)
                                       ->countAllResults();
        
        if ($activeUsers > 0) {
            throw new \Exception("Cannot delete branch because it has {$activeUsers} active users assigned to it.");
        }

        $this->db->transStart();

        $branch = $this->branchModel->find($id);

        $this->branchModel->delete($id);
        
        // Clean up rules for the deleted branch so orphan rules do not linger in rules view
        $this->branchRulesModel->where('branch_id', $id)->delete();

        $this->auditLog->insert([
            'user_id' => $adminId,
            'action' => 'Deleted Branch',
            'model' => 'BranchModel',
            'record_id' => $id,
            'old_value' => json_encode($branch),
            'ip_address' => service('request')->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
