<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use App\Models\DepartmentModel;
use App\Models\AttendanceModel;
use App\Models\UserInfoModel;
use App\Models\UserModel;
use App\Models\HolidayCalendarModel;
use App\Models\LeaveModel;

class AttendanceReportController extends Controller
{
    public function fetchEmployeesByDepartment()
    {
        $departmentId = $this->request->getPost('department_id');
        $userInfoModel = new UserInfoModel();
        
        $builder = $userInfoModel->select('user_info.user_id as id, user_info.firstname, user_info.lastname, user_info.employee_id, department.department_name')
            ->join('department', 'department.id = user_info.department_id', 'left')
            ->join('users', 'users.id = user_info.user_id', 'left')
            ->where("(LOWER(user_info.status) NOT IN ('inactive', 'resigned') OR user_info.status IS NULL)")
            ->where("(user_info.last_working_day IS NULL OR user_info.last_working_day >= CURDATE())");

        $authService = new \App\Services\AuthService(service('request'));
        $branchId = $authService->getBranchId();
        if (!empty($branchId)) {
            $builder->where('users.branch_id', (int)$branchId);
        }

        if (!empty($departmentId) && $departmentId !== 'null') {
            $builder->where('user_info.department_id', (int)$departmentId);
        }

        $employees = $builder->orderBy('user_info.firstname', 'ASC')->findAll();
        
        return $this->response->setJSON([
            'status'    => 'success',
            'employees' => $employees,
            'csrfHash'  => csrf_hash()
        ]);
    }

    public function create()
    {
        $departmentModel = new DepartmentModel();
        $departments = $departmentModel->findAll();

        $authService = new \App\Services\AuthService(service('request'));
        $branchId = $authService->getBranchId();

        $userInfoModel = new UserInfoModel();
        $empBuilder = $userInfoModel->select('user_info.user_id as id, user_info.firstname, user_info.lastname, user_info.employee_id')
            ->join('users', 'users.id = user_info.user_id', 'left')
            ->where("(LOWER(user_info.status) NOT IN ('inactive', 'resigned') OR user_info.status IS NULL)")
            ->where("(user_info.last_working_day IS NULL OR user_info.last_working_day >= CURDATE())")
            ->whereIn('users.role', ['employee', 'hr']);

        if (!empty($branchId)) {
            $empBuilder->where('users.branch_id', (int)$branchId);
        }

        $employees = $empBuilder->orderBy('user_info.firstname', 'ASC')->findAll();

        return view('report/attendanceReport', [
            'departments' => $departments,
            'employees'   => $employees,
            'currentYear' => date('Y'),
            'currentMonth'=> date('n')
        ]);
    }

    private function isEmployeeOnLeave($userId, $date, $leaves)
    {
        foreach ($leaves as $leave) {
            if ((int)$leave['user_id'] === (int)$userId) {
                if ($date >= $leave['start_date'] && $date <= $leave['end_date']) {
                    return $leave;
                }
            }
        }
        return false;
    }

    public function fetchAttendanceReport()
    {
        $departmentId = $this->request->getVar('department_id');
        $employeeId   = $this->request->getVar('employee_id') ?: $this->request->getVar('user_id');
        $startDate    = $this->request->getVar('start_date');
        $endDate      = $this->request->getVar('end_date');
        $year         = $this->request->getVar('year');
        $month        = $this->request->getVar('month');

        $selectedYear  = !empty($year) ? (int)$year : (int)date('Y');
        $selectedMonth = !empty($month) ? (int)$month : (int)date('n');

        $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
        $monthStartDate   = sprintf('%04d-%02d-01', $selectedYear, $selectedMonth);
        $monthEndDate     = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $totalDaysInMonth);

        // Fetch Employees
        $userInfoModel = new UserInfoModel();
        $userBuilder = $userInfoModel->select('user_info.user_id as id, user_info.firstname, user_info.lastname, user_info.employee_id, user_info.profile_image, department.department_name')
            ->join('department', 'department.id = user_info.department_id', 'left')
            ->join('users', 'users.id = user_info.user_id', 'left')
            ->where("(LOWER(user_info.status) NOT IN ('inactive', 'resigned') OR user_info.status IS NULL)")
            ->where("(user_info.last_working_day IS NULL OR user_info.last_working_day >= CURDATE())")
            ->whereIn('users.role', ['employee', 'hr']);

        $authService = new \App\Services\AuthService(service('request'));
        $branchId = $authService->getBranchId();
        if (!empty($branchId)) {
            $userBuilder->where('users.branch_id', (int)$branchId);
        }

        if (!empty($departmentId) && $departmentId !== 'null') {
            $userBuilder->where('user_info.department_id', (int)$departmentId);
        }
        if (!empty($employeeId) && $employeeId !== 'null') {
            $userBuilder->where('user_info.user_id', (int)$employeeId);
        }
        $employeesList = $userBuilder->orderBy('user_info.firstname', 'ASC')->findAll();

        // Fetch Holidays in date range
        $holidayModel = new HolidayCalendarModel();
        $holidays = $holidayModel->where('holiday_date >=', $monthStartDate)
            ->where('holiday_date <=', $monthEndDate)
            ->findAll();
        $holidayMap = [];
        foreach ($holidays as $h) {
            $holidayMap[$h['holiday_date']] = $h['title'] ?? 'Holiday';
        }

        // Fetch Approved Leaves
        $leaveModel = new LeaveModel();
        $leavesBuilder = $leaveModel->where('status', 'Approved')
            ->where('start_date <=', $monthEndDate)
            ->where('end_date >=', $monthStartDate);
        if (!empty($employeeId) && $employeeId !== 'null') {
            $leavesBuilder->where('user_id', (int)$employeeId);
        }
        $leaves = $leavesBuilder->findAll();

        // Fetch Attendance Records
        $attendanceModel = new AttendanceModel();
        $reportData = $attendanceModel->getAttendanceReport(
            $departmentId, 
            $employeeId, 
            $startDate ?: $monthStartDate, 
            $endDate ?: $monthEndDate, 
            $selectedYear, 
            $selectedMonth
        );

        // Map Attendance by User & Date
        $attendanceMap = [];
        foreach ($reportData as $att) {
            $uId = $att['user_id'];
            $attDate = $att['date'];
            $attendanceMap[$uId][$attDate] = $att;
        }

        $today = date('Y-m-d');
        $matrix = [];
        $dailySummaryCounts = []; // for calendar & chart

        for ($d = 1; $d <= $totalDaysInMonth; $d++) {
            $cDate = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $d);
            $dailySummaryCounts[$cDate] = [
                'present'  => 0,
                'absent'   => 0,
                'leave'    => 0,
                'half_day' => 0,
                'holiday'  => isset($holidayMap[$cDate]) ? 1 : 0
            ];
        }

        foreach ($employeesList as $emp) {
            $empId = $emp['id'];
            $days = [];
            $presentCount = 0;
            $absentCount  = 0;
            $leaveCount   = 0;
            $halfDayCount = 0;
            $holidayCount = 0;
            $weekOffCount = 0;

            for ($d = 1; $d <= $totalDaysInMonth; $d++) {
                $currentDate = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $d);
                $dayOfWeek = date('w', strtotime($currentDate)); // 0 = Sunday

                $code = '-';
                $tooltip = '';
                $statusType = 'empty';

                if ($currentDate > $today) {
                    if (isset($holidayMap[$currentDate])) {
                        $code = 'H';
                        $tooltip = 'Holiday: ' . $holidayMap[$currentDate];
                        $statusType = 'holiday';
                        $holidayCount++;
                    } elseif ($dayOfWeek == 0) {
                        $code = 'WO';
                        $tooltip = 'Sunday (Week Off)';
                        $statusType = 'weekoff';
                        $weekOffCount++;
                    } else {
                        $code = '-';
                        $tooltip = 'Future Date';
                        $statusType = 'future';
                    }
                } else {
                    if (isset($attendanceMap[$empId][$currentDate])) {
                        $attRow = $attendanceMap[$empId][$currentDate];
                        $st = strtolower(trim($attRow['status'] ?? ''));
                        $checkIn = !empty($attRow['check_in_time']) ? date('h:i A', strtotime($attRow['check_in_time'])) : '--';
                        $checkOut = !empty($attRow['check_out_time']) ? date('h:i A', strtotime($attRow['check_out_time'])) : '--';
                        $workHours = $attRow['work_hours'] ?? '--';

                        if ($st === 'present') {
                            $code = 'P';
                            $tooltip = "In: {$checkIn} | Out: {$checkOut} | Hrs: {$workHours}";
                            $statusType = 'present';
                            $presentCount++;
                            $dailySummaryCounts[$currentDate]['present']++;
                        } elseif ($st === 'half-day' || $st === 'half day') {
                            $code = 'HD';
                            $tooltip = "Half Day | In: {$checkIn} | Out: {$checkOut} | Hrs: {$workHours}";
                            $statusType = 'halfday';
                            $halfDayCount++;
                            $dailySummaryCounts[$currentDate]['half_day']++;
                        } else {
                            $code = 'A';
                            $tooltip = 'Absent';
                            $statusType = 'absent';
                            $absentCount++;
                            $dailySummaryCounts[$currentDate]['absent']++;
                        }
                    } elseif ($leaveInfo = $this->isEmployeeOnLeave($empId, $currentDate, $leaves)) {
                        $code = 'L';
                        $tooltip = 'Approved Leave: ' . ($leaveInfo['reason'] ?? 'Leave');
                        $statusType = 'leave';
                        $leaveCount++;
                        $dailySummaryCounts[$currentDate]['leave']++;
                    } elseif (isset($holidayMap[$currentDate])) {
                        $code = 'H';
                        $tooltip = 'Holiday: ' . $holidayMap[$currentDate];
                        $statusType = 'holiday';
                        $holidayCount++;
                    } elseif ($dayOfWeek == 0) {
                        $code = 'WO';
                        $tooltip = 'Sunday (Week Off)';
                        $statusType = 'weekoff';
                        $weekOffCount++;
                    } else {
                        $code = 'A';
                        $tooltip = 'Absent (No Punch Record)';
                        $statusType = 'absent';
                        $absentCount++;
                        $dailySummaryCounts[$currentDate]['absent']++;
                    }
                }

                $days[$d] = [
                    'date'       => $currentDate,
                    'day'        => $d,
                    'dayName'    => date('D', strtotime($currentDate)),
                    'code'       => $code,
                    'tooltip'    => $tooltip,
                    'statusType' => $statusType
                ];
            }

            $matrix[] = [
                'user_id'         => $empId,
                'firstname'       => $emp['firstname'] ?? '',
                'lastname'        => $emp['lastname'] ?? '',
                'emp_id'          => $emp['employee_id'] ?? ('EMP#' . $empId),
                'department_name' => $emp['department_name'] ?? 'General',
                'days'            => $days,
                'summary'         => [
                    'present'  => $presentCount,
                    'absent'   => $absentCount,
                    'leave'    => $leaveCount,
                    'half_day' => $halfDayCount,
                    'holiday'  => $holidayCount,
                    'weekoff'  => $weekOffCount
                ]
            ];
        }

        // Build Calendar Events
        $calendarEvents = [];

        // Add Holidays to Calendar
        foreach ($holidayMap as $hDate => $hTitle) {
            $calendarEvents[] = [
                'id'              => 'hol_' . $hDate,
                'title'           => '🎉 ' . $hTitle,
                'start'           => $hDate,
                'allDay'          => true,
                'backgroundColor' => '#ede9fe',
                'borderColor'     => '#ddd6fe',
                'textColor'       => '#6d28d9',
                'extendedProps'   => [
                    'type'        => 'holiday',
                    'description' => $hTitle
                ]
            ];
        }

        // If Single Employee is filtered, show detailed event tiles
        if (!empty($employeeId) && count($matrix) === 1) {
            $singleEmp = $matrix[0];
            foreach ($singleEmp['days'] as $dayNum => $dInfo) {
                if ($dInfo['statusType'] === 'present') {
                    $calendarEvents[] = [
                        'id'              => 'att_' . $dInfo['date'],
                        'title'           => '🟢 Present (' . $dInfo['tooltip'] . ')',
                        'start'           => $dInfo['date'],
                        'allDay'          => true,
                        'backgroundColor' => '#dcfce7',
                        'borderColor'     => '#bbf7d0',
                        'textColor'       => '#15803d'
                    ];
                } elseif ($dInfo['statusType'] === 'halfday') {
                    $calendarEvents[] = [
                        'id'              => 'att_' . $dInfo['date'],
                        'title'           => '🟡 Half Day (' . $dInfo['tooltip'] . ')',
                        'start'           => $dInfo['date'],
                        'allDay'          => true,
                        'backgroundColor' => '#e0f2fe',
                        'borderColor'     => '#bae6fd',
                        'textColor'       => '#0369a1'
                    ];
                } elseif ($dInfo['statusType'] === 'leave') {
                    $calendarEvents[] = [
                        'id'              => 'att_' . $dInfo['date'],
                        'title'           => '🟠 Leave (' . $dInfo['tooltip'] . ')',
                        'start'           => $dInfo['date'],
                        'allDay'          => true,
                        'backgroundColor' => '#ffedd5',
                        'borderColor'     => '#fed7aa',
                        'textColor'       => '#c2410c'
                    ];
                } elseif ($dInfo['statusType'] === 'absent' && $dInfo['date'] <= $today) {
                    $cDayOfWeek = date('w', strtotime($dInfo['date']));
                    if ($cDayOfWeek != 0 && !isset($holidayMap[$dInfo['date']])) {
                        $calendarEvents[] = [
                            'id'              => 'att_' . $dInfo['date'],
                            'title'           => '🔴 Absent',
                            'start'           => $dInfo['date'],
                            'allDay'          => true,
                            'backgroundColor' => '#fee2e2',
                            'borderColor'     => '#fecaca',
                            'textColor'       => '#b91c1c'
                        ];
                    }
                }
            }
        } else {
            // Company-wide summary counts per date
            foreach ($dailySummaryCounts as $dateKey => $cnts) {
                $cDayOfWeek = date('w', strtotime($dateKey));
                $isHoliday = isset($holidayMap[$dateKey]);
                $isFuture = $dateKey > $today;

                if ($cnts['present'] > 0) {
                    $calendarEvents[] = [
                        'id'              => 'p_' . $dateKey,
                        'title'           => '🟢 ' . $cnts['present'] . ' Present',
                        'start'           => $dateKey,
                        'allDay'          => true,
                        'backgroundColor' => '#dcfce7',
                        'borderColor'     => '#bbf7d0',
                        'textColor'       => '#15803d'
                    ];
                }
                if ($cnts['leave'] > 0) {
                    $calendarEvents[] = [
                        'id'              => 'l_' . $dateKey,
                        'title'           => '🟠 ' . $cnts['leave'] . ' On Leave',
                        'start'           => $dateKey,
                        'allDay'          => true,
                        'backgroundColor' => '#ffedd5',
                        'borderColor'     => '#fed7aa',
                        'textColor'       => '#c2410c'
                    ];
                }
                if ($cnts['absent'] > 0 && !$isFuture && $cDayOfWeek != 0 && !$isHoliday) {
                    $calendarEvents[] = [
                        'id'              => 'a_' . $dateKey,
                        'title'           => '🔴 ' . $cnts['absent'] . ' Absent',
                        'start'           => $dateKey,
                        'allDay'          => true,
                        'backgroundColor' => '#fee2e2',
                        'borderColor'     => '#fecaca',
                        'textColor'       => '#b91c1c'
                    ];
                }
            }
        }

        // Chart Data (Present trends)
        $chartData = [];
        foreach ($dailySummaryCounts as $dKey => $counts) {
            $chartData[] = [
                'date'             => $dKey,
                'attendance_count' => $counts['present']
            ];
        }

        return $this->response->setJSON([
            'status'          => 'success',
            'matrixData'      => $matrix,
            'dailyData'       => $reportData,
            'calendarEvents'  => $calendarEvents,
            'chartData'       => $chartData,
            'monthDetails'    => [
                'year'        => $selectedYear,
                'month'       => $selectedMonth,
                'totalDays'   => $totalDaysInMonth,
                'monthName'   => date('F Y', strtotime($monthStartDate))
            ],
            'empty'           => empty($matrix),
            'csrfHash'        => csrf_hash()
        ]);
    }
}