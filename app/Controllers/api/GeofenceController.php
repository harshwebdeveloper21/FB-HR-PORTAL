<?php
namespace App\Controllers\api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\BranchRuleModel;
use App\Models\UserModel;
use App\Models\NotificationModel;
use App\Services\AuthService;

class GeofenceController extends ResourceController
{
    use ResponseTrait;
    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
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

        // Ignore poor accuracy
        if ($accuracy > 20) {
            return $this->respond(['status' => 'ignored', 'message' => 'Accuracy too low']);
        }

        $db = \Config\Database::connect();
        
        // Find active attendance
        $builder = $db->table('attendance');
        $attendance = $builder->where('employee_id', $user->sub)
            ->where('check_out_time', null)
            ->orderBy('id', 'DESC')
            ->get()->getRowArray();

        if (!$attendance) {
            return $this->respond(['status' => 'ignored', 'message' => 'No active check-in']);
        }

        $branchId = $attendance['branch_id'];
        
        // Get branch rules
        $branchRulesModel = new BranchRuleModel();
        $rules = $branchRulesModel->where('branch_id', $branchId)->first();
        
        if (!$rules || empty($rules['enable_geofencing']) || empty($rules['office_latitude'])) {
            return $this->respond(['status' => 'ignored', 'message' => 'Geofencing not enabled for this branch']);
        }

        // Check if within working hours and not lunch break (simplified)
        $currentTime = date('H:i:s');
        if ($currentTime < $rules['start_time'] || $currentTime > $rules['end_time']) {
            return $this->respond(['status' => 'ignored', 'message' => 'Outside working hours']);
        }

        // Calculate distance
        $distance = $this->calculateDistanceMeters(
            $latitude, $longitude,
            $rules['office_latitude'], $rules['office_longitude']
        );
        $radius = $rules['office_radius'] ?? 15;

        // Is Outside?
        $isOutside = $distance > $radius;

        // Get last event
        $eventBuilder = $db->table('geofence_events');
        $lastEvent = $eventBuilder->where('employee_id', $user->sub)
            ->where('event_time >=', date('Y-m-d 00:00:00'))
            ->orderBy('id', 'DESC')
            ->get()->getRowArray();

        $lastStatus = $lastEvent ? $lastEvent['event_type'] : 'RE-ENTRY';
        
        if ($isOutside && $lastStatus !== 'EXIT') {
            // Register EXIT
            $eventBuilder->insert([
                'employee_id' => $user->sub,
                'branch_id' => $branchId,
                'event_type' => 'EXIT',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'event_time' => date('Y-m-d H:i:s')
            ]);
            
            // Notify HR
            $userModel = new UserModel();
            $employee = $userModel->find($user->sub);
            
            $notificationModel = new NotificationModel();
            $hrAdmins = $userModel->whereIn('role', ['hr', 'admin'])->findAll();
            foreach ($hrAdmins as $hr) {
                $notificationModel->insert([
                    'sender_id' => $user->sub,
                    'recipient_id' => $hr['id'],
                    'data' => json_encode([
                        'message' => "Employee {$employee['username']} has left the showroom premises.",
                        'type' => 'geofence_alert'
                    ]),
                    'is_read' => 0
                ]);
            }
            return $this->respond(['status' => 'alert', 'message' => 'You have left the showroom premises during working hours.']);
        } else if (!$isOutside && $lastStatus === 'EXIT') {
            // Register RE-ENTRY
            $duration = strtotime(date('Y-m-d H:i:s')) - strtotime($lastEvent['event_time']);
            $eventBuilder->insert([
                'employee_id' => $user->sub,
                'branch_id' => $branchId,
                'event_type' => 'RE-ENTRY',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'event_time' => date('Y-m-d H:i:s'),
                'duration_outside' => $duration
            ]);
            
            return $this->respond(['status' => 'ok', 'message' => 'You have re-entered the premises.']);
        }

        return $this->respond(['status' => 'ok', 'message' => 'Tracking active', 'distance' => $distance]);
    }
}
