<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveModel extends Model
{
    public const AUTO_ABSENCE_REASON = 'Auto leave for full-day absence';

    protected $table = 'leaves';
    protected $primaryKey = 'id';
    protected $allowedFields = [
       'firstname', 'user_id', 'start_date', 'end_date', 'no_of_day', 'reason', 'leave_id', 'created_by', 'status',
       'manager_approval_status', 'manager_id', 'manager_remarks', 'approved_by', 'leave_duration', 'half_day_type'
    ];
    protected $useTimestamps = true;

    public function getEmployeeReport($employee_id = null, $start_date = null, $end_date = null, $year = null, $month = null, $leave_type = null, $status = null)
    {
        $builder = $this->select('users.username, leave_type.leave_type, leaves.start_date, leaves.end_date, leaves.status')
            ->join('users', 'users.id = leaves.user_id', 'left')
            ->join('user_info ui', 'ui.user_id = users.id', 'left')
            ->join('leave_type', 'leave_type.id = leaves.leave_id', 'left')
            ->where("(LOWER(ui.status) NOT IN ('inactive', 'resigned') OR ui.status IS NULL)")
            ->where("(ui.last_working_day IS NULL OR ui.last_working_day >= CURDATE())")
            ->whereIn('users.role', ['employee', 'hr'])
            ->where('leaves.reason !=', self::AUTO_ABSENCE_REASON);

        if ($employee_id !== null && $employee_id !== '') {
            $builder->where('leaves.user_id', $employee_id);
        }
        if ($start_date !== null && $start_date !== '') {
            $builder->where('leaves.start_date >=', $start_date);
        }
        if ($end_date !== null && $end_date !== '') {
            $builder->where('leaves.end_date <=', $end_date);
        }
        if ($year !== null && $year !== '') {
            $builder->where('YEAR(leaves.start_date)', $year);
        }
        if ($month !== null && $month !== '') {
            $builder->where('MONTH(leaves.start_date)', $month);
        }
        if ($leave_type !== null && $leave_type !== '') {
            $builder->where('leaves.leave_id', $leave_type);
        }
        if ($status !== null && $status !== '') {
            $builder->where('leaves.status', $status);
        }

        return $builder->orderBy('leaves.start_date', 'DESC')->get()->getResultArray();
    }

    public function getLeavesCount($userId, $month)
    {
        $startDate = $month . '-01'; // e.g., "2025-06-01"
        $endDate = date('Y-m-t', strtotime($startDate)); // e.g., "2025-06-30"

        $leaves = $this->where('user_id', $userId)
                    ->where('status', 'Approved')
                    ->where('start_date <=', $endDate) // Leave starts before/on month end
                    ->where('end_date >=', $startDate)  // Leave ends after/on month start
                    ->findAll();

        $totalDays = 0;

        foreach ($leaves as $leave) {
            // Get the overlapping days in the selected month
            $leaveStart = max(strtotime($leave['start_date']), strtotime($startDate));
            $leaveEnd = min(strtotime($leave['end_date']), strtotime($endDate));

            $days = ($leaveEnd - $leaveStart) / (60 * 60 * 24) + 1; // +1 to include both start & end
            $totalDays += $days;
        }

        return $totalDays;
    }

    
}
