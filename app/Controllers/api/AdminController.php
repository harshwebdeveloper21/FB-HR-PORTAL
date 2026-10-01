<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Controller;
use App\Services\AuthService;
use App\Models\UserModel;
use App\Models\LeaveModel;
use App\Models\AttendanceModel;
use App\Models\TaskModel;
use App\Models\UserInfoModel;
use App\Models\PerformanceModel;
use App\Models\OnboardingModel;
use App\Models\InterviewModel;
use App\Models\CandidateModel;
use App\Models\HolidayCalendarModel;
use App\Models\CompanyRulesModel;
use App\Models\StateModel;

class AdminController extends ResourceController
{
    protected $authService;
    protected $userModel;
    protected $leaveModel;
    protected $attendanceModel;
    protected $taskModel;
    protected $userInfoModel;
    protected $performanceModel;
    protected $onboardingModel;
    protected $interviewModel;
    protected $candidateModel;
    protected $stateModel;

    public function __construct()
    {
        $this->authService = new AuthService(service('request'));
        $this->userModel = new UserModel();
        $this->leaveModel = new LeaveModel();
        $this->attendanceModel = new AttendanceModel();
        $this->taskModel = new TaskModel();
        $this->userInfoModel = new UserInfoModel();  // Add the new model
        $this->performanceModel = new PerformanceModel();
        $this->onboardingModel = new OnboardingModel();
        $this->stateModel = new StateModel();
        $this->interviewModel = new InterviewModel();
        $this->candidateModel = new CandidateModel();
    }

    public function profile()
    {
        if (!$this->authService->check()) {
            return redirect()->to('/login');
        }
        $user = $this->authService->user(); // Get logged-in user
        $role = $user->role; // User role
        $cityModel = new \App\Models\CityModel();
        $countryModel = new \App\Models\CountryModel();
        $departmentModel = new \App\Models\DepartmentModel();
        $designationModel = new \App\Models\DesignationModel();
        $stateModel = new \App\Models\StateModel();

        $cities = $cityModel->findAll();

        $countries = $countryModel->findAll();
        $states = $stateModel->findAll();

        $departments = $departmentModel->findAll();

        $designations = $designationModel->findAll();

        return view('dashboard/profile', [
            'cities' => $cities,
            'countries' => $countries,
            'departments' => $departments,
            'designations' => $designations,
            'states' => $states,
            'role' => $role
        ]);
    }

    public function index()
    {
        if (!$this->authService->check()) {
            return redirect()->to('/login');
        }
        $user = $this->authService->user(); // Get logged-in user
        $role = $user->role; // User role

        $filterBranchId = $this->authService->getBranchId();
        if ($role === 'branch_admin' && empty($filterBranchId)) {
            $uRow = $this->userModel->find($user->sub);
            $filterBranchId = $uRow['branch_id'] ?? null;
        }

        $filterDepartmentId = null;
        if ($role === 'department_manager') {
            $uInfo = $this->userInfoModel->where('user_id', $user->sub)->first();
            $filterDepartmentId = $uInfo['department_id'] ?? null;
            if (empty($filterBranchId)) {
                $uRow = $this->userModel->find($user->sub);
                $filterBranchId = $uRow['branch_id'] ?? null;
            }
        }

        // Fetch user info from the users table
        $users = $this->userModel->where('id', $user->sub)->first();

        $userInfo = $this->userInfoModel->where('user_id', $user->sub)->first();

        if (!$users) {
            // Default to a guest user
            $users = [
                'username' => 'GuestUser', // Default username
            ];
        }
        // Store the user info in session
        session()->set([
            'userInfo' => $userInfo,
            'role' => $role,
            'username' => $users['username'], // Store username directly
        ]);

        if (in_array($role, ['admin', 'hr', 'branch_admin', 'department_manager'])) {
            $candidateQuery = $this->candidateModel
                ->select('candidate.id,candidate.candidate_name, candidate.email, candidate.phone_number, candidate.status,jobs.job_title')
                ->join('jobs', 'jobs.id = candidate.job_id', 'left')
                ->join('onboarding', 'onboarding.candidate_id = candidate.id', 'left') // Left join to include candidates without onboarding records
                ->where('(onboarding.onboarding_status IS NULL OR onboarding.onboarding_status != "completed")'); // Exclude completed onboarding
            $candidates = $candidateQuery->orderBy('candidate.created_at', 'DESC')->findAll();
        } else {
            $candidates = [];
        }
        // Fetch latest 5 employees with designation and department
        $currentMonth = date('m');
        $currentYear = date('Y');
        if (in_array($role, ['admin', 'hr', 'branch_admin', 'department_manager'])) {
            $empQuery = $this->userInfoModel
                ->select('user_info.*, designation.designation_name, department.department_name')
                ->join('designation', 'designation.id = user_info.designation_id', 'left')
                ->join('department', 'department.id = users.department_id', 'left')
                ->join('users', 'users.id = user_info.user_id', 'inner')
                ->where('users.is_deleted', 0)
                ->where('MONTH(user_info.joining_date)', $currentMonth)
                ->where('YEAR(user_info.joining_date)', $currentYear);

            if ($role === 'branch_admin' && !empty($filterBranchId)) {
                $empQuery->where('users.branch_id', (int)$filterBranchId)
                         ->whereIn('users.role', ['employee', 'department_manager', 'branch_admin']);
            } elseif ($role === 'department_manager') {
                if (!empty($filterDepartmentId)) {
                    $empQuery->where('users.department_id', (int)$filterDepartmentId);
                }
                $empQuery->where('users.role', 'employee');
            } elseif (in_array($role, ['admin', 'hr'])) {
                if (!empty($filterBranchId)) {
                    $empQuery->where('users.branch_id', (int)$filterBranchId);
                }
                $empQuery->whereIn('users.role', ['employee', 'hr', 'branch_admin', 'department_manager']);
            }
            $employees = $empQuery->orderBy('user_info.joining_date', 'DESC')
                ->limit(5)
                ->findAll();
        } else {
            $employees = $this->userInfoModel
                ->select('user_info.*, designation.designation_name, department.department_name')
                ->join('designation', 'designation.id = user_info.designation_id', 'left')
                ->join('department', 'department.id = users.department_id', 'left')
                ->where('user_info.user_id', $user->sub)
                ->where('MONTH(user_info.joining_date)', $currentMonth)
                ->where('YEAR(user_info.joining_date)', $currentYear)
                ->orderBy('user_info.joining_date', 'DESC')
                ->limit(5)
                ->findAll();
        }

        $this->triggerAutoLeaveOnceDaily();

        $todayCheckinCheckoutHistory = $this->attendanceModel
            ->where('DATE(date)', date('Y-m-d'))
            ->where('user_id', $user->sub)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Calculate today's hours worked and remaining hours for employees
        $todayHoursData = null;
        if ($role == 'employee') {
            $todayHoursData = $this->calculateTodayHours($user->sub);
        }

        // Fetch active announcements
        $announcementModel = new \App\Models\AnnouncementModel();
        $activeAnnouncements = $announcementModel->getActiveAnnouncements($user->sub, $role, 5);

        // Fetch Employee of the Month Data (Top 5)
        $eomModel = new \App\Models\EmployeeOfMonthPerformanceModel();
        $eomData = $eomModel->select('employee_of_month_certificates.*, users.username as user_name, user_info.profile_image')
            ->join('users', 'users.id = employee_of_month_certificates.user_id')
            ->join('user_info', 'user_info.user_id = employee_of_month_certificates.user_id', 'left')
            ->orderBy('employee_of_month_certificates.created_at', 'DESC')
            ->limit(5)
            ->findAll();

        // Fetch Performance Overview Data (Top 5)
        $performanceModel = new \App\Models\PerformanceModel();
        $performanceData = $performanceModel->select('performance.*, users.username, user_info.profile_image')
            ->join('users', 'users.id = performance.user_id')
            ->join('user_info', 'user_info.user_id = performance.user_id', 'left')
            ->orderBy('performance.created_at', 'DESC')
            ->limit(5)
            ->findAll();

        // Fetch Upcoming Trainings (Top 5)
        $trainingModel = new \App\Models\TrainingModel();
        $upcomingTrainings = $trainingModel->select('training.*, users.username, user_info.profile_image')
            ->join('users', 'users.id = training.user_id')
            ->join('user_info', 'user_info.user_id = training.user_id', 'left')
            ->where('training.end_date >=', date('Y-m-d'))
            ->orderBy('training.start_date', 'ASC')
            ->limit(5)
            ->findAll();

        // Fetch Tasks (Top 5)
        $taskModel = new \App\Models\TaskModel();
        $taskQuery = $taskModel->select('task.*, users.username, user_info.profile_image')
            ->join('users', 'users.id = task.user_id')
            ->join('user_info', 'user_info.user_id = task.user_id', 'left')
            ->where('task.due_date >=', date('Y-m-d'));
        if ($role === 'branch_admin' && !empty($filterBranchId)) {
            $taskQuery->where('users.branch_id', (int)$filterBranchId);
        } elseif ($role === 'department_manager' && !empty($filterDepartmentId)) {
            $taskQuery->where('users.department_id', (int)$filterDepartmentId);
        } elseif ($role === 'employee') {
            $taskQuery->where('task.user_id', $user->sub);
        } elseif (!empty($filterBranchId)) {
            $taskQuery->where('users.branch_id', (int)$filterBranchId);
        }
        $tasksData = $taskQuery->orderBy('task.created_at', 'DESC')->limit(5)->findAll();

        // Fetch Recruitment & Hiring (Top 5 Jobs)
        $jobModel = new \App\Models\JobModel();
        $jobsData = $jobModel->where('status', 'open')
            ->groupStart()
                ->where('close_date >=', date('Y-m-d'))
                ->orWhere('close_date', null)
                ->orWhere('close_date', '')
                ->orWhere('close_date', '0000-00-00')
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->findAll();

        // Fetch Complaints (Top 5)
        $complaintModel = new \App\Models\ComplaintModel();
        $complaintQuery = $complaintModel->select('complaints.*, users.username, user_info.profile_image')
            ->join('users', 'users.id = complaints.user_id', 'left')
            ->join('user_info', 'user_info.user_id = complaints.user_id', 'left')
            ->where('complaints.status !=', 'resolved');
        if ($role === 'branch_admin' && !empty($filterBranchId)) {
            $complaintQuery->where('users.branch_id', (int)$filterBranchId);
        } elseif ($role === 'department_manager' && !empty($filterDepartmentId)) {
            $complaintQuery->where('users.department_id', (int)$filterDepartmentId);
        } elseif ($role === 'employee') {
            $complaintQuery->where('complaints.user_id', $user->sub);
        } elseif (!empty($filterBranchId)) {
            $complaintQuery->where('users.branch_id', (int)$filterBranchId);
        }
        $complaintsData = $complaintQuery->orderBy('complaints.created_at', 'DESC')->limit(5)->findAll();

        return view('dashboard/dashboard', [
            'role' => $role,
            'employees' => $employees,
            'userInfo' => $userInfo,
            'candidates' => $candidates ?? 0,
            'todayCheckinCheckoutHistory' => $todayCheckinCheckoutHistory,
            'todayHoursData' => $todayHoursData,
            'activeAnnouncements' => $activeAnnouncements,
            'employeeOfTheMonthData' => $eomData,
            'performanceOverviewData' => $performanceData,
            'upcomingTrainings' => $upcomingTrainings,
            'tasksData' => $tasksData,
            'jobsData' => $jobsData,
            'complaintsData' => $complaintsData
        ]);
    }

    // public function DashboardData()
    // {
    //     if (!$this->authService->check()) {
    //         return redirect()->to('/login');
    //     }

    //     $user = $this->authService->user(); // Get logged-in user
    //     $role = $user->role;
    //     $userId = $user->sub;
    //     $startOfWeek = date('Y-m-d', strtotime('monday this week'));
    //     $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
    //     $today = date('Y-m-d');
    //     $startOfMonth = date('Y-m-01'); // 1st of current month
    //     $endOfMonth = date('Y-m-t');    // Last day of current month
    //     $startOfYear = date('Y-01-01'); // 1st Jan this year
    //     $endOfYear = date('Y-m-d'); // today
    //     // Only admin and HR can see new employees this week
    //     if (in_array($role, ['admin', 'hr'])) {
    //         $totalThisWeekEmployees = $this->userModel
    //             ->where('role', 'employee')
    //             ->where('DATE(created_at) >=', $startOfWeek)
    //             ->where('DATE(created_at) <=', $today)
    //             ->where('is_deleted', 0)
    //             ->countAllResults();
    //         $startOfMonth = date('Y-m-01'); // 1st of current month
    //         $endOfMonth = date('Y-m-t');    // Last day of current month
    //         $totalEmployeesThisMonth = $this->userModel
    //             ->where('role', 'employee')
    //             ->where('DATE(created_at) >=', $startOfMonth)
    //             ->where('DATE(created_at) <=', $endOfMonth)
    //             ->where('is_deleted', 0)
    //             ->countAllResults();
    //         $totalEmployeesThisYear = $this->userModel
    //             ->where('role', 'employee')
    //             ->where('YEAR(created_at)', date('Y')) // Filters based on the current year (e.g., 2025)
    //             ->where('is_deleted', 0)
    //             ->countAllResults();
    //     }

    //     $todayDate = date('Y-m-d');
    //     $totalLeavesToday = $this->leaveModel
    //         ->select('leaves.*, users.username, user_info.profile_image')
    //         ->join('users', 'users.id = leaves.user_id')
    //         ->join('user_info', 'user_info.user_id = users.id')
    //         ->where('start_date <=', $todayDate)
    //         ->where('end_date >=', $todayDate)
    //         ->where('leaves.status', 'approved') // ✅ Fully qualified
    //         ->findAll();
    //     $todayAttendance = $this->attendanceModel
    //         ->select('attendance.check_in_time, attendance.check_out_time, users.username, user_info.profile_image')
    //         ->join('users', 'users.id = attendance.user_id', 'inner')
    //         ->join('user_info', 'user_info.user_id = users.id', 'left')
    //         ->where('attendance.date', $todayDate)
    //         ->where('users.is_deleted', 0)
    //         ->groupBy('attendance.user_id')
    //         ->orderBy('attendance.check_in_time', 'ASC')
    //         ->findAll();
    //     // Leave count for all roles
    //     if (in_array($role, ['admin', 'hr'])) {
    //         $totalLeavesThisWeek = $this->leaveModel
    //             ->where('start_date >=', $startOfWeek)
    //             ->where('start_date <=', $endOfWeek)
    //             ->countAllResults();
    //         $totalLeavesThisYear = $this->leaveModel
    //             ->where('YEAR(created_at)', date('Y')) // Filter by current year
    //             ->countAllResults();
    //         $attendanceCountThisWeek = $this->attendanceModel
    //             ->select('user_id')
    //             ->where('date >=', $startOfWeek)
    //             ->where('date <=', $endOfWeek)
    //             ->groupBy('user_id')
    //             ->countAllResults();
    //         $totalTasksThisWeek = $this->taskModel
    //             ->where('assigned_date >=', $startOfWeek)
    //             ->where('assigned_date <=', $endOfWeek)
    //             ->countAllResults();
    //         $totalLeaves = $this->leaveModel
    //             ->where('start_date >=', $startOfMonth)
    //             ->where('start_date <=', $endOfMonth)
    //             ->countAllResults();
    //         $attendanceCountThisMonth = $this->attendanceModel
    //             ->select('user_id')
    //             ->where('date >=', $startOfMonth)
    //             ->where('date <=', $endOfMonth)
    //             ->groupBy('user_id')
    //             ->countAllResults();
    //         // HR/Admin: count unique employees who marked attendance this year
    //         $attendanceCountThisYear = $this->attendanceModel
    //             ->select('user_id')
    //             ->where('YEAR(date)', date('Y'))
    //             ->groupBy('user_id')
    //             ->countAllResults();
    //         $totalTasksThisMonth = $this->taskModel
    //             ->where('assigned_date >=', $startOfMonth)
    //             ->where('assigned_date <=', $endOfMonth)
    //             ->countAllResults();
    //         $totalTasksThisYear = $this->taskModel
    //             ->where('assigned_date >=', $startOfYear)
    //             ->where('assigned_date <=', $endOfYear)
    //             ->countAllResults();
    //         $remoteEmployees = $this->userInfoModel->where('working_location', 'remote')->countAllResults();
    //         $onSiteEmployees = $this->userInfoModel->where('working_location', 'on-site')->countAllResults();
    //         $workingFormatTotal = $remoteEmployees + $onSiteEmployees;
    //         $remotePercentage = 0;
    //         $onSitePercentage = 0;
    //         if ($workingFormatTotal > 0) {
    //             $remotePercentage = $remoteEmployees / $workingFormatTotal;
    //             $onSitePercentage = 1 - $remotePercentage;
    //         }
    //     } else {
    //         $employeeId = $user->sub; // Get the logged-in employee's ID
    //         $startOfYear = date('Y-01-01'); // 1st January this year
    //         $todayDate = date('Y-m-d');  // Define todayDate for employee
    //         $totalLeavesThisWeek = $this->leaveModel
    //             ->where('user_id', $userId)
    //             ->where('start_date >=', $startOfWeek)
    //             ->where('start_date <=', $endOfWeek)
    //             ->countAllResults();
    //         // Total leaves this year
    //         $totalLeavesThisYear = $this->leaveModel
    //             ->where('user_id', $employeeId) // Only logged-in employee's leaves
    //             ->where('YEAR(created_at)', date('Y')) // start_date is inside current year
    //             ->countAllResults();
    //         $todayDate = date('Y-m-d');
    //         $totalLeavesToday = $this->leaveModel
    //             ->select('leaves.*, users.username, user_info.profile_image')
    //             ->join('users', 'users.id = leaves.user_id')
    //             ->join('user_info', 'user_info.user_id = users.id')
    //             ->where('start_date <=', $todayDate)
    //             ->where('end_date >=', $todayDate)
    //             ->where('leaves.status', 'approved') // ✅ Fully qualified
    //             ->findAll();
    //         $todayAttendance = $this->attendanceModel
    //                 ->select('attendance.check_in_time, users.username, user_info.profile_image')
    //                 ->join('users', 'users.id = attendance.user_id', 'inner')
    //                 ->join('user_info', 'user_info.user_id = users.id', 'left')
    //                 ->where('attendance.date', $todayDate)
    //                 ->where('users.is_deleted', 0)
    //                 ->groupBy('attendance.user_id')
    //                 ->orderBy('attendance.check_in_time', 'ASC')
    //                 ->findAll();
    //         $attendanceCountThisWeek = $this->attendanceModel
    //             ->select('user_id')
    //             ->where('user_id', $employeeId)
    //             ->where('date >=', $startOfWeek)
    //             ->where('date <=', $endOfWeek)
    //             ->groupBy('user_id')
    //             ->countAllResults();
    //         $totalTasksThisWeek = $this->taskModel->where('user_id', $employeeId)
    //             ->where('assigned_date >=', $startOfWeek)
    //             ->where('assigned_date <=', $endOfWeek)
    //             ->countAllResults();

    //         $totalLeaves = $this->leaveModel
    //             ->where('user_id', $employeeId)
    //             ->where('start_date >=', $startOfMonth)
    //             ->where('start_date <=', $endOfMonth)
    //             ->countAllResults();

    //         $attendanceCountThisMonth = $this->attendanceModel
    //             ->where('user_id', $employeeId)
    //             ->where('date >=', $startOfMonth)
    //             ->where('date <=', $endOfMonth)
    //             ->groupBy('user_id')
    //             ->countAllResults();
    //         $attendanceCountThisYear = $this->attendanceModel
    //             ->where('user_id', $employeeId)
    //             ->where('YEAR(date)', date('Y')) // Filter by current year
    //             ->countAllResults();
    //         $totalTasksThisMonth = $this->taskModel
    //             ->where('user_id', $employeeId)
    //             ->where('assigned_date >=', $startOfMonth)
    //             ->where('assigned_date <=', $endOfMonth)
    //             ->countAllResults();
    //         $totalTasksThisYear = $this->taskModel
    //             ->where('user_id', $employeeId)
    //             ->where('assigned_date >=', $startOfYear)
    //             ->where('assigned_date <=', $endOfYear)
    //             ->countAllResults();
    //     }

    //     if ($role == 'admin' || $role == 'hr') {
    //         $departmentData = $this->userInfoModel
    //             ->select("department.department_name, COUNT(user_info.id) as employee_count")
    //             ->join('department', 'department.id = users.department_id', 'left')
    //             ->join('users', 'users.id = user_info.user_id', 'inner')
    //             ->where('users.is_deleted', 0)
    //             ->where('department.department_name IS NOT NULL') // Remove unassigned
    //             ->groupBy('department.department_name')
    //             ->orderBy('department.id', 'DESC') // Sort by latest departments
    //             ->limit(4) // Get only latest 6 departments
    //             ->findAll();
    //     } else {

    //         $departmentData = $this->userInfoModel
    //             ->select("department.department_name, COUNT(user_info.id) as employee_count")
    //             ->join('department', 'department.id = users.department_id', 'left')
    //             ->join('users', 'users.id = user_info.user_id', 'inner')
    //             ->where('users.is_deleted', 0)
    //             ->where('department.department_name IS NOT NULL') // Remove unassigned
    //             ->where('user_info.user_id', $user->sub)
    //             ->groupBy('department.department_name')
    //             ->orderBy('department.id', 'DESC') // Sort by latest departments
    //             ->limit(4) // Get only latest 6 departments
    //             ->findAll();
    //     }
    //     // Determine if there is any data available
    //     $hasData = !empty($departmentData);
    //     // Prepare data for JavaScript
    //     $departmentLabels = [];
    //     $employeeCounts = [];
    //     foreach ($departmentData as $data) {
    //         $departmentLabels[] = $data['department_name'];
    //         $employeeCounts[] = (int) $data['employee_count'];
    //     }
    //     // Fetch birthdays for the current week (Monday-Sunday)
    //     $weekDates = [];
    //     for ($i = 0; $i < 7; $i++) {
    //         $weekDates[] = date('m-d', strtotime("monday this week +$i days"));
    //     }

    //     $birthdayUsers = $this->userInfoModel->select('user_info.*')
    //                 ->join('users', 'users.id = user_info.user_id', 'inner')
    //                 ->where('users.is_deleted', 0)
    //                 ->whereIn('DATE_FORMAT(user_info.date_of_birth, "%m-%d")', $weekDates)
    //                 ->findAll();

    //     $currentMonth = date('m');
    //     $currentYear = date('Y');
    //     if ($role == 'admin' || $role == 'hr') {
    //         $employees = $this->userInfoModel
    //             ->select('user_info.*, designation.designation_name, department.department_name')
    //             ->join('designation', 'designation.id = user_info.designation_id', 'left')
    //             ->join('department', 'department.id = users.department_id', 'left')
    //             ->join('users', 'users.id = user_info.user_id', 'inner')
    //             ->where('users.is_deleted', 0)
    //             ->where('user_info.role', 'employee')
    //             ->where('MONTH(user_info.joining_date)', $currentMonth)
    //             ->where('YEAR(user_info.joining_date)', $currentYear)
    //             ->orderBy('user_info.joining_date', 'DESC')
    //             ->limit(5)
    //             ->findAll();
    //     } else {
    //         $employees = $this->userInfoModel
    //             ->select('user_info.*, designation.designation_name, department.department_name')
    //             ->join('designation', 'designation.id = user_info.designation_id', 'left')
    //             ->join('department', 'department.id = users.department_id', 'left')
    //             ->join('users', 'users.id = user_info.user_id', 'inner')
    //             ->where('users.is_deleted', 0)
    //             ->where('user_info.user_id', $user->sub)
    //             ->where('user_info.role', 'employee')
    //             ->where('MONTH(user_info.joining_date)', $currentMonth)
    //             ->where('YEAR(user_info.joining_date)', $currentYear)
    //             ->orderBy('user_info.joining_date', 'DESC')
    //             ->limit(5)
    //             ->findAll();
    //     }
    //     // Fetch the count of completed and scheduled interviews
    //     if ($role == 'admin' || $role == 'hr') {
    //         $completedInterviews = $this->interviewModel->where('status', 'completed')->countAllResults();
    //         $scheduledInterviews = $this->interviewModel->where('status', 'scheduled')->countAllResults();
    //         // Total interviews (prevent division by zero)
    //         $totalInterviews = $completedInterviews + $scheduledInterviews;
    //     } else {
    //         $completedInterviews = 0;
    //         $scheduledInterviews = 0;
    //         $totalInterviews = 0;
    //     }

    //     if ($role == 'admin' || $role == 'hr') {
    //         $candidates = $this->candidateModel
    //             ->select('candidate.candidate_name, candidate.email, candidate.phone_number, candidate.status,jobs.job_title')
    //             ->join('jobs', 'jobs.id = candidate.job_id', 'left')
    //             ->join('onboarding', 'onboarding.candidate_id = candidate.id', 'left') // Left join to include candidates without onboarding records
    //             ->where('(onboarding.onboarding_status IS NULL OR onboarding.onboarding_status != "completed")') // Exclude completed onboarding
    //             ->orderBy('candidate.created_at', 'DESC')
    //             ->findAll();
    //     } else {
    //         $candidates = $this->candidateModel
    //             ->select('candidate.candidate_name,candidate.email,candidate.phone_number,candidate.status,jobs.job_title')
    //             ->join('jobs', 'jobs.id = candidate.job_id', 'left')
    //             ->join('onboarding', 'onboarding.candidate_id = candidate.id', 'left') // Left join to include candidates without onboarding records
    //             ->where('(onboarding.onboarding_status IS NULL OR onboarding.onboarding_status != "completed")') // Exclude completed onboarding
    //             ->orderBy('candidate.created_at', 'DESC')
    //             ->findAll();
    //     }
    //     // Check if data exists
    //     $hasInterviewData = ($totalInterviews > 0);
    //     return $this->response->setJSON([
    //         'totalThisWeekEmployees' => $totalThisWeekEmployees ?? 0,
    //         'totalLeavesThisWeek' => $totalLeavesThisWeek ?? 0,
    //         'attendanceCountThisWeek' => $attendanceCountThisWeek ?? 0,
    //         'totalTasksThisWeek' => $totalTasksThisWeek ?? 0,
    //         'totalEmployeesThisMonth' => $totalEmployeesThisMonth ?? 0,
    //         'totalLeaves' => $totalLeaves ?? 0,
    //         'attendanceCountThisMonth' => $attendanceCountThisMonth ?? 0,
    //         'totalTasksThisMonth' => $totalTasksThisMonth ?? 0,
    //         'totalEmployeesThisYear' => $totalEmployeesThisYear ?? 0,
    //         'totalLeavesThisYear' => $totalLeavesThisYear ?? 0,
    //         'totalLeavesToday' => !empty($totalLeavesToday) ? $totalLeavesToday : [],
    //         'todayAttendance' => !empty($todayAttendance) ? $todayAttendance : [],
    //         'attendanceCountThisYear' => $attendanceCountThisYear ?? 0,
    //         'totalTasksThisYear' => $totalTasksThisYear ?? 0,
    //         'departmentLabels' => $departmentLabels,
    //         'employeeCounts' => $employeeCounts, // ✅ add this line
    //         'workingFormatTotal' => $workingFormatTotal ?? 0,
    //         'remotePercentage' => $remotePercentage ?? 0,
    //         'onSitePercentage' => $onSitePercentage ?? 0,
    //         'birthdayUsers' => !empty($birthdayUsers) ? $birthdayUsers : [],
    //         'employees' => $employees,
    //         'completedInterviews' => $completedInterviews,
    //         'scheduledInterviews' => $scheduledInterviews,
    //         'hasInterviewData' => $hasInterviewData,
    //         'totalInterviews' => $totalInterviews,
    //         'candidates' => $candidates ?? 0,
    //         'latestComplaints' => $this->getLatestComplaints($role, $userId),
    //     ]);
    // }


    public function DashboardData()
    {
        if (!$this->authService->check()) {
            return redirect()->to('/login');
        }

        $user = $this->authService->user(); // Get logged-in user
        $role = $user->role;
        $userId = $user->sub;
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
        $today = date('Y-m-d');
        $startOfMonth = date('Y-m-01'); // 1st of current month
        $endOfMonth = date('Y-m-t');    // Last day of current month
        $startOfYear = date('Y-01-01'); // 1st Jan this year
        $endOfYear = date('Y-m-d'); // today

        // Determine branch and department filters
        $filterBranchId = $this->authService->getBranchId();
        if ($role === 'branch_admin' && empty($filterBranchId)) {
            $uRow = $this->userModel->find($userId);
            $filterBranchId = $uRow['branch_id'] ?? null;
        }

        $filterDepartmentId = null;
        if ($role === 'department_manager') {
            $uInfo = $this->userInfoModel->where('user_id', $userId)->first();
            $uRow = $this->userModel->find($userId);
            $filterDepartmentId = $uInfo['department_id'] ?? $uRow['department_id'] ?? null;
            $filterBranchId = $uRow['branch_id'] ?? null;
        }

        $branchFilterSql = "";
        if (!empty($filterBranchId)) {
            $branchFilterSql = " AND users.branch_id = " . (int)$filterBranchId . " ";
        }
        $deptFilterSql = "";
        if (!empty($filterDepartmentId)) {
            $deptFilterSql = " AND (users.department_id = " . (int)$filterDepartmentId . " OR users.department_id = " . (int)$filterDepartmentId . ") ";
        }

        if ($role === 'branch_admin') {
            $staffRoles = ['employee', 'department_manager', 'branch_admin'];
        } elseif ($role === 'department_manager') {
            $staffRoles = ['employee', 'department_manager'];
        } elseif (in_array($role, ['admin', 'hr'])) {
            $staffRoles = ['employee', 'hr', 'branch_admin', 'department_manager'];
        } else {
            $staffRoles = ['employee'];
        }
        $rolesList = "'" . implode("','", $staffRoles) . "'";

        $db = \Config\Database::connect();
        $todayDate = date('Y-m-d');

        if (in_array($role, ['admin', 'hr', 'branch_admin', 'department_manager'])) {
            // Count only active (non-inactive, non-resigned) staff
            $activeEmpSQL = "SELECT COUNT(users.id) as cnt
                             FROM users
                             LEFT JOIN user_info ON user_info.user_id = users.id
                             WHERE users.role IN ({$rolesList})
                                AND users.is_deleted = 0
                                {$branchFilterSql}
                                {$deptFilterSql}
                                AND (
                                    user_info.status IS NULL
                                    OR (LOWER(user_info.status) NOT IN ('inactive', 'resigned'))
                                )
                                AND (user_info.last_working_day IS NULL OR user_info.last_working_day >= CURDATE())";
            $activeEmpResult = $db->query($activeEmpSQL)->getRow();
            $activeEmpCount  = (int)($activeEmpResult->cnt ?? 0);

            $totalThisWeekEmployees  = $activeEmpCount;
            $totalEmployeesThisMonth = $activeEmpCount;
            $totalEmployeesThisYear  = $activeEmpCount;
        } else {
            $totalThisWeekEmployees  = 0;
            $totalEmployeesThisMonth = 0;
            $totalEmployeesThisYear  = 0;
        }

        // 1. Fetch active employees (excluding admins, deleted, inactive, or resigned past last working day)
        if (in_array($role, ['admin', 'hr', 'branch_admin', 'department_manager'])) {
            $activeEmpSql = "SELECT users.id, users.username, user_info.profile_image
                             FROM users
                             LEFT JOIN user_info ON user_info.user_id = users.id
                             WHERE users.role IN ({$rolesList})
                               AND users.is_deleted = 0
                               {$branchFilterSql}
                               {$deptFilterSql}
                               AND (
                                   user_info.status IS NULL
                                   OR (LOWER(user_info.status) NOT IN ('inactive', 'resigned'))
                               )
                               AND (user_info.last_working_day IS NULL OR user_info.last_working_day >= '{$todayDate}')
                             ORDER BY users.username ASC";
            $activeEmployees = $db->query($activeEmpSql)->getResultArray();

            // 2. Fetch approved leaves for today
            $leaveSql = "SELECT leaves.*, users.username, user_info.profile_image
                         FROM leaves
                         JOIN users ON users.id = leaves.user_id
                         JOIN user_info ON user_info.user_id = users.id
                         WHERE leaves.start_date <= '{$todayDate}'
                           AND leaves.end_date >= '{$todayDate}'
                           AND leaves.status = 'approved'
                           AND users.role IN ({$rolesList})
                           AND users.is_deleted = 0
                           {$branchFilterSql}
                           {$deptFilterSql}
                           AND (user_info.status IS NULL OR LOWER(user_info.status) NOT IN ('inactive', 'resigned'))
                           AND (user_info.last_working_day IS NULL OR user_info.last_working_day >= '{$todayDate}')
                         ORDER BY users.username ASC";
            $approvedLeavesToday = $db->query($leaveSql)->getResultArray();
        } else {
            $activeEmployees = [];
            $approvedLeavesToday = [];
        }

        $attendanceQuery = $this->attendanceModel
            ->select('attendance.id, attendance.user_id, attendance.check_in_time, attendance.check_out_time, users.username, user_info.profile_image, user_info.working_location')
            ->join('users', 'users.id = attendance.user_id', 'inner')
            ->join('user_info', 'user_info.user_id = users.id', 'left')
            ->where('attendance.date', $todayDate)
            ->where('users.is_deleted', 0);

        if ($role === 'branch_admin') {
            if (!empty($filterBranchId)) {
                $attendanceQuery->where('users.branch_id', (int)$filterBranchId);
            }
            $attendanceQuery->whereIn('users.role', ['employee', 'department_manager', 'branch_admin']);
        } elseif ($role === 'department_manager') {
            if (!empty($filterDepartmentId)) {
                $attendanceQuery->where('users.department_id', (int)$filterDepartmentId);
            }
            if (!empty($filterBranchId)) {
                $attendanceQuery->where('users.branch_id', (int)$filterBranchId);
            }
            $attendanceQuery->whereIn('users.role', ['employee', 'department_manager']);
        } elseif (in_array($role, ['admin', 'hr'])) {
            if (!empty($filterBranchId)) {
                $attendanceQuery->where('users.branch_id', (int)$filterBranchId);
            }
            $attendanceQuery->whereIn('users.role', ['employee', 'hr', 'branch_admin', 'department_manager']);
        } else {
            $attendanceQuery->where('attendance.user_id', $userId);
        }

        $todayAttendanceRaw = $attendanceQuery->orderBy('attendance.id', 'DESC')->findAll();

        // ── Group all records per user, accumulate completed sessions ──
        $todayAttendance = [];
        $byUser = [];
        foreach ($todayAttendanceRaw as $att) {
            $byUser[$att['user_id']][] = $att;
        }
        foreach ($byUser as $uid => $records) {
            usort($records, function ($a, $b) {
                return strcmp($a['check_in_time'], $b['check_in_time']);
            });

            $completedSeconds = 0;
            $activeRecord = null; // latest record that has no checkout

            foreach ($records as $rec) {
                if ($rec['check_out_time']) {
                    $parts = explode(':', $rec['check_out_time'] ? ($rec['work_hours'] ?? '00:00:00') : '00:00:00');
                    if (count($parts) === 3) {
                        $completedSeconds += ((int)$parts[0] * 3600) + ((int)$parts[1] * 60) + (int)$parts[2];
                    }
                } else {
                    $activeRecord = $rec;
                }
            }

            $firstRecord = $records[0];
            $displayRecord = $activeRecord ?? $firstRecord;

            $todayAttendance[] = [
                'id'               => $displayRecord['id'],
                'user_id'          => $displayRecord['user_id'],
                'check_in_time'    => $firstRecord['check_in_time'],
                'check_out_time'   => $displayRecord['check_out_time'],
                'completed_seconds'=> $completedSeconds,
                'username'         => $displayRecord['username'],
                'profile_image'    => $displayRecord['profile_image'],
                'working_location' => $displayRecord['working_location'],
            ];
        }

        // Sort by earliest check-in time
        usort($todayAttendance, function ($a, $b) {
            return strcmp($a['check_in_time'], $b['check_in_time']);
        });

        // ── Build Today's Absent Or Leave list ───────────────────────────────
        $checkedInUserIds = array_column($todayAttendance, 'user_id');

        $totalLeavesToday = [];
        $leaveUserIds = [];

        // 1. Employees on approved leave
        foreach ($approvedLeavesToday as $leave) {
            if (in_array($leave['user_id'], $checkedInUserIds)) {
                continue;
            }
            $leaveUserIds[$leave['user_id']] = true;
            $totalLeavesToday[] = [
                'id'            => $leave['id'],
                'user_id'       => $leave['user_id'],
                'username'      => $leave['username'],
                'profile_image' => $leave['profile_image'],
                'start_date'    => $leave['start_date'],
                'end_date'      => $leave['end_date'],
                'reason'        => $leave['reason'] ?? '',
                'type'          => 'leave',
                'status_text'   => 'On Leave',
            ];
        }

        // 2. Active employees who neither checked in nor have an approved leave today (Absent)
        foreach ($activeEmployees as $emp) {
            $empId = $emp['id'];
            if (in_array($empId, $checkedInUserIds) || isset($leaveUserIds[$empId])) {
                continue;
            }
            $totalLeavesToday[] = [
                'id'            => null,
                'user_id'       => $empId,
                'username'      => $emp['username'],
                'profile_image' => $emp['profile_image'],
                'start_date'    => $todayDate,
                'end_date'      => $todayDate,
                'reason'        => 'Absent',
                'type'          => 'absent',
                'status_text'   => 'Absent',
            ];
        }
        // ────────────────────────────────────────────────────────────────────

        // KPI counts for Management vs Employee
        if (in_array($role, ['admin', 'hr', 'branch_admin', 'department_manager'])) {
            // Leaves This Week
            $leaveQWeek = $this->leaveModel
                ->join('users', 'users.id = leaves.user_id')
                ->where('users.is_deleted', 0)
                ->where('leaves.status', 'approved')
                ->where('((leaves.start_date >= \'' . $startOfWeek . '\' AND leaves.start_date <= \'' . $endOfWeek . '\') OR (leaves.end_date >= \'' . $startOfWeek . '\' AND leaves.end_date <= \'' . $endOfWeek . '\') OR (leaves.start_date <= \'' . $startOfWeek . '\' AND leaves.end_date >= \'' . $endOfWeek . '\'))');
            if (!empty($filterBranchId)) $leaveQWeek->where('users.branch_id', (int)$filterBranchId);
            if (!empty($filterDepartmentId)) {
                $leaveQWeek->join('user_info', 'user_info.user_id = users.id', 'left')->where('users.department_id', (int)$filterDepartmentId);
            }
            if ($role === 'branch_admin') {
                $leaveQWeek->whereIn('users.role', ['employee', 'department_manager', 'branch_admin']);
            } elseif ($role === 'department_manager') {
                $leaveQWeek->where('users.role', 'employee');
            }
            $totalLeavesThisWeek = $leaveQWeek->countAllResults();

            // Leaves This Year
            $leaveQYear = $this->leaveModel
                ->join('users', 'users.id = leaves.user_id')
                ->where('users.is_deleted', 0)
                ->where('leaves.status', 'approved')
                ->where('YEAR(leaves.start_date)', date('Y'));
            if (!empty($filterBranchId)) $leaveQYear->where('users.branch_id', (int)$filterBranchId);
            if (!empty($filterDepartmentId)) {
                $leaveQYear->join('user_info', 'user_info.user_id = users.id', 'left')->where('users.department_id', (int)$filterDepartmentId);
            }
            if ($role === 'branch_admin') {
                $leaveQYear->whereIn('users.role', ['employee', 'department_manager', 'branch_admin']);
            } elseif ($role === 'department_manager') {
                $leaveQYear->where('users.role', 'employee');
            }
            $totalLeavesThisYear = $leaveQYear->countAllResults();

            // Total Leaves This Month
            $leaveQMonth = $this->leaveModel
                ->join('users', 'users.id = leaves.user_id')
                ->where('users.is_deleted', 0)
                ->where('leaves.status', 'approved')
                ->where('((leaves.start_date >= \'' . $startOfMonth . '\' AND leaves.start_date <= \'' . $endOfMonth . '\') OR (leaves.end_date >= \'' . $startOfMonth . '\' AND leaves.end_date <= \'' . $endOfMonth . '\') OR (leaves.start_date <= \'' . $startOfMonth . '\' AND leaves.end_date >= \'' . $endOfMonth . '\'))');
            if (!empty($filterBranchId)) $leaveQMonth->where('users.branch_id', (int)$filterBranchId);
            if (!empty($filterDepartmentId)) {
                $leaveQMonth->join('user_info', 'user_info.user_id = users.id', 'left')->where('users.department_id', (int)$filterDepartmentId);
            }
            if ($role === 'branch_admin') {
                $leaveQMonth->whereIn('users.role', ['employee', 'department_manager', 'branch_admin']);
            } elseif ($role === 'department_manager') {
                $leaveQMonth->where('users.role', 'employee');
            }
            $totalLeaves = $leaveQMonth->countAllResults();

            // Attendance This Week
            $attQWeek = $this->attendanceModel
                ->select('COUNT(DISTINCT attendance.date, attendance.user_id) as cnt', false)
                ->join('users', 'users.id = attendance.user_id')
                ->where('users.is_deleted', 0)
                ->where('attendance.date >=', $startOfWeek)
                ->where('attendance.date <=', $endOfWeek);
            if (!empty($filterBranchId)) $attQWeek->where('users.branch_id', (int)$filterBranchId);
            if (!empty($filterDepartmentId)) {
                $attQWeek->join('user_info', 'user_info.user_id = users.id', 'left')->where('users.department_id', (int)$filterDepartmentId);
            }
            if ($role === 'branch_admin') {
                $attQWeek->whereIn('users.role', ['employee', 'department_manager', 'branch_admin']);
            } elseif ($role === 'department_manager') {
                $attQWeek->whereIn('users.role', ['employee', 'department_manager']);
            }
            $attRowWeek = $attQWeek->get()->getRow();
            $attendanceCountThisWeek = (int)($attRowWeek->cnt ?? 0);

            // Attendance This Month
            $attQMonth = $this->attendanceModel
                ->select('COUNT(DISTINCT attendance.date, attendance.user_id) as cnt', false)
                ->join('users', 'users.id = attendance.user_id')
                ->where('users.is_deleted', 0)
                ->where('attendance.date >=', $startOfMonth)
                ->where('attendance.date <=', $endOfMonth);
            if (!empty($filterBranchId)) $attQMonth->where('users.branch_id', (int)$filterBranchId);
            if (!empty($filterDepartmentId)) {
                $attQMonth->join('user_info', 'user_info.user_id = users.id', 'left')->where('users.department_id', (int)$filterDepartmentId);
            }
            if ($role === 'branch_admin') {
                $attQMonth->whereIn('users.role', ['employee', 'department_manager', 'branch_admin']);
            } elseif ($role === 'department_manager') {
                $attQMonth->whereIn('users.role', ['employee', 'department_manager']);
            }
            $attRowMonth = $attQMonth->get()->getRow();
            $attendanceCountThisMonth = (int)($attRowMonth->cnt ?? 0);

            // Attendance This Year
            $attQYear = $this->attendanceModel
                ->select('COUNT(DISTINCT attendance.date, attendance.user_id) as cnt', false)
                ->join('users', 'users.id = attendance.user_id')
                ->where('users.is_deleted', 0)
                ->where('YEAR(attendance.date)', date('Y'));
            if (!empty($filterBranchId)) $attQYear->where('users.branch_id', (int)$filterBranchId);
            if (!empty($filterDepartmentId)) {
                $attQYear->join('user_info', 'user_info.user_id = users.id', 'left')->where('users.department_id', (int)$filterDepartmentId);
            }
            if ($role === 'branch_admin') {
                $attQYear->whereIn('users.role', ['employee', 'department_manager', 'branch_admin']);
            } elseif ($role === 'department_manager') {
                $attQYear->whereIn('users.role', ['employee', 'department_manager']);
            }
            $attRowYear = $attQYear->get()->getRow();
            $attendanceCountThisYear = (int)($attRowYear->cnt ?? 0);

            // Tasks This Week
            $taskQWeek = $this->taskModel
                ->join('users', 'users.id = task.user_id')
                ->where('users.is_deleted', 0)
                ->where('task.assigned_date >=', $startOfWeek)
                ->where('task.assigned_date <=', $endOfWeek);
            if (!empty($filterBranchId)) $taskQWeek->where('users.branch_id', (int)$filterBranchId);
            if (!empty($filterDepartmentId)) {
                $taskQWeek->join('user_info', 'user_info.user_id = users.id', 'left')->where('users.department_id', (int)$filterDepartmentId);
            }
            if ($role === 'branch_admin') {
                $taskQWeek->whereIn('users.role', ['employee', 'department_manager']);
            } elseif ($role === 'department_manager') {
                $taskQWeek->where('users.role', 'employee');
            }
            $totalTasksThisWeek = $taskQWeek->countAllResults();

            // Tasks This Month
            $taskQMonth = $this->taskModel
                ->join('users', 'users.id = task.user_id')
                ->where('users.is_deleted', 0)
                ->where('task.assigned_date >=', $startOfMonth)
                ->where('task.assigned_date <=', $endOfMonth);
            if (!empty($filterBranchId)) $taskQMonth->where('users.branch_id', (int)$filterBranchId);
            if (!empty($filterDepartmentId)) {
                $taskQMonth->join('user_info', 'user_info.user_id = users.id', 'left')->where('users.department_id', (int)$filterDepartmentId);
            }
            if ($role === 'branch_admin') {
                $taskQMonth->whereIn('users.role', ['employee', 'department_manager']);
            } elseif ($role === 'department_manager') {
                $taskQMonth->where('users.role', 'employee');
            }
            $totalTasksThisMonth = $taskQMonth->countAllResults();

            // Tasks This Year
            $taskQYear = $this->taskModel
                ->join('users', 'users.id = task.user_id')
                ->where('users.is_deleted', 0)
                ->where('task.assigned_date >=', $startOfYear)
                ->where('task.assigned_date <=', $endOfYear);
            if (!empty($filterBranchId)) $taskQYear->where('users.branch_id', (int)$filterBranchId);
            if (!empty($filterDepartmentId)) {
                $taskQYear->join('user_info', 'user_info.user_id = users.id', 'left')->where('users.department_id', (int)$filterDepartmentId);
            }
            if ($role === 'branch_admin') {
                $taskQYear->whereIn('users.role', ['employee', 'department_manager']);
            } elseif ($role === 'department_manager') {
                $taskQYear->where('users.role', 'employee');
            }
            $totalTasksThisYear = $taskQYear->countAllResults();

            // Working location breakdown
            $remoteQ = $this->userInfoModel->join('users', 'users.id = user_info.user_id')
                ->where('users.is_deleted', 0)->where('user_info.working_location', 'remote');
            if (!empty($filterBranchId)) $remoteQ->where('users.branch_id', (int)$filterBranchId);
            if (!empty($filterDepartmentId)) $remoteQ->where('users.department_id', (int)$filterDepartmentId);
            if ($role === 'branch_admin') $remoteQ->whereIn('users.role', ['employee', 'department_manager']);
            elseif ($role === 'department_manager') $remoteQ->where('users.role', 'employee');
            $remoteEmployees = $remoteQ->countAllResults();

            $onSiteQ = $this->userInfoModel->join('users', 'users.id = user_info.user_id')
                ->where('users.is_deleted', 0)->where('user_info.working_location', 'on-site');
            if (!empty($filterBranchId)) $onSiteQ->where('users.branch_id', (int)$filterBranchId);
            if (!empty($filterDepartmentId)) $onSiteQ->where('users.department_id', (int)$filterDepartmentId);
            if ($role === 'branch_admin') $onSiteQ->whereIn('users.role', ['employee', 'department_manager']);
            elseif ($role === 'department_manager') $onSiteQ->where('users.role', 'employee');
            $onSiteEmployees = $onSiteQ->countAllResults();

            $workingFormatTotal = $remoteEmployees + $onSiteEmployees;
            $remotePercentage = $workingFormatTotal > 0 ? ($remoteEmployees / $workingFormatTotal) : 0;
            $onSitePercentage = $workingFormatTotal > 0 ? (1 - $remotePercentage) : 0;
        } else {
            // Employee personal block
            $employeeId = $user->sub;
            $startOfYear = date('Y-01-01');
            $todayDate = date('Y-m-d');
            $totalLeavesThisWeek = $this->leaveModel
                ->where('user_id', $employeeId)
                ->where('status', 'approved')
                ->where('((start_date >= \'' . $startOfWeek . '\' AND start_date <= \'' . $endOfWeek . '\') OR (end_date >= \'' . $startOfWeek . '\' AND end_date <= \'' . $endOfWeek . '\') OR (start_date <= \'' . $startOfWeek . '\' AND end_date >= \'' . $endOfWeek . '\'))')
                ->countAllResults();
            $totalLeavesThisYear = $this->leaveModel
                ->where('user_id', $employeeId)
                ->where('status', 'approved')
                ->where('YEAR(start_date)', date('Y'))
                ->countAllResults();

            $attRowEmpWeek = $this->attendanceModel
                ->select('COUNT(DISTINCT date) as cnt', false)
                ->where('user_id', $employeeId)
                ->where('date >=', $startOfWeek)
                ->where('date <=', $endOfWeek)
                ->get()->getRow();
            $attendanceCountThisWeek = (int)($attRowEmpWeek->cnt ?? 0);
            $totalTasksThisWeek = $this->taskModel->where('user_id', $employeeId)
                ->where('assigned_date >=', $startOfWeek)
                ->where('assigned_date <=', $endOfWeek)
                ->countAllResults();

            $totalLeaves = $this->leaveModel
                ->where('user_id', $employeeId)
                ->where('status', 'approved')
                ->where('((start_date >= \'' . $startOfMonth . '\' AND start_date <= \'' . $endOfMonth . '\') OR (end_date >= \'' . $startOfMonth . '\' AND end_date <= \'' . $endOfMonth . '\') OR (start_date <= \'' . $startOfMonth . '\' AND end_date >= \'' . $endOfMonth . '\'))')
                ->countAllResults();

            $attRowEmpMonth = $this->attendanceModel
                ->select('COUNT(DISTINCT date) as cnt', false)
                ->where('user_id', $employeeId)
                ->where('date >=', $startOfMonth)
                ->where('date <=', $endOfMonth)
                ->get()->getRow();
            $attendanceCountThisMonth = (int)($attRowEmpMonth->cnt ?? 0);
            $attRowEmpYear = $this->attendanceModel
                ->select('COUNT(DISTINCT date) as cnt', false)
                ->where('user_id', $employeeId)
                ->where('YEAR(date)', date('Y'))
                ->get()->getRow();
            $attendanceCountThisYear = (int)($attRowEmpYear->cnt ?? 0);
            $totalTasksThisMonth = $this->taskModel
                ->where('user_id', $employeeId)
                ->where('assigned_date >=', $startOfMonth)
                ->where('assigned_date <=', $endOfMonth)
                ->countAllResults();
            $totalTasksThisYear = $this->taskModel
                ->where('user_id', $employeeId)
                ->where('assigned_date >=', $startOfYear)
                ->where('assigned_date <=', $endOfYear)
                ->countAllResults();
        }

        if (in_array($role, ['admin', 'hr', 'branch_admin', 'department_manager'])) {
            $deptQuery = $this->userInfoModel
                ->select("department.department_name, COUNT(user_info.id) as employee_count")
                ->join('department', 'department.id = users.department_id', 'left')
                ->join('users', 'users.id = user_info.user_id', 'inner')
                ->where('users.is_deleted', 0)
                ->where('department.department_name IS NOT NULL')
                ->where("(LOWER(user_info.status) NOT IN ('inactive', 'resigned') OR user_info.status IS NULL)");
            if (!empty($filterBranchId)) {
                $deptQuery->where('users.branch_id', (int)$filterBranchId);
            }
            if (!empty($filterDepartmentId)) {
                $deptQuery->where('users.department_id', (int)$filterDepartmentId);
            }
            if ($role === 'branch_admin') {
                $deptQuery->whereIn('users.role', ['employee', 'department_manager']);
            } elseif ($role === 'department_manager') {
                $deptQuery->where('users.role', 'employee');
            }
            $departmentData = $deptQuery->groupBy('department.department_name')
                ->orderBy('department.id', 'DESC')
                ->limit(4)
                ->findAll();
        } else {
            $departmentData = $this->userInfoModel
                ->select("department.department_name, COUNT(user_info.id) as employee_count")
                ->join('department', 'department.id = users.department_id', 'left')
                ->join('users', 'users.id = user_info.user_id', 'inner')
                ->where('users.is_deleted', 0)
                ->where('department.department_name IS NOT NULL')
                ->where('user_info.user_id', $user->sub)
                ->groupBy('department.department_name')
                ->orderBy('department.id', 'DESC')
                ->limit(4)
                ->findAll();
        }

        $hasData = !empty($departmentData);
        $departmentLabels = [];
        $employeeCounts = [];
        foreach ($departmentData as $data) {
            $departmentLabels[] = $data['department_name'];
            $employeeCounts[] = (int) $data['employee_count'];
        }

        // Fetch birthdays for the current week (Monday-Sunday)
        $weekDates = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDates[] = date('m-d', strtotime("monday this week +$i days"));
        }

        $birthdayQuery = $this->userInfoModel->select('user_info.*')
            ->join('users', 'users.id = user_info.user_id', 'inner')
            ->where('users.is_deleted', 0)
            ->where("(LOWER(user_info.status) NOT IN ('inactive', 'resigned') OR user_info.status IS NULL)")
            ->whereIn('DATE_FORMAT(user_info.date_of_birth, "%m-%d")', $weekDates);

        if ($role === 'branch_admin' && !empty($filterBranchId)) {
            $birthdayQuery->where('users.branch_id', (int)$filterBranchId)
                          ->whereIn('users.role', ['employee', 'department_manager']);
        } elseif ($role === 'department_manager') {
            if (!empty($filterDepartmentId)) {
                $birthdayQuery->where('users.department_id', (int)$filterDepartmentId);
            }
            if (!empty($filterBranchId)) {
                $birthdayQuery->where('users.branch_id', (int)$filterBranchId);
            }
            $birthdayQuery->where('users.role', 'employee');
        } elseif (!empty($filterBranchId)) {
            $birthdayQuery->where('users.branch_id', (int)$filterBranchId);
        }
        $birthdayUsers = $birthdayQuery->findAll();

        $currentMonth = date('m');
        $currentYear = date('Y');
        if (in_array($role, ['admin', 'hr', 'branch_admin', 'department_manager'])) {
            $empQ = $this->userInfoModel
                ->select('user_info.*, designation.designation_name, department.department_name')
                ->join('designation', 'designation.id = user_info.designation_id', 'left')
                ->join('department', 'department.id = users.department_id', 'left')
                ->join('users', 'users.id = user_info.user_id', 'inner')
                ->where('users.is_deleted', 0)
                ->where('MONTH(user_info.joining_date)', $currentMonth)
                ->where('YEAR(user_info.joining_date)', $currentYear);

            if ($role === 'branch_admin' && !empty($filterBranchId)) {
                $empQ->where('users.branch_id', (int)$filterBranchId)
                     ->whereIn('users.role', ['employee', 'department_manager']);
            } elseif ($role === 'department_manager') {
                if (!empty($filterDepartmentId)) {
                    $empQ->where('users.department_id', (int)$filterDepartmentId);
                }
                $empQ->where('users.role', 'employee');
            } elseif (in_array($role, ['admin', 'hr'])) {
                if (!empty($filterBranchId)) {
                    $empQ->where('users.branch_id', (int)$filterBranchId);
                }
                $empQ->whereIn('users.role', ['employee', 'hr', 'branch_admin', 'department_manager']);
            }
            $employees = $empQ->orderBy('user_info.joining_date', 'DESC')
                ->limit(5)
                ->findAll();
        } else {
            $employees = $this->userInfoModel
                ->select('user_info.*, designation.designation_name, department.department_name')
                ->join('designation', 'designation.id = user_info.designation_id', 'left')
                ->join('department', 'department.id = users.department_id', 'left')
                ->join('users', 'users.id = user_info.user_id', 'inner')
                ->where('users.is_deleted', 0)
                ->where('user_info.user_id', $user->sub)
                ->where('MONTH(user_info.joining_date)', $currentMonth)
                ->where('YEAR(user_info.joining_date)', $currentYear)
                ->orderBy('user_info.joining_date', 'DESC')
                ->limit(5)
                ->findAll();
        }

        // Fetch the count of completed and scheduled interviews
        if (in_array($role, ['admin', 'hr', 'branch_admin'])) {
            $completedInterviews = $this->interviewModel->where('status', 'completed')->countAllResults();
            $scheduledInterviews = $this->interviewModel->where('status', 'scheduled')->countAllResults();
            $totalInterviews = $completedInterviews + $scheduledInterviews;
        } else {
            $completedInterviews = 0;
            $scheduledInterviews = 0;
            $totalInterviews = 0;
        }

        if (in_array($role, ['admin', 'hr', 'branch_admin', 'department_manager'])) {
            $candidates = $this->candidateModel
                ->select('candidate.candidate_name, candidate.email, candidate.phone_number, candidate.status,jobs.job_title')
                ->join('jobs', 'jobs.id = candidate.job_id', 'left')
                ->join('onboarding', 'onboarding.candidate_id = candidate.id', 'left')
                ->where('(onboarding.onboarding_status IS NULL OR onboarding.onboarding_status != "completed")')
                ->orderBy('candidate.created_at', 'DESC')
                ->findAll();
        } else {
            $candidates = [];
        }

        $hasInterviewData = ($totalInterviews > 0);
        return $this->response->setJSON([
            'totalThisWeekEmployees' => $totalThisWeekEmployees ?? 0,
            'totalLeavesThisWeek' => $totalLeavesThisWeek ?? 0,
            'attendanceCountThisWeek' => $attendanceCountThisWeek ?? 0,
            'totalTasksThisWeek' => $totalTasksThisWeek ?? 0,
            'totalEmployeesThisMonth' => $totalEmployeesThisMonth ?? 0,
            'totalLeaves' => $totalLeaves ?? 0,
            'attendanceCountThisMonth' => $attendanceCountThisMonth ?? 0,
            'totalTasksThisMonth' => $totalTasksThisMonth ?? 0,
            'totalEmployeesThisYear' => $totalEmployeesThisYear ?? 0,
            'totalLeavesThisYear' => $totalLeavesThisYear ?? 0,
            'totalLeavesToday' => !empty($totalLeavesToday) ? $totalLeavesToday : [],
            'todayAttendance' => !empty($todayAttendance) ? $todayAttendance : [],
            'attendanceCountThisYear' => $attendanceCountThisYear ?? 0,
            'totalTasksThisYear' => $totalTasksThisYear ?? 0,
            'departmentLabels' => $departmentLabels,
            'employeeCounts' => $employeeCounts,
            'workingFormatTotal' => $workingFormatTotal ?? 0,
            'remotePercentage' => $remotePercentage ?? 0,
            'onSitePercentage' => $onSitePercentage ?? 0,
            'birthdayUsers' => !empty($birthdayUsers) ? $birthdayUsers : [],
            'employees' => $employees,
            'completedInterviews' => $completedInterviews,
            'scheduledInterviews' => $scheduledInterviews,
            'hasInterviewData' => $hasInterviewData,
            'totalInterviews' => $totalInterviews,
            'candidates' => $candidates ?? 0,
            'latestComplaints' => $this->getLatestComplaints($role, $userId, $filterBranchId, $filterDepartmentId),
        ]);
    }


    private function getLatestComplaints($role, $userId, $filterBranchId = null, $filterDepartmentId = null)
    {
        $complaintModel = new \App\Models\ComplaintModel();
        $query = $complaintModel->select('complaints.*, users.username, user_info.profile_image')
            ->join('users', 'users.id = complaints.user_id')
            ->join('user_info', 'user_info.user_id = users.id', 'left')
            ->where('complaints.status !=', 'resolved');

        if ($role === 'branch_admin' && !empty($filterBranchId)) {
            $query->where('users.branch_id', (int)$filterBranchId);
        } elseif ($role === 'department_manager' && !empty($filterDepartmentId)) {
            $query->where('users.department_id', (int)$filterDepartmentId);
        } elseif ($role === 'employee') {
            $query->where('complaints.user_id', $userId);
        } elseif (!empty($filterBranchId)) {
            $query->where('users.branch_id', (int)$filterBranchId);
        }

        return $query->orderBy('complaints.created_at', 'DESC')
            ->limit(5)
            ->findAll();
    }

    public function getYearlyPerformanceData()
    {
        $user = $this->authService->user();
        $role = $user->role;
        $employeeId = $user->sub;
        $currentYear = date('Y');
        $query = $this->performanceModel
            ->select('MONTH(review_date) as month, AVG(rating) as avg_rating')
            ->where('YEAR(review_date)', $currentYear)
            ->groupBy('month')
            ->orderBy('month', 'ASC');
        if ($role === 'employee') {
            $query->where('user_id', $employeeId);
        }
        $data = $query->findAll();
        return $this->respond([
            'year' => $currentYear,
            'monthly_avg' => $data
        ]);
    }

    public function getTaskData()
    {
        $user = $this->authService->user(); // Get logged-in user
        $role = $user->role; // User role
        $taskModel = new TaskModel();
        $currentYear = date('Y'); // Get current year
        // Base query
        $builder = $taskModel
            ->select("MONTH(assigned_date) as month, COUNT(id) as assigned_count, COUNT(CASE WHEN task_status = 'completed' THEN 1 END) as completed_count")
            ->where('YEAR(assigned_date)', $currentYear) // Only current year
            ->groupBy("month")
            ->orderBy("month", "ASC");
        if ($role != 'admin' && $role != 'hr') {
            // Employee role: Add condition for the logged-in user
            $builder->where('user_id', $user->sub);
        }
        $taskData = $builder->findAll();
        // Format response
        $formattedData = [
            'labels' => [],
            'assigned' => [],
            'completed' => [],
        ];
        foreach ($taskData as $task) {
            $formattedData['labels'][] = date("M", mktime(0, 0, 0, $task['month'], 1));
            $formattedData['assigned'][] = (int) $task['assigned_count'];
            $formattedData['completed'][] = (int) $task['completed_count'];
        }
        return $this->response->setJSON($formattedData);
    }
    // day dashbord call
    public function triggerAutoLeaveOnceDaily()
    {
        $today = date('Y-m-d');

        $lastRunFile = WRITEPATH . 'auto_leave_last_run.txt';
        $lastRun = file_exists($lastRunFile) ? trim(file_get_contents($lastRunFile)) : '';

        // if ($lastRun !== $today) {
        $this->autoMarkAbsentLeaves();
        file_put_contents($lastRunFile, $today);
        // }
    }

    public function autoMarkAbsentLeaves()
    {
        $attendanceModel = new AttendanceModel();
        $leaveModel = new LeaveModel();
        $holidayModel = new HolidayCalendarModel();
        $rulesModel = new CompanyRulesModel();
        $userModel = new UserModel();

        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        // 🔹 Get all holidays in the current month
        $holidays = $holidayModel
            ->where('holiday_date >=', $monthStart)
            ->where('holiday_date <=', $monthEnd)
            ->findAll();
        $holidayDates = array_column($holidays, 'holiday_date');

        // 🔹 Get Saturday off pattern from company rules
        $rule = $rulesModel->first();
        if (!$rule || empty($rule['start_time'])) {
            return;
        }
        $currentTime = new \DateTime('now', new \DateTimeZone('Asia/Kolkata'));
        $officeTime = new \DateTime(
            date('Y-m-d') . ' ' . $rule['start_time'],
            new \DateTimeZone('Asia/Kolkata')
        );

        if ($currentTime < $officeTime) {
            return;
        }

        if ($rule['saturday_off_enabled'] == 1) {
            if ($rule['saturday_off_type'] == 'all') {
                $saturdayPattern = "1, 2, 3, 4, 5";
            } else if ($rule['saturday_off_type'] == 'alternate-even') {
                $saturdayPattern = "2, 4";
            } else if ($rule['saturday_off_type'] == 'alternate-odd') {
                $saturdayPattern = "1, 3, 5";
            } else if ($rule['saturday_off_type'] == 'custom') {
                $saturdayPattern = $rule['saturday_off_pattern'];
            } else {
                $saturdayPattern = "0,0";
            }
        } else {
            $saturdayPattern = "0,0";
        }
        $offSaturdays = $this->getOffSaturdaysInMonth($saturdayPattern);

        // 🧹 Clean up any auto-absence leaves mistakenly created for inactive/resigned/deleted employees or outside active employment period
        $db = \Config\Database::connect();
        $db->query("DELETE leaves FROM leaves
            INNER JOIN users ON users.id = leaves.user_id
            LEFT JOIN user_info ON user_info.user_id = users.id
            WHERE leaves.reason = 'Auto leave for full-day absence'
            AND (
                users.is_deleted = 1
                OR LOWER(user_info.status) IN ('inactive', 'resigned', 'fired', 'removed')
                OR (user_info.last_working_day IS NOT NULL AND leaves.start_date > user_info.last_working_day)
                OR (user_info.joining_date IS NOT NULL AND leaves.start_date < user_info.joining_date)
            )");

        // ✅ Get only ACTIVE employees and HRs (exclude inactive, resigned, fired, removed)
        $users = $db->table('users')
            ->select('users.id, users.role, user_info.status, user_info.joining_date, user_info.last_working_day')
            ->join('user_info', 'user_info.user_id = users.id', 'left')
            ->where('users.is_deleted', 0)
            ->whereIn('users.role', ['employee', 'hr'])
            ->where("(user_info.status IS NULL OR LOWER(user_info.status) NOT IN ('inactive', 'resigned', 'fired', 'removed'))")
            ->where("(user_info.last_working_day IS NULL OR user_info.last_working_day >= CURDATE())")
            ->get()
            ->getResultArray();

        // ✅ Get logged-in user ID and role
        $currentUserId = session()->get('user_id');
        $currentUser = $userModel->find($currentUserId);

        // 🔄 Determine created_by ID
        if ($currentUser && in_array($currentUser['role'], ['admin', 'hr'])) {
            $createdBy = $currentUser['id'];
        } else {
            $admin = $userModel->where('role', 'admin')->first();
            $createdBy = $admin ? $admin['id'] : 1; // fallback to ID 1
        }
        foreach ($users as $user) {
            $userId = $user['id'];
            $joiningDate = !empty($user['joining_date']) ? $user['joining_date'] : null;
            $lastWorkingDay = !empty($user['last_working_day']) ? $user['last_working_day'] : null;

            $current = strtotime($monthStart);
            $end = strtotime($today);

            while ($current <= $end) {
                $date = date('Y-m-d', $current);
                $dayOfWeek = date('w', $current); // 0 = Sunday

                // Skip if date is before employee's joining date or after their last working day
                if ($joiningDate && $date < $joiningDate) {
                    $current = strtotime('+1 day', $current);
                    continue;
                }
                if ($lastWorkingDay && $date > $lastWorkingDay) {
                    $current = strtotime('+1 day', $current);
                    continue;
                }

                if (
                    $dayOfWeek == 0 ||
                    in_array($date, $holidayDates) ||
                    ($dayOfWeek == 6 && in_array($date, $offSaturdays))
                ) {
                    $current = strtotime('+1 day', $current);
                    continue;
                }

                // ✅ Skip if already marked present or half-day
                $existingAttendance = $attendanceModel
                    ->where('user_id', $userId)
                    ->where('date', $date)
                    ->whereIn('status', ['present', 'half-day'])
                    ->first();

                if ($existingAttendance) {
                    $current = strtotime('+1 day', $current);
                    continue;
                }

                $existingLeave = $leaveModel
                    ->where('user_id', $userId)
                    ->where('start_date <=', $date)
                    ->where('end_date >=', $date)
                    ->first();

                if (!$existingLeave) {
                    $leaveModel->insert([
                        'user_id' => $userId,
                        'reason' => 'Auto leave for full-day absence',
                        'start_date' => $date,
                        'end_date' => $date,
                        'no_of_day' => 1,
                        'leave_id' => 1,
                        'status' => 'approved',
                        'paid_days' => 0,
                        'unpaid_days' => 1,
                        'created_by' => $createdBy,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                $current = strtotime('+1 day', $current);
            }
        }
    }

    private function getOffSaturdaysInMonth($pattern)
    {
        $offSaturdays = [];
        $month = date('m');
        $year = date('Y');

        // Convert "1,3" to [1, 3]
        $patternArray = array_map('intval', explode(',', str_replace(' ', '', $pattern)));

        $saturdayCount = 0;

        for ($day = 1; $day <= 31; $day++) {
            if (!checkdate($month, $day, $year))
                break;

            $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            $dayOfWeek = date('w', strtotime($date)); // 6 = Saturday

            if ($dayOfWeek == 6) {
                $saturdayCount++;
                if (in_array($saturdayCount, $patternArray)) {
                    $offSaturdays[] = $date;
                }
            }
        }

        return $offSaturdays;
    }

    public function upload_profile_image_admin()
    {
        $id = $this->request->getPost('user_id');
        $profileImage = $this->request->getFile('image');
        $userInfo = $this->userInfoModel->where('user_id', $id)->first();

        if (!$userInfo) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found.'
            ]);
        }

        $profileImageName = $userInfo['profile_image'];

        if ($profileImage && $profileImage->isValid() && !$profileImage->hasMoved()) {
            $newImageName = $profileImage->getRandomName();
            $profileImage->move(FCPATH . 'upload/', $newImageName);

            if (!empty($profileImageName) && file_exists(FCPATH . 'upload/' . $profileImageName)) {
                unlink(FCPATH . 'upload/' . $profileImageName);
            }

            $profileImageName = $newImageName;
        }

        $this->userInfoModel->where('user_id', $id)->set([
            'profile_image' => $profileImageName,
        ])->update();
        $userInfo = session()->get('userInfo');
        $userInfo['profile_image'] = $profileImageName;
        session()->set('userInfo', $userInfo);
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Profile image updated successfully.'
        ]);
    }

    /**
     * Calculate today's hours worked and remaining hours for an employee
     * @param int $userId
     * @return array|null
     */
    private function calculateTodayHours($userId)
    {
        $today = date('Y-m-d');

        // Get ALL today's attendance records (not just the latest)
        $todayAttendanceRecords = $this->attendanceModel
            ->where('date', $today)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        // Get company rules for standard working hours
        $companyRulesModel = new CompanyRulesModel();
        $companyRule = $companyRulesModel->orderBy('id', 'DESC')->first();

        $standardHoursPerDay = 8.0; // Default 8 hours
        if (!empty($companyRule) && isset($companyRule['working_hours_per_day'])) {
            $standardHoursPerDay = (float) $companyRule['working_hours_per_day'];
        }

        $standardHoursSeconds = $standardHoursPerDay * 3600;

        // If no attendance records, return default values
        if (empty($todayAttendanceRecords)) {
            return [
                'hours_worked' => '00:00:00',
                'hours_worked_seconds' => 0,
                'remaining_hours' => '00:00:00',
                'remaining_hours_seconds' => 0,
                'standard_hours' => sprintf('%02d:%02d:%02d', floor($standardHoursPerDay), floor(($standardHoursPerDay - floor($standardHoursPerDay)) * 60), 0),
                'is_checked_in' => false,
                'is_checked_out' => false
            ];
        }

        $totalWorkedSeconds = 0;
        $completedHoursSeconds = 0; // Hours from completed check-in/check-out pairs
        $currentTime = date('H:i:s');
        $latestCheckInTime = null;
        $latestCheckOutTime = null;
        $isCheckedIn = false;
        $isCheckedOut = false;
        $mealBreakSeconds = 0;

        // Default meal break (30 minutes)
        if (!empty($companyRule) && isset($companyRule['lunch_break'])) {
            $mealBreakParts = explode(':', $companyRule['lunch_break']);
            $mealBreakSeconds = ($mealBreakParts[0] * 3600) + ($mealBreakParts[1] * 60) + ($mealBreakParts[2] ?? 0);
        } else {
            $mealBreakSeconds = 30 * 60; // Default 30 minutes
        }

        // Get the latest record (most recent) to determine current status
        // Records are ordered by created_at ASC, so the last one in array is latest
        $latestRecord = end($todayAttendanceRecords);
        $latestRecordIndex = key($todayAttendanceRecords);
        reset($todayAttendanceRecords);

        // Determine current check-in/check-out status from latest record
        if ($latestRecord) {
            if (!empty($latestRecord['check_in_time']) && empty($latestRecord['check_out_time'])) {
                // Latest record has check-in but no check-out = currently checked in
                $isCheckedIn = true;
                $isCheckedOut = false;
                $latestCheckInTime = $latestRecord['check_in_time'];
            } elseif (!empty($latestRecord['check_in_time']) && !empty($latestRecord['check_out_time'])) {
                // Latest record has both = checked out
                $isCheckedIn = false;
                $isCheckedOut = true;
                $latestCheckInTime = $latestRecord['check_in_time'];
                $latestCheckOutTime = $latestRecord['check_out_time'];
            }
        }

        // Process all attendance records to calculate total hours
        $recordIndex = 0;
        foreach ($todayAttendanceRecords as $attendance) {
            $isLatestRecord = ($recordIndex === $latestRecordIndex);

            // If this record has both check-in and check-out, it's a completed session
            if (!empty($attendance['check_in_time']) && !empty($attendance['check_out_time'])) {
                $workHoursSeconds = 0;

                // Calculate actual session duration first to validate stored work_hours
                $checkInParts = explode(':', $attendance['check_in_time']);
                $checkOutParts = explode(':', $attendance['check_out_time']);

                $checkInSeconds = ($checkInParts[0] * 3600) + ($checkInParts[1] * 60) + ($checkInParts[2] ?? 0);
                $checkOutSeconds = ($checkOutParts[0] * 3600) + ($checkOutParts[1] * 60) + ($checkOutParts[2] ?? 0);

                $rawSessionSeconds = max(0, $checkOutSeconds - $checkInSeconds);

                // Try to use stored work_hours if available and valid (already has meal break subtracted)
                if (!empty($attendance['work_hours']) && $attendance['work_hours'] !== '00:00:00') {
                    // Parse work_hours (format: HH:MM:SS)
                    $workHoursParts = explode(':', $attendance['work_hours']);
                    $storedWorkHoursSeconds = ($workHoursParts[0] * 3600) + ($workHoursParts[1] * 60) + ($workHoursParts[2] ?? 0);

                    // Validate: stored work_hours should be reasonable (not more than raw session, not negative)
                    // If stored value seems wrong, recalculate
                    if ($storedWorkHoursSeconds > 0 && $storedWorkHoursSeconds <= $rawSessionSeconds) {
                        $workHoursSeconds = $storedWorkHoursSeconds;
                    }
                }

                // If work_hours is missing, zero, or invalid, calculate manually
                if ($workHoursSeconds <= 0) {
                    $sessionSeconds = $rawSessionSeconds;

                    // Only subtract meal break if total gross day duration is at least 5 hours
                    // Calculate total gross duration for all records
                    $totalGrossDaySeconds = 0;
                    foreach ($todayAttendanceRecords as $r) {
                        if (!empty($r['check_in_time']) && !empty($r['check_out_time'])) {
                            $rCi = explode(':', $r['check_in_time']);
                            $rCo = explode(':', $r['check_out_time']);
                            $totalGrossDaySeconds += (($rCo[0] * 3600 + $rCo[1] * 60 + ($rCo[2] ?? 0)) - ($rCi[0] * 3600 + $rCi[1] * 60 + ($rCi[2] ?? 0)));
                        }
                    }

                    if ($totalGrossDaySeconds >= (5 * 3600)) {
                        $sessionSeconds = max(0, $sessionSeconds - $mealBreakSeconds);
                    }

                    $workHoursSeconds = $sessionSeconds;
                }

                // Add to totals
                $totalWorkedSeconds += $workHoursSeconds;
                $completedHoursSeconds += $workHoursSeconds;
            } elseif (!empty($attendance['check_in_time']) && empty($attendance['check_out_time'])) {
                // Only process if this is the latest active session
                if ($isLatestRecord) {
                    // Use DateTime with explicit IST timezone to avoid system timezone issues
                    $tz = new \DateTimeZone('Asia/Kolkata');
                    $checkInDt = new \DateTime($today . ' ' . $attendance['check_in_time'], $tz);
                    $currentDt = new \DateTime('now', $tz);

                    // Calculate worked seconds for active session (server-accurate)
                    $activeSessionSeconds = max(0, $currentDt->getTimestamp() - $checkInDt->getTimestamp());

                    // Store elapsed at page-load for the frontend counter
                    $elapsedSecondsAtLoad = $activeSessionSeconds;

                    // Don't subtract meal break for active session - show actual elapsed time
                    $totalWorkedSeconds += $activeSessionSeconds;
                }
            }

            $recordIndex++;
        }

        // Format hours worked
        $hours = floor($totalWorkedSeconds / 3600);
        $minutes = floor(($totalWorkedSeconds % 3600) / 60);
        $seconds = $totalWorkedSeconds % 60;
        $hoursWorkedFormatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        // Calculate remaining hours
        $remainingSeconds = max(0, $standardHoursSeconds - $totalWorkedSeconds);
        $remainingHours = floor($remainingSeconds / 3600);
        $remainingMinutes = floor(($remainingSeconds % 3600) / 60);
        $remainingSecs = $remainingSeconds % 60;
        $remainingHoursFormatted = sprintf('%02d:%02d:%02d', $remainingHours, $remainingMinutes, $remainingSecs);

        // Format standard hours
        $standardHoursFormatted = sprintf('%02d:%02d:%02d', floor($standardHoursPerDay), floor(($standardHoursPerDay - floor($standardHoursPerDay)) * 60), 0);

        // Compute elapsed seconds using DateTime with IST timezone (fixes UTC system timezone bug)
        $elapsedSecondsAtLoad = 0;
        if ($isCheckedIn && !empty($latestCheckInTime)) {
            $tz = new \DateTimeZone('Asia/Kolkata');
            $checkInDt = new \DateTime($today . ' ' . $latestCheckInTime, $tz);
            $currentDt = new \DateTime('now', $tz);
            $elapsedSecondsAtLoad = max(0, $currentDt->getTimestamp() - $checkInDt->getTimestamp());
        }



        
        return [
            'hours_worked' => $hoursWorkedFormatted,
            'hours_worked_seconds' => $totalWorkedSeconds,
            'completed_hours_seconds' => $completedHoursSeconds,
            'remaining_hours' => $remainingHoursFormatted,
            'remaining_hours_seconds' => $remainingSeconds,
            'standard_hours' => $standardHoursFormatted,
            'standard_hours_decimal' => $standardHoursPerDay,
            'standard_hours_seconds' => $standardHoursSeconds,
            'meal_break_seconds' => $mealBreakSeconds,
            'is_checked_in' => $isCheckedIn,
            'is_checked_out' => $isCheckedOut,
            'check_in_time' => $latestCheckInTime,
            'check_out_time' => $latestCheckOutTime,
            'elapsed_seconds_at_load' => $elapsedSecondsAtLoad  // Pre-calculated by server (IST-accurate)
        ];
    }
}
