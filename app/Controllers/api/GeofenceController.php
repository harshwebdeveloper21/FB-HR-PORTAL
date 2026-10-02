<?php
namespace App\Controllers\api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\BranchRuleModel;
use App\Models\UserModel;
use App\Models\NotificationModel;
use App\Services\AuthService;
use App\Services\PushNotificationService;

class GeofenceController extends ResourceController
{
    use ResponseTrait;
    protected $authService;
    protected $pushNotificationService;

    public function __construct()
    {
        $this->authService = new AuthService(\Config\Services::request());
        $this->pushNotificationService = new PushNotificationService();
    }

    /**
     * Calculate distance between two points in meters using Haversine formula
     */
    private function calculateDistanceMeters($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371000; // in meters
        
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        return $angle * $earth_radius;
    }

    /**
     * Ping location every 30-60s during active checkin
     */
    public function pingLocation()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized');
        }

        $payload = $this->request->getJSON();
        $latitude = $payload->latitude ?? null;
        $longitude = $payload->longitude ?? null;
        $accuracy = $payload->accuracy ?? 999;
        
        if (!$latitude || !$longitude) {
            return $this->fail('Location missing');
        }

        // Geofence Settings - Relaxed accuracy for mobile GPS
        $maxAccuracyM = 200;  // Accept GPS up to 200m accuracy (mobile GPS is often 30-100m)
        $gpsBufferM = 10;
        $exitConfirmReadings = 3;  // Need 3 consecutive readings outside
        $exitConfirmSeconds = 30;  // Over 30 seconds

        // Only skip very poor accuracy readings (e.g. indoor WiFi-only)
        if ($accuracy > $maxAccuracyM) {
            return $this->respond(['status' => 'ignored', 'message' => 'Accuracy too low (' . $accuracy . 'm)']);
        }

        $db = \Config\Database::connect();
        
        // Find active attendance (checked in, not checked out today) - use correct column: user_id
        $attendance = $db->table('attendance')
            ->where('user_id', $user->sub)
            ->where('check_out_time IS NULL', null, false)
            ->where('date', date('Y-m-d'))
            ->orderBy('id', 'DESC')
            ->get()->getRowArray();

        if (!$attendance) {
            return $this->respond(['status' => 'ignored', 'message' => 'No active check-in today']);
        }

        $branchId = $attendance['branch_id'];
        
        // Get branch rules (lat/lon/radius)
        $branchRulesModel = new BranchRuleModel();
        $rules = $branchRulesModel->where('branch_id', $branchId)->first();
        
        if (!$rules || empty($rules['enable_geofencing']) || empty($rules['office_latitude'])) {
            return $this->respond(['status' => 'ignored', 'message' => 'Geofencing not enabled for this branch']);
        }

        // Calculate distance
        $distance = $this->calculateDistanceMeters(
            $latitude, $longitude,
            (float)$rules['office_latitude'], (float)$rules['office_longitude']
        );
        
        $radius = (float)($rules['office_radius'] ?? 50);

        // Threshold = radius + GPS buffer
        $threshold = $radius + $gpsBufferM;
        
        // Get current state from Session instead of DB
        $session = session();
        $stateKey = 'geofence_state_' . $user->sub;
        $state = $session->get($stateKey);
        
        if (!$state) {
            $state = [
                'is_outside' => false,
                'outside_since' => null,
                'consecutive_out' => 0
            ];
        }

        $isCurrentlyOutside = $state['is_outside'];
        $decision = 'INSIDE';
        
        if ($distance > $threshold) {
            $decision = 'OUTSIDE_BUFFER';
            $state['consecutive_out'] += 1;
            if (!$state['outside_since']) {
                $state['outside_since'] = date('Y-m-d H:i:s');
            }
            
            // Check confirmation criteria
            $timeOut = strtotime(date('Y-m-d H:i:s')) - strtotime($state['outside_since']);
            
            if (!$isCurrentlyOutside && $state['consecutive_out'] >= $exitConfirmReadings && $timeOut >= $exitConfirmSeconds) {
                // Confirmed Exit!
                $state['is_outside'] = true;
                $decision = 'CONFIRMED_EXIT';
                
                // Notify HR
                $this->notifyHRExit($user->sub);
                
                // Log event
                $db->table('geofence_events')->insert([
                    'employee_id' => $user->sub,
                    'branch_id' => $branchId,
                    'event_type' => 'EXIT',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'event_time' => $state['outside_since'] // Use original time they left
                ]);
            }

        } else if ($distance <= $radius) {
            $decision = 'INSIDE_RADIUS';
            
            if ($isCurrentlyOutside) {
                // Re-entry!
                $decision = 'CONFIRMED_REENTRY';
                $duration = strtotime(date('Y-m-d H:i:s')) - strtotime($state['outside_since']);
                
                $db->table('geofence_events')->insert([
                    'employee_id' => $user->sub,
                    'branch_id' => $branchId,
                    'event_type' => 'RE-ENTRY',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'event_time' => date('Y-m-d H:i:s'),
                    'duration_outside' => $duration
                ]);
            }
            
            // Reset state
            $state = [
                'is_outside' => false,
                'outside_since' => null,
                'consecutive_out' => 0
            ];
        }

        // Save state to session
        $session->set($stateKey, $state);

        if ($decision === 'CONFIRMED_EXIT') {
            return $this->respond(['status' => 'alert', 'message' => 'You have left the showroom premises during working hours.']);
        }
        
        return $this->respond(['status' => 'ok', 'message' => 'Tracking active', 'distance' => $distance]);
    }
    
    private function notifyHRExit($userId) {
        $userModel = new UserModel();
        $employee = $userModel->find($userId);
        
        $employeeName = $employee ? $employee['username'] : 'Employee';
        $currentTime = date('H:i:s');
        $currentDate = date('Y-m-d');
        
        // Wrap push notification in try-catch so a failure doesn't break the ping endpoint
        try {
            $notificationSettingsModel = new \App\Models\NotificationSettingsModel();
            if ($notificationSettingsModel->isAttendanceNotificationsEnabled()) {
                $this->pushNotificationService->notifyAdmins(
                    'Employee Left Building',
                    $employeeName . ' has left the showroom premises at ' . $currentTime,
                    [
                        'type'     => 'geofence_alert',
                        'user_id'  => $userId,
                        'username' => $employeeName,
                        'time'     => $currentTime,
                        'date'     => $currentDate,
                        'url'      => base_url('/dashboard') // Redirect to dashboard when clicked
                    ]
                );
            }
        } catch (\Throwable $e) {
            log_message('error', 'Geofence push notification failed: ' . $e->getMessage());
        }

        // Also insert into database notification table (if not handled by notifyAdmins already)
        $notificationModel = new NotificationModel();
        $hrAdmins = $userModel->whereIn('role', ['hr', 'admin'])->findAll();
        foreach ($hrAdmins as $hr) {
            $notificationModel->insert([
                'sender_id' => $userId,
                'recipient_id' => $hr['id'],
                'data' => json_encode([
                    'message' => "Employee {$employeeName} has left the showroom premises.",
                    'type' => 'geofence_alert'
                ]),
                'is_read' => 0
            ]);
        }
    }

    public function testPage()
    {
        $data['title'] = 'Geofence Live Tester';
        return view('geofence/test', $data);
    }
}
