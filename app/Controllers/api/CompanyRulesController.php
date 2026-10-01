<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BranchRulesModel;

class CompanyRulesController extends BaseController
{
    protected $rulesModel;

    public function __construct()
    {
        $this->rulesModel = new BranchRulesModel();
    }

    public function company_rules()
    {
        return view('company_rules/show');
    }

    public function display_rules()
    {
        return view('company_rules/company_rule_view');
    }

    public function create_rules()
    {
        return view('company_rules/create');
    }

    public function rules()
    {
        $authService = new \App\Services\AuthService($this->request);
        $branchId = $authService->getBranchId();

        $builder = $this->rulesModel->select('branch_rules.*, branches.name as branch_name')
                                    ->join('branches', 'branches.id = branch_rules.branch_id', 'left');

        if (!empty($branchId)) {
            $builder = $builder->where('branch_rules.branch_id', $branchId);
        }
        
        $rules = $builder->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $rules
        ]);
    }

    public function rules_get()
    {
        $branchId = $this->request->getGet('branch_id');

        if (!empty($branchId)) {
            $rules = $this->rulesModel->where('branch_id', $branchId)->first();
        } else {
            $rules = $this->rulesModel->groupStart()->where('branch_id', null)->orWhere('branch_id', 0)->groupEnd()->first();
            if (!$rules) {
                // Fallback for legacy global row
                $rules = $this->rulesModel->first();
            }
        }
        if ($rules) {
            // Values are already in $rules from company_rules table
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $rules
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'No rules found.'
            ]);
        }
    }

    public function store()
    {
        $data = $this->request->getJSON(true);

        // Validate required fields
        if (empty($data['working_hours_per_day'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => [
                    'working_hours_per_day' => 'Working hours per day is required.'
                ]
            ])->setStatusCode(400);
        }

        // Prepare data for insertion/update
        $insertData = [
            // Payroll Configuration
            // 'enable_payroll' => ($data['enable_payroll'] === true) ? 1 : 0,
            'payroll_type' => $data['payroll_type'] ?? 'monthly',
            'working_hours_per_day' => $data['working_hours_per_day'],
            'half_day_hours' => $data['half_day_hours'] ?? null,
            'enable_overtime' => ($data['enable_overtime'] === false) ? 0 : 1,
            'overtime_multiplier' => $data['overtime_multiplier'] ?? 1.5,
            'min_overtime_count_in_minutes' => $data['min_overtime_count_in_minutes'] ?? 30,

            // Attendance
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'lunch_break' => $data['lunch_break'] ?? null,
            'grace_period' => $data['grace_period'] ?? 0,

            // Sunday Configuration
            'sunday_off' => ($data['sunday_off'] === true) ? 1 : 0,
            'sunday_pay_type' => $data['sunday_pay_type'] ?? 'unpaid',

            // Saturday Configuration
            'saturday_off_enabled' => ($data['saturday_off_enabled'] === false) ? 0 : 1,
            'saturday_off_type' => $data['saturday_off_type'] ?? 'all',
            'saturday_off_pattern' => $data['saturday_off_pattern'] ?? null,
            'saturday_pay_type' => $data['saturday_pay_type'] ?? 'regular',

            'saturday_half_day_pattern' => $data['saturday_half_day_pattern'] ?? null,

            // Saturday Working Hours Override
            'saturday_working_hours'     => isset($data['saturday_working_hours']) ? (float) $data['saturday_working_hours'] : 4,
            'saturday_full_day_override' => empty($data['saturday_full_day_override']) ? 0 : 1,

            // Leave Management
            // 'yearly_holidays' => $data['yearly_holidays'] ?? 0,
            // 'sick_leaves' => $data['sick_leaves'] ?? 0,
            // 'casual_leaves' => $data['casual_leaves'] ?? 0,
            // 'carry_forward_leaves' => isset($data['carry_forward_leaves']) ? 1 : 0,
            // 'max_carry_forward' => $data['max_carry_forward'] ?? 0,

            // Tax Configuration
            'enable_tax' => ($data['enable_tax'] === true) ? 1 : 0,
            'tax_type' => $data['tax_type'] ?? 'fixed',
            'tax' => $data['tax'] ?? 0,
            'salary_above_tax' => $data['salary_above_tax'] ?? 0,

            // working days configuration
            'include_holidays_in_working_days' => ($data['include_holidays_in_working_days'] === true) ? 1 : 0,
            'sandwich_leave' => ($data['sandwich_leave'] === true) ? 1 : 0,

            // PF Configuration
            // 'enable_pf' => isset($data['enable_pf']) ? 1 : 0,
            // 'employee_pf' => $data['employee_pf'] ?? 0,
            // 'employer_pf' => $data['employer_pf'] ?? 0,

            // ESI Configuration
            // 'enable_esi' => isset($data['enable_esi']) ? 1 : 0,
            // 'employee_esi' => $data['employee_esi'] ?? 0,
            // 'employer_esi' => $data['employer_esi'] ?? 0,

            // Shift Management
            // 'enable_shifts' => isset($data['enable_shifts']) ? 1 : 0,
            // 'night_shift_allowance' => $data['night_shift_allowance'] ?? 0,

            // Biometric & Attendance
            // 'enable_biometric' => isset($data['enable_biometric']) ? 1 : 0,
            'enable_geofencing' => ($data['enable_geofencing'] === true) ? 1 : 0,
            // 'auto_checkout' => isset($data['auto_checkout']) ? 1 : 0,

            // Branch
            'branch_id' => !empty($data['branch_id']) ? (int)$data['branch_id'] : null,
            'office_latitude'  => isset($data['office_latitude']) && $data['office_latitude'] !== '' ? (float)$data['office_latitude'] : null,
            'office_longitude' => isset($data['office_longitude']) && $data['office_longitude'] !== '' ? (float)$data['office_longitude'] : null,
            'office_radius'    => isset($data['office_radius']) && $data['office_radius'] !== '' ? (float)$data['office_radius'] : null,
        ];

        try {
            $branchId = !empty($data['branch_id']) ? (int)$data['branch_id'] : null;
            
            // Check if rule exists for this specific branch
            if (!empty($branchId)) {
                $existingRule = $this->rulesModel->where('branch_id', $branchId)->first();
            } else {
                $existingRule = $this->rulesModel->groupStart()->where('branch_id', null)->orWhere('branch_id', 0)->groupEnd()->first();
                if (!$existingRule && !empty($data['id'])) {
                     $existingRule = $this->rulesModel->find($data['id']);
                }
            }

            if ($existingRule) {
                // Update existing record
                $this->rulesModel->update($existingRule['id'], $insertData);
                $message = 'Company rules updated successfully.';
            } else {
                // Insert new record
                $this->rulesModel->insert($insertData);
                $message = 'Company rules created successfully.';
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Company Rules Store Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to save company rules: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Calculate salary based on rules
     */
    public function calculateSalary()
    {
        $data = $this->request->getJSON(true);
        $rules = $this->rulesModel->first();

        if (!$rules) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Company rules not configured.'
            ])->setStatusCode(400);
        }

        $baseSalary = $data['base_salary'] ?? 0;
        $workedHours = $data['worked_hours'] ?? 0;
        $workedDays = $data['worked_days'] ?? 0;

        $calculations = [
            'base_salary' => $baseSalary,
            'gross_salary' => $baseSalary,
            'deductions' => [],
            'allowances' => [],
            'net_salary' => $baseSalary
        ];

        // Calculate based on payroll type
        if ($rules['payroll_type'] === 'hourly') {
            $calculations['gross_salary'] = $workedHours * ($baseSalary / ($rules['working_hours_per_day'] * 26));
        } elseif ($rules['payroll_type'] === 'daily') {
            $calculations['gross_salary'] = $workedDays * ($baseSalary / 26);
        }

        // Tax calculation
        if ($rules['enable_tax'] && $calculations['gross_salary'] >= $rules['salary_above_tax']) {
            $taxAmount = 0;
            if ($rules['tax_type'] === 'fixed') {
                $taxAmount = $rules['tax'];
            } elseif ($rules['tax_type'] === 'percentage') {
                $taxAmount = ($calculations['gross_salary'] * $rules['tax']) / 100;
            }
            $calculations['deductions']['tax'] = $taxAmount;
        }

        // PF calculation
        if ($rules['enable_pf']) {
            $pfAmount = ($calculations['gross_salary'] * $rules['employee_pf']) / 100;
            $calculations['deductions']['pf'] = $pfAmount;
        }

        // ESI calculation
        if ($rules['enable_esi']) {
            $esiAmount = ($calculations['gross_salary'] * $rules['employee_esi']) / 100;
            $calculations['deductions']['esi'] = $esiAmount;
        }

        // Calculate net salary
        $totalDeductions = array_sum($calculations['deductions']);
        $calculations['net_salary'] = $calculations['gross_salary'] - $totalDeductions;

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $calculations
        ]);
    }

    /**
     * Validate attendance based on rules
     */
    public function validateAttendance()
    {
        $data = $this->request->getJSON(true);
        $rules = $this->rulesModel->first();

        if (!$rules) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Company rules not configured.'
            ])->setStatusCode(400);
        }

        $checkIn = strtotime($data['check_in_time']);
        $checkOut = strtotime($data['check_out_time']);
        $date = $data['date'];

        $response = [
            'is_late' => false,
            'is_half_day' => false,
            'is_full_day' => false,
            'overtime_hours' => 0,
            'status' => 'present'
        ];

        // Check if late
        $startTime = strtotime($rules['start_time']);
        $graceTime = $startTime + ($rules['grace_period'] * 60);
        
        if ($checkIn > $graceTime) {
            $response['is_late'] = true;
            $response['late_minutes'] = round(($checkIn - $startTime) / 60);
        }

        // Calculate worked hours
        $workedSeconds = $checkOut - $checkIn;
        if ($rules['lunch_break']) {
            $lunchBreakSeconds = strtotime($rules['lunch_break']) - strtotime('00:00:00');
            $workedSeconds -= $lunchBreakSeconds;
        }
        $workedHours = $workedSeconds / 3600;

        // Check half day or full day
        if ($workedHours >= $rules['working_hours_per_day']) {
            $response['is_full_day'] = true;
            
            // Calculate overtime
            if ($rules['enable_overtime'] && $workedHours > $rules['working_hours_per_day']) {
                $response['overtime_hours'] = $workedHours - $rules['working_hours_per_day'];
            }
        } elseif ($workedHours >= $rules['half_day_hours']) {
            $response['is_half_day'] = true;
            $response['status'] = 'half_day';
        } else {
            $response['status'] = 'insufficient_hours';
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $response
        ]);
    }
}