<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchRulesModel extends Model
{
    protected $table      = 'branch_rules';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'branch_id',
        'enable_payroll', 'payroll_type', 'working_hours_per_day',
        'include_holidays_in_working_days', 'half_day_hours',
        'sunday_off', 'sunday_pay_type',
        'saturday_off_enabled', 'saturday_off_type', 'saturday_off_pattern',
        'saturday_half_day_enabled', 'saturday_half_day_pattern',
        'saturday_pay_type', 'saturday_working_hours', 'saturday_full_day_override',
        'yearly_holidays', 'enable_tax', 'tax_type', 'tax', 'salary_above_tax',
        'lunch_break', 'start_time', 'half_time', 'end_time',
        'grace_period', 'grace_minutes',
        'enable_overtime', 'overtime_multiplier', 'overtime_rate_type',
        'min_overtime_count_in_minutes', 'sandwich_leave', 'enable_geofencing',
        'office_latitude', 'office_longitude', 'office_radius',
        'created_at', 'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $returnType    = 'array';

    /**
     * Get default rules without querying company_rules
     */
    public function getDefaultRules(): array
    {
        return [
            'payroll_type'               => 'monthly',
            'working_hours_per_day'      => 8,
            'half_day_hours'             => 4,
            'sunday_off'                 => 1,
            'sunday_pay_type'            => 'unpaid',
            'saturday_off_enabled'       => 0,
            'saturday_off_type'          => 'all',
            'saturday_working_hours'     => 4,
            'saturday_full_day_override' => 1,
            'yearly_holidays'            => 0,
            'enable_tax'                 => 0,
            'lunch_break'                => '01:00:00',
            'start_time'                 => '09:00:00',
            'half_time'                  => '13:00:00',
            'end_time'                   => '18:00:00',
            'grace_period'               => 10,
            'grace_minutes'              => 10,
            'enable_overtime'            => 0,
            'enable_geofencing'          => 0,
            'office_latitude'            => null,
            'office_longitude'           => null,
            'office_radius'              => 100,
            'sandwich_leave'             => 0,
        ];
    }

    /**
     * Get rules for a given branch. Falls back to default branch_rules if none set.
     */
    public function getRulesForBranch(int $branchId): ?array
    {
        $rules = $this->where('branch_id', $branchId)->first();

        if (!$rules) {
            $fallback = $this->orderBy('id', 'ASC')->first();
            return $fallback ?: $this->getDefaultRules();
        }

        return $rules;
    }

    /**
     * Get branch_id for a user then return that branch's rules.
     */
    public function getRulesForUser(int $userId): ?array
    {
        $user = $this->db->table('users')
            ->select('branch_id')
            ->where('id', $userId)
            ->get()->getRowArray();

        if (!$user || empty($user['branch_id'])) {
            $fallback = $this->orderBy('id', 'ASC')->first();
            return $fallback ?: $this->getDefaultRules();
        }

        return $this->getRulesForBranch((int)$user['branch_id']);
    }

    /**
     * Create a branch rules row by copying existing branch defaults.
     */
    public function createDefaultForBranch(int $branchId): void
    {
        $existing = $this->where('branch_id', $branchId)->first();
        if ($existing) {
            return; // already exists
        }

        $fallback = $this->orderBy('id', 'ASC')->first();
        $now      = date('Y-m-d H:i:s');

        if ($fallback) {
            unset($fallback['id']);
            $data = array_intersect_key($fallback, array_flip($this->allowedFields));
        } else {
            $data = $this->getDefaultRules();
        }

        $data['branch_id']   = $branchId;
        $data['created_at']  = $now;
        $data['updated_at']  = $now;
        $data['grace_minutes'] = $data['grace_minutes'] ?? $data['grace_period'] ?? 10;

        $this->insert($data);
    }
}
