<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyRulesModel extends Model
{
    protected $table            = 'company_rules';
    protected $primaryKey       = 'id';

    protected $allowedFields    = [
        'enable_payroll',
        'payroll_type',
        'working_hours_per_day',
        'include_holidays_in_working_days',
        'half_day_hours',
        'sunday_off',
        'sunday_pay_type',
        'saturday_off_enabled',
        'saturday_off_type',
        'saturday_off_pattern',
        'saturday_half_day_enabled',
        'saturday_half_day_pattern',
        'saturday_pay_type',
        'saturday_working_hours',
        'saturday_full_day_override',
        'yearly_holidays',
        'enable_tax',
        'tax_type',
        'tax',
        'salary_above_tax',
        'lunch_break',
        'start_time',
        'half_time',
        'end_time',
        'grace_period',
        'enable_geofencing',
        'enable_overtime',
        'overtime_multiplier',
        'overtime_rate_type',
        'min_overtime_count_in_minutes',
        'sandwich_leave',
        'branch_id',
        'office_latitude',
        'office_longitude',
        'office_radius',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $returnType       = 'array';
}
