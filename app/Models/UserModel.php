<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'username', 'email', 'password', 'role', 'created_at', 'is_read',
        'is_deleted', 'chat_status', 'last_activity',
        // Branch & Department support
        'branch_id', 'department_id', 'can_transfer_staff',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    public function getEmployeeReport($filters)
    {
        $hasEmployeeFilter = !empty($filters['employee_id']);

        $builder = $this->db->table('users u')
            ->select('ui.firstname, ui.lastname, ui.joining_date, ui.status, ui.last_working_day,
                    d.department_name, des.designation_name, u.is_deleted, u.updated_at')
            ->join('user_info ui', 'ui.user_id = u.id')
            ->join('department d', 'd.id = ui.department_id', 'left')
            ->join('designation des', 'des.id = ui.designation_id', 'left')
            // ->where('u.is_deleted', 0)
            ->whereIn('u.role', ['employee', 'hr']);

        if (!empty($filters['department_id']) && !$hasEmployeeFilter) {
            $builder->where('ui.department_id', $filters['department_id']);
        }

        if ($hasEmployeeFilter) {
            $builder->where('u.id', $filters['employee_id']);
        }

        if (!empty($filters['joining_from'])) {
            $builder->where('ui.joining_date >=', $filters['joining_from']);
        }

        if (!empty($filters['joining_to'])) {
            $builder->where('ui.joining_date <=', $filters['joining_to']);
        }

        if (!empty($filters['year'])) {
            $builder->where('YEAR(ui.joining_date)', $filters['year']);
        }

        if (!empty($filters['month'])) {
            $builder->where('MONTH(ui.joining_date)', $filters['month']);
        }

        if (!empty($filters['status']) && strtolower($filters['status']) !== 'all' && strtolower($filters['status']) !== 'all status') {
            if (strtolower($filters['status']) === 'active') {
                $builder->where("(LOWER(ui.status) NOT IN ('inactive', 'resigned') OR ui.status IS NULL)");
                $builder->where("(ui.last_working_day IS NULL OR ui.last_working_day >= CURDATE())");
            } else {
                $builder->groupStart()
                        ->where("LOWER(ui.status) IN ('inactive', 'resigned')")
                        ->orWhere('(ui.last_working_day IS NOT NULL AND ui.last_working_day < CURDATE() AND (ui.status IS NULL OR LOWER(ui.status) NOT IN (\'inactive\', \'resigned\')))')
                        ->groupEnd();
            }
        }

        return $builder->orderBy('ui.joining_date', 'DESC')->get()->getResultArray();
    }

    public function getDepartmentWiseEmployeeCount()
    {
        $builder = $this->db->table('users u')
            ->select('d.id as department_id, d.department_name, COUNT(u.id) as employee_count')
            ->join('user_info ui', 'ui.user_id = u.id')
            ->join('department d', 'd.id = ui.department_id')
            ->where('u.role', 'employee')
            ->groupBy('d.id, d.department_name')
            ->orderBy('d.department_name');
        
        return $builder->get()->getResultArray();
    }
    
    public function getEmployeeSummary($filters)
    {
        $hasEmployeeFilter = !empty($filters['employee_id']);

        $builder = $this->db->table('users u')
            ->select('COUNT(u.id) as total_employees')
            ->join('user_info ui', 'ui.user_id = u.id')
            ->where("(LOWER(ui.status) NOT IN ('inactive', 'resigned') OR ui.status IS NULL)")
            ->where("(ui.last_working_day IS NULL OR ui.last_working_day >= CURDATE())")
            ->whereIn('u.role', ['employee', 'hr']);

        if (!empty($filters['department_id']) && !$hasEmployeeFilter) {
            $builder->where('ui.department_id', $filters['department_id']);
        }

        if ($hasEmployeeFilter) {
            $builder->where('u.id', $filters['employee_id']);
        }

        if (!empty($filters['joining_from'])) {
            $builder->where('ui.joining_date >=', $filters['joining_from']);
        }

        if (!empty($filters['joining_to'])) {
            $builder->where('ui.joining_date <=', $filters['joining_to']);
        }

        if (!empty($filters['year'])) {
            $builder->where('YEAR(ui.joining_date)', $filters['year']);
        }

        if (!empty($filters['month'])) {
            $builder->where('MONTH(ui.joining_date)', $filters['month']);
        }

        $totalResult = $builder->get()->getRowArray();

        // Department wise count
        $deptBuilder = $this->db->table('users u')
            ->select('d.department_name, COUNT(u.id) as total')
            ->join('user_info ui', 'ui.user_id = u.id')
            ->join('department d', 'd.id = ui.department_id')
            ->where("(LOWER(ui.status) NOT IN ('inactive', 'resigned') OR ui.status IS NULL)")
            ->where("(ui.last_working_day IS NULL OR ui.last_working_day >= CURDATE())")
            ->whereIn('u.role', ['employee', 'hr'])
            ->groupBy('d.id, d.department_name')
            ->orderBy('d.department_name');

        if (!empty($filters['department_id']) && !$hasEmployeeFilter) {
            $deptBuilder->where('ui.department_id', $filters['department_id']);
        }

        if ($hasEmployeeFilter) {
            $deptBuilder->where('u.id', $filters['employee_id']);
        }

        if (!empty($filters['joining_from'])) {
            $deptBuilder->where('ui.joining_date >=', $filters['joining_from']);
        }

        if (!empty($filters['joining_to'])) {
            $deptBuilder->where('ui.joining_date <=', $filters['joining_to']);
        }

        if (!empty($filters['year'])) {
            $deptBuilder->where('YEAR(ui.joining_date)', $filters['year']);
        }

        if (!empty($filters['month'])) {
            $deptBuilder->where('MONTH(ui.joining_date)', $filters['month']);
        }

        $deptWise = $deptBuilder->get()->getResultArray();

        $activeEmployees = 0;
        $deletedEmployees = 0;
        
        $summaryFilters = $filters;
        $summaryFilters['status'] = 'all';

        foreach ($this->getEmployeeReport($summaryFilters) as $employee) {
            $isInactive = false;
            if (isset($employee['status']) && in_array(strtolower($employee['status']), ['inactive', 'resigned'])) {
                $isInactive = true;
            } elseif (!empty($employee['last_working_day']) && $employee['last_working_day'] < date('Y-m-d')) {
                $isInactive = true;
            }

            if ($isInactive) {
                $deletedEmployees++;
            } else {
                $activeEmployees++;
            }
        }

        return [
            'total_employees' => $totalResult['total_employees'] ?? 0,
            'active_employees' => $activeEmployees,
            'deleted_employees' => $deletedEmployees,
            'department_wise' => $deptWise
        ];
    }

    public function getUsersWithStatus()
    {
        return $this->select('id, name, status')->findAll();
    }
}
