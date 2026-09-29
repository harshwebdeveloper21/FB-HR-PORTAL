<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use App\Models\PushSubscriptionModel;
use App\Services\AuthService;
use App\Services\PushNotificationService;

class PushNotificationController extends ResourceController
{
    protected $authService;
    protected $pushSubscriptionModel;
    protected $pushNotificationService;

    public function __construct()
    {
        $this->authService = new AuthService(service('request'));
        $this->pushSubscriptionModel = new PushSubscriptionModel();
        $this->pushNotificationService = new PushNotificationService();
    }

    /**
     * Get VAPID public key
     */
    public function getPublicKey()
    {
        // No auth required for public key (but we can still check if needed)
        $publicKey = $this->pushNotificationService->getPublicKey();
        
        if (empty($publicKey)) {
            return $this->respond([
                'status' => 'error',
                'message' => 'VAPID public key is not configured. Please contact administrator.'
            ], 500);
        }
        
        return $this->respond([
            'status' => 'success',
            'publicKey' => $publicKey
        ]);
    }

    /**
     * Subscribe to push notifications
     */
    public function subscribe()
    {
        log_message('info', 'Push subscription request received');
        
        $user = $this->authService->check();
        if (!$user) {
            log_message('error', 'Push subscription failed: Unauthorized');
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Allow admins, employees, HR, branch admins, and department managers to subscribe
        if (!in_array($user->role, ['admin', 'employee', 'hr', 'branch_admin', 'department_manager'])) {
            log_message('warning', 'Push subscription denied - User ID: ' . $user->sub . ', Role: ' . $user->role . ' (Unauthorized role for push notifications)');
            return $this->respond([
                'status' => 'error',
                'message' => 'Only admins, employees, branch admins, and HR can subscribe to push notifications'
            ], 403);
        }

        log_message('info', 'Push subscription - User ID: ' . $user->sub . ', Role: ' . $user->role);

        $json = $this->request->getJSON(true);
        
        if (!$json) {
            log_message('error', 'Push subscription failed: Invalid JSON');
            return $this->respond([
                'status' => 'error',
                'message' => 'Invalid JSON data'
            ], 400);
        }
        
        if (!isset($json['endpoint']) || !isset($json['keys'])) {
            log_message('error', 'Push subscription failed: Missing endpoint or keys. Data: ' . json_encode($json));
            return $this->respond([
                'status' => 'error',
                'message' => 'Endpoint and keys are required',
                'received' => array_keys($json ?? [])
            ], 400);
        }

        $endpoint = $json['endpoint'];
        $keys = json_encode($json['keys']);

        log_message('info', 'Push subscription - Endpoint: ' . substr($endpoint, 0, 50) . '...');

        // Check if subscription already exists
        $existing = $this->pushSubscriptionModel->where('endpoint', $endpoint)->first();
        
        if ($existing) {
            // Update existing subscription
            log_message('info', 'Updating existing subscription ID: ' . $existing['id']);
            $this->pushSubscriptionModel->update($existing['id'], [
                'user_id' => $user->sub,
                'keys' => $keys
            ]);
            
            log_message('info', 'Subscription updated successfully');
            return $this->respond([
                'status' => 'success',
                'message' => 'Subscription updated successfully'
            ]);
        }

        // Create new subscription
        $data = [
            'user_id' => $user->sub,
            'endpoint' => $endpoint,
            'keys' => $keys
        ];

        log_message('info', 'Creating new subscription for user: ' . $user->sub);
        
        try {
            $inserted = $this->pushSubscriptionModel->insert($data);
            
            if ($inserted) {
                $insertId = $this->pushSubscriptionModel->getInsertID();
                log_message('info', 'Subscription created successfully with ID: ' . $insertId);
                
                // Verify it was saved
                $saved = $this->pushSubscriptionModel->find($insertId);
                if ($saved) {
                    log_message('info', 'Subscription verified in database');
                } else {
                    log_message('error', 'Subscription was not found after insert!');
                }
                
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Subscribed to push notifications successfully',
                    'subscription_id' => $insertId
                ]);
            } else {
                $errors = $this->pushSubscriptionModel->errors();
                log_message('error', 'Subscription insert failed. Errors: ' . json_encode($errors));
                
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to subscribe',
                    'errors' => $errors
                ], 500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Subscription insert exception: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to subscribe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    public function unsubscribe()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $json = $this->request->getJSON(true);
        $endpoint = $json['endpoint'] ?? null;

        if (!$endpoint) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Endpoint is required'
            ], 400);
        }

        // Delete subscription
        $deleted = $this->pushSubscriptionModel
            ->where('user_id', $user->sub)
            ->where('endpoint', $endpoint)
            ->delete();

        if ($deleted) {
            return $this->respond([
                'status' => 'success',
                'message' => 'Unsubscribed successfully'
            ]);
        }

        return $this->respond([
            'status' => 'error',
            'message' => 'Subscription not found'
        ], 404);
    }

    /**
     * Test push notification (for debugging)
     */
    public function test()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Only admin can test
         if (!in_array($user->role, ['admin', 'hr'])) {
            return $this->respond(['status' => 'error', 'message' => 'Only admin can test notifications'], 403);
        }

        // Send test notification
        $result = $this->pushNotificationService->notifyAdmins(
            'Test Notification',
            'This is a test push notification from SanviHR',
            [
                'type' => 'test',
                'url' => base_url('/dashboard')
            ]
        );

        return $this->respond([
            'status' => 'success',
            'message' => 'Test notification sent',
            'result' => $result
        ]);
    }

    /**
     * Get subscription status for current user
     */
    public function getStatus()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $subscriptions = $this->pushSubscriptionModel->getUserSubscriptions($user->sub);

        return $this->respond([
            'status' => 'success',
            'subscriptions' => $subscriptions,
            'count' => count($subscriptions)
        ]);
    }
}

