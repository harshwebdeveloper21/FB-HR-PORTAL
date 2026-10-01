<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\AuthService;
use App\Models\BranchRulesModel;
use App\Models\BranchModel;
use App\Models\AuditLogModel;

/**
 * BranchRulesController â€” manages per-branch company rules.
 *
 * - Admin can edit rules for ANY branch.
 * - HR can view and edit rules for their OWN branch only.
 * - Branch rules control attendance calculation (late, half-day, overtime).
 */
class BranchRulesController extends ResourceController
{
    protected $authService;
    protected $branchRulesModel;
    protected $branchModel;
    protected $auditLog;

    public function __construct()
    {
        $this->authService      = new AuthService(service('request'));
        $this->branchRulesModel = new BranchRulesModel();
        $this->branchModel      = new BranchModel();
        $this->auditLog         = new AuditLogModel();
    }

    // -- Helper ----------------------------------------------------------------

    private function getAuthedUser(): ?object
    {
        return $this->authService->check() ?: null;
    }

    private function resolveTargetBranchId(?int $requestedBranchId): int|false
    {
        $user = $this->getAuthedUser();
        if (!$user) {
            return false;
        }

        if ($user->role === 'admin') {
            // Admin can target any branch
            // Fall back to global session branch filter if no explicit branch requested
            if (!$requestedBranchId) {
                $requestedBranchId = $this->authService->getBranchId();
            }
            if (!$requestedBranchId) {
                return false; // admin must specify branch_id
            }
            return $requestedBranchId;
        }

        // HR: always use their own branch_id, ignore any request param
        $branchId = $this->authService->getBranchId();
        return $branchId ?: false;
    }

    // -- Page Views ------------------------------------------------------------

    public function editPage($branchId = null)
    {
        $user = $this->getAuthedUser();
        if (!$user) {
            return redirect()->to('/login');
        }

        if ($user->role === 'hr') {
            $branchId = $this->authService->getBranchId();
        }

        if (!$branchId && $user->role === 'admin') {
            $firstBranch = $this->branchModel->where('status', 'active')->orderBy('id', 'ASC')->first();
            if ($firstBranch) {
                $branchId = $firstBranch['id'];
            } else {
                return redirect()->to('/dashboard')->with('error', 'No branches found.');
            }
        }

        $branch = $this->branchModel->find($branchId);
        if (!$branch) {
            return redirect()->to('/dashboard')->with('error', 'Branch not found.');
        }

        $rules = $this->branchRulesModel->getRulesForBranch((int)$branchId);
        $allBranches = $user->role === 'admin' ? $this->branchModel->getActiveBranches() : [];

        return view('branch_rules/edit', [
            'branch'     => $branch,
            'rules'      => $rules,
            'allBranches' => $allBranches,
            'role'       => $user->role,
        ]);
    }

    // -- API Endpoints ---------------------------------------------------------

    /**
     * GET api/branch-rules/get?branch_id=X â€” get rules for a branch
     */
    public function get()
    {
        $user = $this->getAuthedUser();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $requestedBranchId = (int)($this->request->getGet('branch_id') ?? 0);
        $branchId = $this->resolveTargetBranchId($requestedBranchId);

        if ($branchId === false) {
            return $this->respond(['status' => 'error', 'message' => 'branch_id is required.'], 422);
        }

        $rules  = $this->branchRulesModel->getRulesForBranch($branchId);
        $branch = $this->branchModel->find($branchId);

        // Branch-specific location settings
        if ($rules && $branch) {
            $rules['office_latitude']  = !empty($rules['office_latitude']) ? $rules['office_latitude'] : ($branch['latitude'] ?? null);
            $rules['office_longitude'] = !empty($rules['office_longitude']) ? $rules['office_longitude'] : ($branch['longitude'] ?? null);
            $rules['office_radius']    = !empty($rules['office_radius']) ? $rules['office_radius'] : ($branch['radius'] ?? 100);
            if (!empty($rules['office_latitude']) && !empty($rules['office_longitude'])) {
                $rules['enable_geofencing'] = 1;
            }
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $rules,
            'branch' => $branch,
        ]);
    }

    /**
     * POST api/branch-rules/store — save branch rules
     */
    public function store()
    {
        $user = $this->getAuthedUser();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        $requestedBranchId = (int)($data['branch_id'] ?? 0);
        $branchId = $this->resolveTargetBranchId($requestedBranchId);

        if ($branchId === false) {
            return $this->respond(['status' => 'error', 'message' => 'branch_id is required.'], 422);
        }

        // HR can only edit their own branch
        if ($user->role === 'hr' && $branchId !== $this->authService->getBranchId()) {
            return $this->respond(['status' => 'error', 'message' => 'Access denied.'], 403);
        }

        $existing = $this->branchRulesModel->where('branch_id', $branchId)->first();

        $insertData = [
            'branch_id'                       => $branchId,
            'payroll_type'                    => $data['payroll_type'] ?? 'monthly',
            'working_hours_per_day'           => $data['working_hours_per_day'] ?? 8,
            'half_day_hours'                  => $data['half_day_hours'] ?? 4,
            'enable_overtime'                 => ($data['enable_overtime'] === true || $data['enable_overtime'] == 1) ? 1 : 0,
            'overtime_multiplier'             => $data['overtime_multiplier'] ?? 1.5,
            'min_overtime_count_in_minutes'   => $data['min_overtime_count_in_minutes'] ?? 30,
            'start_time'                      => $data['start_time'] ?? null,
            'end_time'                        => $data['end_time'] ?? null,
            'lunch_break'                     => $data['lunch_break'] ?? null,
            'grace_period'                    => $data['grace_period'] ?? 0,
            'grace_minutes'                   => $data['grace_minutes'] ?? $data['grace_period'] ?? 10,
            'sunday_off'                      => ($data['sunday_off'] === true || $data['sunday_off'] == 1) ? 1 : 0,
            'sunday_pay_type'                 => $data['sunday_pay_type'] ?? 'unpaid',
            'saturday_off_enabled'            => ($data['saturday_off_enabled'] === true || $data['saturday_off_enabled'] == 1) ? 1 : 0,
            'saturday_off_type'               => $data['saturday_off_type'] ?? 'all',
            'saturday_off_pattern'            => $data['saturday_off_pattern'] ?? null,
            'saturday_pay_type'               => $data['saturday_pay_type'] ?? 'regular',
            'saturday_half_day_pattern'       => $data['saturday_half_day_pattern'] ?? null,
            'saturday_working_hours'          => (float)($data['saturday_working_hours'] ?? 4),
            'saturday_full_day_override'      => ($data['saturday_full_day_override'] === true || $data['saturday_full_day_override'] == 1) ? 1 : 0,
            'enable_tax'                      => ($data['enable_tax'] === true || $data['enable_tax'] == 1) ? 1 : 0,
            'tax_type'                        => $data['tax_type'] ?? 'fixed',
            'tax'                             => $data['tax'] ?? 0,
            'salary_above_tax'                => $data['salary_above_tax'] ?? 0,
            'include_holidays_in_working_days'=> ($data['include_holidays_in_working_days'] === true || $data['include_holidays_in_working_days'] == 1) ? 1 : 0,
            'sandwich_leave'                  => ($data['sandwich_leave'] === true || $data['sandwich_leave'] == 1) ? 1 : 0,
            'enable_geofencing'               => ($data['enable_geofencing'] === true || $data['enable_geofencing'] == 1) ? 1 : 0,
            'office_latitude'                 => isset($data['office_latitude']) && $data['office_latitude'] !== '' ? (string)$data['office_latitude'] : (isset($data['latitude']) && $data['latitude'] !== '' ? (string)$data['latitude'] : null),
            'office_longitude'                => isset($data['office_longitude']) && $data['office_longitude'] !== '' ? (string)$data['office_longitude'] : (isset($data['longitude']) && $data['longitude'] !== '' ? (string)$data['longitude'] : null),
            'office_radius'                   => isset($data['office_radius']) && $data['office_radius'] !== '' ? (int)$data['office_radius'] : (isset($data['radius']) && $data['radius'] !== '' ? (int)$data['radius'] : 100),
        ];

        try {
            $actorId = $user->sub ?? $user->id ?? null;

            if ($existing) {
                $this->branchRulesModel->update($existing['id'], $insertData);
                $this->auditLog->log($actorId, 'branch_rules.update', 'BranchRules', $existing['id'], $existing, $insertData);
                $message = 'Branch rules updated successfully.';
            } else {
                $this->branchRulesModel->insert($insertData);
                $this->auditLog->log($actorId, 'branch_rules.create', 'BranchRules', null, null, $insertData);
                $message = 'Branch rules created successfully.';
            }

            // Bidirectional sync: update branch module
            if (!empty($branchId)) {
                $branchUpdate = [
                    'latitude'  => $insertData['office_latitude'],
                    'longitude' => $insertData['office_longitude'],
                    'radius'    => $insertData['office_radius'] ?? 100,
                ];
                $this->branchModel->update($branchId, $branchUpdate);
            }

            return $this->respond(['status' => 'success', 'message' => $message]);

        } catch (\Exception $e) {
            log_message('error', 'BranchRulesController::store error: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Failed to save branch rules.'], 500);
        }
    }
}
