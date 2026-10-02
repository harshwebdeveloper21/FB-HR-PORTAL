<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Services\AuthService;

class WeeklyOffController extends ResourceController
{
    use ResponseTrait;

    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService(service('request'));
    }

    /**
     * GET api/weekly-off/employees
     * Returns all employees with their current weekly_off day
     */
    public function getEmployees()
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $db = \Config\Database::connect();

        $employees = $db->table('users u')
            ->select('u.id, u.username, u.branch_id, b.name as branch_name,
                      ui.firstname, ui.lastname, ui.profile_image, ui.employee_id,
                      COALESCE(ui.weekly_off, "none") as weekly_off')
            ->join('user_info ui', 'ui.user_id = u.id', 'left')
            ->join('branches b', 'b.id = u.branch_id', 'left')
            ->whereIn('u.role', ['employee', 'department_manager', 'branch_admin', 'hr'])
            ->where('u.is_deleted', 0)
            ->orderBy('ui.firstname', 'ASC')
            ->get()->getResultArray();

        return $this->respond([
            'status' => 'success',
            'data'   => $employees,
        ]);
    }

    /**
     * POST api/weekly-off/assign
     * Body: { employee_ids: [1,2,3], weekly_off: "monday" }
     */
    public function assign()
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $json = $this->request->getJSON(true);
        $employeeIds = $json['employee_ids'] ?? [];
        $weeklyOff   = $json['weekly_off']   ?? '';

        $validDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'none'];
        if (!in_array(strtolower($weeklyOff), $validDays)) {
            return $this->respond(['status' => 'error', 'message' => 'Invalid day selected.'], 422);
        }

        if (empty($employeeIds)) {
            return $this->respond(['status' => 'error', 'message' => 'No employees selected.'], 422);
        }

        $db = \Config\Database::connect();
        $updated = 0;

        foreach ($employeeIds as $empId) {
            $empId = (int)$empId;
            // Check if user_info row exists
            $existing = $db->table('user_info')->where('user_id', $empId)->get()->getRowArray();
            if ($existing) {
                $db->table('user_info')
                    ->where('user_id', $empId)
                    ->update(['weekly_off' => $weeklyOff === 'none' ? null : strtolower($weeklyOff)]);
                $updated++;
            }
        }

        return $this->respond([
            'status'  => 'success',
            'message' => "Weekly off updated for {$updated} employee(s).",
            'updated' => $updated,
        ]);
    }
}
