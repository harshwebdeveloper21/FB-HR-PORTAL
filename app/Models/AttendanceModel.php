<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'date', 'check_in_time', 'check_out_time', 'meal_break', 'work_hours', 'overtime',
        'lunch_start_time', 'lunch_end_time', 'lunch_duration', 'lunch_duration_seconds', 'lunch_is_overdue', 'lunch_overdue_minutes',
        'created_at', 'updated_at', 'status', 'checkin_method', 'is_late', 'late_minutes',
        // Check-in location (old column names – kept for legacy rows)
        'ip_address', 'device_info', 'latitude', 'longitude', 'location_address',
        // Check-out location (old column names – kept for legacy rows)
        'checkout_ip_address', 'checkout_device_info', 'checkout_latitude', 'checkout_longitude', 'checkout_location_address',
        // Check-in location (new column names matching DB schema)
        'check_in_ip_address', 'check_in_latitude', 'check_in_longitude', 'check_in_location_name',
        // Check-out location (new column names matching DB schema)
        'check_out_ip_address', 'check_out_latitude', 'check_out_longitude', 'check_out_location_name',
        // Branch
        'branch_id',
    ];
    protected $useTimestamps = true;

    public function getAttendanceReport($departmentId = null, $employeeId = null, $startDate = null, $endDate = null, $year = null, $month = null)
    {
        $builder = $this->db->table('attendance');
        $builder->select('attendance.id, attendance.user_id, user_info.firstname, user_info.lastname, user_info.employee_id, user_info.profile_image, department.department_name, attendance.date, attendance.status, attendance.check_in_time, attendance.check_out_time, attendance.work_hours, attendance.overtime, attendance.is_late')
               ->join('user_info', 'user_info.user_id = attendance.user_id', 'left')
               ->join('department', 'department.id = user_info.department_id', 'left')
               ->where("(LOWER(user_info.status) NOT IN ('inactive', 'resigned') OR user_info.status IS NULL)")
               ->where("(user_info.last_working_day IS NULL OR user_info.last_working_day >= CURDATE())");

        if (!empty($employeeId) && $employeeId !== 'null' && $employeeId !== 'undefined' && $employeeId !== '') {
            $builder->where('attendance.user_id', (int)$employeeId);
        }

        if (!empty($departmentId) && $departmentId !== 'null' && $departmentId !== 'undefined' && $departmentId !== '') {
            $builder->where('user_info.department_id', (int)$departmentId);
        }

        if (!empty($startDate)) {
            $builder->where('attendance.date >=', $startDate);
        }

        if (!empty($endDate)) {
            $builder->where('attendance.date <=', $endDate);
        }

        if (!empty($year)) {
            $builder->where('YEAR(attendance.date)', $year);
        }

        if (!empty($month)) {
            $builder->where('MONTH(attendance.date)', $month);
        }

        return $builder->orderBy('attendance.date', 'DESC')->get()->getResultArray();
    }
        public function getHoursByStatus()
        {
            $builder = $this->db->table($this->table);
            $builder->select('status, SUM(work_hours) as total_hours');
            $builder->groupBy('status');
            $query = $builder->get();
            return $query->getResultArray();
        }



        
}
