<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use App\Models\LeaveModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Services\AuthService;
use App\Models\UserModel;
use App\Models\CompanyRulesModel;
use App\Models\HolidayCalendarModel;
use App\Models\LocationSettingsModel;
use App\Models\NotificationSettingsModel;
use App\Services\PushNotificationService;

class AttendanceController extends ResourceController
{
    private $attendanceModel;
    private $leaveModel;
    private $authService;
    private $companyRulesModel;
    private $pushNotificationService;
    private $userModel;
    private $holidayCalendarModel;
    private $locationSettingsModel;
    private $notificationSettingsModel;

    /**
     * ═══════════════════════════════════════════════════════════════
     * SINGLE SOURCE OF TRUTH — Daily Attendance Status
     * ═══════════════════════════════════════════════════════════════
     * Multi-punch rule: always use FIRST check-in → LAST check-out
     * of the day as the gross work window, then:
     *   1. Subtract meal break (unless Saturday half-day)
     *   2. Add grace minutes before comparing thresholds
     *   3. Apply thresholds:
     *        net < 4 h  → absent
     *        net 4–6 h  → half-day
     *        net ≥ 6 h  → present
     *   4. Hourly payroll: present if any hours worked > 0
     *
     * @param string      $date       Y-m-d
     * @param string      $firstIn    Earliest check-in  HH:MM:SS
     * @param string      $lastOut    Latest check-out   HH:MM:SS
     * @param string|null $mealBreak  Meal break duration HH:MM:SS or null
     * @param array       $rule       Company rules row
     * @return array { net_seconds, work_hours, status }
     */
    private function calculateDayStatus(
        string $date,
        string $firstIn,
        string $lastOut,
        ?string $mealBreak,
        array $rule
    ): array {
        // Guard: both times required
        if (empty($firstIn) || empty($lastOut)) {
            return ['net_seconds' => 0, 'work_hours' => '00:00:00', 'status' => 'absent'];
        }

        // 1. Gross window: last check-out − first check-in
        $inSec  = $this->timeToSeconds($firstIn);
        $outSec = $this->timeToSeconds($lastOut);
        if ($outSec <= $inSec) {
            return ['net_seconds' => 0, 'work_hours' => '00:00:00', 'status' => 'absent'];
        }
        $grossSec = $outSec - $inSec;

        // 2. Subtract meal break (skip on Saturday half-day)
        $mealSec = 0;
        if (!$this->isSaturdayHalfDay($date, $rule) && !empty($mealBreak)) {
            $mealSec = $this->timeToSeconds($mealBreak);
        }
        $netSec = max(0, $grossSec - $mealSec);

        // 3. Grace buffer (add before comparing thresholds)
        $graceSec     = ((int)($rule['grace_minutes'] ?? 0)) * 60;
        $effectiveSec = $netSec + $graceSec;

        // 4. Status thresholds — Saturday override or standard
        $payrollType = $rule['payroll_type'] ?? 'monthly';
        if ($payrollType === 'hourly') {
            $status = $netSec > 0 ? 'present' : 'absent';
        } else {
            // Detect if this is a Working Saturday with the 4-hour full-day override
            $isSatWorking = $this->isSaturdayWorking($date, $rule);
            $overrideEnabled = (int) ($rule['saturday_full_day_override'] ?? 1) === 1;

            if ($isSatWorking && $overrideEnabled) {
                $satHours       = (float) ($rule['saturday_working_hours'] ?? 4);
                $fullDaySec     = $satHours * 3600;
                $halfDaySec     = ($satHours / 2) * 3600;
            } else {
                $fullDaySec = 6 * 3600;  // ≥ 6 h → Present
                $halfDaySec = 4 * 3600;  // 4–6 h → Half-Day
            }

            if ($effectiveSec >= $fullDaySec) {
                $status = 'present';
            } elseif ($effectiveSec >= $halfDaySec) {
                $status = 'half-day';
            } else {
                $status = 'absent';
            }
        }

        $workHours = sprintf(
            '%02d:%02d:%02d',
            intdiv($netSec, 3600),
            intdiv($netSec % 3600, 60),
            $netSec % 60
        );

        return [
            'net_seconds' => $netSec,
            'work_hours'  => $workHours,
            'status'      => $status,
        ];
    }

    private function timeToSeconds(?string $time): int
    {
        if (empty($time)) {
            return 0;
        }

        $parts = array_map('intval', explode(':', $time));

        $h = $parts[0] ?? 0;
        $m = $parts[1] ?? 0;
        $s = $parts[2] ?? 0;

        return ($h * 3600) + ($m * 60) + $s;
    }


    public function __construct()
    {
        $this->attendanceModel = new AttendanceModel();
        $this->companyRulesModel = new CompanyRulesModel();
        $this->leaveModel = new LeaveModel();
        $this->authService = new AuthService(service('request'));
        $this->pushNotificationService = new PushNotificationService();
        $this->hierarchyService = new \App\Services\HierarchyService();
        $this->userModel = new UserModel();
    }

    /**
     * Get the correct company rule set for a specific user (branch-aware).
     * If the user has a branch_id with a branch_rules row, that row is returned.
     * Otherwise falls back to global company_rules.
     */
    private function getBranchRulesForUser(int $userId): array
    {
        $branchRulesModel = new \App\Models\BranchRulesModel();
        $rule = $branchRulesModel->getRulesForUser($userId);

        if ($rule) {
            // Normalize: branch_rules uses grace_minutes; company_rules uses grace_period
            if (!isset($rule['grace_minutes']) && isset($rule['grace_period'])) {
                $rule['grace_minutes'] = $rule['grace_period'];
            }
            return $rule;
        }

        // Absolute fallback — global company_rules
        return $this->companyRulesModel->orderBy('id', 'DESC')->first() ?? [];
    }

    public function display()
    {
        return view('attendence/attendence');
    }

    public function getStatus()
    {
        // Authenticate user
        $user = $this->authService->check();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $date = date('Y-m-d');
        $userModel = new UserModel();
        $userData = $userModel->find($user->sub);
        if (!$userData) {
            return $this->respond(['status' => 'error', 'message' => 'User not found'], 404);
        }

        // Check if user has face photo for biometric attendance
        $userInfoModel = new \App\Models\UserInfoModel();
        $userInfo = $userInfoModel->where('user_id', $user->sub)->first();
        $hasFacePhoto = !empty($userInfo['face_photo']);
        $isRemote = !empty($userInfo['working_location']) && strtolower(trim($userInfo['working_location'])) === 'remote';

        // Check today's attendance record for the authenticated user
        $attendanceRecord = $this->attendanceModel
            ->where('user_id', $user->sub)
            ->where('date', $date)
            ->orderBy('id', 'DESC')
            ->first();
        $response = [
            'status' => 'success',
            'role' => $userData['role'], // Include user role
            'has_face_photo' => $hasFacePhoto, // Include face photo status
            'is_remote' => $isRemote,
        ];
        if (!$attendanceRecord) {
            $response['data'] = 'not_checked_in';
        } elseif ($attendanceRecord['check_in_time'] && !$attendanceRecord['check_out_time']) {
            $response['data'] = 'checked_in';
        } else {
            $response['data'] = 'checked_out';
        }

        return $this->respond($response);
    }

    public function checkIn()
    {
        date_default_timezone_set('Asia/Kolkata'); // Set server timezone

        $user = $this->authService->check();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        if (!in_array($user->role, ['hr', 'branch_admin', 'department_manager', 'employee'])) {
            return $this->respond(['status' => 'error', 'message' => 'Access denied for this role'], 403);
        }

        // Always use server's current date and time (Asia/Kolkata timezone)
        // This ensures the date is always correct regardless of client timezone
        $date = date('Y-m-d'); // Today's date in server timezone
        $checkInTime = date('Y-m-d H:i:s'); // Current server time
        // Extract only the time part for storage (HH:MM:SS format)
        $timeOnly = date('H:i:s');

        // Check for yesterday's incomplete attendance (check-in without checkout)
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $yesterdayAttendance = $this->attendanceModel
            ->where('user_id', $user->sub)
            ->where('date', $yesterday)
            ->where('check_out_time', null)
            ->where('check_in_time IS NOT NULL')
            ->orderBy('id', 'DESC')
            ->first();

        // If found incomplete yesterday's attendance, update it with checkout time
        if ($yesterdayAttendance) {
            // Get company rules to determine standard checkout time
            $companyRule = $this->companyRulesModel->orderBy('id', 'DESC')->first() ?? [];
            $fullDayHours = $companyRule['working_hours_per_day'] ?? 8;

            // Calculate standard checkout time based on check-in time + working hours
            $checkInParts = explode(':', $yesterdayAttendance['check_in_time']);
            $checkInHour = (int) $checkInParts[0];
            $checkInMinute = (int) $checkInParts[1];

            // Add working hours to check-in time to get checkout time
            $checkoutHour = $checkInHour + $fullDayHours;
            $checkoutMinute = $checkInMinute;

            // Handle hour overflow (e.g., if check-in at 10:00 AM + 8 hours = 6:00 PM)
            if ($checkoutHour >= 24) {
                $checkoutHour = $checkoutHour - 24;
            }

            $yesterdayCheckoutTime = sprintf('%02d:%02d:00', $checkoutHour, $checkoutMinute);

            // Calculate work hours for yesterday's record
            $calculation = $this->calculateWorkHours($yesterday, $yesterdayAttendance['meal_break'], $yesterdayAttendance['check_in_time'], $yesterdayCheckoutTime);

            $updateData = [
                'check_out_time' => $yesterdayCheckoutTime,
                'work_hours' => $calculation['work_hours'],
                'overtime' => $calculation['overtime'],
                'status' => $calculation['status']
            ];

            $this->attendanceModel->update($yesterdayAttendance['id'], $updateData);

            log_message('info', 'Auto-checkout for user ' . $user->sub . ' on ' . $yesterday . ' at ' . $yesterdayCheckoutTime . ' (Work hours: ' . $calculation['work_hours'] . ')');

            // Store the auto-checkout info to include in response
            $autoCheckoutInfo = [
                'date' => $yesterday,
                'checkout_time' => $yesterdayCheckoutTime,
                'work_hours' => $calculation['work_hours'],
                'status' => $calculation['status']
            ];
        } else {
            $autoCheckoutInfo = null;
        }

        // Get latest attendance for today
        $latestAttendance = $this->attendanceModel
            ->where('user_id', $user->sub)
            ->where('date', $date)
            ->orderBy('id', 'DESC')
            ->first();

        // ── Multi-punch guard: only block a new check-in if the employee is
        //    ALREADY clocked in (no checkout yet). When the previous session
        //    is closed (has a check_out_time), always create a new row so
        //    multiple check-in/check-out pairs can be recorded in one day.
        // NOTE: We intentionally skip the old "update check-in time" branch
        //       so that every tap of the Check-In button creates a fresh row.


        // If user has already checked out or no record exists, create a new check-in record
        // This allows checking in again after checkout

        $userInfoModel = new \App\Models\UserInfoModel();
        $employeeInfo = $userInfoModel->where('user_id', $user->sub)->first();
        $isRemote = !empty($employeeInfo['working_location']) &&
            strtolower(trim($employeeInfo['working_location'])) === 'remote';

        if ($isRemote) {
            log_message('info', '🏠 Remote employee check-in (no location required): user_id=' . $user->sub . ' at ' . $timeOnly);
        }

        // ── Capture check-in location info ───────────────────────────────────
        $clientIp   = $this->request->getIPAddress();
        $userAgent  = $this->request->getUserAgent()->getAgentString();
        $bodyJson       = $this->request->getJSON(true) ?? [];
        $checkinLat     = isset($bodyJson['latitude'])  ? (float)$bodyJson['latitude']  : null;
        $checkinLng     = isset($bodyJson['longitude']) ? (float)$bodyJson['longitude'] : null;
        $locationStatus = isset($bodyJson['location_status']) ? $bodyJson['location_status'] : null;

        // ── Geofencing Enforcement ───────────────────────────────────────────
        if (!$isRemote) {
            // Use branch-specific rules for this employee
            $companyRule = $this->getBranchRulesForUser((int)$user->sub);
            $enableGeofencing = isset($companyRule['enable_geofencing']) && $companyRule['enable_geofencing'] == 1;

            if ($enableGeofencing) {
                $officeLocation = $this->getOfficeLocationForUser((int)$user->sub);

                if ($officeLocation && $officeLocation['latitude'] && $officeLocation['longitude']) {
                    if ($checkinLat === null || $checkinLng === null) {
                        return $this->respond([
                            'status' => 'error',
                            'message' => 'Location access is required to check in.'
                        ], 400);
                    }

                    $distance = $this->calculateDistance(
                        $checkinLat, 
                        $checkinLng, 
                        (float)$officeLocation['latitude'], 
                        (float)$officeLocation['longitude']
                    );

                    $allowedRadius = (float)($officeLocation['radius'] ?? 0);

                    // Add a 10 meter tolerance if exact match is required to account for basic GPS drift
                    if ($allowedRadius == 0 && $distance > 10) {
                         return $this->respond([
                            'status' => 'error',
                            'message' => 'You are outside the exact office location. Please check in from the office.'
                        ], 400);
                    } else if ($allowedRadius > 0 && $distance > $allowedRadius) {
                        return $this->respond([
                            'status' => 'error',
                            'message' => "You are outside the allowed office location range. Please check in from within {$allowedRadius} meters of the office."
                        ], 400);
                    }
                }
            }
        }

        // ── Reverse-geocode GPS coordinates via Nominatim ────────────────────
        $locationName = null;
        if ($checkinLat !== null && $checkinLng !== null) {
            $nominatimUrl = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$checkinLat}&lon={$checkinLng}&zoom=18&addressdetails=1";
            $ctx = stream_context_create(['http' => ['header' => "User-Agent: FableadHRPortal/1.0\r\n", 'timeout' => 5]]);
            $geoData = @file_get_contents($nominatimUrl, false, $ctx);
            if ($geoData) {
                $geoJson = json_decode($geoData, true);
                if (!empty($geoJson['display_name'])) {
                    $locationName = $geoJson['display_name'];
                }
            }
            // Fallback if Nominatim fails
            if (!$locationName) {
                $locationName = "GPS: {$checkinLat}, {$checkinLng}";
            }
        }

        // If user has already checked out or no record exists, create a new check-in record
        // This allows checking in again after checkout
        $data = [
            'user_id'               => $user->sub,
            'date'                  => $date,
            'check_in_time'         => $timeOnly,
            'status'                => 'present',
            // New canonical column names
            'check_in_ip_address'   => $clientIp,
            'check_in_latitude'     => $checkinLat,
            'check_in_longitude'    => $checkinLng,
            'check_in_location_name'=> $locationName,
            'check_in_location_status' => $locationStatus,
        ];

        // Delete any leave record for today if exists
        $this->leaveModel
            ->where('user_id', $user->sub)
            ->where('start_date', $date)
            ->where('end_date', $date)
            ->delete();

        if ($this->attendanceModel->insert($data)) {
            // Check if attendance notifications are enabled (wrap in try-catch
            // so a push-notification failure never corrupts the success response)
            try {
                $notificationSettingsModel = new NotificationSettingsModel();
                if ($notificationSettingsModel->isAttendanceNotificationsEnabled()) {
                    $this->hierarchyService->dispatchCheckInNotification((int)$user->sub, $timeOnly, $date);
                }
            } catch (\Throwable $e) {
                log_message('error', 'Check-in push notification failed: ' . $e->getMessage());
                // Do NOT rethrow – the check-in itself succeeded
            }

            $response = [
                'status'  => 'success',
                'message' => 'Checked in successfully'
            ];

            if ($autoCheckoutInfo) {
                $response['auto_checkout'] = $autoCheckoutInfo;
                $response['message'] .= '. Yesterday\'s attendance was automatically checked out at ' . $autoCheckoutInfo['checkout_time'] . ' (' . $autoCheckoutInfo['work_hours'] . ' hours)';
            }

            return $this->respond($response);
        }

        return $this->respond(['status' => 'error', 'message' => 'Check-in failed'], 500);
    }


    /**
     * Calculate work hours from check-in and check-out times
     * @param string $date Date in Y-m-d format
     * @param string $checkInTime Time in H:i:s format
     * @param string $checkOutTime Time in H:i:s format
     * @return array ['work_hours' => formatted time, 'work_hours_seconds' => seconds, 'status' => status]
     */
    private function calculateWorkHours($date, $mealbreakTime, $checkInTime, $checkOutTime)
    {
        if (empty($checkInTime) || empty($checkOutTime)) {
            return [
                'work_hours' => null,
                'work_hours_seconds' => 0,
                'overtime' => '00:00:00',
                'overtime_seconds' => 0,
                'status' => 'absent'
            ];
        }

        $checkInTimestamp = strtotime($date . ' ' . $checkInTime);
        $checkOutTimestamp = strtotime($date . ' ' . $checkOutTime);

        if (!$checkInTimestamp || !$checkOutTimestamp || $checkOutTimestamp <= $checkInTimestamp) {
            log_message('debug', 'Invalid timestamps or checkout <= checkin');
            return [
                'work_hours' => '00:00:00',
                'work_hours_seconds' => 0,
                'overtime' => '00:00:00',
                'overtime_seconds' => 0,
                'status' => 'absent'
            ];
        }

        /* ---------------------------------------------------
        1. GROSS WORK DURATION
        --------------------------------------------------- */
        $grossWorkSeconds = $checkOutTimestamp - $checkInTimestamp;

        /* ---------------------------------------------------
        2. COMPANY RULES (branch-aware)
        --------------------------------------------------- */
        // Look up the staff member's branch_id from the attendance record, then
        // fetch that branch's rules. Falls back to global company_rules if needed.
        $branchRulesModel = new \App\Models\BranchRulesModel();
        // $userId is already bound as the function param in the caller context;
        // we derive it from the attendance date record via user lookup.
        // For calculateWorkHours we accept an optional $userId param — but since
        // it doesn't have access to it, we read the global rule here as fallback
        // and let the call sites override per staff.  The branch-aware path is
        // handled in checkIn/checkOut where we pass $userId explicitly.
        $companyRule = $this->companyRulesModel->orderBy('id', 'DESC')->first() ?? [];
        $isSaturdayHalfDay = $this->isSaturdayHalfDay($date, $companyRule);

        /* ---------------------------------------------------
        3. MEAL BREAK
        --------------------------------------------------- */
        $mealBreakSeconds = 0;

        if (!$isSaturdayHalfDay && !empty($mealbreakTime)) {
            $time = new \DateTime($mealbreakTime);
            $mealBreakSeconds =
                ($time->format('H') * 3600) +
                ($time->format('i') * 60) +
                $time->format('s');
        }

        /* ---------------------------------------------------
        4. NET WORKING HOURS
        --------------------------------------------------- */
        $workHoursInSeconds = max(0, $grossWorkSeconds - $mealBreakSeconds);

        /* ---------------------------------------------------
        5. COMPANY SETTINGS
        --------------------------------------------------- */
        $payrollType = $companyRule['payroll_type'] ?? 'monthly';
        $graceMinutes = (int) ($companyRule['grace_minutes'] ?? 10);
        $graceSeconds = $graceMinutes * 60;

        /* ---------------------------------------------------
        6. OVERTIME CALCULATION (UNCHANGED)
        --------------------------------------------------- */
        $fullDayHours = (float) ($companyRule['working_hours_per_day'] ?? 8);
        $halfDayHours = (float) ($companyRule['half_day_hours'] ?? 5);

        $fullDaySeconds = $fullDayHours * 3600;
        $halfDaySeconds = $halfDayHours * 3600;

        $overtimeSeconds = 0;

        if (!empty($companyRule) && (int) ($companyRule['enable_overtime'] ?? 0) === 1) {
            $overtimeStartSeconds = $isSaturdayHalfDay
                ? $halfDaySeconds
                : ($fullDaySeconds + $mealBreakSeconds);

            if ($grossWorkSeconds > $overtimeStartSeconds) {
                $overtimeSeconds = $grossWorkSeconds - $overtimeStartSeconds;

                $minOvertimeSeconds =
                    ((float) ($companyRule['min_overtime_count_in_minutes'] ?? 0)) * 60;

                if ($overtimeSeconds < $minOvertimeSeconds) {
                    $overtimeSeconds = 0;
                }
            }
        }

        /* ---------------------------------------------------
        7. ATTENDANCE STATUS — Saturday-Aware Thresholds
        --------------------------------------------------- */
        $status = 'absent';

        $effectiveSeconds = $workHoursInSeconds + $graceSeconds;

        if ($payrollType === 'hourly') {
            $status = $workHoursInSeconds > 0 ? 'present' : 'absent';
        } else {
            // Check if this is a working Saturday with the 4-hour full-day override
            $isSatWorking    = $this->isSaturdayWorking($date, $companyRule);
            $overrideEnabled = (int) ($companyRule['saturday_full_day_override'] ?? 1) === 1;

            if ($isSatWorking && $overrideEnabled) {
                // Use saturday_working_hours as the full-day benchmark
                $satHours         = (float) ($companyRule['saturday_working_hours'] ?? 4);
                $fullDayThreshold = $satHours * 3600;
                $halfDayThreshold = ($satHours / 2) * 3600;
            } else {
                $fullDayThreshold = 6 * 3600; // standard: 6 h = present
                $halfDayThreshold = 4 * 3600; // standard: 4 h = half-day
            }

            if ($effectiveSeconds >= $fullDayThreshold) {
                $status = 'present';
            } elseif ($effectiveSeconds >= $halfDayThreshold) {
                $status = 'half-day';
            } else {
                $status = 'absent';
            }
        }

        /* ---------------------------------------------------
        8. FORMAT OUTPUT
        --------------------------------------------------- */
        $formattedWorkHours = sprintf(
            '%02d:%02d:%02d',
            floor($workHoursInSeconds / 3600),
            floor(($workHoursInSeconds % 3600) / 60),
            $workHoursInSeconds % 60
        );

        $formattedOvertime = sprintf(
            '%02d:%02d:%02d',
            floor($overtimeSeconds / 3600),
            floor(($overtimeSeconds % 3600) / 60),
            $overtimeSeconds % 60
        );

        return [
            'work_hours' => $formattedWorkHours,
            'work_hours_seconds' => $workHoursInSeconds,
            'overtime' => $formattedOvertime,
            'overtime_seconds' => $overtimeSeconds,
            'status' => $status,
            'is_saturday_half_day' => $isSaturdayHalfDay
        ];
    }

    /**
     * Check if a given date is a Saturday half-day based on company rules
     * @param string $date Date in Y-m-d format
     * @param array $companyRule Company rules array
     * @return bool True if it's a Saturday half-day
     */
    private function isSaturdayHalfDay($date, $companyRule)
    {
        // Check if it's Saturday first
        $dayOfWeek = date('N', strtotime($date));
        if ($dayOfWeek != 6) { // Not Saturday
            return false;
        }

        // Check if saturday_half_day_pattern is enabled
        if (empty($companyRule['saturday_half_day_pattern'])) {
            return false;
        }

        // Get the pattern (e.g., "1,2" or "1,3,5")
        $halfDayPattern = explode(',', $companyRule['saturday_half_day_pattern']);

        // Calculate which Saturday of the month this is
        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $saturdayCount = 0;

        $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($day = 1; $day <= $totalDays; $day++) {
            $checkDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            $checkTimestamp = strtotime($checkDate);

            if (date('N', $checkTimestamp) == 6) { // Is Saturday
                $saturdayCount++;
                if ($checkDate == $date) {
                    // Check if this Saturday number is in the half-day pattern
                    return in_array($saturdayCount, $halfDayPattern);
                }
            }
        }

        return false;
    }

    /**
     * Determine if a given date is a WORKING Saturday (not an off Saturday).
     *
     * A Saturday is "working" when ALL of the following are true:
     *  1. The date is a Saturday (ISO day 6).
     *  2. saturday_off_enabled == 1 (the company has the Saturday-off feature on).
     *  3. This specific Saturday is NOT in the off-pattern.
     *
     * Off-pattern logic mirrors CompanyRuleTrait::isWorkingDay():
     *   - 'all'            → every Saturday is off  → never working
     *   - 'alternate-even' → 2nd, 4th Saturday off  → 1st, 3rd, 5th are working
     *   - 'alternate-odd'  → 1st, 3rd, 5th Saturday off → 2nd, 4th are working
     *   - 'custom'         → pattern lists the off Saturdays (e.g. "1,3,5")
     *
     * @param  string $date        Y-m-d
     * @param  array  $companyRule Company rules row
     * @return bool                True when this Saturday is a working day
     */
    private function isSaturdayWorking(string $date, ?array $companyRule): bool
    {
        if (empty($companyRule)) {
            return false;
        }

        // Must be a Saturday (ISO 6)
        if ((int) date('N', strtotime($date)) !== 6) {
            return false;
        }

        // Feature must be enabled
        if ((int) ($companyRule['saturday_off_enabled'] ?? 0) !== 1) {
            // Saturday-off feature is disabled → treat Saturday as a normal working day;
            // the override still applies in this case.
            return true;
        }

        $offType    = $companyRule['saturday_off_type'] ?? 'all';
        $dayOfMonth = (int) date('j', strtotime($date));
        $weekNumber = (int) ceil($dayOfMonth / 7); // 1st, 2nd, 3rd … Saturday of month

        // Every Saturday is off
        if ($offType === 'all') {
            return false;
        }

        // Alternate off patterns
        if (in_array($offType, ['alternate-even', 'alternate-odd'], true)) {
            $isOffSaturday =
                ($offType === 'alternate-even' && $weekNumber % 2 === 0) ||
                ($offType === 'alternate-odd'  && $weekNumber % 2 !== 0);
            // Working = NOT an off Saturday
            return !$isOffSaturday;
        }

        // Custom off pattern (e.g. "1,3,5")
        if ($offType === 'custom') {
            $offPattern = array_filter(
                array_map('intval', explode(',', $companyRule['saturday_off_pattern'] ?? ''))
            );
            return !in_array($weekNumber, $offPattern, true);
        }

        // Default: treat as working
        return true;
    }


    public function checkOut()
    {
        date_default_timezone_set('Asia/Kolkata');

        $user = $this->authService->check();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        if (!in_array($user->role, ['hr', 'branch_admin', 'department_manager', 'employee'])) {
            return $this->respond(['status' => 'error', 'message' => 'Access denied for this role'], 403);
        }

        // Always use server's current date and time (Asia/Kolkata timezone)
        $date = date('Y-m-d'); // Today's date in server timezone
        $checkOutTimeOnly = date('H:i:s'); // Current server time (HH:MM:SS)

        // Fetch the latest attendance record without checkout
        $latestAttendance = $this->attendanceModel
            ->where('user_id', $user->sub)
            ->where('date', $date)
            ->where('check_out_time', null)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$latestAttendance) {
            return $this->respond(['status' => 'error', 'message' => 'No check-in record found for today'], 400);
        }

        // ── Multi-punch: find the FIRST check-in of the day ──────────────────
        // Status must reflect the full day window (first check-in → this checkout)
        // so that a 2nd/3rd punch checkout saves the correct cumulative status.
        $firstRecord = $this->attendanceModel
            ->where('user_id', $user->sub)
            ->where('date', $date)
            ->orderBy('id', 'ASC')
            ->first();
        $firstCheckIn = $firstRecord['check_in_time'] ?? $latestAttendance['check_in_time'];

        // Load company rules for calculateDayStatus
        $companyRule = $this->companyRulesModel->orderBy('id', 'DESC')->first();

        // Calculate status over the full day window
        $dayCalc = $this->calculateDayStatus(
            $date,
            $firstCheckIn,
            $checkOutTimeOnly,
            $latestAttendance['meal_break'],
            $companyRule ?? []
        );

        // Keep overtime from the single-session calculateWorkHours (overtime unchanged)
        $sessionCalc = $this->calculateWorkHours(
            $date,
            $latestAttendance['meal_break'],
            $latestAttendance['check_in_time'],
            $checkOutTimeOnly
        );

        // ── Capture check-out location info ──────────────────────────────────
        $coIp   = $this->request->getIPAddress();
        $coBody = $this->request->getJSON(true) ?? [];
        $coLat  = isset($coBody['latitude'])  ? (float)$coBody['latitude']  : null;
        $coLng  = isset($coBody['longitude']) ? (float)$coBody['longitude'] : null;
        $coLocationStatus = isset($coBody['location_status']) ? $coBody['location_status'] : null;

        $userInfoModel = new \App\Models\UserInfoModel();
        $employeeInfo = $userInfoModel->where('user_id', $user->sub)->first();
        $isRemote = !empty($employeeInfo['working_location']) &&
            strtolower(trim($employeeInfo['working_location'])) === 'remote';

        // ── Geofencing Enforcement ───────────────────────────────────────────
        if (!$isRemote) {
            $enableGeofencing = isset($companyRule['enable_geofencing']) && $companyRule['enable_geofencing'] == 1;

            if ($enableGeofencing) {
                $officeLocation = $this->getOfficeLocationForUser((int)$user->sub);

                if ($officeLocation && $officeLocation['latitude'] && $officeLocation['longitude']) {
                    if ($coLat === null || $coLng === null) {
                        return $this->respond([
                            'status' => 'error',
                            'message' => 'Location access is required to check out.'
                        ], 400);
                    }

                    $distance = $this->calculateDistance(
                        $coLat, 
                        $coLng, 
                        (float)$officeLocation['latitude'], 
                        (float)$officeLocation['longitude']
                    );

                    $allowedRadius = (float)($officeLocation['radius'] ?? 0);

                    // Add a 10 meter tolerance if exact match is required to account for basic GPS drift
                    if ($allowedRadius == 0 && $distance > 10) {
                         return $this->respond([
                            'status' => 'error',
                            'message' => 'You are outside the exact office location. Please check out from the office.'
                        ], 400);
                    } else if ($allowedRadius > 0 && $distance > $allowedRadius) {
                        return $this->respond([
                            'status' => 'error',
                            'message' => "You are outside the allowed office location range. Please check out from within {$allowedRadius} meters of the office."
                        ], 400);
                    }
                }
            }
        }

        // ── Reverse-geocode checkout GPS coordinates via Nominatim ───────────
        $coLocationName = null;
        if ($coLat !== null && $coLng !== null) {
            $coNominatimUrl = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$coLat}&lon={$coLng}&zoom=18&addressdetails=1";
            $coCtx = stream_context_create(['http' => ['header' => "User-Agent: FableadHRPortal/1.0\r\n", 'timeout' => 5]]);
            $coGeoData = @file_get_contents($coNominatimUrl, false, $coCtx);
            if ($coGeoData) {
                $coGeoJson = json_decode($coGeoData, true);
                if (!empty($coGeoJson['display_name'])) {
                    $coLocationName = $coGeoJson['display_name'];
                }
            }
            if (!$coLocationName) {
                $coLocationName = "GPS: {$coLat}, {$coLng}";
            }
        }

        $data = [
            'check_out_time'            => $checkOutTimeOnly,
            'work_hours'                => $dayCalc['work_hours'],
            'overtime'                  => $sessionCalc['overtime'],
            'meal_break'                => $latestAttendance['meal_break'],
            'status'                    => $dayCalc['status'],
            // New canonical column names
            'check_out_ip_address'      => $coIp,
            'check_out_latitude'        => $coLat,
            'check_out_longitude'       => $coLng,
            'check_out_location_name'   => $coLocationName,
            'check_out_location_status' => $coLocationStatus,
        ];

        if ($this->attendanceModel->update($latestAttendance['id'], $data)) {
            // Wrap push notification in try-catch so a VAPID/encryption failure
            // never leaks into the checkout success response.
            try {
                $notificationSettingsModel = new NotificationSettingsModel();
                if ($notificationSettingsModel->isAttendanceNotificationsEnabled()) {
                    $employee     = $this->userModel->find($user->sub);
                    $employeeName = $employee ? $employee['username'] : 'Employee';
                    log_message('info', '📝 Employee check-out: ' . $employeeName . ' at ' . $checkOutTimeOnly);
                    $this->pushNotificationService->notifyAdmins(
                        'Employee Check-Out',
                        $employeeName . ' has checked out at ' . $checkOutTimeOnly . ' (Status: ' . $dayCalc['status'] . ')',
                        [
                            'type'     => 'checkout',
                            'user_id'  => $user->sub,
                            'username' => $employeeName,
                            'time'     => $checkOutTimeOnly,
                            'date'     => $date,
                            'status'   => $dayCalc['status'],
                            'url'      => base_url('/attendence')
                        ]
                    );
                }
            } catch (\Throwable $e) {
                log_message('error', 'Check-out push notification failed: ' . $e->getMessage());
                // Do NOT rethrow – the check-out itself succeeded
            }

            return $this->respond([
                'status'  => 'success',
                'message' => 'Checked out successfully (' . $dayCalc['status'] . ')',
                'data'    => $data
            ]);
        }

        return $this->respond(['status' => 'error', 'message' => 'Check-out failed'], 500);
    }

    public function getAttendanceData()
    {
        // Check Authorization
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Assuming that HR has role "hr" and employees have role "employee"
        $userRole = $user->role; // "employee" or "hr"

        // If HR or admin is logged in, filter by branch if set
        if ($userRole === 'hr' || $userRole === 'admin') {
            $branchId = $this->request->getGet('branch_id');
            if ($branchId === null || $branchId === '') {
                $branchId = $this->authService->getBranchId();
            } else {
                $branchId = (int)$branchId;
            }

            if (!empty($branchId)) {
                $attendanceData = $this->attendanceModel
                    ->select('attendance.*')
                    ->join('users', 'users.id = attendance.user_id')
                    ->where('users.branch_id', (int)$branchId)
                    ->where('users.is_deleted', 0)
                    ->orderBy('attendance.date', 'DESC')
                    ->orderBy('attendance.id', 'DESC')
                    ->findAll();
            } else {
                $attendanceData = $this->attendanceModel->orderBy('date', 'DESC')->orderBy('id', 'DESC')->findAll();
            }
        } elseif ($userRole === 'branch_admin') {
            $branchId = (int)$this->authService->getBranchId();
            $attendanceData = $this->attendanceModel
                ->select('attendance.*')
                ->join('users', 'users.id = attendance.user_id')
                ->where('users.branch_id', $branchId)
                ->where('users.is_deleted', 0)
                ->orderBy('attendance.date', 'DESC')
                ->orderBy('attendance.id', 'DESC')
                ->findAll();
        } elseif ($userRole === 'department_manager') {
            $currUser = $this->hierarchyService->getUserDetails((int)$user->sub);
            $mgrDeptId = (int)($currUser['department_id'] ?: $currUser['ui_department_id'] ?: 0);
            $query = $this->attendanceModel
                ->select('attendance.*')
                ->join('users', 'users.id = attendance.user_id')
                ->where('users.is_deleted', 0);
            if ($mgrDeptId) {
                $query->groupStart()
                    ->where('users.department_id', $mgrDeptId)
                    ->orWhere('users.id', $user->sub)
                    ->groupEnd();
            } else {
                $query->where('users.id', $user->sub);
            }
            $attendanceData = $query->orderBy('attendance.date', 'DESC')->orderBy('attendance.id', 'DESC')->findAll();
        } else {
            // If an employee is logged in, they can only see their own attendance records
            $attendanceData = $this->attendanceModel->where('user_id', $user->sub)->orderBy('date', 'DESC')->orderBy('id', 'DESC')->findAll();
        }
        $userModel = new UserModel();
        $userInfoModel = new \App\Models\UserInfoModel();


        // Fetch the employee names from the associated User model
        // Also recalculate work hours for existing records to ensure accuracy
        foreach ($attendanceData as &$record) {
            // Assuming attendance has a user_id and you have a relation with User model
            $employee = $userModel->find($record['user_id']); // Fix the reference to user_id, not record['id']
            $record['employee_name'] = $employee ? $employee['username'] : 'Unknown'; // Add employee name to the record

            $userInfo = $userInfoModel->where('user_id', $record['user_id'])->first();
            $record['profile_image'] = $userInfo ? $userInfo['profile_image'] : null;

            // Recalculate work hours if both check-in and check-out times exist
            // This ensures old records with incorrect calculations are displayed correctly
            if (!empty($record['check_in_time']) && !empty($record['check_out_time'])) {
                $calculation = $this->calculateWorkHours($record['date'], $record['meal_break'], $record['check_in_time'], $record['check_out_time']);
                $record['work_hours'] = $calculation['work_hours'];
                // Update status as well to ensure it matches the recalculated work hours
                $record['status'] = $calculation['status'];
            }
        }
        // Return the attendance data
        return $this->respond(['status' => 'success', 'data' => $attendanceData]);
    }

    public function getLeaveDetails($userId, $dateStr = null)
    {
        // Check if the user is authorized
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Fetch leave details for the specific user
        $builder = $this->leaveModel->where('user_id', $userId);

        // If a date is provided, filter the leave records by the given date
        if ($dateStr) {
            $builder->where('start_date <=', $dateStr)
                ->where('end_date >=', $dateStr);
        }

        $leaveData = $builder->findAll();

        if (!$leaveData) {
            return $this->failNotFound('No leave records found for this employee');
        }

        // Return the leave data
        return $this->respond(['status' => 'success', 'data' => $leaveData]);
    }

    // public function getAttendance($month, $year)
    // {
    //     $userModel = new \App\Models\UserModel();
    //     $userInfoModel = new \App\Models\UserInfoModel();
    //     $attendanceModel = new AttendanceModel();
    //     $holidayCalendarModel = new HolidayCalendarModel();
    //     $companyRulesModel = new CompanyRulesModel();

    //     $authUser = $this->authService->user();
    //     if (!$authUser) {
    //         return $this->failUnauthorized('Unauthorized access');
    //     }

    //     // Get users based on role
    //     if ($authUser->role === 'admin' || $authUser->role === 'hr') {
    //         $users = $userModel->where('is_deleted', 0)->whereIn('role', ['employee', 'hr'])->findAll();
    //     } else {
    //         $users = $userModel->where('id', $authUser->sub)->findAll();
    //     }

    //     $userIds = array_column($users, 'id');
    //     $userInfoList = $userInfoModel->whereIn('user_id', $userIds)->findAll();

    //     $userInfoMap = [];
    //     foreach ($userInfoList as $info) {
    //         $userInfoMap[$info['user_id']] = $info;
    //     }

    //     // Get attendance records
    //     $attendanceData = $attendanceModel
    //         ->where('MONTH(date)', $month)
    //         ->where('YEAR(date)', $year)
    //         ->findAll();

    //     // Get holidays
    //     $holidays = $holidayCalendarModel
    //         ->where('MONTH(holiday_date)', $month)
    //         ->where('YEAR(holiday_date)', $year)
    //         ->orderBy('holiday_date', 'ASC')
    //         ->findAll();

    //     $companyRule = $companyRulesModel->first();
    //     if ($companyRule['saturday_off_enabled'] == 1) {
    //         if ($companyRule['saturday_off_type'] == 'all') {
    //             $saturdayOffIndexes = [1, 2, 3, 4, 5];
    //         }else if ($companyRule['saturday_off_type'] == 'alternate-even') {
    //             $saturdayOffIndexes = [2, 4];
    //         }else if ($companyRule['saturday_off_type'] == 'alternate-odd') {
    //             $saturdayOffIndexes = [1, 3, 5];
    //         }else if ($companyRule['saturday_off_type'] == 'custom') {
    //             $saturdayOffIndexes = explode(',', $companyRule['saturday_off_pattern'] );
    //         }else {
    //             $saturdayOffIndexes = [];
    //         }
    //     }else{
    //         // if ($companyRule['saturday_off_type'] == 'all') {
    //         //     $saturdayOffIndexes = [1, 2, 3, 4, 5];
    //         // }else if ($companyRule['saturday_off_type'] == 'alternate-even') {
    //         //     $saturdayOffIndexes = [2, 4];
    //         // }else if ($companyRule['saturday_off_type'] == 'alternate-odd') {
    //         //     $saturdayOffIndexes = [1, 3, 5];
    //         // }else if ($companyRule['saturday_off_type'] == 'custom') {
    //         //     $saturdayOffIndexes = explode(',', $companyRule['saturday_off_pattern'] );
    //         // }else {
    //             $saturdayOffIndexes = [];
    //         // }
    //     }

    //     // Calculate Saturday off dates
    //     $saturdayOffDates = [];
    //     $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    //     $saturdayCount = 0;

    //     for ($day = 1; $day <= $totalDays; $day++) {
    //         $date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
    //         $timestamp = strtotime($date);
    //         if (date('N', $timestamp) == 6) { // Saturday
    //             $saturdayCount++;
    //             if (in_array($saturdayCount, $saturdayOffIndexes)) {
    //                 $saturdayOffDates[] = $date;
    //             }
    //         }
    //     }

    //     // Format users with attendance
    //     $attendanceWithUsers = [];
    //     foreach ($users as $user) {
    //         $userId = $user['id'];

    //         $userAttendance = array_filter($attendanceData, function ($attendance) use ($userId) {
    //             return $attendance['user_id'] == $userId;
    //         });

    //         // Group attendance by date and prioritize records with check_in_time but no check_out_time
    //         $attendanceByDate = [];
    //         foreach ($userAttendance as $record) {
    //             $date = $record['date'];

    //             // If no record exists for this date, add it
    //             if (!isset($attendanceByDate[$date])) {
    //                 $attendanceByDate[$date] = $record;
    //             } else {
    //                 // If current record has check_in_time but no check_out_time (currently working), prioritize it
    //                 $currentHasWorking = !empty($record['check_in_time']) && empty($record['check_out_time']);
    //                 $existingHasWorking = !empty($attendanceByDate[$date]['check_in_time']) && empty($attendanceByDate[$date]['check_out_time']);

    //                 if ($currentHasWorking && !$existingHasWorking) {
    //                     // Current record is working, existing is not - use current
    //                     $attendanceByDate[$date] = $record;
    //                 } elseif (!$currentHasWorking && !$existingHasWorking) {
    //                     // Both are checked out - use the most recent one (higher ID)
    //                     if ($record['id'] > $attendanceByDate[$date]['id']) {
    //                         $attendanceByDate[$date] = $record;
    //                     }
    //                 } elseif ($currentHasWorking && $existingHasWorking) {
    //                     // Both are working - use the most recent one (higher ID)
    //                     if ($record['id'] > $attendanceByDate[$date]['id']) {
    //                         $attendanceByDate[$date] = $record;
    //                     }
    //                 }
    //             }
    //         }

    //         $formattedAttendance = [];
    //         foreach ($attendanceByDate as $record) {
    //             $formattedAttendance[] = [
    //                 'date' => $record['date'],
    //                 'check_in_time' => $record['check_in_time'],
    //                 'check_out_time' => $record['check_out_time'],
    //                 'status' => $record['status'],
    //                 'is_late' => $record['is_late'],
    //                 'late_minutes' => $record['late_minutes'],
    //                 'overtime' => $record['overtime'] ?? null,
    //             ];
    //         }

    //         $profileImage = $userInfoMap[$userId]['profile_image'] ?? null;

    //         $attendanceWithUsers[] = [
    //             'user_id' => $userId,
    //             'employee_name' => $user['username'],
    //             'profile_image' => $profileImage,
    //             'attendance' => $formattedAttendance,
    //         ];
    //     }

    //     return $this->response->setJSON([
    //         'status' => 'success',
    //         'data' => [
    //             'users' => $attendanceWithUsers,
    //             'holidays' => $holidays,
    //             'saturdayOffDates' => $saturdayOffDates
    //         ]
    //     ]);
    // }
    public function getAttendance($month, $year)
    {
        $userModel = new \App\Models\UserModel();
        $userInfoModel = new \App\Models\UserInfoModel();
        $attendanceModel = new AttendanceModel();
        $holidayCalendarModel = new HolidayCalendarModel();
        $companyRulesModel = new CompanyRulesModel();
        $companyLogoModel = new \App\Models\CompanyLogoModel();

        $authUser = $this->authService->user();
        if (!$authUser) {
            return $this->failUnauthorized('Unauthorized access');
        }

        // 🔹 Branch filter (from query param, session, or HR assigned branch)
        $branchId = $this->request->getGet('branch_id');
        if ($branchId === null || $branchId === '') {
            $branchId = $this->authService->getBranchId();
        } else {
            $branchId = (int)$branchId;
        }

        // 🔹 Get users based on role hierarchy
        $userQuery = $userModel->where('is_deleted', 0);
        if (in_array($authUser->role, ['admin', 'hr'])) {
            $userQuery->whereIn('role', ['hr', 'branch_admin', 'department_manager', 'employee']);
            if (!empty($branchId)) {
                $userQuery->where('branch_id', (int)$branchId);
            }
        } elseif ($authUser->role === 'branch_admin') {
            $userQuery->whereIn('role', ['branch_admin', 'department_manager', 'employee']);
            $assignedBranch = $this->authService->getBranchId();
            if ($assignedBranch) {
                $userQuery->where('branch_id', $assignedBranch);
            }
        } elseif ($authUser->role === 'department_manager') {
            $userQuery->groupStart()
                ->where('role', 'employee')
                ->orWhere('id', $authUser->sub)
                ->groupEnd();
            $assignedBranch = $this->authService->getBranchId();
            if ($assignedBranch) {
                $userQuery->where('branch_id', $assignedBranch);
            }
            $currUser = $this->hierarchyService->getUserDetails((int)$authUser->sub);
            $mgrDeptId = (int)($currUser['department_id'] ?: $currUser['ui_department_id'] ?: 0);
            if ($mgrDeptId) {
                $userQuery->where('department_id', $mgrDeptId);
            }
        } else {
            $userQuery->where('id', $authUser->sub);
        }

        $users = $userQuery->findAll();

        $userIds = array_column($users, 'id');

        // 🔹 User info
        $userInfoList = !empty($userIds)
            ? $userInfoModel->whereIn('user_id', $userIds)->findAll()
            : [];
        $userInfoMap = [];
        foreach ($userInfoList as $info) {
            $userInfoMap[$info['user_id']] = $info;
        }

        // 🔹 Attendance data
        $attendanceData = [];
        if (!empty($userIds)) {
            $attendanceData = $attendanceModel
                ->whereIn('user_id', $userIds)
                ->where('MONTH(date)', $month)
                ->where('YEAR(date)', $year)
                ->findAll();
        }

        // 🔹 Holidays
        $holidays = $holidayCalendarModel
            ->where('MONTH(holiday_date)', $month)
            ->where('YEAR(holiday_date)', $year)
            ->orderBy('holiday_date', 'ASC')
            ->findAll();

        $holidayDates = array_column($holidays, 'holiday_date');
        $holidayMap = array_flip($holidayDates);

        // 🔹 Leave data
        $startOfMonthDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $endOfMonthDate = date('Y-m-t', strtotime($startOfMonthDate));
        $leavesData = [];
        if (!empty($userIds)) {
            $leavesData = $this->leaveModel
                ->whereIn('user_id', $userIds)
                ->where('status', 'approved')
                ->where("((start_date >= '$startOfMonthDate' AND start_date <= '$endOfMonthDate') OR (end_date >= '$startOfMonthDate' AND end_date <= '$endOfMonthDate') OR (start_date <= '$startOfMonthDate' AND end_date >= '$endOfMonthDate'))")
                ->findAll();
        }

        $leavesByUser = [];
        foreach ($leavesData as $leave) {
            $leavesByUser[$leave['user_id']][] = $leave;
        }

        // 🔹 Company / Branch rules
        if (!empty($branchId)) {
            $branchRulesModel = new \App\Models\BranchRulesModel();
            $companyRule = $branchRulesModel->getRulesForBranch((int)$branchId) ?? [];
        } else {
            $companyRule = $companyRulesModel->orderBy('id', 'DESC')->first() ?? [];
        }
        $isIncludedHoliday = $companyRule['include_holidays_in_working_days'] ?? 0;

        if (!empty($companyRule) && ($companyRule['saturday_off_enabled'] ?? 0) == 1) {
            switch ($companyRule['saturday_off_type'] ?? '') {
                case 'all':
                    $saturdayOffIndexes = [1, 2, 3, 4, 5];
                    break;
                case 'alternate-even':
                    $saturdayOffIndexes = [2, 4];
                    break;
                case 'alternate-odd':
                    $saturdayOffIndexes = [1, 3, 5];
                    break;
                case 'custom':
                    $saturdayOffIndexes = !empty($companyRule['saturday_off_pattern'])
                        ? explode(',', $companyRule['saturday_off_pattern'])
                        : [];
                    break;
                default:
                    $saturdayOffIndexes = [];
            }
        } else {
            $saturdayOffIndexes = [];
        }

        // 🔹 Calculate Saturday off dates
        $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $saturdayOffDates = [];
        $saturdayCount = 0;

        for ($day = 1; $day <= $totalDays; $day++) {
            $date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            if (date('N', strtotime($date)) == 6) {
                $saturdayCount++;
                if (in_array($saturdayCount, $saturdayOffIndexes)) {
                    $saturdayOffDates[] = $date;
                }
            }
        }

        $saturdayOffMap = array_flip($saturdayOffDates);

        // 🔹 Final response
        $attendanceWithUsers = [];

        foreach ($users as $user) {
            $userId = $user['id'];

            // Filter user attendance for this month
            $userAttendance = array_filter($attendanceData, function ($row) use ($userId) {
                return $row['user_id'] == $userId;
            });

            $userInfo = $userInfoMap[$userId] ?? [];
            $userStatus = strtolower(trim($userInfo['status'] ?? 'Active'));
            $lastWorkingDay = !empty($userInfo['last_working_day']) ? trim($userInfo['last_working_day']) : null;
            $joiningDate = !empty($userInfo['joining_date']) ? trim($userInfo['joining_date']) : null;

            $hasAttendance = !empty($userAttendance);
            $hasLeaves = !empty($leavesByUser[$userId]);
            $hasActivityInMonth = $hasAttendance || $hasLeaves;

            $isInactive = in_array($userStatus, ['inactive', 'resigned', 'fired', 'removed']);

            // If employee is inactive/resigned without last_working_day, derive from latest punch in month
            if ($isInactive && empty($lastWorkingDay) && !empty($userAttendance)) {
                $punchDates = [];
                foreach ($userAttendance as $row) {
                    $st = strtolower($row['status'] ?? '');
                    $hasPunch = !empty($row['check_in_time']) && $row['check_in_time'] !== '00:00:00';
                    if (!in_array($st, ['absent', 'leave']) || $hasPunch) {
                        $punchDates[] = substr($row['date'], 0, 10);
                    }
                }
                if (!empty($punchDates)) {
                    rsort($punchDates);
                    $lastWorkingDay = $punchDates[0];
                }
            }

            // 1. If employee joined after the selected month and has no activity in this month -> skip
            if ($joiningDate && $joiningDate > $endOfMonthDate && !$hasActivityInMonth) {
                continue;
            }

            // 2. If employee has last_working_day before this month began and has no activity in this month -> skip
            if ($lastWorkingDay && $lastWorkingDay < $startOfMonthDate && !$hasActivityInMonth) {
                continue;
            }

            // 3. If employee is inactive/resigned without last_working_day and has no activity in this month
            if ($isInactive && empty($lastWorkingDay) && !$hasActivityInMonth) {
                $updatedDate = !empty($userInfo['updated_at']) ? substr($userInfo['updated_at'], 0, 10) : (!empty($user['updated_at']) ? substr($user['updated_at'], 0, 10) : null);
                if ($updatedDate && $updatedDate < $startOfMonthDate) {
                    continue;
                }
                // If it's the current or future month and employee is already inactive with no activity, skip
                $currentMonthStart = date('Y-m-01');
                if ($startOfMonthDate >= $currentMonthStart) {
                    continue;
                }
            }

            // ── Group all sessions by date ────────────────────────────────────────
            $byDate = [];
            foreach ($userAttendance as $record) {
                $byDate[$record['date']][] = $record;
            }

            // Company rules (used for calculateDayStatus + late-detection)
            $userBranchRule       = $this->getBranchRulesForUser($userId);
            $companyRuleForStatus = !empty($userBranchRule) ? $userBranchRule : (!empty($companyRule) ? $companyRule : ($companyRulesModel->orderBy('id', 'DESC')->first() ?? []));
            $startTimeForStatus   = $companyRuleForStatus['start_time'] ?? '09:30:00';
            $graceMinutes         = (int)($companyRuleForStatus['grace_period'] ?? ($companyRuleForStatus['grace_minutes'] ?? 0));
            $graceSeconds         = $graceMinutes * 60;
            // calculateDayStatus() reads 'grace_minutes'; DB column is 'grace_period' — alias it.
            $companyRuleForStatus['grace_minutes'] = $graceMinutes;


            $attendanceByDate = [];
            foreach ($byDate as $date => $recs) {
                // Sort ascending → $recs[0] = earliest check-in
                usort($recs, fn($a, $b) => strcmp($a['check_in_time'], $b['check_in_time']));

                $first              = $recs[0];
                $activeRecord       = null;
                $lastCheckOut       = null;
                $lastCheckOutRecord = null;   // track the RECORD whose checkout is latest

                foreach ($recs as $rec) {
                    if ($rec['check_out_time']) {
                        // Track the latest checkout time AND the record it belongs to
                        if ($lastCheckOut === null || strcmp($rec['check_out_time'], $lastCheckOut) > 0) {
                            $lastCheckOut       = $rec['check_out_time'];
                            $lastCheckOutRecord = $rec;
                        }
                    } else {
                        $activeRecord = $rec; // still clocked in
                    }
                }

                // $displayRec is only used for location fields below
                $displayRec = $lastCheckOutRecord;   // the record with the latest checkout

                // ── Status: FIRST check-in → LAST check-out (multi-punch rule) ──────
                if ($activeRecord) {
                    // Still clocked in → show as present (working)
                    $computedStatus = 'present';
                    $totalWorkHours = '00:00:00'; // will be updated at checkout
                } else {
                    // Recalculate from full-day window using canonical function
                    $mealBreak = $first['meal_break'] ?? null; // use first session's meal break
                    $dayCalc   = $this->calculateDayStatus(
                        $date,
                        $first['check_in_time'],
                        $lastCheckOut,
                        $mealBreak,
                        $companyRuleForStatus
                    );
                    $computedStatus = $dayCalc['status'];
                    $totalWorkHours = $dayCalc['work_hours'];

                    // Persist corrected status + work_hours back to the last session's DB row
                    // so the display layer always reads a trustworthy stored value.
                    $lastRec = end($recs);
                    if ($lastRec && ($lastRec['status'] !== $computedStatus || $lastRec['work_hours'] !== $totalWorkHours)) {
                        $this->attendanceModel->update($lastRec['id'], [
                            'status'     => $computedStatus,
                            'work_hours' => $totalWorkHours,
                        ]);
                    }
                }

                // ── Late detection: always based on FIRST check-in ───────────────────
                $firstCheckInSecs = $this->timeToSeconds($first['check_in_time']);
                $startTimeSecs    = $this->timeToSeconds($startTimeForStatus);
                $isLateCalc       = ($firstCheckInSecs > ($startTimeSecs + $graceSeconds)) ? 1 : 0;
                $lateMinutesCalc  = $isLateCalc
                    ? (int)ceil(($firstCheckInSecs - $startTimeSecs - $graceSeconds) / 60)
                    : 0;

                // Use $displayRec as the base array; fall back to $first when no checkout
                // exists yet (active session) to avoid array_merge(null, ...) TypeError.
                $baseRec = $displayRec ?? $first;
                $attendanceByDate[$date] = array_merge($baseRec, [
                    'check_in_time'              => $first['check_in_time'],
                    'check_out_time'             => $activeRecord ? null : $lastCheckOut,
                    'work_hours'                 => $totalWorkHours,
                    'status'                     => $computedStatus,
                    'is_late'                    => $isLateCalc,
                    'late_minutes'               => $lateMinutesCalc,
                    // ── First check-in location (new column names) ───────
                    'check_in_ip_address'        => $first['check_in_ip_address'] ?? ($first['ip_address'] ?? null),
                    'check_in_latitude'          => $first['check_in_latitude']   ?? ($first['latitude']   ?? null),
                    'check_in_longitude'         => $first['check_in_longitude']  ?? ($first['longitude']  ?? null),
                    'check_in_location_name'     => $first['check_in_location_name'] ?? ($first['location_address'] ?? null),
                    'check_in_location_status'   => $first['check_in_location_status'] ?? null,
                    // ── Last check-out location (new column names) ───────
                    'check_out_ip_address'       => ($activeRecord || !$displayRec) ? null : ($displayRec['check_out_ip_address']    ?? ($displayRec['checkout_ip_address']        ?? null)),
                    'check_out_latitude'         => ($activeRecord || !$displayRec) ? null : ($displayRec['check_out_latitude']      ?? ($displayRec['checkout_latitude']           ?? null)),
                    'check_out_longitude'        => ($activeRecord || !$displayRec) ? null : ($displayRec['check_out_longitude']     ?? ($displayRec['checkout_longitude']          ?? null)),
                    'check_out_location_name'    => ($activeRecord || !$displayRec) ? null : ($displayRec['check_out_location_name'] ?? ($displayRec['checkout_location_address']   ?? null)),
                    'check_out_location_status'  => ($activeRecord || !$displayRec) ? null : ($displayRec['check_out_location_status'] ?? null),
                ]);
            }

            // 🔹 Build full month attendance (THIS FIXES 26 JAN ISSUE)
            // $formattedAttendance = [];

            // for ($day = 1; $day <= $totalDays; $day++) {
            //     $date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);

            //     // Holiday
            //     if (isset($holidayMap[$date])) {
            //         $formattedAttendance[] = [
            //             'date' => $date,
            //             'check_in_time' => null,
            //             'check_out_time' => null,
            //             'status' => 'holiday',
            //             'is_late' => null,
            //             'late_minutes' => null,
            //             'overtime' => null,
            //         ];
            //         continue;
            //     }

            //     // Saturday off
            //     if (isset($saturdayOffMap[$date])) {
            //         $formattedAttendance[] = [
            //             'date' => $date,
            //             'check_in_time' => null,
            //             'check_out_time' => null,
            //             'status' => 'week_off',
            //             'is_late' => null,
            //             'late_minutes' => null,
            //             'overtime' => null,
            //         ];
            //         continue;
            //     }

            //     // Attendance exists
            //     if (isset($attendanceByDate[$date])) {
            //         $record = $attendanceByDate[$date];
            //         $formattedAttendance[] = [
            //             'date' => $date,
            //             'check_in_time' => $record['check_in_time'],
            //             'check_out_time' => $record['check_out_time'],
            //             'status' => $record['status'],
            //             'is_late' => $record['is_late'],
            //             'late_minutes' => $record['late_minutes'],
            //             'overtime' => $record['overtime'] ?? null,
            //         ];
            //         continue;
            //     }

            //     // ABSENT
            //     $formattedAttendance[] = [
            //         'date' => $date,
            //         'check_in_time' => null,
            //         'check_out_time' => null,
            //         'status' => 'absent',
            //         'is_late' => null,
            //         'late_minutes' => null,
            //         'overtime' => null,
            //     ];
            // }
            // 🔹 Build full month attendance (THIS FIXES 26 JAN ISSUE)
            $formattedAttendance = [];

            for ($day = 1; $day <= $totalDays; $day++) {
                $date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);

                // If inactive and date is after last working day, mark as absent (never week-off or holiday)
                if ($isInactive && $lastWorkingDay && $date > $lastWorkingDay) {
                    $formattedAttendance[] = [
                        'date' => $date,
                        'check_in_time' => null,
                        'check_out_time' => null,
                        'status' => 'absent',
                        'is_late' => null,
                        'late_minutes' => null,
                        'overtime' => null,
                    ];
                    continue;
                }

                // Holiday
                if (isset($holidayMap[$date])) {
                    $formattedAttendance[] = [
                        'date' => $date,
                        'check_in_time' => null,
                        'check_out_time' => null,
                        'status' => 'holiday',
                        'is_late' => null,
                        'late_minutes' => null,
                        'overtime' => null,
                    ];
                    continue;
                }

                // Sunday off (date('N') returns 7 for Sunday)
                if (date('N', strtotime($date)) == 7) {
                    // If the employee actually checked in on this Sunday, use their real record
                    if (isset($attendanceByDate[$date])) {
                        $record = $attendanceByDate[$date];
                        $formattedAttendance[] = [
                            'date'                    => $date,
                            'check_in_time'           => $record['check_in_time'],
                            'check_out_time'          => $record['check_out_time'],
                            'status'                  => $record['status'], // Show actual status (present/half-day) if they checked in on Week Off
                            'is_late'                 => $record['is_late'],
                            'late_minutes'            => $record['late_minutes'],
                            'overtime'                => $record['overtime'] ?? null,
                            'work_hours'              => $record['work_hours'] ?? null,
                            'completed_seconds'       => $record['completed_seconds'] ?? 0,
                            'check_in_ip_address'     => $record['check_in_ip_address']    ?? null,
                            'check_in_latitude'       => $record['check_in_latitude']      ?? null,
                            'check_in_longitude'      => $record['check_in_longitude']     ?? null,
                            'check_in_location_name'  => $record['check_in_location_name'] ?? null,
                            'check_out_ip_address'    => $record['check_out_ip_address']   ?? null,
                            'check_out_latitude'      => $record['check_out_latitude']     ?? null,
                            'check_out_longitude'     => $record['check_out_longitude']    ?? null,
                            'check_out_location_name' => $record['check_out_location_name'] ?? null,
                        ];
                    } else {
                        // No check-in on this Sunday → mark as Week Off with no data
                        $formattedAttendance[] = [
                            'date'           => $date,
                            'check_in_time'  => null,
                            'check_out_time' => null,
                            'status'         => 'Week Off',
                            'is_late'        => null,
                            'late_minutes'   => null,
                            'overtime'       => null,
                        ];
                    }
                    continue;
                }

                // Saturday off
                if (isset($saturdayOffMap[$date])) {
                    // If the employee actually checked in on this Saturday Off, use their real record
                    if (isset($attendanceByDate[$date])) {
                        $record = $attendanceByDate[$date];
                        $formattedAttendance[] = [
                            'date'                    => $date,
                            'check_in_time'           => $record['check_in_time'],
                            'check_out_time'          => $record['check_out_time'],
                            'status'                  => $record['status'], // Show actual status (present/half-day) if they checked in on Week Off
                            'is_late'                 => $record['is_late'],
                            'late_minutes'            => $record['late_minutes'],
                            'overtime'                => $record['overtime'] ?? null,
                            'work_hours'              => $record['work_hours'] ?? null,
                            'completed_seconds'       => $record['completed_seconds'] ?? 0,
                            'check_in_ip_address'     => $record['check_in_ip_address']    ?? null,
                            'check_in_latitude'       => $record['check_in_latitude']      ?? null,
                            'check_in_longitude'      => $record['check_in_longitude']     ?? null,
                            'check_in_location_name'  => $record['check_in_location_name'] ?? null,
                            'check_out_ip_address'    => $record['check_out_ip_address']   ?? null,
                            'check_out_latitude'      => $record['check_out_latitude']     ?? null,
                            'check_out_longitude'     => $record['check_out_longitude']    ?? null,
                            'check_out_location_name' => $record['check_out_location_name'] ?? null,
                        ];
                    } else {
                        // No check-in on this Saturday Off → mark as Week Off with no data
                        $formattedAttendance[] = [
                            'date'           => $date,
                            'check_in_time'  => null,
                            'check_out_time' => null,
                            'status'         => 'Week Off',
                            'is_late'        => null,
                            'late_minutes'   => null,
                            'overtime'       => null,
                        ];
                    }
                    continue;
                }

                // Attendance exists
                if (isset($attendanceByDate[$date])) {
                    $record = $attendanceByDate[$date];
                    $formattedAttendance[] = [
                        'date'                   => $date,
                        'check_in_time'          => $record['check_in_time'],
                        'check_out_time'         => $record['check_out_time'],
                        'status'                 => $record['status'],
                        'is_late'                => $record['is_late'],
                        'late_minutes'           => $record['late_minutes'],
                        'overtime'               => $record['overtime'] ?? null,
                        'work_hours'             => $record['work_hours'] ?? null,
                        'completed_seconds'      => $record['completed_seconds'] ?? 0,
                        // ── Location fields ──────────────────────────────
                        'check_in_ip_address'    => $record['check_in_ip_address']    ?? null,
                        'check_in_latitude'      => $record['check_in_latitude']      ?? null,
                        'check_in_longitude'     => $record['check_in_longitude']     ?? null,
                        'check_in_location_name' => $record['check_in_location_name'] ?? null,
                        'check_out_ip_address'   => $record['check_out_ip_address']   ?? null,
                        'check_out_latitude'     => $record['check_out_latitude']     ?? null,
                        'check_out_longitude'    => $record['check_out_longitude']    ?? null,
                        'check_out_location_name'=> $record['check_out_location_name']?? null,
                    ];
                    continue;
                }

                // Check for leave
                $isOnLeave = false;
                if (isset($leavesByUser[$userId])) {
                    foreach ($leavesByUser[$userId] as $leave) {
                        if ($date >= $leave['start_date'] && $date <= $leave['end_date']) {
                            $isOnLeave = true;
                            break;
                        }
                    }
                }

                if ($isOnLeave) {
                    $formattedAttendance[] = [
                        'date' => $date,
                        'check_in_time' => null,
                        'check_out_time' => null,
                        'status' => 'leave',
                        'is_late' => null,
                        'late_minutes' => null,
                        'overtime' => null,
                    ];
                    continue;
                }

                // ABSENT
                $formattedAttendance[] = [
                    'date' => $date,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'status' => 'absent',
                    'is_late' => null,
                    'late_minutes' => null,
                    'overtime' => null,
                ];
            }

            $attendanceWithUsers[] = [
                'user_id' => $userId,
                'employee_name' => $user['username'],
                'profile_image' => $userInfoMap[$userId]['profile_image'] ?? null,
                'status' => $userInfoMap[$userId]['status'] ?? 'Active',
                'last_working_day' => $lastWorkingDay,
                'joining_date' => $userInfoMap[$userId]['joining_date'] ?? null,
                'attendance' => $formattedAttendance,
            ];
        }

        // 🔹 Company info for location fallback
        $companyRecord  = $companyLogoModel->first() ?? [];
        $companyAddress = $companyRecord['company_address'] ?? '';
        $companyName    = $companyRecord['company_name']    ?? '';

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'users'             => $attendanceWithUsers,
                'isIncludedHoliday' => $isIncludedHoliday,
                'holidays'          => $holidays,
                'saturdayOffDates'  => $saturdayOffDates,
                'company'           => [
                    'address' => $companyAddress,
                    'name'    => $companyName,
                ],
            ]
        ]);
    }


    private function calculateAttendanceStats(): array
    {
        $userModel = new UserModel();
        $attendanceModel = new AttendanceModel();
        $today = date('Y-m-d');
        $currentYear = date('Y');
        $currentMonth = date('m');
        $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

        $filterBranchId = $this->authService->getBranchId();

        // Total employees in scope
        $totalEmpBuilder = $userModel->whereIn('role', ['employee', 'hr', 'branch_admin', 'department_manager'])->where('is_deleted', 0);
        if (!empty($filterBranchId)) {
            $totalEmpBuilder->where('branch_id', (int)$filterBranchId);
        }
        $totalEmployees = $totalEmpBuilder->countAllResults();

        // Scope user IDs query
        $userScopeSubQuery = function ($query) use ($filterBranchId) {
            $query->select('id')->from('users')->whereIn('role', ['employee', 'hr', 'branch_admin', 'department_manager'])->where('is_deleted', 0);
            if (!empty($filterBranchId)) {
                $query->where('branch_id', (int)$filterBranchId);
            }
        };

        $fullDayPresent = $attendanceModel
            ->select('user_id')
            ->where('status', 'present')
            ->where('date', $today)
            ->whereIn('user_id', $userScopeSubQuery)
            ->distinct()
            ->countAllResults();

        $halfDayPresent = $attendanceModel
            ->select('user_id')
            ->where('status', 'half-day')
            ->where('date', $today)
            ->whereIn('user_id', $userScopeSubQuery)
            ->distinct()
            ->countAllResults();

        $presentEmployees = $fullDayPresent + ($halfDayPresent * 0.5);

        $absentEmpQuery = $userModel
            ->whereIn('role', ['employee', 'hr', 'branch_admin', 'department_manager'])
            ->where('is_deleted', 0)
            ->whereNotIn('id', function ($query) use ($today) {
                $query->select('user_id')
                    ->from('attendance')
                    ->where('date', $today);
            });
        if (!empty($filterBranchId)) {
            $absentEmpQuery->where('branch_id', (int)$filterBranchId);
        }
        $absentEmployees = $absentEmpQuery->countAllResults();

        $workingDays = $attendanceModel
            ->distinct()
            ->select('date')
            ->like('date', date('Y-m'))
            ->whereIn('user_id', $userScopeSubQuery)
            ->countAllResults();

        return [
            'totalEmployees' => $totalEmployees,
            'presentEmployees' => $presentEmployees,
            'absentEmployees' => $absentEmployees,
            'workingDays' => $workingDays,
            'totalDaysInMonth' => $totalDaysInMonth,
        ];
    }

    public function view()
    {
        $user = $this->authService->user(); // Get logged-in user

        // Check if user is authenticated
        if (!$user) {
            return redirect()->to('/login')->with('error', 'Please login to access this page.');
        }

        $stats = $this->calculateAttendanceStats();

        return view('attendence/view', array_merge([
            'role' => $user->role,
        ], $stats));
    }

    public function viewCalendar()
    {
        $user = $this->authService->user(); // Get logged-in user

        // Check if user is authenticated
        if (!$user) {
            return redirect()->to('/login')->with('error', 'Please login to access this page.');
        }

        $stats = $this->calculateAttendanceStats();

        return view('attendence/view_calendar', array_merge([
            'role' => $user->role,
        ], $stats));
    }

    public function getDashboardStats()
    {
        $user = $this->authService->user(); // Get logged-in user
        if (!$user) {
            return $this->failUnauthorized('Unauthorized');
        }

        $stats = $this->calculateAttendanceStats();

        return $this->response->setJSON(array_merge([
            'role' => $user->role,
        ], $stats));
    }

    public function getFacePhoto()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $userInfoModel = new \App\Models\UserInfoModel();
        $userInfo = $userInfoModel->where('user_id', $user->sub)->first();

        if (!$userInfo || empty($userInfo['face_photo'])) {
            return $this->respond([
                'status' => 'error',
                'message' => 'No face photo registered. Please contact admin.',
                'face_photo' => null
            ]);
        }

        return $this->respond([
            'status' => 'success',
            'face_photo' => base_url('upload/faces/' . $userInfo['face_photo'])
        ]);
    }

    public function faceCheckIn()
    {
        date_default_timezone_set('Asia/Kolkata');

        $user = $this->authService->check();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        if (!in_array($user->role, ['hr', 'branch_admin', 'department_manager', 'employee'])) {
            return $this->respond(['status' => 'error', 'message' => 'Access denied for this role'], 403);
        }

        // Get the face image from request (base64)
        $json = $this->request->getJSON(true);
        $faceImage = $json['face_image'] ?? null;
        $checkInTimeInput = $json['check_in_time'] ?? date('Y-m-d H:i:s');
        $userLatitude = $json['latitude'] ?? null;
        $userLongitude = $json['longitude'] ?? null;
        $locationAccuracy = isset($json['location_accuracy']) ? (float) $json['location_accuracy'] : null;

        if (!$faceImage) {
            return $this->respond(['status' => 'error', 'message' => 'Face image is required'], 400);
        }

        // Check location if location settings are configured
        $locationSettings = $this->getOfficeLocationForUser((int)$user->sub);

        // Check if this employee is a remote worker — remote employees skip location validation
        $userInfoForLocation = new \App\Models\UserInfoModel();
        $employeeInfo = $userInfoForLocation->where('user_id', $user->sub)->first();
        $isRemoteEmployee = !empty($employeeInfo['working_location']) &&
            strtolower(trim($employeeInfo['working_location'])) === 'remote';

        if (!$isRemoteEmployee && $locationSettings && ($locationSettings['latitude'] != 0 || $locationSettings['longitude'] != 0)) {
            // Location verification is enabled
            if ($userLatitude === null || $userLongitude === null) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Location permission is required for check-in. Please enable location access.'
                ], 400);
            }

            // Ensure coordinates are floats
            $officeLat = (float) $locationSettings['latitude'];
            $officeLng = (float) $locationSettings['longitude'];
            $userLat = (float) $userLatitude;
            $userLng = (float) $userLongitude;

            // Calculate distance between user location and office location
            $distance = $this->calculateDistance(
                $officeLat,
                $officeLng,
                $userLat,
                $userLng
            );

            $radius = (float) $locationSettings['radius'];

            // Adjust radius tolerance based on location accuracy (for desktop browsers with poor GPS)
            // If accuracy is very poor (>1000m, typical for IP-based geolocation), add significant tolerance
            $effectiveRadius = $radius;
            if ($locationAccuracy !== null && $locationAccuracy > 1000) {
                $maxTotalRadius = 15000; // 15km max for desktop
                $tolerance = min($locationAccuracy * 1.5, $maxTotalRadius - $radius); // 1.5x accuracy for extra buffer
                $effectiveRadius = $radius + $tolerance;
                log_message('debug', 'Poor location accuracy detected (' . round($locationAccuracy, 2) . 'm). Adjusted radius tolerance: ' . round($effectiveRadius, 2) . 'm (original radius: ' . $radius . 'm, tolerance: ' . round($tolerance, 2) . 'm)');
            } else if ($locationAccuracy !== null && $locationAccuracy > 500) {
                // For moderate accuracy (500-1000m), add smaller tolerance
                $tolerance = min($locationAccuracy * 0.75, $radius * 1.5);
                $effectiveRadius = $radius + $tolerance;
                log_message('debug', 'Moderate location accuracy detected (' . round($locationAccuracy, 2) . 'm). Adjusted radius tolerance: ' . round($effectiveRadius, 2) . 'm');
            }

            // Debug info (can be removed later)
            log_message('debug', 'Location Check - Office: ' . $officeLat . ', ' . $officeLng . ' | User: ' . $userLat . ', ' . $userLng . ' | Distance: ' . $distance . 'm | Radius: ' . $radius . 'm | Effective Radius: ' . round($effectiveRadius, 2) . 'm | Accuracy: ' . ($locationAccuracy !== null ? round($locationAccuracy, 2) . 'm' : 'unknown'));

            // If radius is 0, check exact location (within 10 meters tolerance)
            if ($radius == 0) {
                // For exact location, also consider accuracy
                $tolerance = 10;
                if ($locationAccuracy !== null && $locationAccuracy > 100) {
                    $tolerance = min(50, $locationAccuracy * 0.3); // Max 50m tolerance for exact location
                }
                if ($distance > $tolerance) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => 'You are not at the correct location. Please check in from the office location.',
                        'distance' => round($distance, 2)
                    ], 400);
                }
            } else {
                // Check if user is within the allowed radius (with tolerance for poor accuracy)
                if ($distance > $effectiveRadius) {
                    $message = 'You are outside the allowed check-in radius (' . $radius . ' meters). Current distance: ' . round($distance, 2) . ' meters.';
                    if ($locationAccuracy !== null && $locationAccuracy > 1000) {
                        $message .= ' Note: Your location accuracy is low (±' . round($locationAccuracy, 0) . 'm). On desktop, please ensure WiFi is enabled for better location accuracy, or contact admin for assistance.';
                    } else if ($locationAccuracy !== null && $locationAccuracy > 500) {
                        $message .= ' Note: Your location accuracy is moderate (±' . round($locationAccuracy, 0) . 'm). Please ensure location services are enabled.';
                    }
                    return $this->respond([
                        'status' => 'error',
                        'message' => $message,
                        'distance' => round($distance, 2),
                        'allowed_radius' => $radius,
                        'effective_radius' => round($effectiveRadius, 2),
                        'location_accuracy' => $locationAccuracy !== null ? round($locationAccuracy, 2) : null,
                        'debug' => [
                            'office_location' => ['lat' => $officeLat, 'lng' => $officeLng],
                            'user_location' => ['lat' => $userLat, 'lng' => $userLng]
                        ]
                    ], 400);
                }
            }
        }

        // Verify user has face photo registered
        $userInfoModel = new \App\Models\UserInfoModel();
        $userInfo = $userInfoModel->where('user_id', $user->sub)->first();

        if (!$userInfo || empty($userInfo['face_photo'])) {
            return $this->respond([
                'status' => 'error',
                'message' => 'No face photo registered. Please contact admin to upload your face photo.'
            ], 400);
        }

        // Always use server's current date and time (Asia/Kolkata timezone)
        // This ensures the date is always correct regardless of client timezone
        $date = date('Y-m-d'); // Today's date in server timezone
        $timeOnly = date('H:i:s'); // Current server time (HH:MM:SS)

        // Get latest attendance for today
        $latestAttendance = $this->attendanceModel
            ->where('user_id', $user->sub)
            ->where('date', $date)
            ->orderBy('id', 'DESC')
            ->first();

        if ($latestAttendance && !$latestAttendance['check_out_time']) {

            $updateData = [
                'check_in_time' => $timeOnly, // Store only time (HH:MM:SS)
                'checkin_method' => 'face_recognition'
            ];

            $this->attendanceModel->update($latestAttendance['id'], $updateData);

            return $this->respond([
                'status' => 'success',
                'message' => 'Face verified! Check-in time updated successfully.',
                'data' => $updateData
            ]);
        }

        $companyRule = $this->companyRulesModel->orderBy('id', 'DESC')->first();
        // Defaults
        $break = '00:30:00';
        $isLate = 0;
        $lateMinutes = 0;
        // $halfDayHourTime = "02:00:00";
        if ($companyRule) {

            $break = $companyRule['lunch_break'] ?? '00:30:00';
            $startTime = $companyRule['start_time'];          // e.g. 10:00:00
            $gracePeriod = (int) $companyRule['grace_period'];  // minutes

            $checkInSeconds = $this->timeToSeconds($timeOnly);
            $startSeconds = $this->timeToSeconds($startTime);
            $graceSeconds = $gracePeriod * 60;

            if ($checkInSeconds > ($startSeconds + $graceSeconds)) {

                $isLate = 1;
                $lateSeconds = $checkInSeconds - ($startSeconds + $graceSeconds);
                $lateMinutes = ceil($lateSeconds / 60);

            }
        }

        // Create new attendance record
        $data = [
            'user_id' => $user->sub,
            'date' => $date, // Today's date from server
            'check_in_time' => $timeOnly, // Current time from server (HH:MM:SS)
            'meal_break' => $break,
            'status' => 'present',
            'checkin_method' => 'face_recognition',
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes
        ];

        // Delete any leave record for today if exists
        $this->leaveModel
            ->where('user_id', $user->sub)
            ->where('start_date', $date)
            ->where('end_date', $date)
            ->delete();

        if ($this->attendanceModel->insert($data)) {
            // Check if attendance notifications are enabled
            $notificationSettingsModel = new NotificationSettingsModel();
            if ($notificationSettingsModel->isAttendanceNotificationsEnabled()) {
                $this->hierarchyService->dispatchCheckInNotification((int)$user->sub, $timeOnly, $date);
            } else {
                log_message('info', '📝 Attendance notifications are disabled - skipping push notification');
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Face verified! Checked in successfully via face recognition.'
            ]);
        }

        return $this->respond(['status' => 'error', 'message' => 'Face check-in failed. Please try again.'], 500);
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in meters
     * 
     * @param float $lat1 Latitude of first point
     * @param float $lon1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lon2 Longitude of second point
     * @return float Distance in meters
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Delete today's attendance records for the authenticated user
     * Or delete all today's records if user is HR/Admin
     */
    public function deleteTodayAttendance()
    {
        date_default_timezone_set('Asia/Kolkata');

        $user = $this->authService->check();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $today = date('Y-m-d');

        // If user is HR or Admin, delete all today's attendance records
        if (in_array($user->role, ['hr', 'admin'])) {
            $deleted = $this->attendanceModel
                ->where('date', $today)
                ->delete();

            return $this->respond([
                'status' => 'success',
                'message' => "Deleted {$deleted} attendance record(s) for today ({$today})",
                'deleted_count' => $deleted
            ]);
        }

        // If user is employee, delete only their own today's attendance records
        $deleted = $this->attendanceModel
            ->where('user_id', $user->sub)
            ->where('date', $today)
            ->delete();

        return $this->respond([
            'status' => 'success',
            'message' => "Deleted {$deleted} attendance record(s) for today ({$today})",
            'deleted_count' => $deleted
        ]);
    }

    public function getDayAttendanceRecords()
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['hr', 'admin'])) {
            return $this->failUnauthorized('Access denied');
        }

        $userId = $this->request->getGet('user_id');
        $date = $this->request->getGet('date');

        if (!$userId || !$date) {
            return $this->fail('Invalid parameters');
        }

        // Fetch ALL sessions for this day (not just the latest)
        $records = $this->attendanceModel
            ->where('user_id', $userId)
            ->where('date', $date)
            ->orderBy('check_in_time', 'ASC')  // earliest first
            ->findAll();

        // Add a human-readable duration per row (for the modal Duration column)
        foreach ($records as &$rec) {
            if (!empty($rec['check_in_time']) && !empty($rec['check_out_time'])) {
                // Use stored work_hours if available, otherwise compute
                $rec['duration'] = $rec['work_hours'] ?? $this->computeDuration(
                    $rec['check_in_time'],
                    $rec['check_out_time']
                );
            } else {
                $rec['duration'] = null; // still active
            }
        }
        unset($rec);

        return $this->respond([
            'status' => 'success',
            'data' => $records
        ]);
    }

    /** Quick helper: compute HH:MM:SS duration between two HH:MM:SS strings */
    private function computeDuration(string $checkIn, string $checkOut): string
    {
        $inSec = $this->timeToSeconds($checkIn);
        $outSec = $this->timeToSeconds($checkOut);
        $diff = max(0, $outSec - $inSec);
        return sprintf('%02d:%02d:%02d', intdiv($diff, 3600), intdiv($diff % 3600, 60), $diff % 60);
    }

    public function updateDayAttendanceRecords()
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['hr', 'admin'])) {
            return $this->failUnauthorized('Access denied');
        }

        $payload = $this->request->getJSON(true);

        $userId = $payload['user_id'] ?? null;
        $date = $payload['date'] ?? null;
        $records = $payload['records'] ?? [];
        $manualStatus = $payload['status'] ?? null; // New: manual status override

        if (!$userId || !$date || empty($records)) {
            return $this->fail('Invalid data');
        }

        // Get company rules
        $companyRule = $this->companyRulesModel->orderBy('id', 'DESC')->first() ?? [];
        $mealBreak = $companyRule['lunch_break'] ?? '00:30:00';
        $startTime = $companyRule['start_time'] ?? '09:00:00';
        $gracePeriod = (int) ($companyRule['grace_period'] ?? 0); // minutes

        // Check if this is a new attendance record (no existing records)
        $existingRecords = $this->attendanceModel
            ->where('user_id', $userId)
            ->where('date', $date)
            ->findAll();

        $isNewRecord = empty($existingRecords);

        if ($isNewRecord) {
            $record = $records[0];

            $workHours = '00:00:00';
            $overTimeHours = '00:00:00';
            $calculatedStatus = 'absent';

            if (!empty($record['check_in_time']) && !empty($record['check_out_time'])) {
                $calculation = $this->calculateWorkHours(
                    $date,
                    $mealBreak,
                    $record['check_in_time'],
                    $record['check_out_time']
                );
                $workHours = $calculation['work_hours'];
                $overTimeHours = $calculation['overtime'];
                // Use the status already calculated by calculateWorkHours()
                $calculatedStatus = $calculation['status'];

            }

            // Calculate late status for check-in time
            $isLate = 0;
            $lateMinutes = 0;
            if (!empty($record['check_in_time'])) {
                $checkInSeconds = $this->timeToSeconds($record['check_in_time']);
                $startSeconds = $this->timeToSeconds($startTime);
                $graceSeconds = $gracePeriod * 60;

                if ($checkInSeconds > ($startSeconds + $graceSeconds)) {
                    $isLate = 1;
                    $lateSeconds = $checkInSeconds - ($startSeconds + $graceSeconds);
                    $lateMinutes = ceil($lateSeconds / 60);
                }
            }

            // Use manual status if provided, otherwise use calculated status
            $finalStatus = $manualStatus ?? $calculatedStatus;

            // If manual status is "absent", force work hours to 0
            if ($manualStatus === 'absent') {
                $workHours = '00:00:00';
            }

            $attendanceData = [
                'user_id' => $userId,
                'date' => $date,
                'check_in_time' => $record['check_in_time'] ?? null,
                'check_out_time' => $record['check_out_time'] ?? null,
                'meal_break' => $mealBreak,
                'work_hours' => $workHours,
                'overtime' => !empty($record['check_in_time']) && !empty($record['check_out_time']) ? $calculation['overtime'] : '00:00:00',
                'status' => $finalStatus,
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes
            ];

            if ($isNewRecord || $record['id'] === 'new') {
                // Create new attendance record
                $this->attendanceModel->insert($attendanceData);

                // Delete any conflicting leave records
                $this->leaveModel
                    ->where('user_id', $userId)
                    ->where('start_date <=', $date)
                    ->where('end_date >=', $date)
                    ->delete();
            } else {
                // Update existing record
                $this->attendanceModel->update($record['id'], $attendanceData);
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Attendance updated successfully',
                'work_hours' => $workHours,
                'overtime' => !empty($record['check_in_time']) && !empty($record['check_out_time']) ? $calculation['overtime'] : '00:00:00',
                'final_status' => $finalStatus,
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes
            ]);
        }

        // Handle multiple records (existing logic)
        // Use timeToSeconds() — DateTime cannot parse a bare HH:MM:SS string
        $mealSeconds = $this->timeToSeconds($mealBreak);

        $totalSeconds = 0;
        $earliestCheckIn = null;
        $isLate = 0;
        $lateMinutes = 0;

        foreach ($records as $row) {
            // Calculate late status based on the earliest check-in time
            if (!empty($row['check_in_time'])) {
                $currentCheckInSeconds = $this->timeToSeconds($row['check_in_time']);

                if ($earliestCheckIn === null || $currentCheckInSeconds < $earliestCheckIn) {
                    $earliestCheckIn = $currentCheckInSeconds;

                    // Calculate if this earliest check-in is late
                    $startSeconds = $this->timeToSeconds($startTime);
                    $graceSeconds = $gracePeriod * 60;

                    if ($currentCheckInSeconds > ($startSeconds + $graceSeconds)) {
                        $isLate = 1;
                        $lateSeconds = $currentCheckInSeconds - ($startSeconds + $graceSeconds);
                        $lateMinutes = ceil($lateSeconds / 60);
                    } else {
                        $isLate = 0;
                        $lateMinutes = 0;
                    }
                }
            }

            // Update individual punch with late information
            $this->attendanceModel->update($row['id'], [
                'check_in_time' => $row['check_in_time'],
                'check_out_time' => $row['check_out_time'],
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes
            ]);

            // Calculate duration
            if (!empty($row['check_in_time']) && !empty($row['check_out_time'])) {
                $in = strtotime($date . ' ' . $row['check_in_time']);
                $out = strtotime($date . ' ' . $row['check_out_time']);

                if ($out > $in) {
                    $totalSeconds += ($out - $in);
                }
            }
        }

        // Deduct meal break ONCE
        if ($totalSeconds > $mealSeconds) {
            $totalSeconds -= $mealSeconds;
        }

        // Company rules (5 hrs = half day when half_day_hours not set)
        $requiredSeconds = ($companyRule['working_hours_per_day'] ?? 8) * 3600;
        $halfDayHours = isset($companyRule['half_day_hours']) && $companyRule['half_day_hours'] !== '' && $companyRule['half_day_hours'] !== null
            ? (float) $companyRule['half_day_hours'] : 5;
        $halfDaySeconds = $halfDayHours * 3600;

        // Determine status - use manual status if provided
        if ($manualStatus) {
            $status = $manualStatus;
        } else {
            $status = 'absent';
            if ($totalSeconds >= $requiredSeconds) {
                $status = 'present';
            } elseif ($totalSeconds >= $halfDaySeconds) {
                $status = 'half-day';
            }
        }

        // If manual status is "absent", force work hours to 0
        if ($manualStatus === 'absent') {
            $totalSeconds = 0;
        }

        $formattedHours = gmdate('H:i:s', $totalSeconds);

        // Update ALL records of that day with final result
        $this->attendanceModel
            ->where('user_id', $userId)
            ->where('date', $date)
            ->set([
                'work_hours' => $formattedHours,
                'status' => $status,
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes
            ])
            ->update();

        // Delete any conflicting leave records
        $this->leaveModel
            ->where('user_id', $userId)
            ->where('start_date <=', $date)
            ->where('end_date >=', $date)
            ->delete();

        return $this->respond([
            'status' => 'success',
            'message' => 'Attendance updated successfully',
            'work_hours' => $formattedHours,
            'final_status' => $status,
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes
        ]);
    }

    public function bulkUpdateAttendanceRecords()
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['hr', 'admin'])) {
            return $this->failUnauthorized('Access denied');
        }

        $payload = $this->request->getJSON(true);

        $userId = $payload['user_id'] ?? null;
        $fromDate = $payload['from_date'] ?? null;
        $toDate = $payload['to_date'] ?? null;
        $status = $payload['status'] ?? null;

        $checkIn = $payload['check_in_time'] ?? null;
        $checkOut = $payload['check_out_time'] ?? null;

        if (!$userId || !$fromDate || !$toDate || !$status) {
            return $this->fail('Invalid data');
        }

        // Company rules
        $companyRule = $this->companyRulesModel->orderBy('id', 'DESC')->first() ?? [];
        $mealBreak = $companyRule['lunch_break'] ?? '00:30:00';
        $startTime = $companyRule['start_time'] ?? '09:00:00';
        $gracePeriod = (int) ($companyRule['grace_period'] ?? 0);

        $start = new \DateTime($fromDate);
        $end = new \DateTime($toDate);
        $end->modify('+1 day');

        foreach (new \DatePeriod($start, new \DateInterval('P1D'), $end) as $dt) {

            $date = $dt->format('Y-m-d');

            if (date('w', strtotime($date)) == 0) {
                continue;
            }

            $holidayCalendarModel = new HolidayCalendarModel();
            // Skip holidays
            if ($holidayCalendarModel->where('holiday_date', $date)->first()) {
                continue;
            }

            $workHours = '00:00:00';
            $overtime = '00:00:00';
            $isLate = 0;
            $lateMinutes = 0;

            if ($checkIn) {
                $checkInSec = $this->timeToSeconds($checkIn);
                $startSec = $this->timeToSeconds($startTime);
                $graceSec = $gracePeriod * 60;

                if ($checkInSec > ($startSec + $graceSec)) {
                    $isLate = 1;
                    $lateMinutes = ceil(
                        ($checkInSec - ($startSec + $graceSec)) / 60
                    );
                }
            }

            if ($status !== 'absent' && $checkIn && $checkOut) {

                $calc = $this->calculateWorkHours(
                    $date,
                    $mealBreak,
                    $checkIn,
                    $checkOut
                );

                $workHours = $calc['work_hours'];
                $overtime = $calc['overtime'];
            }

            $attendanceData = [
                'user_id' => $userId,
                'date' => $date,
                'check_in_time' => $status === 'absent' ? null : $checkIn,
                'check_out_time' => $status === 'absent' ? null : $checkOut,
                'meal_break' => $mealBreak,
                'work_hours' => $status === 'absent' ? '00:00:00' : $workHours,
                'overtime' => $status === 'absent' ? '00:00:00' : $overtime,
                'status' => $status,
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes
            ];

            $existing = $this->attendanceModel
                ->where('user_id', $userId)
                ->where('date', $date)
                ->first();

            if ($existing) {
                $this->attendanceModel->update($existing['id'], $attendanceData);
            } else {
                $this->attendanceModel->insert($attendanceData);
            }

            // 🧹 Remove conflicting leave
            $this->leaveModel
                ->where('user_id', $userId)
                ->where('start_date <=', $date)
                ->where('end_date >=', $date)
                ->delete();
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Bulk attendance updated successfully'
        ]);
    }

    /**
     * Multi Attendance Manage: bulk update attendance status, check-in, check-out for selected employees on a specific date.
     */
    public function multiAttendanceManage()
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['hr', 'admin'])) {
            return $this->failUnauthorized('Access denied');
        }

        $payload = $this->request->getJSON(true);
        $userIds = $payload['user_ids'] ?? [];
        $date = !empty($payload['date']) ? trim($payload['date']) : date('Y-m-d');
        $status = strtolower(trim($payload['status'] ?? 'present'));
        $checkIn = !empty($payload['check_in_time']) ? trim($payload['check_in_time']) : null;
        $checkOut = !empty($payload['check_out_time']) ? trim($payload['check_out_time']) : null;

        if (empty($userIds) || !is_array($userIds)) {
            return $this->fail('Please select at least one employee.');
        }

        if (empty($date)) {
            return $this->fail('Please select a valid date.');
        }

        // Format times to HH:MM:SS if needed
        if ($checkIn && strlen($checkIn) === 5) {
            $checkIn .= ':00';
        }
        if ($checkOut && strlen($checkOut) === 5) {
            $checkOut .= ':00';
        }

        // Company rules for calculations
        $companyRule = $this->companyRulesModel->orderBy('id', 'DESC')->first() ?? [];
        $mealBreak = $companyRule['lunch_break'] ?? '00:30:00';
        $startTime = $companyRule['start_time'] ?? '09:00:00';
        $gracePeriod = (int) ($companyRule['grace_period'] ?? 0);

        $workHours = '00:00:00';
        $overtime = '00:00:00';
        $isLate = 0;
        $lateMinutes = 0;

        if ($status === 'absent') {
            $checkIn = null;
            $checkOut = null;
            $workHours = '00:00:00';
            $overtime = '00:00:00';
        } else {
            if (!empty($checkIn) && !empty($checkOut)) {
                $calc = $this->calculateWorkHours($date, $mealBreak, $checkIn, $checkOut);
                $workHours = $calc['work_hours'];
                $overtime = $calc['overtime'];
            }
            if (!empty($checkIn)) {
                $checkInSec = $this->timeToSeconds($checkIn);
                $startSec = $this->timeToSeconds($startTime);
                $graceSec = $gracePeriod * 60;
                if ($checkInSec > ($startSec + $graceSec)) {
                    $isLate = 1;
                    $lateMinutes = (int) ceil(($checkInSec - ($startSec + $graceSec)) / 60);
                }
            }
        }

        $successCount = 0;
        foreach ($userIds as $userId) {
            $userId = (int) $userId;
            if ($userId <= 0) {
                continue;
            }

            $attendanceData = [
                'user_id' => $userId,
                'date' => $date,
                'check_in_time' => $checkIn,
                'check_out_time' => $checkOut,
                'meal_break' => $mealBreak,
                'work_hours' => $workHours,
                'overtime' => $overtime,
                'status' => $status,
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes,
            ];

            $existing = $this->attendanceModel
                ->where('user_id', $userId)
                ->where('date', $date)
                ->first();

            if ($existing) {
                $this->attendanceModel->update($existing['id'], $attendanceData);
            } else {
                $this->attendanceModel->insert($attendanceData);
            }

            // Remove conflicting leave records if marked present or half-day
            if (in_array($status, ['present', 'half-day'])) {
                $this->leaveModel
                    ->where('user_id', $userId)
                    ->where('start_date <=', $date)
                    ->where('end_date >=', $date)
                    ->delete();
            }

            $successCount++;
        }

        return $this->respond([
            'status' => 'success',
            'message' => "Attendance updated for {$successCount} employee(s) successfully.",
        ]);
    }
    /**
     * Get office location for a user (prioritizing branch location over global settings)
     */
    private function getOfficeLocationForUser($userId)
    {
        $user = (new \App\Models\UserModel())->find($userId);
        if ($user && !empty($user['branch_id'])) {
            $branch = (new \App\Models\BranchModel())->find($user['branch_id']);
            if ($branch && !empty($branch['latitude']) && !empty($branch['longitude'])) {
                return [
                    'latitude' => $branch['latitude'],
                    'longitude' => $branch['longitude'],
                    'radius' => isset($branch['radius']) ? $branch['radius'] : 100,
                ];
            }
        }
        
        $locationSettingsModel = new \App\Models\LocationSettingsModel();
        return $locationSettingsModel->first();
    }
}


