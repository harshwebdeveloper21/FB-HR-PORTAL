<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Dompdf\Dompdf;
use App\Models\AccountDetailModel;
use App\Models\PayrollModel;
use App\Models\UserInfoModel;
use App\Models\LeaveTypeModel;
use App\Models\LeaveModel;
use App\Models\CompanyLogoModel;
use App\Models\DepartmentModel;
use App\Models\DesignationModel;
use App\Models\OnboardingModel;
use App\Models\CompanyRulesModel;
use App\Models\AttendanceModel;
use App\Models\HolidayCalendarModel;
use App\Services\AuthService;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\EmployeeLeaveModel;
use App\Traits\CompanyRuleTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PayrollController extends ResourceController
{
    use CompanyRuleTrait;

    private $payrollModel;
    private $authService;

    public function __construct()
    {
        $this->payrollModel = new PayrollModel();
        $this->authService = new AuthService(service("request"));
    }

    public function page()
    {
        $userModel = new \App\Models\UserModel();
        $role = session()->get("role"); // Assuming the user's role is stored in the session

        $employees = $userModel
            ->where("is_deleted", 0)
            ->where("role", "employee")
            ->findAll();

        if ($role === "admin") {
            $reviewers = $userModel
                ->where("is_deleted", 0)
                ->whereIn("role", ["hr", "employee"])
                ->findAll();
        } elseif ($role === "hr") {
            $reviewers = $employees;
        } else {
            $reviewers = [];
        }
        $leaveTypeModel = new LeaveTypeModel();

        $leaveTypes = $leaveTypeModel->findAll();
        return view("payroll/payroll", [
            "employees" => $reviewers,
            "leaveTypes" => $leaveTypes, // Pass the filtered reviewers array
        ]);
    }

    public function display()
    {
        return view("payroll/view");
    }

    // GET: List payroll details for all users
    public function index()
    {
        $payroll = $this->payrollModel->findAll();
        return $this->response->setJSON([
            "status" => "success",
            "data" => $payroll,
        ]);
    }

    public function getSalary()
    {
        $userId = $this->request->getGet("user_id");
        $monthYear = $this->request->getGet("month_year"); // format: YYYY-MM

        $userModel = new UserInfoModel();
        $accountModel = new AccountDetailModel();
        $companyRulesModel = new CompanyRulesModel();

        $user = $userModel->where("user_id", $userId)->first();
        $account = $accountModel->where("user_id", $userId)->first();
        $company_rules = $companyRulesModel->first();

        // Determine which salary was active for the given month
        $effectiveSalary = (float)($user["salary"] ?? 0);

        if (!empty($monthYear)) {
            // Parse the payroll month: use the last day of the month as the cutoff
            $payrollMonthEnd = date('Y-m-t', strtotime($monthYear . '-01'));

            // Fetch increment history ordered by effective_from_date DESC
            $db = \Config\Database::connect();
            $incrementHistory = $db->table('salary_increment_history')
                ->where('employee_id', $userId)
                ->orderBy('effective_from_date', 'DESC')
                ->get()
                ->getResultArray();

            if (!empty($incrementHistory)) {
                // Find the most recent increment whose effective_from_date <= last day of payroll month
                $salaryForMonth = null;
                foreach ($incrementHistory as $record) {
                    if ($record['effective_from_date'] <= $payrollMonthEnd) {
                        $salaryForMonth = (float)$record['new_salary'];
                        break;
                    }
                }

                if ($salaryForMonth !== null) {
                    // An increment was active during this payroll month
                    $effectiveSalary = $salaryForMonth;
                } else {
                    // All increments are after this payroll month — use the oldest previous_salary
                    $oldest = end($incrementHistory);
                    $effectiveSalary = (float)($oldest['previous_salary'] ?? $effectiveSalary);
                }
            }
        }

        return $this->response->setJSON([
            "salary"           => $effectiveSalary,
            "tax"              => $company_rules["tax"] ?? 0,
            "salary_above_tax" => $company_rules["salary_above_tax"] ?? 0,
            "acc_number"       => $account["acc_number"] ?? "",
            "bank_name"        => $account["bank_name"] ?? "",
            "ifsc_code"        => $account["ifsc_code"] ?? "",
            "acc_in_name"      => $account["acc_in_name"] ?? "",
            "branch_name"      => $account["branch_name"] ?? "",
            "branch_code"      => $account["branch_code"] ?? "",
            "company_rules"    => $company_rules,
        ]);
    }

    public function create()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized(
                "Unauthorized: Token missing or invalid",
            );
        }

        if (!in_array($user->role, ["admin", "hr"])) {
            return $this->failForbidden(
                "Forbidden: You do not have access to this resource",
            );
        }

        $data = $this->request->getPost();

        $userId = $data["user_id"] ?? null;
        $paymentDate = $data["payment_date"] ?? null;

        $year = date("Y", strtotime($paymentDate));
        $month = date("m", strtotime($paymentDate));
        $monthYear = sprintf("%04d-%02d", $year, $month);
        $existingPayroll = $this->payrollModel
            ->where("user_id", $userId)
            ->where("month_year", $monthYear)
            ->first();

        if ($existingPayroll) {
            return $this->respond(
                [
                    "status" => "error",
                    "message" =>
                        "This Employee Payroll record already exists for this month.",
                ],
                400,
            );
        }
        $validationRules = [
            "user_id" => [
                "rules" => "required|integer",
                "errors" => [
                    "required" => "Employee field is required.",
                    "integer" => "Employee ID must be a valid number.",
                ],
            ],
            "salary_amount" => [
                "rules" => "required|decimal",
                "errors" => [
                    "required" => "Salary Amount field is required.",
                    "decimal" => "Salary Amount must be a valid decimal value.",
                ],
            ],
            "net_salary" => [
                "rules" => "required|decimal",
                "errors" => [
                    "required" => "Net Salary field is required.",
                    "decimal" => "Net Salary must be a valid decimal value.",
                ],
            ],
            "payment_date" => [
                "rules" => "permit_empty|valid_date",
                "errors" => [
                    "required" => "Payment Date field is required.",
                    "valid_date" =>
                        "Please enter a valid date format (YYYY-MM-DD).",
                ],
            ],
            "payment_status" => [
                "rules" => "permit_empty|string",
                "errors" => [
                    "required" => "Payment Status field is required.",
                    "string" => "Payment Status must be a valid string.",
                ],
            ],
            "month_year" => [
                "rules" => 'required|regex_match[/^\d{4}-(0[1-9]|1[0-2])$/]',
                "errors" => [
                    "required" => "Month & Year field is required.",
                    "regex_match" =>
                        "Month & Year must be in YYYY-MM format (e.g., 2025-04).",
                ],
            ],
        ];

        if (!$this->validate($validationRules)) {
            return $this->respond(
                [
                    "status" => "error",
                    "message" => $this->validator->getErrors(),
                ],
                400,
            );
        }
        $data["created_by"] = $user->sub;
        $data["total_half_day"] = $data["total_halfday_leaves"] ?? 0;

        if ($this->payrollModel->insert($data)) {
            $payrollId = $this->payrollModel->insertID(); // ✅ Get the last inserted ID

            if ($payrollId) {
                // Notify Admin, HR, and the specific Employee
                $notificationModel = new \App\Models\NotificationModel();
                $userModel = new \App\Models\UserModel();
                $employee = $userModel->find($data["user_id"]);
                $sender = $userModel->find($user->sub);

                $recipients = $userModel
                    ->whereIn("role", ["admin", "hr"])
                    ->orWhere("id", $data["user_id"]) // Include employee
                    ->findAll();

                foreach ($recipients as $recipient) {
                    $notificationModel->insert([
                        "sender_id" => $user->sub,
                        "recipient_id" => $recipient["id"],
                        "data" => json_encode([
                            "type" => "payroll",
                            "username" => $sender["username"],
                            "employee" => $employee["username"],
                            "user_id" => $data["user_id"],
                            "message" =>
                                "Payroll processed for " .
                                $employee["username"] .
                                " on " .
                                date(
                                    "M d, Y",
                                    strtotime($data["payment_date"]),
                                ),
                        ]),
                        "is_read" => 0,
                    ]);
                }
            }

            return $this->respond(
                [
                    "status" => "success",
                    "message" => "Payroll record added successfully",
                    "data" => $this->payrollModel->find(
                        $this->payrollModel->insertID(),
                    ),
                ],
                201,
            );
        }

        return $this->respond(
            ["status" => "error", "message" => "Failed to add payroll record"],
            500,
        );
    }

    // Get All Payroll Records (Admin & HR Only)
    // public function getAll()
    // {
    //     $user = $this->authService->check();
    //     if (!$user) {
    //         return $this->failUnauthorized(
    //             "Unauthorized: Token missing or invalid",
    //         );
    //     }

    //     // Get month filter from query params
    //     $month = $this->request->getGet("month");

    //     $this->payrollModel
    //         ->select(
    //             "payroll.id, payroll.user_id as employee_id, payroll.salary_amount, payroll.month_year, payroll.net_salary, payroll.payment_date, payroll.created_at, user_info.profile_image, users.username",
    //         )
    //         ->join("users", "users.id = payroll.user_id")
    //         ->join("user_info", "user_info.user_id = payroll.user_id");

    //     // Apply month filter if provided
    //     if ($month) {
    //         $this->payrollModel->where("payroll.month_year", $month);
    //     }

    //     // Role-based filtering
    //     if ($user->role === "admin") {
    //         // Admin can see all records (no filter)
    //         $records = $this->payrollModel
    //             ->orderBy("created_at", "DESC")
    //             ->findAll();
    //     } elseif ($user->role === "hr") {
    //         // HR can only see employee records (exclude admin & HR)
    //         $records = $this->payrollModel
    //             ->where("users.role", "employee")
    //             ->orderBy("created_at", "DESC")
    //             ->findAll();
    //     } elseif ($user->role === "employee") {
    //         // Employee can only see their own records
    //         $records = $this->payrollModel
    //             ->where("payroll.user_id", $user->sub)
    //             ->orderBy("created_at", "DESC")
    //             ->findAll();
    //     } else {
    //         return $this->failForbidden("Forbidden: Unauthorized role");
    //     }

    //     return $this->respond(["status" => "success", "data" => $records]);
    // }

    public function getAll()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized(
                "Unauthorized: Token missing or invalid",
            );
        }

        // Get month filter from query params
        $month = $this->request->getGet("month");

        $this->payrollModel
            ->select(
                "payroll.id, payroll.user_id as employee_id, payroll.salary_amount, payroll.month_year, payroll.net_salary, payroll.payment_date, payroll.created_at, payroll.total_leaves, payroll.total_half_day, payroll.used_paid_leaves, payroll.used_sick_leaves, payroll.tax_deduction, payroll.salary_deduction, payroll.remaining_paid_leaves, payroll.remaining_sick_leaves, payroll.overtime_pay, payroll.total_overtime_hours, user_info.profile_image, users.username, employee_leaves.casual_leave",
            )
            ->join("users", "users.id = payroll.user_id")
            ->join("user_info", "user_info.user_id = payroll.user_id")
            ->join("employee_leaves", "employee_leaves.employee_id = payroll.user_id", "left");

        // Apply month filter if provided
        if ($month) {
            $this->payrollModel->where("payroll.month_year", $month);
        }

        // Role-based filtering
        if ($user->role === "admin") {
            // Admin can see all records (no filter)
            $records = $this->payrollModel
                ->orderBy("users.username", "ASC")
                ->findAll();
        } elseif ($user->role === "hr") {
            // HR can see employee records and their own records
            $records = $this->payrollModel
                ->groupStart()
                    ->where("users.role", "employee")
                    ->orWhere("payroll.user_id", $user->sub)
                ->groupEnd()
                ->orderBy("users.username", "ASC")
                ->findAll();
        } elseif ($user->role === "employee") {
            // Employee can only see their own records
            $records = $this->payrollModel
                ->where("payroll.user_id", $user->sub)
                ->orderBy("users.username", "ASC")
                ->findAll();
        } else {
            return $this->failForbidden("Forbidden: Unauthorized role");
        }

        return $this->respond(["status" => "success", "data" => $records]);
    }
    // Get Payroll Records for a Specific Employee (Employee-Specific Access)
    public function getByEmployee($employeeId = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized(
                "Unauthorized: Token missing or invalid",
            );
        }

        // Employees can only view their own payroll records
        if ($user->role === "employee" && $user->sub !== $employeeId) {
            return $this->failForbidden(
                "Forbidden: You can only access your own performance records",
            );
        }

        $records = $this->payrollModel->where("id", $employeeId)->findAll();
        return $this->respond(["status" => "success", "data" => $records]);
    }

    // Update Payroll Record (Admin & HR Only)
    public function update($id = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized(
                "Unauthorized: Token missing or invalid",
            );
        }

        if (!in_array($user->role, ["admin", "hr"])) {
            return $this->failForbidden(
                "Forbidden: You do not have access to this resource",
            );
        }

        // Check if payroll record exists
        $existingPayroll = $this->payrollModel->find($id);
        if (!$existingPayroll) {
            return $this->respond(
                ["status" => "error", "message" => "Payroll record not found"],
                404,
            );
        }

        $data = $this->request->getPost();

        // Validation with custom error messages
        $validationRules = [
            "salary_amount" => [
                "rules" => "required|decimal",
                "errors" => [
                    "required" => "Salary Amount is required.",
                    "decimal" => "Salary Amount must be a valid decimal value.",
                ],
            ],
            "tax_deduction" => [
                // permit_empty so a 0 value (or missing field) passes cleanly
                "rules" => "permit_empty|decimal",
                "errors" => [
                    "decimal" => "Tax Deduction must be a valid decimal value.",
                ],
            ],
            // "bonuses" is intentionally omitted — the field is not rendered in the
            // Edit Payroll form, so requiring it always caused a 400 Bad Request.
            "net_salary" => [
                "rules" => "required|decimal",
                "errors" => [
                    "required" => "Net Salary is required.",
                    "decimal" => "Net Salary must be a valid decimal value.",
                ],
            ],
            "payment_date" => [
                "rules" => "permit_empty|valid_date",
                "errors" => [
                    "required" => "Payment Date is required.",
                    "valid_date" =>
                        "Please enter a valid date format (YYYY-MM-DD).",
                ],
            ],
            "payment_status" => [
                "rules" => "permit_empty|string",
                "errors" => [
                    "required" => "Payment Status is required.",
                    "string" => "Payment Status must be a valid string.",
                ],
            ],
            "month_year" => [
                "rules" => 'required|regex_match[/^\d{4}-(0[1-9]|1[0-2])$/]',
                "errors" => [
                    "required" => "Month & Year is required.",
                    "regex_match" =>
                        "Month & Year must be in YYYY-MM format (e.g., 2025-04).",
                ],
            ],
        ];

        if (!$this->validate($validationRules)) {
            return $this->respond(
                [
                    "status" => "error",
                    "message" => $this->validator->getErrors(),
                ],
                400,
            );
        }

        // Map total_halfday_leaves to total_half_day (consistent with create method)
        if (isset($data["total_halfday_leaves"])) {
            $data["total_half_day"] = $data["total_halfday_leaves"];
            unset($data["total_halfday_leaves"]);
        }

        // Remove fields that shouldn't be updated
        unset($data["id"]);
        unset($data["created_by"]);
        unset($data["created_at"]);

        // Update the record
        if ($this->payrollModel->update($id, $data)) {
            return $this->respond([
                "status" => "success",
                "message" => "Payroll record updated successfully",
                "data" => $this->payrollModel->find($id),
            ]);
        }

        return $this->respond(
            [
                "status" => "error",
                "message" => "Failed to update payroll record",
            ],
            500,
        );
    }

    // Delete Payroll Record (Admin Only)
    public function delete($id = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized(
                "Unauthorized: Token missing or invalid",
            );
        }

        if (!in_array($user->role, ["admin", "hr"])) {
            return $this->failForbidden(
                "Forbidden: Only Admin and HR can delete payroll records",
            );
        }

        if ($this->payrollModel->delete($id)) {
            return $this->respond([
                "status" => "success",
                "message" => "Payroll record deleted successfully",
            ]);
        }

        return $this->respond(
            [
                "status" => "error",
                "message" => "Failed to delete payroll record",
            ],
            500,
        );
    }

    public function profilePage()
    {
        return view("payroll/profile");
    }

    public function getProfile($id = null)
    {
        $user = $this->authService->check();
        $companyRulesModel = new CompanyRulesModel();
        $company_rules = $companyRulesModel->first();

        $salary_above_tax = $company_rules["salary_above_tax"] ?? 0;
        $tax = $company_rules["tax"] ?? 0;
        if (!$user) {
            return $this->failUnauthorized(
                "Unauthorized: Token missing or invalid",
            );
        }

        // Fetch payroll details along with user and designation info
        $record = $this->payrollModel
            ->select(
                "payroll.*,payroll.salary_amount, users.username as employee_name, user_info.profile_image, user_info.firstname, user_info.lastname, user_info.email, user_info.employee_id,leave_type.leave_type, designation.designation_name as designation, department.department_name as department",
            )
            ->join("users", "users.id = payroll.user_id", "left")
            ->join("leave_type", "leave_type.id = payroll.leave_type", "left")
            ->join("user_info", "user_info.user_id = users.id", "left")
            ->join("designation", "designation.id = user_info.designation_id", "left")
            ->join("department", "department.id = user_info.department_id", "left")
            ->where("payroll.id", $id)
            ->first();

        if (!$record) {
            return $this->failNotFound("Payroll record not found");
        }

        $workingDays = 'N/A';
        $unpaidLeaves = 0;
        $monthYear = is_array($record) ? ($record['month_year'] ?? '') : ($record->month_year ?? '');
        
        // Calculate working days dynamically
        if (!empty($monthYear)) {
            $parts = explode('-', $monthYear);
            if (count($parts) === 2) {
                $year = (int)$parts[0];
                $monthNum = (int)$parts[1];
                
                $holidayModel = new \App\Models\HolidayCalendarModel();
                $holidayRows = $holidayModel
                    ->where("MONTH(holiday_date)", $monthNum)
                    ->where("YEAR(holiday_date)", $year)
                    ->findAll();
                $holidayDates = array_column($holidayRows, "holiday_date");
                
                $workingDaysData = $this->getWorkingDaysData($monthNum, $year, $company_rules, $holidayDates);
                $workingDays = $workingDaysData['working_days'] ?? 'N/A';
            }
        }

        // Calculate unpaid leaves dynamically
        $totalLeaves = (float)(is_array($record) ? ($record["total_leaves"] ?? 0) : ($record->total_leaves ?? 0));
        $usedPaidLeaves = (float)(is_array($record) ? ($record["used_paid_leaves"] ?? 0) : ($record->used_paid_leaves ?? 0));
        $usedSickLeaves = (float)(is_array($record) ? ($record["used_sick_leaves"] ?? 0) : ($record->used_sick_leaves ?? 0));
        $halfDaysCount = (float)(is_array($record) ? ($record["total_half_day"] ?? 0) : ($record->total_half_day ?? 0));
        $unpaidLeaves = max($totalLeaves + ($halfDaysCount * 0.5) - $usedPaidLeaves - $usedSickLeaves, 0);

        if (is_array($record)) {
            $record['working_days'] = $workingDays;
            $record['unpaid_leaves'] = $unpaidLeaves;
        } else {
            $record->working_days = $workingDays;
            $record->unpaid_leaves = $unpaidLeaves;
        }

        return $this->respond([
            "status" => "success",
            "data" => $record,
            "salary_above_tax" => $salary_above_tax,
            "tax" => $tax,
        ]);
    }

    public function getMonthLeaves()
    {
        $userId = $this->request->getPost("user_id");
        $month = (int) $this->request->getPost("month");
        $year = (int) $this->request->getPost("year");

        if (!$userId || $month < 1 || $month > 12 || $year < 2000) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Invalid input data",
            ]);
        }

        $leaveModel = new LeaveModel();
        $attendanceModel = new AttendanceModel();

        $startOfMonth =
            "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-01";
        $endOfMonth = date("Y-m-t", strtotime($startOfMonth));

        $companyRulesModel = new CompanyRulesModel();
        $leaveModel = new LeaveModel();

        $rules = $companyRulesModel->first();

        $holidayCalendarModel = new HolidayCalendarModel();
        $holidayRows = $holidayCalendarModel
            ->where("holiday_date >=", $startOfMonth)
            ->where("holiday_date <=", $endOfMonth)
            ->findAll();
        $holidayDates = array_column($holidayRows, "holiday_date");

        $totalLeaveDaysInMonth = $this->countMonthlyLeaves(
            $userId,
            "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT),
            $rules,
            $leaveModel,
            $holidayDates,
        );

        // Half-day leaves from attendance – exclude Saturdays that are company-wide
        // scheduled half-days (saturday_half_day_enabled). Those are not employee-specific
        // absences and must not inflate the deduction count.
        $satHalfDayDates = $this->getSaturdayHalfDayDates($month, $year, $rules);
        $halfDayRows = $attendanceModel
            ->where("user_id", $userId)
            ->where("status", "half-day")
            ->where("date >=", $startOfMonth)
            ->where("date <=", $endOfMonth)
            ->findAll();
        $halfDays = 0;
        foreach ($halfDayRows as $hdRow) {
            $dateKey = substr($hdRow["date"], 0, 10);
            if (!isset($satHalfDayDates[$dateKey]) && !$this->isWeekOffDay($dateKey, $rules)) {
                $halfDays++;
            }
        }
       
        return $this->response->setJSON([
            "status" => "success",
            "total_leaves" => $totalLeaveDaysInMonth,
            "total_half_day_leaves" => $halfDays,
        ]);
    }

    public function getWorkedHours()
    {
        $userId = $this->request->getPost("user_id");
        $month = (int) $this->request->getPost("month");
        $year = (int) $this->request->getPost("year");

        if (!$userId || $month < 1 || $month > 12 || $year < 2000) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Invalid input data",
            ]);
        }

        $attendanceModel = new AttendanceModel();

        $startOfMonth =
            "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-01";
        $endOfMonth = date("Y-m-t", strtotime($startOfMonth));

        // Convert TIME to seconds and sum
        $result = $attendanceModel
            ->select(
                "
                SUM(TIME_TO_SEC(work_hours)) AS work_seconds,
                SUM(TIME_TO_SEC(overtime)) AS overtime_seconds
            ",
            )
            ->where("user_id", $userId)
            ->where("date >=", $startOfMonth)
            ->where("date <=", $endOfMonth)
            ->first();

        $totalSeconds =
            ($result["work_seconds"] ?? 0) + ($result["overtime_seconds"] ?? 0);

        // Convert seconds → hours (decimal)
        $totalHours = round($totalSeconds / 3600, 2);
        $overtimeHours = round(($result["overtime_seconds"] ?? 0) / 3600, 2);

        return $this->response->setJSON([
            "status" => "success",
            "total_worked_hours" => $totalHours,
            "total_overtime_hours" => $overtimeHours,
            "total_worked_time" => gmdate("H:i:s", $totalSeconds),
        ]);
    }
    /**
     * Get working days for a month (same logic as group salary-details: holidays and Sundays excluded).
     */
    public function getWorkingDays()
    {
        $month = (int) $this->request->getPost("month");
        $year = (int) $this->request->getPost("year");

        if ($month < 1 || $month > 12 || $year < 2000) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Invalid month/year",
            ]);
        }

        $companyRulesModel = new CompanyRulesModel();
        $holidayCalendarModel = new HolidayCalendarModel();
        $rules = $companyRulesModel->first();

        $startOfMonth = sprintf("%04d-%02d-01", $year, $month);
        $endOfMonth = date("Y-m-t", strtotime($startOfMonth));
        $holidayRows = $holidayCalendarModel
            ->where("holiday_date >=", $startOfMonth)
            ->where("holiday_date <=", $endOfMonth)
            ->findAll();
        $holidayDates = array_column($holidayRows, "holiday_date");

        $workingDaysData = $this->getWorkingDaysData($month, $year, $rules, $holidayDates);
        $workingDays = $workingDaysData["working_days"];
        $daysInMonth = $workingDaysData["total_days"];
        $sundays = $workingDaysData["sundays"];
        $saturdays = $workingDaysData["saturdays"];

        return $this->response->setJSON([
            "status" => "success",
            "working_days" => max($workingDays, 1),
            "total_days" => $daysInMonth,
            "sundays" => $sundays,
            "saturdays" => $saturdays,
        ]);
    }

    public function calculatePayroll()
    {
        $userId = $this->request->getPost("user_id");
        $month = (int) $this->request->getPost("month");
        $year = (int) $this->request->getPost("year");
        $payrollType = $this->request->getPost("payroll_type");
        $baseSalary = (float) $this->request->getPost("salary_amount");
        $usedPaidLeaves =
            (float) ($this->request->getPost("usedPaidLeaves") ?? 0);

        $companyRulesModel = new CompanyRulesModel();
        $attendanceModel = new AttendanceModel();
        $leaveModel = new LeaveModel();
        $holidayCalendarModel = new HolidayCalendarModel();

        $rules = $companyRulesModel->first();

        $startOfMonth =
            "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-01";
        $endOfMonth = date("Y-m-t", strtotime($startOfMonth));

        $holidayRows = $holidayCalendarModel
            ->where("holiday_date >=", $startOfMonth)
            ->where("holiday_date <=", $endOfMonth)
            ->findAll();
        $holidayDates = array_column($holidayRows, "holiday_date");

        $workingDaysData = $this->getWorkingDaysData($month, $year, $rules, $holidayDates);
        $workingDays = $workingDaysData["working_days"];

        $totalLeavesInput = $this->request->getPost("total_leaves");
        if ($totalLeavesInput !== null) {
            $totalLeaves = (float) $totalLeavesInput;
        } else {
            $totalLeaves = $this->countMonthlyLeaves(
                $userId,
                "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT),
                $rules,
                $leaveModel,
                $holidayDates,
            );
        }

        // Half-days – fetch rows so we can base deduction on missing hours (using gross worked time).
        $halfDayRows = $attendanceModel
            ->select("work_hours, date, status, check_in_time, check_out_time")
            ->where("user_id", $userId)
            ->where("status", "half-day")
            ->where("date >=", $startOfMonth)
            ->where("date <=", $endOfMonth)
            ->findAll();

        // Exclude Saturdays that are company-wide scheduled half-days; they are not
        // employee-specific absences and must not inflate the deduction count.
        // Also exclude any week-off days (Sundays and Week-off Saturdays).
        $satHalfDayDatesCalc = $this->getSaturdayHalfDayDates($month, $year, $rules);
        $halfDayRows = array_values(array_filter($halfDayRows, function ($r) use ($satHalfDayDatesCalc, $rules) {
            $dateKey = substr($r['date'], 0, 10);
            return !isset($satHalfDayDatesCalc[$dateKey]) && !$this->isWeekOffDay($dateKey, $rules);
        }));

        $fullDayHoursRule = isset($rules["working_hours_per_day"])
            ? (float) $rules["working_hours_per_day"]
            : 8.0;

        $halfDaysInput = $this->request->getPost("total_halfday_leaves");
        if ($halfDaysInput !== null) {
            $halfDays = (float) $halfDaysInput;
        } else {
            $halfDays = 0;
            foreach ($halfDayRows as $row) {
                // Use gross worked hours from check-in/out to decide if it is truly a half‑day.
                $displayWorkedSeconds = 0;
                if (!empty($row["check_in_time"]) && !empty($row["check_out_time"])) {
                    $inTs = strtotime($row["date"] . " " . $row["check_in_time"]);
                    $outTs = strtotime($row["date"] . " " . $row["check_out_time"]);
                    if ($outTs > $inTs) {
                        $displayWorkedSeconds = $outTs - $inTs;
                    }
                }
                $displayWorkedHours = $displayWorkedSeconds / 3600;

                // Count as half‑day only when gross worked hours are clearly below a full day
                if ($displayWorkedHours + 0.01 < $fullDayHoursRule) {
                    $halfDays++;
                }
            }
        }

        // Calculate how paid leave covers full days and half days
        // If usedPaidLeaves = 1.5, it means 1 full day + 1 half day (0.5)
        $fullDaysCoveredByPaidLeave = min(floor($usedPaidLeaves), $totalLeaves);
        $halfDaysCoveredByPaidLeave =
            ($usedPaidLeaves - $fullDaysCoveredByPaidLeave) * 2; // 0.5 = 1 half day

        // Unpaid leaves only (full days not covered by paid leave)
        $unpaidLeaves = max($totalLeaves - $fullDaysCoveredByPaidLeave, 0);

        // Unpaid half days (half days not covered by paid leave)
        $unpaidHalfDays = max($halfDays - $halfDaysCoveredByPaidLeave, 0);

        $deduction = 0;
        $workedHours = 0;
        $overtimeSeconds = 0;

        // Get overtime data for all payroll types
        $overtimeResult = $attendanceModel
            ->select("SUM(TIME_TO_SEC(overtime)) AS overtime_seconds")
            ->where("user_id", $userId)
            ->where("date >=", $startOfMonth)
            ->where("date <=", $endOfMonth)
            ->first();
        $overtimeSeconds = $overtimeResult["overtime_seconds"] ?? 0;

        // Late arrival is tracked for info purposes only – NOT deducted from salary
        $lateDeduction = 0;
        $totalLateMinutes = 0;
        if ($workingDays > 0 && ($rules["working_hours_per_day"] ?? 8) > 0) {
            $lateResult = $attendanceModel
                ->select("COALESCE(SUM(late_minutes), 0) AS total_late_minutes")
                ->where("user_id", $userId)
                ->where("date >=", $startOfMonth)
                ->where("date <=", $endOfMonth)
                ->where("status !=", "half-day")
                ->first();
            $totalLateMinutes = (int) ($lateResult["total_late_minutes"] ?? 0);
            // $lateDeduction intentionally remains 0 – late arrival does not affect salary
        }

        switch ($payrollType) {
            case "hourly":
                // Convert TIME → seconds
                $result = $attendanceModel
                    ->select(
                        "
                        SUM(TIME_TO_SEC(work_hours)) AS work_seconds,
                        SUM(TIME_TO_SEC(overtime)) AS overtime_seconds
                    ",
                    )
                    ->where("user_id", $userId)
                    ->where("date >=", $startOfMonth)
                    ->where("date <=", $endOfMonth)
                    ->first();

                $totalSeconds =
                    ($result["work_seconds"] ?? 0) +
                    ($result["overtime_seconds"] ?? 0);

                $workedHours = round($totalSeconds / 3600, 2);

                // Expected hours reduced by PAID leaves
                $expectedHours =
                    ($workingDays - $usedPaidLeaves) *
                    ($rules["working_hours_per_day"] ?? 8);

                $expectedHours = max($expectedHours, 0);

                $perHourRate =
                    $baseSalary /
                    ($workingDays * ($rules["working_hours_per_day"] ?? 8));

                $missingHours = max($expectedHours - $workedHours, 0);
                $deduction = $missingHours * $perHourRate; // late arrival excluded
                break;

            case "daily":
                $perDayRate = $baseSalary / $workingDays;
                $deduction = ($unpaidLeaves * $perDayRate) + ($unpaidHalfDays * ($perDayRate / 2)); // late arrival excluded
                break;

            case "monthly":
            default:
                $perDayRate = $baseSalary / $workingDays;

                // For monthly payroll, align with breakdown popup logic:
                // full-day deduction is based on *all* leave days in the month,
                // regardless of how many paid leaves are configured in the form.
                $fullDayDeduction = $totalLeaves * $perDayRate;

                // Half-day deduction based on missing hours across all half-day records,
                // mirroring the logic used in getDeductionBreakdown and salary-details grid.
                $halfDayMissingHoursTotal = 0.0;
                foreach ($halfDayRows as $row) {
                    $wh = $row["work_hours"] ?? null;
                    $workedHoursFloat = 0.0;
                    if (!empty($wh) && $wh !== "00:00:00") {
                        $parts = array_map("intval", explode(":", $wh));
                        $workedHoursFloat =
                            ($parts[0] ?? 0) +
                            ($parts[1] ?? 0) / 60 +
                            ($parts[2] ?? 0) / 3600;
                    }
                    // Treat entries with full-day or more hours as full present (no half-day deduction)
                    if ($workedHoursFloat + 0.01 >= $fullDayHoursRule) {
                        continue;
                    }
                    $requiredHours = ($rules["working_hours_per_day"] ?? 8);
                    $missingHours = max($requiredHours - $workedHoursFloat, 0);
                    $halfDayMissingHoursTotal += $missingHours;
                }

                $perHourRateForHalfDay =
                    $baseSalary /
                    ($workingDays * ($rules["working_hours_per_day"] ?? 8));
                $halfDayDeduction = $perHourRateForHalfDay > 0
                    ? round($halfDayMissingHoursTotal * $perHourRateForHalfDay, 2)
                    : 0;

                $deduction = $fullDayDeduction + $halfDayDeduction; // late arrival excluded
                break;
        }

        // Tax
        $taxDeduction = $this->calculateTaxForSalary((float) $baseSalary, $rules);

        // Overtime pay calculation
        $overtimePay = 0;
        if (($rules["enable_overtime"] ?? 0) == 1 && $overtimeSeconds > 0) {
            $overtimeHours = round($overtimeSeconds / 3600, 2);

            if ($overtimeHours > 0) {
                $hourlyRate =
                    $baseSalary /
                    ($workingDays * ($rules["working_hours_per_day"] ?? 8));

                if (
                    ($rules["overtime_rate_type"] ?? "multiplier") ===
                    "multiplier"
                ) {
                    $overtimeRate =
                        $hourlyRate * ($rules["overtime_multiplier"] ?? 1.5);
                } else {
                    $overtimeRate = $hourlyRate;
                }

                $overtimePay = $overtimeHours * $overtimeRate;
            }
        }

        $netSalary = max(
            $baseSalary - $deduction - $taxDeduction,
            0,
        );

        // $netSalary = max($baseSalary - $deduction - $taxDeduction, 0);

        return $this->response->setJSON([
            "status" => "success",
            "data" => [
                "base_salary" => round($baseSalary, 2),
                "working_days" => $workingDays,
                "present_days" => max($workingDays - $totalLeaves - ($halfDays * 0.5), 0),
                "total_leaves" => $totalLeaves,
                "paid_leaves_used" => $usedPaidLeaves,
                "unpaid_leaves" => $unpaidLeaves,
                "half_days" => $halfDays,
                "unpaid_half_days" => $unpaidHalfDays,
                "worked_hours" => $workedHours,
                "per_hour_salary" => round(
                    $baseSalary /
                    ($workingDays * ($rules["working_hours_per_day"] ?? 8)),
                    2,
                ),
                "total_overtime_hours" => round($overtimeSeconds / 3600, 2),
                "overtime_pay" => round($overtimePay, 2),
                "late_deduction" => round($lateDeduction, 2),
                "salary_deduction" => round($deduction, 2),
                "tax_deduction" => round($taxDeduction, 2),
                "net_salary" => round($netSalary, 2),
                "pattern" => $rules["saturday_off_pattern"],
            ],
        ]);
    }

    /**
     * Helper function to calculate working days.
     * Holiday and Sunday (when off) are excluded so they "count as present" for payroll.
     *
     * @param array $holidayDates List of holiday dates in Y-m-d format for this month (optional)
     */
    private function getWorkingDaysData($month, $year, $rules, $holidayDates = [])
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $sundays = 0;
        $saturdays = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayOfWeek = date("w", strtotime("$year-$month-$day"));
            if ($dayOfWeek == 0) {
                $sundays++;
            }
            if ($dayOfWeek == 6) {
                $saturdays++;
            }
        }

        $rules = $rules ?? [];
        $workingDays = $daysInMonth;
        if (($rules["include_holidays_in_working_days"] ?? 0) == 1) {
            // Holidays count as working days (paid)
        } else {
            if (($rules["sunday_off"] ?? 0) == 1) {
                $workingDays -= $sundays;
            }

            $satOffType = $rules["saturday_off_type"] ?? "";
            $satOffPattern = $rules["saturday_off_pattern"] ?? "";
            $satOffCount = empty($satOffPattern) ? 0 : count(explode(",", $satOffPattern));

            if (($rules["saturday_off_enabled"] ?? 0) == 1) {
                if ($satOffType == "all") {
                    $workingDays -= $saturdays;
                } elseif (in_array($satOffType, ["alternate-even", "alternate-odd", "custom"])) {
                    $workingDays -= $satOffCount;
                }
            } else {
                if ($satOffType == "all") {
                    $workingDays -= $saturdays;
                } elseif (in_array($satOffType, ["alternate-even", "alternate-odd", "custom"])) {
                    $workingDays -= $satOffCount;
                }
            }

            // Exclude holidays from working days so holiday counts as present (no deduction)
            foreach ($holidayDates as $hd) {
                $d = (int) date("w", strtotime($hd));
                if ($d === 0 && ($rules["sunday_off"] ?? 0) == 1) {
                    continue;
                }
                if ($d === 6 && ($rules["saturday_off_enabled"] ?? 0) == 1) {
                    continue;
                }
                $workingDays--;
            }
        }
        $countHalfDays = 0;
        if (
            isset($rules["saturday_half_day_enabled"]) &&
            $rules['saturday_half_day_enabled'] == 1
        ) {
            $countHalfDays = count(explode(",", $rules['saturday_half_day_pattern']));
            // $workingDays -= $countHalfDays;
        }
        return [
            // "working_days" => max($workingDays, 1),
            "working_days" => max($workingDays, 1),
            "half_days" => $countHalfDays,
            "total_days" => $daysInMonth,
            "sundays" => $sundays,
            "saturdays" => $saturdays,
        ];
    }

    /**
     * Build a lookup (date string => true) of Saturday dates in the given month
     * that are company-wide scheduled half-days (saturday_half_day_enabled).
     *
     * The saturday_half_day_pattern is a comma-separated list of week numbers
     * within the month (1 = first Saturday, 2 = second Saturday, etc.).
     *
     * These dates should NOT be counted as employee-specific half-day absences
     * because they are a company rule, not an individual deduction.
     *
     * @param int   $month Month number (1–12)
     * @param int   $year  Four-digit year
     * @param array $rules Company rules array
     * @return array<string,true> Associative array keyed by 'Y-m-d' date strings
     */
    private function getSaturdayHalfDayDates(int $month, int $year, array $rules): array
    {
        if (empty($rules['saturday_half_day_enabled']) || $rules['saturday_half_day_enabled'] != 1) {
            return [];
        }

        $patternStr = $rules['saturday_half_day_pattern'] ?? '';
        if ($patternStr === '' || $patternStr === null) {
            return [];
        }

        // Parse the pattern: list of week numbers (1-based) for Saturdays
        $patternWeeks = array_filter(
            array_map('intval', explode(',', $patternStr)),
            fn($w) => $w > 0
        );

        if (empty($patternWeeks)) {
            return [];
        }

        // Enumerate all Saturdays in the month and map week-number → date
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $saturdayLookup = [];
        $satCount = 0;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $dow = (int) date('w', strtotime($dateStr)); // 6 = Saturday
            if ($dow === 6) {
                $satCount++;
                if (in_array($satCount, $patternWeeks, true)) {
                    $saturdayLookup[$dateStr] = true;
                }
            }
        }

        return $saturdayLookup;
    }

    /**
     * Determine whether a given Saturday date is a "Week Off" Saturday
     * according to company rules.  Week-Off Saturdays are those excluded
     * from working days (so attendance on them earns Extra Day credit).
     *
     * Rules checked:
     *  - saturday_off_type = 'all'         → every Saturday is week-off
     *  - saturday_off_type = 'alternate-*' → Saturdays matching off-pattern
     *  - saturday_off_type = 'custom'      → Saturdays matching off-pattern
     *
     * @param  string $dateStr  'Y-m-d' Saturday date
     * @param  array  $rules    Company rules row
     * @return bool
     */
    private function isSaturdayWeekOff(string $dateStr, array $rules): bool
    {
        $satOffEnabled = !empty($rules['saturday_off_enabled']) && $rules['saturday_off_enabled'] == 1;
        $satOffType    = $rules['saturday_off_type'] ?? 'all';

        if (!$satOffEnabled && $satOffType === 'all') {
            // Even if the toggle is off, if type is 'all' it was historically treated as off
            // Guard: only count as week-off when explicitly enabled OR type is 'all'
        }

        if ($satOffType === 'all') {
            return true; // every Saturday is a week-off
        }

        // For alternate / custom types, resolve which week-number this Saturday is
        $patternStr = $rules['saturday_off_pattern'] ?? '';
        if ($patternStr === '' || $patternStr === null) {
            return false;
        }
        $offWeeks = array_filter(
            array_map('intval', explode(',', $patternStr)),
            fn($w) => $w > 0
        );
        if (empty($offWeeks)) {
            return false;
        }

        // Determine which Saturday-of-month this date is (1 = first Saturday, etc.)
        [$year, $month, $day] = array_map('intval', explode('-', $dateStr));
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $satCount = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $ds = sprintf('%04d-%02d-%02d', $year, $month, $d);
            if ((int) date('w', strtotime($ds)) === 6) {
                $satCount++;
                if ($ds === $dateStr) {
                    return in_array($satCount, $offWeeks, true);
                }
            }
        }
        return false;
    }

    /**
     * Determine whether a given date is a "Week Off" day (Sunday or Week-Off Saturday)
     *
     * @param  string $dateStr  'Y-m-d' date
     * @param  array  $rules    Company rules row
     * @return bool
     */
    private function isWeekOffDay(string $dateStr, array $rules): bool
    {
        $dow = (int) date('w', strtotime($dateStr)); // 0=Sun, 6=Sat
        if ($dow === 0 && ($rules['sunday_off'] ?? 0) == 1) {
            return true;
        }
        if ($dow === 6 && $this->isSaturdayWeekOff($dateStr, $rules)) {
            return true;
        }
        return false;
    }

    public function getLeaveDetails()
    {
        $leaveId = $this->request->getPost("leave_id");
        $userId = $this->request->getPost("user_id");
        $monthYear = $this->request->getPost("month_year");

        $leaveTypeModel = new \App\Models\LeaveTypeModel();
        $attendanceModel = new AttendanceModel();
        // Get number_of_leaves for selected leave type
        $leaveType = $leaveTypeModel->find($leaveId);
        $totalLeaves = 0;

        if ($leaveId == 1 || $leaveId == 2) { // Paid or Casual
            $employeeLeaveModel = new EmployeeLeaveModel();
            $employeeBalance = $employeeLeaveModel->where('employee_id', $userId)->first();
            if ($employeeBalance) {
                $totalLeaves = ($leaveId == 1) ? $employeeBalance['paid_leave'] : $employeeBalance['casual_leave'];
            } else {
                $totalLeaves = $leaveType ? (int) $leaveType["number_of_leaves"] : 0;
            }
        } else {
            $totalLeaves = $leaveType ? (int) $leaveType["number_of_leaves"] : 0;
        }

        // employee_leaves stores the current master balance for paid/casual leave.
        $remaining = max((float) $totalLeaves, 0);
        // Fix: use proper start/end-of-month dates instead of bare YYYY-MM string
        $startOfMonthLD = $monthYear . '-01';
        $endOfMonthLD = date('Y-m-t', strtotime($startOfMonthLD));
        $companyRulesModelLD = new CompanyRulesModel();
        $rulesLD = $companyRulesModelLD->first();
        [$yearLD, $monthNumLD] = array_map('intval', explode('-', $monthYear));
        $satHalfDayDatesLD = $this->getSaturdayHalfDayDates($monthNumLD, $yearLD, $rulesLD);
        $halfDayRowsLD = $attendanceModel
            ->where("user_id", $userId)
            ->where("status", "half-day")
            ->where("date >=", $startOfMonthLD)
            ->where("date <=", $endOfMonthLD)
            ->findAll();
        $halfDays = 0;
        foreach ($halfDayRowsLD as $hdRowLD) {
            $dateKey = substr($hdRowLD['date'], 0, 10);
            if (!isset($satHalfDayDatesLD[$dateKey]) && !$this->isWeekOffDay($dateKey, $rulesLD)) {
                $halfDays++;
            }
        }

        // Get allow_half_day from leave type
        $allowHalfDay = $leaveType
            ? (bool) ($leaveType["allow_half_day"] ?? 0)
            : false;

        return $this->response->setJSON([
            "total_leaves" => $totalLeaves,
            "used_leaves" => 0,
            "remaining_leaves" => $remaining,
            "total_half_day_leaves" => $halfDays,
            "allow_half_day" => $allowHalfDay,
        ]);
    }

    public function getRemainingPaidLeaves($userId)
    {
        $employeeLeaveModel = new EmployeeLeaveModel();
        $leaveBalance = $employeeLeaveModel
            ->where("employee_id", $userId)
            ->first();

        return $this->respond([
            "status" => "success",
            "remaining_paid_leaves" => (float) ($leaveBalance["paid_leave"] ?? 0),
        ]);
    }

    public function downloadMultiple()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized(
                "Unauthorized: Token missing or invalid",
            );
        }

        // Check if user is admin or hr
        $role = session()->get("role");
        if (!in_array($role, ["admin", "hr"])) {
            return $this->failForbidden(
                "Only admin and hr can download multiple slips",
            );
        }

        $input = $this->request->getJSON();
        $employeeIds = $input->employee_ids ?? [];
        $month = $input->month ?? null;

        if (empty($employeeIds) || !$month) {
            return $this->failValidationErrors(
                "Employee IDs and month are required",
            );
        }

        // Validate month format
        try {
            $date = new \DateTime($month . "-01");
        } catch (\Exception $e) {
            return $this->failValidationErrors("Invalid month format");
        }

        $payrollModel = new PayrollModel();
        $userInfoModel = new UserInfoModel();
        $companyModel = new CompanyLogoModel();
        $designationModel = new DesignationModel();
        $departmentModel = new DepartmentModel();
        $onboardingModel = new OnboardingModel();
        $attendanceModel = new AttendanceModel();
        $companyRulesModel = new CompanyRulesModel();

        $successCount = 0;
        $failedCount = 0;
        $slipsHtml = [];

        foreach ($employeeIds as $empId) {
            try {
                $payroll = $payrollModel
                    ->where("user_id", $empId)
                    ->where("month_year", $month)
                    ->first();

                if (!$payroll) {
                    $failedCount++;
                    continue;
                }

                // Get user info (employee details)
                $userInfo = $userInfoModel
                    ->where("user_id", $payroll["user_id"])
                    ->first();
                if (!$userInfo) {
                    $failedCount++;
                    continue;
                }

                // Get designation name
                $designation = $designationModel->find(
                    $userInfo["designation_id"],
                );

                // Get department name
                $department = $departmentModel->find(
                    $userInfo["department_id"],
                );

                $onboarding = $onboardingModel
                    ->where("job_id", $userInfo["job_id"])
                    ->first();

                // Get latest company info (logo, name, address)
                $company = $companyModel->orderBy("id", "DESC")->first();
                $companyLogoBase64 = "";
                $logoFile = !empty($company["pdf_logo"]) ? $company["pdf_logo"] : ($company["logo_img"] ?? "");
                $companyLogoPath = FCPATH . "upload/" . $logoFile;
                if (empty($logoFile) || !is_file($companyLogoPath)) {
                    $companyLogoPath = FCPATH . "public/assets/images/fab_logo.jpg";
                }
                if (is_file($companyLogoPath)) {
                    $type = pathinfo($companyLogoPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($companyLogoPath);
                    $companyLogoBase64 = "data:image/" . $type . ";base64," . base64_encode($data);
                }

                $db = \Config\Database::connect();
                $companyAddressRow = $db->table('company_address')->orderBy('id', 'DESC')->get()->getRowArray();
                if ($companyAddressRow && !empty($companyAddressRow['office_address'])) {
                    if ($company) {
                        $company['company_address'] = $companyAddressRow['office_address'];
                    }
                }

                // Calculate working days and leave details from payroll month_year
                $monthYear = $payroll["month_year"];
                [$year, $monthNum] = explode("-", $monthYear);

                $rules = $companyRulesModel->first();

                // Get working days data
                $workingDaysData = $this->getWorkingDaysData(
                    (int) $monthNum,
                    (int) $year,
                    $rules,
                    [],
                );
                $workingDays = $workingDaysData["working_days"];

                // Get attendance data for the month
                $startOfMonth =
                    "$year-" . str_pad($monthNum, 2, "0", STR_PAD_LEFT) . "-01";
                $endOfMonth = date("Y-m-t", strtotime($startOfMonth));

                $attendanceData = $attendanceModel
                    ->where("user_id", $payroll["user_id"])
                    ->where("date >=", $startOfMonth)
                    ->where("date <=", $endOfMonth)
                    ->findAll();

                // Count present and absent days
                $presentDays = 0;
                $absentDays = 0;
                $halfDays = 0;

                foreach ($attendanceData as $record) {
                    if ($record["status"] === "present") {
                        $presentDays++;
                    } elseif ($record["status"] === "absent") {
                        $absentDays++;
                    } elseif ($record["status"] === "half-day") {
                        $halfDays++;
                    }
                }

                // Get leave details
                $totalLeaves = $payroll["total_leaves"] ?? 0;
                $usedPaidLeaves = $payroll["used_paid_leaves"] ?? 0;
                $usedSickLeaves = $payroll["used_sick_leaves"] ?? 0;
                $halfDaysCount = isset($payroll["total_half_day"]) ? $payroll["total_half_day"] : $halfDays;
                $unpaidLeaves = max($totalLeaves + ($halfDaysCount * 0.5) - $usedPaidLeaves - $usedSickLeaves, 0);

                // Calculate salary components
                $baseSalary = $payroll["salary_amount"] ?? 0;
                $overtimePay = $payroll["overtime_pay"] ?? 0;
                $bonuses = $payroll["bonuses"] ?? 0;
                $taxDeduction = $payroll["tax_deduction"] ?? 0;
                $salaryDeduction = $payroll["salary_deduction"] ?? 0;
                $totalLeaves = $payroll["total_leaves"] ?? 0;
                $usedPaidLeaves = $payroll["used_paid_leaves"] ?? 0;

                // Calculate total earnings (base + overtime + bonuses)
                $totalEarnings = $baseSalary + $overtimePay + $bonuses;

                // Calculate total deductions
                $totalDeductions = $salaryDeduction + $taxDeduction;

                // Build calculated data array
                $calculatedData = [
                    "working_days" => $workingDays,
                    "present_days" => $presentDays,
                    "absent_days" => $absentDays,
                    "total_leaves" => $totalLeaves,
                    "used_paid_leaves" => $usedPaidLeaves,
                    "unpaid_leaves" => $unpaidLeaves,
                    "half_days" => isset($payroll["total_half_day"]) ? $payroll["total_half_day"] : $halfDays,
                    "worked_hours" => $payroll["worked_hours"] ?? 0,
                    "total_overtime_hours" =>
                        $payroll["total_overtime_hours"] ?? 0,
                    "total_earnings" => $totalEarnings,
                    "salary_deduction" => $salaryDeduction,
                    "total_deductions" => $totalDeductions,
                ];

                // Combine all data to pass to view
                $data = [
                    "payroll" => $payroll,
                    "user" => $userInfo,
                    "designation" => $designation,
                    "department" => $department,
                    "company" => $company,
                    "companyLogoBase64" => $companyLogoBase64,
                    "onboarding" => $onboarding,
                    "calculatedData" => $calculatedData,
                ];

                // Generate HTML for this slip
                $slipHtml = view("payroll/salary_slip", $data);
                $slipsHtml[] = $slipHtml;
                $successCount++;
            } catch (\Exception $e) {
                log_message(
                    "error",
                    "Failed to generate slip for employee " .
                    $empId .
                    ": " .
                    $e->getMessage(),
                );
                $failedCount++;
                continue;
            }
        }

        if ($successCount === 0 || empty($slipsHtml)) {
            return $this->fail("No salary slips could be generated");
        }

        try {
            // '<div style="page-break-after: always;"></div>',
            $combinedHtml = implode($slipsHtml);

            $dompdf = new \Dompdf\Dompdf([
                "isRemoteEnabled" => true,
                "isHtml5ParserEnabled" => true,
                "isFontSubsettingEnabled" => true,
            ]);

            $dompdf->loadHtml($combinedHtml);
            $dompdf->setPaper("A4", "portrait");
            $dompdf->render();

            $filename = "salary-slips-" . $month . ".pdf";

            return $this->response
                ->setContentType("application/pdf")
                ->setBody($dompdf->output())
                ->setHeader(
                    "Content-Disposition",
                    'attachment; filename="' . $filename . '"',
                );
        } catch (\Exception $e) {
            log_message("error", "PDF generation failed: " . $e->getMessage());
            return $this->fail("Failed to generate PDF: " . $e->getMessage());
        }
    }

    /**
     * Download multiple salary slips by payroll IDs
     * Used by the payroll view page
     */
    public function downloadMultipleByIds()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized("Unauthorized: Token missing or invalid");
        }

        $role = session()->get("role");
        if (!in_array($role, ["admin", "hr"])) {
            return $this->failForbidden("Only admin and hr can download multiple slips");
        }

        $input = $this->request->getJSON();
        $payrollIds = $input->payroll_ids ?? [];
        $month = $input->month ?? date("Y-m");

        if (empty($payrollIds)) {
            return $this->failValidationErrors("Payroll IDs are required");
        }

        $pdfOutput = $this->generateCombinedSlipPdf($payrollIds);

        if (!$pdfOutput) {
            return $this->fail("No salary slips could be generated");
        }

        $filename = "salary-slips-" . $month . ".pdf";

        return $this->response
            ->setContentType("application/pdf")
            ->setBody($pdfOutput)
            ->setHeader("Content-Disposition", 'attachment; filename="' . $filename . '"');
    }

    public function downloadSlip($id)
    {
        $payrollModel = new PayrollModel();
        $userInfoModel = new UserInfoModel();
        $companyModel = new CompanyLogoModel();
        $designationModel = new DesignationModel();
        $departmentModel = new DepartmentModel();
        $onboardingModel = new OnboardingModel();
        $attendanceModel = new AttendanceModel();
        $companyRulesModel = new CompanyRulesModel();

        $payroll = $payrollModel->find($id);

        if (!$payroll) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException(
                "Payroll record not found",
            );
        }

        // Get user info (employee details)
        $userInfo = $userInfoModel
            ->where("user_id", $payroll["user_id"])
            ->first();

        // Get designation name
        $designation = $userInfo
            ? $designationModel->find($userInfo["designation_id"])
            : null;

        // Get department name
        $department = $userInfo
            ? $departmentModel->find($userInfo["department_id"])
            : null;
        $onboarding = $userInfo
            ? $onboardingModel->where("job_id", $userInfo["job_id"])->first()
            : null;

        // Get latest company info (logo, name, address)
        $company = $companyModel->orderBy("id", "DESC")->first();
        $companyLogoBase64 = "";
        $logoFile = !empty($company["pdf_logo"]) ? $company["pdf_logo"] : ($company["logo_img"] ?? "");
        $companyLogoPath = FCPATH . "upload/" . $logoFile;
        if (empty($logoFile) || !is_file($companyLogoPath)) {
            $companyLogoPath = FCPATH . "public/assets/images/fab_logo.jpg";
        }
        if (is_file($companyLogoPath)) {
            $type = pathinfo($companyLogoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($companyLogoPath);
            $companyLogoBase64 = "data:image/" . $type . ";base64," . base64_encode($data);
        }

        $db = \Config\Database::connect();
        if ($db->tableExists('company_address')) {
            $companyAddressRow = $db->table('company_address')->orderBy('id', 'DESC')->get()->getRowArray();
            if ($companyAddressRow && !empty($companyAddressRow['office_address'])) {
                if ($company) {
                    $company['company_address'] = $companyAddressRow['office_address'];
                }
            }
        }

        // Calculate working days and leave details from payroll month_year (same logic as group/single)
        $monthYear = $payroll["month_year"]; // Format: YYYY-MM
        [$year, $month] = explode("-", $monthYear);

        $rules = $companyRulesModel->first();

        $holidayCalendarModel = new HolidayCalendarModel();
        $startOfMonth =
            "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-01";
        $endOfMonth = date("Y-m-t", strtotime($startOfMonth));
        $holidayRows = $holidayCalendarModel
            ->where("holiday_date >=", $startOfMonth)
            ->where("holiday_date <=", $endOfMonth)
            ->findAll();
        $holidayDates = array_column($holidayRows, "holiday_date");

        // Get working days data (exclude holidays so slip matches group/single)
        $workingDaysData = $this->getWorkingDaysData(
            (int) $month,
            (int) $year,
            $rules,
            $holidayDates,
        );
        $workingDays = $workingDaysData["working_days"];

        // Get attendance data for the month
        $attendanceData = $attendanceModel
            ->where("user_id", $payroll["user_id"])
            ->where("date >=", $startOfMonth)
            ->where("date <=", $endOfMonth)
            ->findAll();

        // Count present and absent days
        $presentDays = 0;
        $absentDays = 0;
        $halfDays = 0;

        foreach ($attendanceData as $record) {
            if ($record["status"] === "present") {
                $presentDays++;
            } elseif ($record["status"] === "absent") {
                $absentDays++;
            } elseif ($record["status"] === "half-day") {
                $halfDays++;
            }
        }

        // Get leave details
        $totalLeaves = $payroll["total_leaves"] ?? 0;
        $usedPaidLeaves = $payroll["used_paid_leaves"] ?? 0;
        $usedSickLeaves = $payroll["used_sick_leaves"] ?? 0;
        $halfDaysCount = isset($payroll["total_half_day"]) ? $payroll["total_half_day"] : $halfDays;
        $unpaidLeaves = max($totalLeaves + ($halfDaysCount * 0.5) - $usedPaidLeaves - $usedSickLeaves, 0);

        // Calculate salary components
        $baseSalary = $payroll["salary_amount"] ?? 0;
        $overtimePay = $payroll["overtime_pay"] ?? 0;
        $bonuses = $payroll["bonuses"] ?? 0;
        $taxDeduction = $payroll["tax_deduction"] ?? 0;
        $salaryDeduction = $payroll["salary_deduction"] ?? 0;

        // Calculate total earnings (base + overtime + bonuses)
        $totalEarnings = $baseSalary + $overtimePay + $bonuses;

        // Calculate total deductions
        $totalDeductions = $salaryDeduction + $taxDeduction;

        // Build calculated data array
        $calculatedData = [
            "working_days" => $workingDays,
            "present_days" => $presentDays,
            "absent_days" => $absentDays,
            "total_leaves" => $totalLeaves,
            "used_paid_leaves" => $usedPaidLeaves,
            "unpaid_leaves" => $unpaidLeaves,
            "half_days" => isset($payroll["total_half_day"]) ? $payroll["total_half_day"] : $halfDays,
            "worked_hours" => $payroll["worked_hours"] ?? 0,
            "total_overtime_hours" => $payroll["total_overtime_hours"] ?? 0,
            "total_earnings" => $totalEarnings,
            "salary_deduction" => $salaryDeduction,
            "total_deductions" => $totalDeductions,
        ];

        // Combine all data to pass to view
        $data = [
            "payroll" => $payroll,
            "user" => $userInfo,
            "designation" => $designation,
            "department" => $department,
            "company" => $company,
            "companyLogoBase64" => $companyLogoBase64,
            "onboarding" => $onboarding,
            "calculatedData" => $calculatedData,
        ];

        // Prepare the HTML for the PDF
        $html = view("payroll/salary_slip", $data);

        // Load DOMPDF
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper("A4", "portrait");
        $dompdf->render();
        $username = $userInfo
            ? preg_replace(
                "/\s+/",
                "_",
                strtolower($userInfo["firstname"] ?? "user"),
            )
            : "user";
        return $this->response
            ->setContentType("application/pdf")
            ->setBody($dompdf->output())
            ->setHeader(
                "Content-Disposition",
                'attachment; filename="salary-slip-' .
                $username .
                "-" .
                $id .
                '.pdf"',
            );
    }

    public function save()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized(
                "Unauthorized: Token missing or invalid",
            );
        }

        if (!in_array($user->role, ["admin", "hr"])) {
            return $this->failForbidden(
                "Forbidden: You do not have access to this resource",
            );
        }

        $accountModel = new \App\Models\AccountDetailModel();
        $data = $this->request->getPost();
        $data["created_by"] = $user->sub;

        $validation = \Config\Services::validation();

        $validationRules = [
            "users_id" => [
                "rules" => "required|integer",
                "errors" => [
                    "required" => "Employee is required.",
                    "integer" => "Invalid employee ID.",
                ],
            ],
            "acc_numbers" => [
                "rules" => "required|is_unique[account_detail.acc_number]",
                "errors" => [
                    "required" => "Account number is required.",
                    "is_unique" => "This account number already exists.",
                ],
            ],
            "bank_names" => [
                "rules" => "required",
                "errors" => ["required" => "Bank name is required."],
            ],
            "ifsc_codes" => [
                "rules" => "required",
                "errors" => ["required" => "IFSC code is required."],
            ],
            "acc_in_names" => [
                "rules" => "required",
                "errors" => ["required" => "Account holder name is required."],
            ],
            "branch_names" => [
                "rules" => "required",
                "errors" => ["required" => "Branch name is required."],
            ],
            "branch_codes" => [
                "rules" => "required",
                "errors" => ["required" => "Branch code is required."],
            ],
            "created_by" => [
                "rules" => "required",
                "errors" => ["required" => "Creator information is missing."],
            ],
        ];

        if (!$validation->setRules($validationRules)->run($data)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(["errors" => $validation->getErrors()]);
        }
        $model = new \App\Models\AccountDetailModel();
        $data = [
            "user_id" => $this->request->getPost("users_id"),
            "acc_in_name" => $this->request->getPost("acc_in_names"),
            "acc_number" => $this->request->getPost("acc_numbers"),
            "bank_name" => $this->request->getPost("bank_names"),
            "ifsc_code" => $this->request->getPost("ifsc_codes"),
            "branch_name" => $this->request->getPost("branch_names"),
            "branch_code" => $this->request->getPost("branch_codes"),
            "created_by" => session()->get("user_id"),
        ];
        $model->save($data);

        // Fetch the latest inserted data to return back
        $account = $model->where("user_id", $data["user_id"])->first();

        return $this->response->setJSON([
            "status" => "success",
            "data" => $account,
        ]);
    }

    public function groupsalaryPage()
    {
        // Redirect to salary-details with current month if accessed directly
        $currentMonth = date("Y-m");
        return redirect()->to("/payroll/salary-details?month=" . $currentMonth);
    }
    

    public function salaryDetails()
    {
        $month = $this->request->getGet("month"); // format: YYYY-MM
        if (!$month) {
            // Default to last month
            $lastMonth = new \DateTime("first day of last month");
            $month = $lastMonth->format("Y-m");
        }

        try {
            $date = new \DateTime($month . "-01");
            $daysInMonth = (int) $date->format("t");
            $startOfMonth = $date->format("Y-m-01");
            $endOfMonth = $date->format("Y-m-t");
            $year = (int) $date->format("Y");
            $monthNum = (int) $date->format("m");
        } catch (\Exception $e) {
            return redirect()
                ->to("/payroll")
                ->with("error", "Invalid month format.");
        }

        $userModel = new \App\Models\UserModel();
        $userInfoModel = new \App\Models\UserInfoModel();
        $leaveModel = new \App\Models\LeaveModel();
        $payrollModel = new \App\Models\PayrollModel();
        $companyRulesModel = new \App\Models\CompanyRulesModel();
        $attendanceModel = new \App\Models\AttendanceModel();
        $holidayCalendarModel = new HolidayCalendarModel();

        $rules = $companyRulesModel->first() ?? [];

        $holidayRows = $holidayCalendarModel
            ->where("holiday_date >=", $startOfMonth)
            ->where("holiday_date <=", $endOfMonth)
            ->findAll();
        $holidayDates = array_column($holidayRows, "holiday_date");

        // Calculate working days (holidays and Sundays excluded = count as present)
        $workingDaysData = $this->getWorkingDaysData($monthNum, $year, $rules, $holidayDates);
        $workingDays = $workingDaysData["working_days"];
        $workingHoursPerDay = (float) ($rules["working_hours_per_day"] ?? 8);
        $workingHours = $workingDaysData["working_days"] * $workingHoursPerDay;
        $saturday_half_day_enabled = $rules['saturday_half_day_enabled'] ?? 0;
        $saturday_half_day_pattern = $rules['saturday_half_day_pattern'] ?? '';
        $half_day_hours = isset($rules['half_day_hours']) && $rules['half_day_hours'] !== '' && $rules['half_day_hours'] !== null
            ? (float) $rules['half_day_hours'] : 5;
        $halfDays = $workingDaysData['half_days'];
        $halfDayWorkHours = $half_day_hours * $halfDays;
        $totalWorkHours = $workingHours + $halfDayWorkHours;

        $authService = new \App\Services\AuthService($this->request);
        $branchId = $authService->getBranchId();
        $user = $authService->user();
        $authRole = $user->role ?? 'employee';

        $staffRoles = [];
        if ($authRole === 'branch_admin') {
            $staffRoles = ['employee', 'department_manager', 'branch_admin'];
        } elseif ($authRole === 'department_manager') {
            $staffRoles = ['employee'];
        } elseif (in_array($authRole, ['admin', 'hr'])) {
            $staffRoles = ['employee', 'hr', 'branch_admin', 'department_manager'];
        } else {
            $staffRoles = ['employee'];
        }

        $employeeBuilder = $userInfoModel
            ->select('user_info.*, users.username')
            ->join('users', 'users.id = user_info.user_id')
            ->where('users.is_deleted', 0)
            ->whereIn('users.role', $staffRoles);
            
        if (!empty($branchId)) {
            $employeeBuilder = $employeeBuilder->where('users.branch_id', $branchId);
        }

        $employees = $employeeBuilder->orderBy('users.username', 'ASC')->findAll();

        // Filter out Inactive/Resigned employees who have zero attendance in the selected month
        $inactiveUserIds = [];
        foreach ($employees as $emp) {
            $status = trim($emp['status'] ?? 'Active');
            if (in_array(strtolower($status), ['inactive', 'resigned'])) {
                $inactiveUserIds[] = $emp['user_id'];
            }
        }
        
        $activeInactiveUserIds = [];
        if (!empty($inactiveUserIds)) {
            $attCounts = $attendanceModel->select('user_id')
                ->whereIn('user_id', $inactiveUserIds)
                ->where('date >=', $startOfMonth)
                ->where('date <=', $endOfMonth)
                ->whereNotIn('status', ['absent', 'leave'])
                ->groupBy('user_id')
                ->findAll();
            $activeInactiveUserIds = array_column($attCounts, 'user_id');
        }

        $filteredEmployees = [];
        foreach ($employees as $emp) {
            $status = trim($emp['status'] ?? 'Active');
            if (in_array(strtolower($status), ['inactive', 'resigned'])) {
                if (!in_array($emp['user_id'], $activeInactiveUserIds)) {
                    continue; // Exclude inactive/resigned employee with zero attendance
                }
            }
            $filteredEmployees[] = $emp;
        }
        $employees = $filteredEmployees;

        $employeeLeaveModel = new \App\Models\EmployeeLeaveModel();
        foreach ($employees as &$emp) {
            // Get existing payroll if saved
            $payroll = $payrollModel
                ->where("user_id", $emp["user_id"])
                ->where("month_year", $month)
                ->first();
            $emp["is_saved"] = $payroll ? true : false;
            $emp["payroll_id"] = $payroll ? ($payroll["id"] ?? null) : null;
            $emp["days_in_month"] = $workingDays;
            $emp["hours_in_month"] = $totalWorkHours;

            // Determine the active salary for this month based on increment history
            $payrollMonthEnd = $endOfMonth; // calculated earlier as Y-m-t
            $db = \Config\Database::connect();
            $incrementHistory = $db->table('salary_increment_history')
                ->where('employee_id', $emp["user_id"])
                ->orderBy('effective_from_date', 'DESC')
                ->get()
                ->getResultArray();

            $effectiveSalary = (float)($emp["salary"] ?? 0);
            if (!empty($incrementHistory)) {
                $salaryForMonth = null;
                foreach ($incrementHistory as $record) {
                    if ($record['effective_from_date'] <= $payrollMonthEnd) {
                        $salaryForMonth = (float)$record['new_salary'];
                        break;
                    }
                }
                if ($salaryForMonth !== null) {
                    $effectiveSalary = $salaryForMonth;
                } else {
                    $oldest = end($incrementHistory);
                    $effectiveSalary = (float)($oldest['previous_salary'] ?? $effectiveSalary);
                }
            }
            $emp["salary"] = $effectiveSalary;

            // If a payroll record already exists for this month, use its saved salary_amount
            // (the value entered when payroll was created, e.g. ₹100,000) instead of the
            // employee profile salary from user_info (which may be a different/default value).
            if ($payroll && !empty($payroll["salary_amount"]) && (float) $payroll["salary_amount"] > 0) {
                $emp["salary"] = (float) $payroll["salary_amount"];
            }

            $emp["per_day"] = round($emp["salary"] / $workingDays, 2);
            $emp["per_hour"] = round($emp["salary"] / ($workingDays * $workingHoursPerDay), 2);

            $ruleTaxAmount = $this->calculateTaxForSalary((float) $emp["salary"], $rules);
            $emp["tax_amount"] = $ruleTaxAmount;
            $emp["tax_deduction"] = $ruleTaxAmount;
            $emp["tax"] =
                $ruleTaxAmount > 0
                    ? "₹" . (fmod($ruleTaxAmount, 1) !== 0.0 ? number_format($ruleTaxAmount, 2) : number_format($ruleTaxAmount, 0))
                    : "No Tax";

            // ── Extra Day (Sat/Sun attendance on week-off days) ──────────────────
            // Saturday week-off: partial work (<working_hours_per_day) = 0.5 extra;
            // full work (>=working_hours_per_day) = 1.0 extra.
            // Sunday is always 1.0 (always a day off).
            // Leave / Used-Leave columns are NEVER touched here.
            $holidayLookupForExt = array_flip($holidayDates);
            $fullDayHrsForExtra  = (float) ($rules['working_hours_per_day'] ?? 8.0);
            $weekendAttRows = $attendanceModel
                ->where('user_id', $emp['user_id'])
                ->where('date >=', $startOfMonth)
                ->where('date <=', $endOfMonth)
                ->whereNotIn('status', ['absent', 'leave'])
                ->findAll();
            $extraDayDetails = [];
            foreach ($weekendAttRows as $wRow) {
                $wDateStr = substr($wRow['date'], 0, 10);
                $wDow = (int) date('w', strtotime($wDateStr)); // 0=Sun, 6=Sat
                if ($wDow !== 0 && $wDow !== 6) continue;           // only Sat/Sun
                if (isset($holidayLookupForExt[$wDateStr])) continue; // skip holidays
                if (empty($wRow['check_in_time'])) continue;          // must have a punch

                // Compute gross worked hours from check-in/check-out timestamps
                $workedHrsForExtra = 0.0;
                if (!empty($wRow['check_in_time']) && !empty($wRow['check_out_time'])) {
                    $inTs  = strtotime($wDateStr . ' ' . $wRow['check_in_time']);
                    $outTs = strtotime($wDateStr . ' ' . $wRow['check_out_time']);
                    if ($outTs > $inTs) {
                        $workedHrsForExtra = ($outTs - $inTs) / 3600.0;
                    }
                } elseif (!empty($wRow['work_hours']) && $wRow['work_hours'] !== '00:00:00') {
                    // Fallback: use stored work_hours column
                    $parts = array_map('intval', explode(':', $wRow['work_hours']));
                    $workedHrsForExtra = ($parts[0] ?? 0) + ($parts[1] ?? 0) / 60.0 + ($parts[2] ?? 0) / 3600.0;
                }

                // Determine credit: Saturday week-off => 0.5 or 1.0; Sunday always 1.0
                if ($wDow === 6 && $this->isSaturdayWeekOff($wDateStr, $rules)) {
                    // Week-Off Saturday: credit based on hours worked
                    $extraCredit  = ($workedHrsForExtra + 0.01 >= $fullDayHrsForExtra) ? 1.0 : 0.5;
                    $isHalfExtra  = ($extraCredit < 1.0);
                } else {
                    // Sunday (or a non-week-off Saturday): always full extra
                    $extraCredit = 1.0;
                    $isHalfExtra = false;
                }

                $extraDayDetails[] = [
                    'date'           => $wDateStr,
                    'day_name'       => date('l', strtotime($wDateStr)),
                    'check_in_time'  => $wRow['check_in_time'],
                    'check_out_time' => $wRow['check_out_time'] ?? null,
                    'worked_hours'   => round($workedHrsForExtra, 2),
                    'extra_credit'   => $extraCredit,
                    'is_half_extra'  => $isHalfExtra,
                ];
            }
            // Deduplicate by date — keep the entry with the highest credit for the day
            $seenExtDates    = [];
            $uniqueExtDetails = [];
            foreach ($extraDayDetails as $ed) {
                $existing = array_search($ed['date'], array_column($uniqueExtDetails, 'date'));
                if ($existing === false) {
                    $seenExtDates[]    = $ed['date'];
                    $uniqueExtDetails[] = $ed;
                } else {
                    // Keep whichever has greater credit
                    if ($ed['extra_credit'] > $uniqueExtDetails[$existing]['extra_credit']) {
                        $uniqueExtDetails[$existing] = $ed;
                    }
                }
            }
            // Sum credits (float: 0.5 + 1.0 + 0.5 = 2.0, etc.)
            $totalExtraCredit = 0.0;
            foreach ($uniqueExtDetails as $ed) {
                $totalExtraCredit += $ed['extra_credit'];
            }
            $emp['extra_days']        = $totalExtraCredit;   // now a float, e.g. 0.5, 1.5
            $emp['extra_day_pay']     = round($totalExtraCredit * $emp['per_day'], 2);
            $emp['extra_day_details'] = $uniqueExtDetails;

            if ($payroll) {
                // ── Load payroll values ──────────────────────────────────────────────
                $emp["leaves"] = (float) ($payroll["total_leaves"] ?? 0);
                $emp["half_days"] = (float) ($payroll["total_half_day"] ?? 0);
                $emp["used_paid_leaves"] = (float) ($payroll["used_paid_leaves"] ?? 0);
                $emp["used_sick_leaves"] = (float) ($payroll["used_sick_leaves"] ?? 0);

                // If an inactive/resigned employee has 0 saved leaves, recalculate from attendance
                $empStatus = strtolower(trim($emp['status'] ?? 'active'));
                if ($emp["leaves"] == 0 && in_array($empStatus, ['inactive', 'resigned', 'fired', 'removed'])) {
                    $recalcLeaves = $this->countMonthlyLeaves(
                        $emp["user_id"],
                        sprintf("%04d-%02d", $year, $monthNum),
                        $rules,
                        $leaveModel,
                        $holidayDates,
                    );
                    if ($recalcLeaves > 0) {
                        $emp["leaves"] = $recalcLeaves;
                    }
                }

                // For the management page, we ensure deductions match the counts shown to fix 
                // inconsistencies (e.g., leaves=1 but deduction=0).
                $suggestedBaseDed = ($emp["leaves"] * $emp["per_day"]) + ($emp["half_days"] * ($emp["per_day"] / 2));
                $paidLeaveCredit = ($emp["used_paid_leaves"] + $emp["used_sick_leaves"]) * $emp["per_day"];

                $storedDed = (float) ($payroll["salary_deduction"] ?? 0);
                // Override if stored deduction is 0 but leaves/half-days exist
                if ($storedDed == 0 && ($emp["leaves"] > 0 || $emp["half_days"] > 0)) {
                    $emp["salary_deduction"] = round(max($suggestedBaseDed - $paidLeaveCredit, 0), 2);
                } else {
                    $emp["salary_deduction"] = round($storedDed, 2);
                }

                // Tax is governed by Company Rules based on current salary
                $emp["tax_deduction"] = $ruleTaxAmount;
                $emp["tax_amount"] = $ruleTaxAmount;
                $emp["tax"] =
                    $ruleTaxAmount > 0
                        ? "₹" . (fmod($ruleTaxAmount, 1) !== 0.0 ? number_format($ruleTaxAmount, 2) : number_format($ruleTaxAmount, 0))
                        : "No Tax";

                $emp["overtime_pay"] = round((float) ($payroll["overtime_pay"] ?? 0), 2);
                $emp["total_overtime_hours"] = (float) ($payroll["total_overtime_hours"] ?? 0);
                $emp["late_deduction"] = 0;

                // base_deduction is used by JS for re-computation.
                $emp["base_deduction"] = round($emp["salary_deduction"] + $paidLeaveCredit, 2);

                $savedSalary = (float) ($payroll["salary_amount"] ?? $emp["salary"]);
                $savedSalaryDed = (float) ($payroll["salary_deduction"] ?? 0);
                $savedTax = round((float) ($payroll["tax_deduction"] ?? 0), 2);
                $savedOvertime = (float) ($payroll["overtime_pay"] ?? 0);
                $savedNetSalary = round((float) ($payroll["net_salary"] ?? 0), 2);

                // Baseline net salary of the saved record at the time it was saved
                $savedBaseNetSalary = round(
                    $savedSalary
                    - $savedSalaryDed
                    - $savedTax
                    + $savedOvertime,
                    2
                );

                // Any genuine extra day pay saved in net salary would exceed savedBaseNetSalary
                $savedExtraDayPay = 0.0;
                if ($savedNetSalary > $savedBaseNetSalary && ($emp['extra_days'] ?? 0) > 0) {
                    $savedExtraDayPay = round($savedNetSalary - $savedBaseNetSalary, 2);
                }

                // Current base net salary using current salary, current salary_deduction, rule tax, and overtime
                $baseNetSalary = round(
                    $emp["salary"]
                    - $emp["salary_deduction"]
                    - $emp["tax_deduction"]
                    + ($emp["overtime_pay"] ?? 0),
                    2
                );

                if ($savedExtraDayPay > 0) {
                    $emp["extra_day_pay"] = $savedExtraDayPay;
                    $emp["net_salary"] = round($baseNetSalary + $savedExtraDayPay, 2);
                } else {
                    $emp["net_salary"] = $baseNetSalary;
                    // Keep extra_day_pay as the default calculation (what they could add),
                    // but don't add it to net_salary until they click save in the modal.
                }
            } else {
                $totalLeaves = $this->countMonthlyLeaves(
                    $emp["user_id"],
                    sprintf("%04d-%02d", $year, $monthNum),
                    $rules,
                    $leaveModel,
                    $holidayDates,
                );
                $usedPaidLeaves = 0;

                $emp["leaves"] = $totalLeaves;
                $emp["used_paid_leaves"] = $usedPaidLeaves;
                $emp["used_sick_leaves"] = 0;

                $halfDayRows = $attendanceModel
                    ->select("work_hours, status, date, check_in_time, check_out_time")
                    ->where("user_id", $emp["user_id"])
                    ->where("status", "half-day")
                    ->where("date >=", $startOfMonth)
                    ->where("date <=", $endOfMonth)
                    ->findAll();
                // Exclude Saturdays that are company-wide scheduled half-days and week-off days
                $satHalfDayDatesForEmp = $this->getSaturdayHalfDayDates($monthNum, $year, $rules);
                $halfDayRows = array_filter($halfDayRows, function ($r) use ($satHalfDayDatesForEmp, $rules) {
                    $dateKey = substr($r['date'], 0, 10);
                    return !isset($satHalfDayDatesForEmp[$dateKey]) && !$this->isWeekOffDay($dateKey, $rules);
                });
                $halfDayRows = array_values($halfDayRows);
                $emp["half_days"] = count($halfDayRows);

                $fullDaysCoveredByPaidLeave = min(floor($usedPaidLeaves + $emp["used_sick_leaves"]), $totalLeaves);
                $halfDaysCoveredByPaidLeave = ($usedPaidLeaves + $emp["used_sick_leaves"] - $fullDaysCoveredByPaidLeave) * 2;
                $unpaidFullDays = max($totalLeaves - $fullDaysCoveredByPaidLeave, 0);
                $unpaidHalfDays = max($emp["half_days"] - $halfDaysCoveredByPaidLeave, 0);

                $fullDayDeduction = $unpaidFullDays * $emp["per_day"];

                // Half-day deduction based on missing hours (same logic as breakdown)
                $halfDayMissingHoursTotal = 0.0;
                foreach ($halfDayRows as $row) {
                    $displayWorkedSeconds = 0;
                    if (!empty($row["check_in_time"]) && !empty($row["check_out_time"])) {
                        $inTs = strtotime(substr($row["date"], 0, 10) . " " . $row["check_in_time"]);
                        $outTs = strtotime(substr($row["date"], 0, 10) . " " . $row["check_out_time"]);
                        if ($outTs > $inTs) {
                            $displayWorkedSeconds = $outTs - $inTs;
                        }
                    }

                    $displayWorkedHours = $displayWorkedSeconds / 3600;
                    $fullDayHoursRule = isset($rules["working_hours_per_day"])
                        ? (float) $rules["working_hours_per_day"]
                        : 8.0;
                    if ($displayWorkedHours + 0.01 >= $fullDayHoursRule) {
                        continue;
                    }

                    $requiredHours = $workingHoursPerDay > 0 ? $workingHoursPerDay : $fullDayHoursRule;
                    $missingHours = max($requiredHours - $displayWorkedHours, 0);
                    $halfDayMissingHoursTotal += $missingHours;
                }

                $perHourRateForHalfDay = $workingDays > 0 && $workingHoursPerDay > 0
                    ? $emp["salary"] / ($workingDays * $workingHoursPerDay)
                    : 0;
                $halfDayDeduction = $perHourRateForHalfDay > 0
                    ? round($halfDayMissingHoursTotal * $perHourRateForHalfDay, 2)
                    : 0;

                $leaveHalfDeduction = $fullDayDeduction + $halfDayDeduction;

                // Late arrival is tracked for info only – NOT deducted from salary
                $lateDeduction = 0;
                $overtimePay = 0;
                $overtimeSeconds = 0;
                if ($workingDays > 0 && $workingHoursPerDay > 0) {
                    if (($rules["enable_overtime"] ?? 0) == 1) {
                        $otResult = $attendanceModel
                            ->select("SUM(TIME_TO_SEC(overtime)) AS overtime_seconds")
                            ->where("user_id", $emp["user_id"])
                            ->where("date >=", $startOfMonth)
                            ->where("date <=", $endOfMonth)
                            ->first();
                        $overtimeSeconds = (int) ($otResult["overtime_seconds"] ?? 0);
                        if ($overtimeSeconds > 0) {
                            $overtimeHours = round($overtimeSeconds / 3600, 2);
                            $hourlyRate = $emp["salary"] / ($workingDays * $workingHoursPerDay);
                            $multiplier = ($rules["overtime_rate_type"] ?? "multiplier") === "multiplier"
                                ? ($rules["overtime_multiplier"] ?? 1.5) : 1;
                            $overtimePay = round($overtimeHours * $hourlyRate * $multiplier, 2);
                        }
                    }
                }

                $emp["late_deduction"] = 0; // late arrival not deducted
                $baseFullDayDeduction = $totalLeaves * $emp["per_day"];
                $baseLeaveHalfDeduction = $baseFullDayDeduction + $halfDayDeduction;
                $emp["base_deduction"] = round($baseLeaveHalfDeduction, 2);
                $emp["overtime_pay"] = $overtimePay;
                $emp["total_overtime_hours"] = round($overtimeSeconds / 3600, 2);
                $emp["salary_deduction"] = round($leaveHalfDeduction, 2);
                $emp["tax_deduction"] = $emp["tax_amount"];
                $emp["net_salary"] = round(
                    $emp["salary"]
                    - $emp["salary_deduction"]
                    - $emp["tax_deduction"]
                    + ($emp["overtime_pay"] ?? 0),
                    2,
                );
            }

            $leaveBalance = $employeeLeaveModel
                ->where('employee_id', $emp['user_id'])
                ->first();
            $currentPaidBalance = (float) ($leaveBalance['paid_leave'] ?? 0);
            $currentSickBalance = (float) ($leaveBalance['casual_leave'] ?? 0);

            // Use employee_leaves as the master balance, then rebuild the row's
            // editable opening balance by adding back the currently saved month's
            // used leaves (half-days do NOT consume paid leave quota).
            $emp['opening_paid_leaves'] = $currentPaidBalance
                + (float) ($payroll['used_paid_leaves'] ?? 0);
            $emp['opening_casual_leaves'] = $currentSickBalance
                + (float) ($payroll['used_sick_leaves'] ?? 0);

            // Remaining = opening minus only the used paid/sick leaves.
            // Half-days are NOT deducted from paid leave balance.
            $emp['remaining_paid_leaves'] = max(
                $emp['opening_paid_leaves'] - (float) ($emp['used_paid_leaves'] ?? 0),
                0,
            );
            $emp['remaining_casual_leaves'] = max(
                $emp['opening_casual_leaves'] - (float) ($emp['used_sick_leaves'] ?? 0),
                0,
            );

            // Calculate Total Adjustment (Additions - Deductions)
            $emp['total_adjustment'] = ($emp['overtime_pay'] ?? 0) - ($emp['salary_deduction'] ?? 0) - ($emp['tax_deduction'] ?? 0);
        }

        return view("payroll/salary-details", [
            "month" => $month,
            "employees" => $employees,
        ]);
    }

    public function saveAll()
    {
        $request = $this->request;

        $employeeIds = $request->getPost("employee_id");
        $salaries = $request->getPost("salary");
        $leaves = $request->getPost("leaves");
        $half_day = $request->getPost("half_day");
        $paid_leave = $request->getPost("paid_leave");
        $sick_leave = $request->getPost("sick_leave") ?? [];
        $deductions = $request->getPost("deduction");
        $netSalaries = $request->getPost("net_salary");
        $overtime_pay = $request->getPost("overtime_pay");
        $total_overtime_hours = $request->getPost("total_overtime_hours");
        $month = $request->getPost("month");

        $payrollModel = new \App\Models\PayrollModel();
        $employeeLeaveModel = new EmployeeLeaveModel();
        $companyRulesModel = new \App\Models\CompanyRulesModel();
        $rules = $companyRulesModel->first();
        $db = \Config\Database::connect();

        $db->transBegin();

        try {
            foreach ($employeeIds as $index => $empId) {
                $existing = $payrollModel
                    ->where("user_id", $empId)
                    ->where("month_year", $month)
                    ->first();

                $totalHalfDays = (float) ($half_day[$index] ?? 0);
                $usedPaidLeaves = (float) ($paid_leave[$index] ?? 0);
                $usedSickLeaves = (float) ($sick_leave[$index] ?? 0);
                $remainingLeaves = $this->syncEmployeeLeaveBalance(
                    $employeeLeaveModel,
                    (int) $empId,
                    (string) $month,
                    $usedPaidLeaves,
                    $usedSickLeaves,
                    $existing
                );

                // Determine tax deduction based on company rules
                $taxDeduction = $this->calculateTaxForSalary((float) $salaries[$index], $rules);

                $data = [
                    "user_id" => $empId,
                    "month_year" => $month,
                    "salary_amount" => $salaries[$index],
                    "total_leaves" => $leaves[$index],
                    "total_half_day" => $half_day[$index],
                    "used_paid_leaves" => $usedPaidLeaves,
                    "used_sick_leaves" => $usedSickLeaves,
                    "remaining_paid_leaves" => $remainingLeaves["paid_leave"],
                    "remaining_sick_leaves" => $remainingLeaves["casual_leave"],
                    "salary_deduction" => $deductions[$index],
                    "tax_deduction" => $taxDeduction,
                    "net_salary" => $netSalaries[$index],
                    "overtime_pay" => isset($overtime_pay[$index]) ? (float) $overtime_pay[$index] : 0,
                    "total_overtime_hours" => isset($total_overtime_hours[$index]) ? (float) $total_overtime_hours[$index] : 0,
                    "payment_date" => $existing ? $existing["payment_date"] : date("Y-m-d H:i:s"),
                    "payment_status" => $existing ? $existing["payment_status"] : "Paid",
                ];

                if ($existing) {
                    // Update existing record with latest calculated values from salary-details page
                    $data["id"] = $existing["id"];
                    $payrollModel->save($data);
                } else {
                    $data["created_at"] = date("Y-m-d H:i:s");
                    $payrollModel->insert($data);
                }

                if ($payrollModel->errors() || $employeeLeaveModel->errors()) {
                    throw new \RuntimeException("Failed to save payroll leave balances.");
                }
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException("Database transaction failed.");
            }
        } catch (\Throwable $e) {
            $db->transRollback();

            return $this->response->setStatusCode(500)->setJSON([
                "status" => "error",
                "message" => "Failed to save payroll data.",
            ]);
        }

        $db->transCommit();

        return $this->response->setJSON([
            "status" => "success",
            "message" => "Payroll data saved successfully for all employees.",
        ]);
    }

    /**
     * Get deduction breakdown for an employee in a month (for info modal).
     * Returns: leaves (dates), late (date + minutes), absent (dates), half-day (dates), overtime (date + hours), and amount breakdown.
     */
    public function getDeductionBreakdown()
    {
        $userId = $this->request->getPost("user_id") ?: $this->request->getGet("user_id");
        $month = $this->request->getPost("month") ?: $this->request->getGet("month");
        if (!$userId || !$month) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "user_id and month are required.",
            ])->setStatusCode(400);
        }
        $date = new \DateTime($month . "-01");
        $startOfMonth = $date->format("Y-m-01");
        $endOfMonth = $date->format("Y-m-t");

        $attendanceModel = new AttendanceModel();
        $leaveModel = new LeaveModel();
        $companyRulesModel = new CompanyRulesModel();
        $holidayCalendarModel = new HolidayCalendarModel();
        $rules = $companyRulesModel->first();
        $workingHoursPerDay = (float) ($rules["working_hours_per_day"] ?? 8);

        $holidayRows = $holidayCalendarModel
            ->where("holiday_date >=", $startOfMonth)
            ->where("holiday_date <=", $endOfMonth)
            ->findAll();
        $holidayDates = array_column($holidayRows, "holiday_date");

        $workingDaysData = $this->getWorkingDaysData(
            (int) $date->format("m"),
            (int) $date->format("Y"),
            $rules,
            $holidayDates,
        );
        $workingDays = $workingDaysData["working_days"];

        $manualSalary = $this->request->getPost("salary_amount") ?: $this->request->getGet("salary_amount");
        if ($manualSalary !== null && (float) $manualSalary > 0) {
            $salary = (float) $manualSalary;
        } else {
            $userInfo = (new UserInfoModel())->where("user_id", $userId)->first();
            $salary = $userInfo["salary"] ?? 0;
        }
        $perDay = $workingDays > 0 ? round($salary / $workingDays, 2) : 0;
        $perHour = ($workingDays > 0 && $workingHoursPerDay > 0) ? round($salary / ($workingDays * $workingHoursPerDay), 2) : 0;

        $attendance = $attendanceModel
            ->where("user_id", $userId)
            ->where("date >=", $startOfMonth)
            ->where("date <=", $endOfMonth)
            ->orderBy("date", "ASC")
            ->findAll();

        // Group statuses by date so that if a day has any present/half-day/etc record,
        // it is not treated as absent for payroll purposes.
        $statusesByDate = [];
        foreach ($attendance as $row) {
            $dateKey = substr($row["date"], 0, 10);
            $statusKey = strtolower($row["status"] ?? "");
            if (!isset($statusesByDate[$dateKey])) {
                $statusesByDate[$dateKey] = [];
            }
            if ($statusKey !== "") {
                $statusesByDate[$dateKey][] = $statusKey;
            }
        }

        // Build a quick lookup of dates that are covered by approved leave applications
        $leaveApplications = $leaveModel
            ->where("user_id", $userId)
            ->whereIn("status", ["approved", "Approved"])
            ->where("start_date <=", $endOfMonth)
            ->where("end_date >=", $startOfMonth)
            ->orderBy("start_date", "ASC")
            ->findAll();
        $leaveCoveredDates = [];
        foreach ($leaveApplications as $lv) {
            $start = max($lv["start_date"], $startOfMonth);
            $end = min($lv["end_date"], $endOfMonth);
            $current = strtotime($start);
            $endTs = strtotime($end);
            while ($current <= $endTs) {
                $leaveCoveredDates[date("Y-m-d", $current)] = true;
                $current = strtotime("+1 day", $current);
            }
        }

        $holidayLookup = array_flip($holidayDates);
        // Build lookup of Saturday dates that are company-wide scheduled half-days
        $satBreakdownDates = $this->getSaturdayHalfDayDates(
            (int) $date->format("m"),
            (int) $date->format("Y"),
            $rules
        );
        $absentDates = [];
        $halfDayDates = [];
        $lateList = [];
        $overtimeList = [];
        $halfDayMissingHoursTotal = 0.0;
        foreach ($attendance as $row) {
            $dateStr = $row["date"];
            $dateKey = substr($dateStr, 0, 10);
            $allStatusesForDate = $statusesByDate[$dateKey] ?? [];
            $hasNonAbsentStatusForDate = false;
            foreach ($allStatusesForDate as $st) {
                if ($st !== "absent" && $st !== "") {
                    $hasNonAbsentStatusForDate = true;
                    break;
                }
            }
            $dayOfWeek = (int) date("w", strtotime($dateStr)); // 0=Sun,6=Sat

            // Determine if this day is a non-working day (holiday/Sunday/Saturday-off).
            $isHoliday = isset($holidayLookup[$dateStr]);
            $isSundayOff = ($dayOfWeek === 0 && ($rules["sunday_off"] ?? 0) == 1);
            $isSaturdayOff = false;
            if (($rules["saturday_off_enabled"] ?? 0) == 1 && $dayOfWeek === 6) {
                if (($rules["saturday_off_type"] ?? "") === "all") {
                    $isSaturdayOff = true;
                }
            }
            $isNonWorking = $isHoliday || $isSundayOff || $isSaturdayOff;

            // Only treat a day as "absent" for payroll if:
            // - status is 'absent'
            // - it is a working day (not holiday/week off)
            // - it is NOT already covered by an approved leave
            if (
                $row["status"] === "absent" &&
                !$isNonWorking &&
                empty($leaveCoveredDates[$dateKey]) &&
                !$hasNonAbsentStatusForDate
            ) {
                $absentDates[] = ["date" => $dateStr, "label" => date("d M Y", strtotime($dateStr))];
            }
            if ($row["status"] === "half-day") {
                // Skip Saturdays that are company-wide scheduled half-days – those are
                // not employee-specific absences and should not appear in the deduction breakdown.
                if (isset($satBreakdownDates[$dateKey])) {
                    continue;
                }
                // Compute gross worked time from check-in/out (same as attendance history).
                $displayWorkedSeconds = 0;
                if (!empty($row["check_in_time"]) && !empty($row["check_out_time"])) {
                    $inTs = strtotime(substr($row["date"], 0, 10) . " " . $row["check_in_time"]);
                    $outTs = strtotime(substr($row["date"], 0, 10) . " " . $row["check_out_time"]);
                    if ($outTs > $inTs) {
                        $displayWorkedSeconds = $outTs - $inTs;
                    }
                }

                $displayWorkedHours = $displayWorkedSeconds / 3600;
                $fullDayHoursRule = isset($rules["working_hours_per_day"])
                    ? (float) $rules["working_hours_per_day"]
                    : 8.0;

                // If gross worked hours reach (or exceed) a full day, treat as full present (no half‑day here).
                if ($displayWorkedHours + 0.01 >= $fullDayHoursRule) {
                    continue;
                }

                // Calculate missing hours for this date based on gross worked time
                $requiredHours = $workingHoursPerDay > 0
                    ? $workingHoursPerDay
                    : (float) ($rules["working_hours_per_day"] ?? 8);
                $missingHours = max($requiredHours - $displayWorkedHours, 0);
                $halfDayMissingHoursTotal += $missingHours;

                $workedLabel = $displayWorkedSeconds > 0
                    ? sprintf(
                        "%dh %dm",
                        floor($displayWorkedHours),
                        round(($displayWorkedHours - floor($displayWorkedHours)) * 60)
                    )
                    : "0h 0m";
                $missingLabel = $missingHours > 0
                    ? sprintf("%.0fh %.0fm", floor($missingHours), round(($missingHours - floor($missingHours)) * 60))
                    : "0h 0m";

                $halfDayDates[] = [
                    "date" => $row["date"],
                    "label" => date("d M Y", strtotime($row["date"])),
                    "worked_text" => $workedLabel,
                    "missing_text" => $missingLabel,
                    "missing_hours" => $missingHours,
                ];
            }
            if ($row["status"] != "half-day") {
                if (!empty($row["is_late"]) && (int) $row["late_minutes"] > 0) {
                    $mins = (int) $row["late_minutes"];
                    $lateList[] = [
                        "date" => $row["date"],
                        "label" => date("d M Y", strtotime($row["date"])),
                        "late_minutes" => $mins,
                        "late_text" => $mins >= 60 ? floor($mins / 60) . "h " . ($mins % 60) . "m" : $mins . " min",
                    ];
                }
            }
            if (!empty($row["overtime"]) && $row["overtime"] !== "00:00:00") {
                $parts = array_map("intval", explode(":", $row["overtime"]));
                $secs = ($parts[0] ?? 0) * 3600 + ($parts[1] ?? 0) * 60 + ($parts[2] ?? 0);
                $hours = round($secs / 3600, 2);
                $overtimeList[] = [
                    "date" => $row["date"],
                    "label" => date("d M Y", strtotime($row["date"])),
                    "overtime_hours" => $hours,
                    "overtime_text" => ($parts[0] ?? 0) . "h " . ($parts[1] ?? 0) . "m",
                ];
            }
        }

        $leaveList = [];
        foreach ($leaveApplications as $lv) {
            $start = $lv["start_date"];
            $end = $lv["end_date"];
            if ($start < $startOfMonth)
                $start = $startOfMonth;
            if ($end > $endOfMonth)
                $end = $endOfMonth;
            $leaveList[] = [
                "start_date" => $start,
                "end_date" => $end,
                "label" => date("d M", strtotime($start)) . " - " . date("d M Y", strtotime($end)),
                "reason" => $lv["reason"] ?? "",
            ];
        }

        // Include inactive period in leave breakdown for inactive/resigned employees
        $empUserInfo = (new UserInfoModel())->where("user_id", $userId)->first();
        $empUserStatus = strtolower(trim($empUserInfo["status"] ?? "active"));
        if (in_array($empUserStatus, ["inactive", "resigned", "fired", "removed"])) {
            $lastWorkingDay = !empty($empUserInfo["last_working_day"]) ? trim($empUserInfo["last_working_day"]) : null;
            $latestPunch = $attendanceModel
                ->where("user_id", $userId)
                ->where("date >=", $startOfMonth)
                ->where("date <=", $endOfMonth)
                ->where("(status NOT IN ('absent', 'leave') OR (check_in_time IS NOT NULL AND check_in_time != '' AND check_in_time != '00:00:00'))")
                ->orderBy("date", "DESC")
                ->first();
            $latestPunchDate = !empty($latestPunch["date"]) ? substr($latestPunch["date"], 0, 10) : null;

            $inactiveAfterDate = null;
            if (!empty($lastWorkingDay) && $lastWorkingDay >= $startOfMonth && $lastWorkingDay <= $endOfMonth) {
                $inactiveAfterDate = $lastWorkingDay;
                if (!empty($latestPunchDate) && $latestPunchDate > $inactiveAfterDate) {
                    $inactiveAfterDate = $latestPunchDate;
                }
            } elseif (!empty($latestPunchDate)) {
                $inactiveAfterDate = $latestPunchDate;
            }

            if ($inactiveAfterDate && $inactiveAfterDate < $endOfMonth) {
                $inactiveStart = date("Y-m-d", strtotime($inactiveAfterDate . " +1 day"));
                $leaveList[] = [
                    "start_date" => $inactiveStart,
                    "end_date" => $endOfMonth,
                    "label" => date("d M", strtotime($inactiveStart)) . " - " . date("d M Y", strtotime($endOfMonth)),
                    "reason" => "Inactive after " . date("d M Y", strtotime($inactiveAfterDate)),
                ];
            }
        }

        $totalLeavesInput = $this->request->getPost("total_leaves") ?: $this->request->getGet("total_leaves");
        if ($totalLeavesInput !== null) {
            $totalLeaves = (float) $totalLeavesInput;
        } else {
            $totalLeaves = $this->countMonthlyLeaves($userId, $month, $rules, $leaveModel, $holidayDates);
        }

        $halfDaysInput = $this->request->getPost("total_halfday_leaves") ?: $this->request->getGet("total_halfday_leaves");
        if ($halfDaysInput !== null) {
            $halfDayCount = (float) $halfDaysInput;
            $halfDayMissingHoursTotal = $halfDayCount * ($workingHoursPerDay / 2);
        } else {
            $halfDayCount = count($halfDayDates);
        }

        $totalLateMinutes = array_sum(array_column($lateList, "late_minutes"));
        $lateDeduction = round(($totalLateMinutes / 60) * $perHour, 2);
        $leaveDeduction = $totalLeaves * $perDay;
        // Half-day deduction is proportional to total missing hours across all half-day dates
        $halfDayDeduction = $perHour > 0
            ? round($halfDayMissingHoursTotal * $perHour, 2)
            : 0;
        $totalOvertimeSeconds = 0;
        foreach ($attendance as $row) {
            if (!empty($row["overtime"]) && $row["overtime"] !== "00:00:00") {
                $parts = array_map("intval", explode(":", $row["overtime"]));
                $totalOvertimeSeconds += ($parts[0] ?? 0) * 3600 + ($parts[1] ?? 0) * 60 + ($parts[2] ?? 0);
            }
        }
        $overtimeHours = round($totalOvertimeSeconds / 3600, 2);
        $multiplier = ($rules["overtime_rate_type"] ?? "multiplier") === "multiplier" ? ($rules["overtime_multiplier"] ?? 1.5) : 1;
        $overtimePay = ($workingDays > 0 && $workingHoursPerDay > 0 && $overtimeHours > 0)
            ? round($overtimeHours * $perHour * $multiplier, 2) : 0;

        return $this->response->setJSON([
            "status" => "success",
            "data" => [
                "employee_name" => $userInfo["firstname"] ?? "Employee",
                "month_label" => $date->format("F Y"),
                "leaves" => ["dates" => $leaveList, "count" => $totalLeaves, "deduction_amount" => round($leaveDeduction, 2)],
                "absent" => ["dates" => $absentDates, "count" => count($absentDates)],
                "half_day" => [
                    "dates" => $halfDayDates,
                    "count" => $halfDayCount,
                    "deduction_amount" => round($halfDayDeduction, 2),
                    "total_missing_hours" => $halfDayMissingHoursTotal,
                ],
                "late" => ["list" => $lateList, "total_minutes" => $totalLateMinutes, "deduction_amount" => $lateDeduction],
                "overtime" => ["list" => $overtimeList, "total_hours" => $overtimeHours, "pay_amount" => $overtimePay],
                "summary" => [
                    "total_deduction" => round($leaveDeduction + $halfDayDeduction, 2), // late arrival excluded
                    "overtime_added" => $overtimePay,
                    "per_day_salary" => $perDay,
                    "per_hour_salary" => $perHour,
                ],
            ],
        ]);
    }

    /**
     * API: Return extra-day (Sat/Sun full-day) attendance details for a given
     * employee and month.  Used to power the info popup in salary-details.
     *
     * POST params: user_id, month (YYYY-MM)
     */
    public function getExtraDayDetails()
    {
        $userId = $this->request->getPost('user_id') ?: $this->request->getGet('user_id');
        $month  = $this->request->getPost('month')   ?: $this->request->getGet('month');

        if (!$userId || !$month) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'user_id and month are required.',
            ])->setStatusCode(400);
        }

        $date         = new \DateTime($month . '-01');
        $startOfMonth = $date->format('Y-m-01');
        $endOfMonth   = $date->format('Y-m-t');

        $attendanceModel      = new AttendanceModel();
        $companyRulesModel    = new CompanyRulesModel();
        $holidayCalendarModel = new HolidayCalendarModel();
        $userInfoModel        = new UserInfoModel();

        $rules = $companyRulesModel->first();

        // Build holiday lookup for this month
        $holidayRows  = $holidayCalendarModel
            ->where('holiday_date >=', $startOfMonth)
            ->where('holiday_date <=', $endOfMonth)
            ->findAll();
        $holidayLookup = array_flip(array_column($holidayRows, 'holiday_date'));

        // Working-days data (to compute per-day salary)
        $workingDaysData    = $this->getWorkingDaysData(
            (int) $date->format('m'),
            (int) $date->format('Y'),
            $rules,
            array_column($holidayRows, 'holiday_date')
        );
        $workingDays        = $workingDaysData['working_days'];
        $workingHoursPerDay = (float) ($rules['working_hours_per_day'] ?? 8);

        // Salary (use saved payroll salary_amount when available)
        $payrollModel = new PayrollModel();
        $savedPayroll = $payrollModel
            ->where('user_id', $userId)
            ->where('month_year', $month)
            ->first();
        if ($savedPayroll && !empty($savedPayroll['salary_amount']) && (float) $savedPayroll['salary_amount'] > 0) {
            $salary = (float) $savedPayroll['salary_amount'];
        } else {
            $userInfo = $userInfoModel->where('user_id', $userId)->first();
            $salary   = (float) ($userInfo['salary'] ?? 0);
        }
        $perDay = $workingDays > 0 ? round($salary / $workingDays, 2) : 0;

        // Fetch all weekend attendance (including half-day status for Saturday partial work)
        $attRows = $attendanceModel
            ->where('user_id', $userId)
            ->where('date >=', $startOfMonth)
            ->where('date <=', $endOfMonth)
            ->whereNotIn('status', ['absent', 'leave'])
            ->findAll();

        $fullDayHrsModal = (float) ($rules['working_hours_per_day'] ?? 8.0);
        $seenDates       = [];
        $details         = [];
        $totalExtraCredit = 0.0;

        foreach ($attRows as $row) {
            $dateStr = substr($row['date'], 0, 10);
            $dow     = (int) date('w', strtotime($dateStr)); // 0=Sun, 6=Sat
            if ($dow !== 0 && $dow !== 6) continue;
            if (isset($holidayLookup[$dateStr])) continue;
            if (empty($row['check_in_time'])) continue;

            // Compute gross worked hours
            $workedHrs = 0.0;
            if (!empty($row['check_in_time']) && !empty($row['check_out_time'])) {
                $inTs  = strtotime($dateStr . ' ' . $row['check_in_time']);
                $outTs = strtotime($dateStr . ' ' . $row['check_out_time']);
                if ($outTs > $inTs) {
                    $workedHrs = ($outTs - $inTs) / 3600.0;
                }
            } elseif (!empty($row['work_hours']) && $row['work_hours'] !== '00:00:00') {
                $parts = array_map('intval', explode(':', $row['work_hours']));
                $workedHrs = ($parts[0] ?? 0) + ($parts[1] ?? 0) / 60.0 + ($parts[2] ?? 0) / 3600.0;
            }

            // Determine credit
            if ($dow === 6 && $this->isSaturdayWeekOff($dateStr, $rules)) {
                $extraCredit = ($workedHrs + 0.01 >= $fullDayHrsModal) ? 1.0 : 0.5;
                $isHalfExtra = ($extraCredit < 1.0);
            } else {
                $extraCredit = 1.0;
                $isHalfExtra = false;
            }

            // Deduplicate — if already seen, keep higher credit entry
            if (in_array($dateStr, $seenDates, true)) {
                foreach ($details as &$existing) {
                    if ($existing['date'] === $dateStr && $extraCredit > $existing['extra_credit']) {
                        $existing['extra_credit']   = $extraCredit;
                        $existing['is_half_extra']  = $isHalfExtra;
                        $existing['worked_hours']   = round($workedHrs, 2);
                        $existing['check_in_time']  = $row['check_in_time'];
                        $existing['check_out_time'] = $row['check_out_time'] ?? null;
                    }
                }
                unset($existing);
                continue;
            }

            $seenDates[] = $dateStr;
            $details[]   = [
                'date'           => $dateStr,
                'day_name'       => date('l', strtotime($dateStr)),
                'formatted_date' => date('d M Y', strtotime($dateStr)),
                'check_in_time'  => $row['check_in_time'],
                'check_out_time' => $row['check_out_time'] ?? null,
                'worked_hours'   => round($workedHrs, 2),
                'extra_credit'   => $extraCredit,
                'is_half_extra'  => $isHalfExtra,
            ];
        }

        // Sum float credits
        foreach ($details as $d2) {
            $totalExtraCredit += $d2['extra_credit'];
        }

        $extraDays   = $totalExtraCredit;                  // float e.g. 0.5, 1.5
        $extraDayPay = round($totalExtraCredit * $perDay, 2);

        // Fetch employee name for display
        $userInfoForName = $userInfoModel->where('user_id', $userId)->first();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'employee_name' => ($userInfoForName['firstname'] ?? '') . ' ' . ($userInfoForName['lastname'] ?? ''),
                'month_label'   => $date->format('F Y'),
                'extra_days'    => $extraDays,
                'extra_day_pay' => $extraDayPay,
                'per_day'       => $perDay,
                'details'       => $details,
            ],
        ]);
    }

    public function getPreviousAdjustment()
    {
        $userId = $this->request->getPost("user_id");
        $monthYear = $this->request->getPost("month"); // This is the current month being processed

        if (!$userId || !$monthYear) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Missing parameters"
            ]);
        }

        // Calculate last month
        $lastMonth = date("Y-m", strtotime($monthYear . " -1 month"));

        $payrollModel = new PayrollModel();
        $previous = $payrollModel
            ->where("user_id", $userId)
            ->where("month_year", $lastMonth)
            ->first();

        return $this->response->setJSON([
            "status" => "success",
            "data" => [
                "amount" => $previous["adjustment_amount"] ?? 0,
                "remark" => $previous["adjustment_remark"] ?? "No adjustment recorded"
            ],
            "last_month_label" => date("F Y", strtotime($lastMonth))
        ]);
    }

    public function savedata()
    {
        $userId = $this->request->getPost("employee_id");
        $month = $this->request->getPost("month");
        $salary = $this->request->getPost("salary");

        $payrollModel = new PayrollModel();
        $employeeLeaveModel = new EmployeeLeaveModel();
        $companyRulesModel = new \App\Models\CompanyRulesModel();
        $rules = $companyRulesModel->first();
        $taxDeduction = $this->calculateTaxForSalary((float) $salary, $rules);

        $totalHalfDays = (float) ($this->request->getPost("half_day") ?? 0);
        $usedPaidLeaves = (float) ($this->request->getPost("paid_leave") ?? 0);
        $usedSickLeaves = (float) ($this->request->getPost("sick_leave") ?? 0);

        $existing = $payrollModel
            ->where("user_id", $userId)
            ->where("month_year", $month)
            ->first();

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $remainingLeaves = $this->syncEmployeeLeaveBalance(
                $employeeLeaveModel,
                (int) $userId,
                (string) $month,
                $usedPaidLeaves,
                $usedSickLeaves,
                $existing
            );

            $data = [
                "user_id" => $userId,
                "month_year" => $month,
                "salary_amount" => $salary,
                "total_leaves" => $this->request->getPost("leaves"),
                "total_half_day" => $this->request->getPost("half_day"),
                "used_paid_leaves" => $usedPaidLeaves,
                "used_sick_leaves" => $usedSickLeaves,
                "remaining_paid_leaves" => $remainingLeaves["paid_leave"],
                "remaining_sick_leaves" => $remainingLeaves["casual_leave"],
                "salary_deduction" => $this->request->getPost("deduction"),
                "tax_deduction" => $taxDeduction,
                "net_salary" => $this->request->getPost("net_salary"),
                "overtime_pay" => (float) ($this->request->getPost("overtime_pay") ?? 0),
                "total_overtime_hours" => (float) ($this->request->getPost("total_overtime_hours") ?? 0),
                "adjustment_amount" => (float) ($this->request->getPost("adjustment_amount") ?? 0),
                "adjustment_remark" => $this->request->getPost("adjustment_remark"),
                "payment_date" => $existing ? $existing["payment_date"] : date("Y-m-d H:i:s"),
                "payment_status" => $existing ? $existing["payment_status"] : "Paid",
            ];

            if ($existing) {
                $data["id"] = $existing["id"];
                $payrollModel->save($data);
            } else {
                $payrollModel->insert($data);
            }

            $payrollErrors = $payrollModel->errors();
            $leaveErrors   = $employeeLeaveModel->errors();
            if ($payrollErrors || $leaveErrors || $db->transStatus() === false) {
                $errMsg = '';
                if ($payrollErrors) {
                    $errMsg .= 'Payroll: ' . implode(', ', $payrollErrors) . ' ';
                }
                if ($leaveErrors) {
                    $errMsg .= 'Leave: ' . implode(', ', $leaveErrors) . ' ';
                }
                if ($db->transStatus() === false) {
                    $errMsg .= 'DB transaction failed. ';
                }
                throw new \RuntimeException(trim($errMsg) ?: "Failed to save salary data.");
            }
        } catch (\Throwable $e) {
            $db->transRollback();

            return $this->response->setStatusCode(500)->setJSON([
                "status"  => "error",
                "message" => $e->getMessage() ?: "Failed to save salary for the employee.",
            ]);
        }

        $db->transCommit();

        return $this->response->setStatusCode(200)->setJSON([
            "status" => "success",
            "message" => $existing
                ? "Salary updated for the employee."
                : "Salary saved for the employee.",
        ]);
    }

    private function syncEmployeeLeaveBalance(
        EmployeeLeaveModel $employeeLeaveModel,
        int $employeeId,
        string $monthYear,
        float $usedPaidLeaves,
        float $usedSickLeaves,
        ?array $existingPayroll = null
    ): array {
        $leaveBalance = $employeeLeaveModel
            ->where("employee_id", $employeeId)
            ->first();
        $currentPaidBalance = (float) ($leaveBalance["paid_leave"] ?? 0);
        $currentSickBalance = (float) ($leaveBalance["casual_leave"] ?? 0);
        $openingPaidLeave = $currentPaidBalance
            + (float) ($existingPayroll["used_paid_leaves"] ?? 0);
        $openingSickLeave = $currentSickBalance
            + (float) ($existingPayroll["used_sick_leaves"] ?? 0);
        $remainingPaidLeave = max(
            $openingPaidLeave - $usedPaidLeaves,
            0,
        );
        $remainingSickLeave = max(
            $openingSickLeave - $usedSickLeaves,
            0,
        );

        $leaveData = [
            "employee_id" => $employeeId,
            "paid_leave" => $remainingPaidLeave,
            "casual_leave" => $remainingSickLeave,
        ];

        if (!empty($leaveBalance["id"])) {
            $leaveData["id"] = $leaveBalance["id"];
        }

        $employeeLeaveModel->save($leaveData);

        return [
            "opening_paid_leave" => $openingPaidLeave,
            "opening_casual_leave" => $openingSickLeave,
            "paid_leave" => $remainingPaidLeave,
            "casual_leave" => $remainingSickLeave,
        ];
    }

    private function generateCombinedSlipPdf(array $payrollIds)
    {
        $payrollModel = new PayrollModel();
        $userInfoModel = new UserInfoModel();
        $companyModel = new CompanyLogoModel();
        $designationModel = new DesignationModel();
        $departmentModel = new DepartmentModel();
        $onboardingModel = new OnboardingModel();
        $attendanceModel = new AttendanceModel();
        $companyRulesModel = new CompanyRulesModel();

        $slipsHtml = [];

        foreach ($payrollIds as $payrollId) {
            try {
                $payroll = $payrollModel->find($payrollId);
                if (!$payroll)
                    continue;

                $userInfo = $userInfoModel->where("user_id", $payroll["user_id"])->first();
                if (!$userInfo)
                    continue;

                $designation = $designationModel->find($userInfo["designation_id"]);
                $department = $departmentModel->find($userInfo["department_id"]);
                $onboarding = $onboardingModel->where("job_id", $userInfo["job_id"])->first();
                $company = $companyModel->orderBy("id", "DESC")->first();

                $companyLogoBase64 = "";
                $logoFile = !empty($company["pdf_logo"]) ? $company["pdf_logo"] : ($company["logo_img"] ?? "");
                $companyLogoPath = FCPATH . "upload/" . $logoFile;
                if (empty($logoFile) || !is_file($companyLogoPath)) {
                    $companyLogoPath = FCPATH . "public/assets/images/fab_logo.jpg";
                }
                if (is_file($companyLogoPath)) {
                    $type = pathinfo($companyLogoPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($companyLogoPath);
                    $companyLogoBase64 = "data:image/" . $type . ";base64," . base64_encode($data);
                }

                $db = \Config\Database::connect();
                if ($db->tableExists('company_address')) {
                    $companyAddressRow = $db->table('company_address')->orderBy('id', 'DESC')->get()->getRowArray();
                    if ($companyAddressRow && !empty($companyAddressRow['office_address'])) {
                        if ($company) {
                            $company['company_address'] = $companyAddressRow['office_address'];
                        }
                    }
                }

                $monthYear = $payroll["month_year"];
                [$year, $monthNum] = explode("-", $monthYear);
                $rules = $companyRulesModel->first();

                $workingDaysData = $this->getWorkingDaysData((int) $monthNum, (int) $year, $rules, []);
                $workingDays = $workingDaysData["working_days"];

                $startOfMonth = "$year-" . str_pad($monthNum, 2, "0", STR_PAD_LEFT) . "-01";
                $endOfMonth = date("Y-m-t", strtotime($startOfMonth));
                $attendanceData = $attendanceModel->where("user_id", $payroll["user_id"])
                    ->where("date >=", $startOfMonth)->where("date <=", $endOfMonth)->findAll();

                $presentDays = 0;
                $absentDays = 0;
                $halfDays = 0;
                $unpaidLeaves = 0;
                foreach ($attendanceData as $att) {
                    $status = strtolower($att["status"]);
                    if ($status === "present")
                        $presentDays++;
                    elseif ($status === "absent")
                        $absentDays++;
                    elseif ($status === "half-day" || $status === "halfday")
                        $halfDays++;
                    elseif ($status === "unpaid leave")
                        $unpaidLeaves++;
                }

                $baseSalary = floatval($payroll["salary_amount"]);
                $totalEarnings = $baseSalary + floatval($payroll["overtime_pay"] ?? 0) + floatval($payroll["bonuses"] ?? 0);
                $totalDeductions = floatval($payroll["salary_deduction"] ?? 0) + floatval($payroll["tax_deduction"] ?? 0);

                $calculatedData = [
                    "working_days" => $workingDays,
                    "present_days" => $presentDays,
                    "absent_days" => $absentDays,
                    "total_leaves" => $payroll["total_leaves"] ?? 0,
                    "used_paid_leaves" => $payroll["used_paid_leaves"] ?? 0,
                    "unpaid_leaves" => max((float)($payroll["total_leaves"] ?? 0) + ((float)(isset($payroll["total_half_day"]) ? $payroll["total_half_day"] : $halfDays) * 0.5) - (float)($payroll["used_paid_leaves"] ?? 0) - (float)($payroll["used_sick_leaves"] ?? 0), 0),
                    "half_days" => isset($payroll["total_half_day"]) ? $payroll["total_half_day"] : $halfDays,
                    "worked_hours" => $payroll["worked_hours"] ?? 0,
                    "total_overtime_hours" => $payroll["total_overtime_hours"] ?? 0,
                    "total_earnings" => $totalEarnings,
                    "total_deductions" => $totalDeductions,
                    "salary_deduction" => $payroll["salary_deduction"] ?? 0,
                ];

                $slipData = [
                    "payroll" => $payroll,
                    "user" => $userInfo,
                    "designation" => $designation,
                    "department" => $department,
                    "company" => $company,
                    "companyLogoBase64" => $companyLogoBase64,
                    "onboarding" => $onboarding,
                    "calculatedData" => $calculatedData,
                ];

                $slipsHtml[] = view("payroll/salary_slip", $slipData);
            } catch (\Exception $e) {
                log_message("error", "Failed to generate slip: " . $e->getMessage());
            }
        }

        if (empty($slipsHtml))
            return null;

        try {
            $dompdf = new \Dompdf\Dompdf([
                "isRemoteEnabled" => true,
                "isHtml5ParserEnabled" => true,
                "isFontSubsettingEnabled" => true,
            ]);
            $dompdf->loadHtml(implode($slipsHtml));
            $dompdf->setPaper("A4", "portrait");
            $dompdf->render();
            return $dompdf->output();
        } catch (\Exception $e) {
            log_message("error", "PDF generation failed: " . $e->getMessage());
            return null;
        }
    }

    public function getEmployees()
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['admin', 'hr'])) {
            return $this->failUnauthorized('Unauthorized');
        }

        $branchId = $this->authService->getBranchId();

        $userInfoModel = new \App\Models\UserInfoModel();
        $builder = $userInfoModel->select('user_info.user_id, user_info.firstname, user_info.lastname')
            ->join('users', 'users.id = user_info.user_id')
            ->orderBy('user_info.firstname', 'ASC');

        if ($branchId !== null) {
            $builder->where('users.branch_id', $branchId);
        }

        $employees = $builder->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $employees
        ]);
    }

    public function downloadYearly()
    {
        $userAuth = $this->authService->check();
        if (!$userAuth || !in_array($userAuth->role, ['admin', 'hr'])) {
            return $this->failUnauthorized('Unauthorized');
        }

        $input = $this->request->getJSON();
        $userId = $input->user_id ?? null;
        $year = $input->year ?? null;
        $startMonth = $input->start_month ?? '01';
        $endMonth = $input->end_month ?? '12';

        if (!$userId || !$year) {
            return $this->failValidationErrors('User ID and Year are required');
        }

        $startStr = "$year-" . str_pad($startMonth, 2, "0", STR_PAD_LEFT);
        $endStr = "$year-" . str_pad($endMonth, 2, "0", STR_PAD_LEFT);

        $payrollModel = new \App\Models\PayrollModel();
        $payrolls = $payrollModel->where('user_id', $userId)
            ->where('month_year >=', $startStr)
            ->where('month_year <=', $endStr)
            ->orderBy('month_year', 'ASC')
            ->findAll();

        if (empty($payrolls)) {
            return $this->failNotFound('No payroll records found for this range');
        }

        $payrollIds = array_column($payrolls, 'id');
        $filename = "salary-slips-$startStr-to-$endStr.pdf";

        $pdfOutput = $this->generateCombinedSlipPdf($payrollIds);

        if (!$pdfOutput) {
            return $this->fail("No salary slips could be generated");
        }

        return $this->response
            ->setContentType("application/pdf")
            ->setBody($pdfOutput)
            ->setHeader("Content-Disposition", 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export Payroll records to styled Excel (.xlsx)
     */
    public function exportExcel()
    {
        $user = $this->authService->user();
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Unauthorized']);
        }

        $departmentId = $this->request->getGet('department_id');
        $month = $this->request->getGet('month');
        $year = $this->request->getGet('year');
        $status = $this->request->getGet('status');
        $search = $this->request->getGet('search');

        $builder = $this->payrollModel->builder();
        $builder->select('payroll.*, user_info.firstname, user_info.lastname, department.department_name, designation.designation_name')
            ->join('user_info', 'user_info.user_id = payroll.user_id', 'left')
            ->join('department', 'department.id = user_info.department_id', 'left')
            ->join('designation', 'designation.id = user_info.designation_id', 'left');

        if (!in_array($user->role, ['admin', 'hr'])) {
            $builder->where('payroll.user_id', $user->sub);
        }

        if (!empty($departmentId)) {
            $builder->where('department.id', (int)$departmentId);
        }
        if (!empty($month) && !empty($year)) {
            $my = sprintf('%04d-%02d', (int)$year, (int)$month);
            $builder->where('payroll.month_year', $my);
        } elseif (!empty($year)) {
            $builder->like('payroll.month_year', (string)$year, 'after');
        } elseif (!empty($month)) {
            $builder->like('payroll.month_year', sprintf('-%02d', (int)$month), 'before');
        }

        if (!empty($status)) {
            $builder->where('payroll.payment_status', $status);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('user_info.firstname', $search)
                ->orLike('user_info.lastname', $search)
                ->orLike('department.department_name', $search)
                ->orLike('payroll.month_year', $search)
                ->groupEnd();
        }

        $records = $builder->orderBy('payroll.month_year', 'DESC')
            ->orderBy('payroll.id', 'DESC')
            ->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payroll Report');

        $headers = [
            'A1' => 'S.No',
            'B1' => 'Employee Name',
            'C1' => 'Department',
            'D1' => 'Designation',
            'E1' => 'Month / Year',
            'F1' => 'Base Salary (₹)',
            'G1' => 'Total Leaves',
            'H1' => 'Half Days',
            'I1' => 'Overtime Hours',
            'J1' => 'Overtime Pay (₹)',
            'K1' => 'Bonuses (₹)',
            'L1' => 'Tax Deduction (₹)',
            'M1' => 'Salary Deduction (₹)',
            'N1' => 'Adjustment (₹)',
            'O1' => 'Adjustment Remark',
            'P1' => 'Net Salary (₹)',
            'Q1' => 'Payment Status',
            'R1' => 'Payment Date',
            'S1' => 'Bank Name',
            'T1' => 'Account Number',
            'U1' => 'IFSC Code'
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E66136']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ];
        $sheet->getStyle('A1:U1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $rowNum = 2;
        $sno = 1;
        foreach ($records as $item) {
            $fullName = trim(($item['firstname'] ?? '') . ' ' . ($item['lastname'] ?? '')) ?: 'N/A';
            $baseSalary = (float)($item['salary_amount'] ?? 0);
            $otPay = (float)($item['overtime_pay'] ?? 0);
            $bonuses = (float)($item['bonuses'] ?? 0);
            $taxDeduct = (float)($item['tax_deduction'] ?? 0);
            $salDeduct = (float)($item['salary_deduction'] ?? 0);
            $adjAmount = (float)($item['adjustment_amount'] ?? 0);
            $netSalary = (float)($item['net_salary'] ?? 0);

            $sheet->setCellValue('A' . $rowNum, $sno++);
            $sheet->setCellValue('B' . $rowNum, $fullName);
            $sheet->setCellValue('C' . $rowNum, $item['department_name'] ?? '-');
            $sheet->setCellValue('D' . $rowNum, $item['designation_name'] ?? '-');
            $sheet->setCellValue('E' . $rowNum, $item['month_year'] ?? '-');
            $sheet->setCellValue('F' . $rowNum, $baseSalary);
            $sheet->setCellValue('G' . $rowNum, $item['total_leaves'] ?? 0);
            $sheet->setCellValue('H' . $rowNum, $item['total_half_day'] ?? 0);
            $sheet->setCellValue('I' . $rowNum, $item['total_overtime_hours'] ?? 0);
            $sheet->setCellValue('J' . $rowNum, $otPay);
            $sheet->setCellValue('K' . $rowNum, $bonuses);
            $sheet->setCellValue('L' . $rowNum, $taxDeduct);
            $sheet->setCellValue('M' . $rowNum, $salDeduct);
            $sheet->setCellValue('N' . $rowNum, $adjAmount);
            $sheet->setCellValue('O' . $rowNum, $item['adjustment_remark'] ?? '-');
            $sheet->setCellValue('P' . $rowNum, $netSalary);
            $sheet->setCellValue('Q' . $rowNum, ucfirst($item['payment_status'] ?? 'Pending'));
            $sheet->setCellValue('R' . $rowNum, !empty($item['payment_date']) ? date('Y-m-d', strtotime($item['payment_date'])) : '-');
            $sheet->setCellValue('S' . $rowNum, $item['bank_name'] ?? '-');
            $sheet->setCellValueExplicit('T' . $rowNum, (string)($item['acc_number'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('U' . $rowNum, $item['ifsc_code'] ?? '-');

            // Format Currency columns
            $sheet->getStyle('F' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('J' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('K' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('L' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('M' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('N' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('P' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');

            $rowNum++;
        }

        $lastRow = $rowNum > 2 ? $rowNum - 1 : 2;
        $borderStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
        ];
        $sheet->getStyle('A1:U' . $lastRow)->applyFromArray($borderStyle);

        foreach (range('A', 'U') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        $filename = 'Payroll_Report_' . date('Y_m_d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
