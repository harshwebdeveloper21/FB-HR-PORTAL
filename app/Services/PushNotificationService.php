<?php

namespace App\Services;

/**
 * Push Notification Service
 * 
 * This service uses VAPID (Voluntary Application Server Identification) 
 * which is a FREE, open standard for web push notifications.
 * 
 * NO PAID SERVICES REQUIRED:
 * - Uses browser's native Push API (free)
 * - Uses VAPID standard (free, open source)
 * - No Firebase, OneSignal, or other paid services needed
 * - No subscriptions or monthly fees
 * - Everything runs on your own server
 */
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use App\Models\PushSubscriptionModel;
use App\Models\UserModel;

class PushNotificationService
{
    private $pushSubscriptionModel;
    private $vapidPublicKey;
    private $vapidPrivateKey;
    private $vapidSubject;

    public function __construct()
    {
        $this->pushSubscriptionModel = new PushSubscriptionModel();
        
        // VAPID keys - Generate using: php generate-vapid-keys.php
        // Then add to .env file or set as environment variables
        $this->vapidPublicKey = getenv('VAPID_PUBLIC_KEY') ?: env('VAPID_PUBLIC_KEY', 'BIWhrLgu53FPxc2pTeSyMQPanBC6_1CV48mhtfNxB0zTuKz3NwuoCzicYU7UCF9__mLKYdS8wToIPWBU3A_g23E');
        $this->vapidPrivateKey = getenv('VAPID_PRIVATE_KEY') ?: env('VAPID_PRIVATE_KEY', 'VIkw1X1aRcE9XKDJDz3ZL0zMZ5m4ASpZ7SCsto8basg');
        $this->vapidSubject = getenv('VAPID_SUBJECT') ?: env('VAPID_SUBJECT', 'mailto:admin@sanvihr.fableadtech.com');
        
        // Validate keys
        if (empty($this->vapidPublicKey) || empty($this->vapidPrivateKey)) {
            log_message('error', 'VAPID keys are not configured. Please run: php generate-vapid-keys.php and add keys to .env file');
        } else {
            log_message('info', 'VAPID keys loaded successfully');
        }
    }

    /**
     * Build a WebPush client and fail with an actionable error if dependency loading is broken.
     */
    private function createWebPushClient(): WebPush
    {
        $this->ensureWebPushDependencyLoaded();

        return new WebPush([
            'VAPID' => [
                'subject' => $this->vapidSubject,
                'publicKey' => $this->vapidPublicKey,
                'privateKey' => $this->vapidPrivateKey,
            ],
        ]);
    }

    private function ensureWebPushDependencyLoaded(): void
    {
        if (class_exists(WebPush::class)) {
            return;
        }

        $autoloadPaths = [];

        if (defined('ROOTPATH')) {
            $autoloadPaths[] = ROOTPATH . 'vendor/autoload.php';
        }

        // Fallback for non-standard runtime entry points.
        $autoloadPaths[] = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

        foreach (array_unique($autoloadPaths) as $autoloadPath) {
            if (is_file($autoloadPath)) {
                require_once $autoloadPath;
            }
        }

        if (! class_exists(WebPush::class)) {
            throw new \RuntimeException(
                'Web push dependency is missing. Run "composer install" in project root and restart PHP/Apache.',
            );
        }
    }

    /**
     * Send push notification to all admins
     */
    public function notifyAdmins($title, $message, $data = [])
    {
        log_message('info', '🔔 ===== PUSH NOTIFICATION REQUEST =====');
        log_message('info', '🔔 Title: ' . $title);
        log_message('info', '🔔 Message: ' . $message);
        
        $subscriptions = $this->pushSubscriptionModel->getAdminSubscriptions();
        
        log_message('info', '🔔 Found ' . count($subscriptions) . ' Admin/HR subscription(s)');
        
        if (empty($subscriptions)) {
            log_message('warning', '⚠️⚠️⚠️ NO ADMIN/HR SUBSCRIPTIONS FOUND!');
            log_message('warning', '⚠️ Admins/HR need to login on mobile and enable push notifications');
            log_message('warning', '⚠️ Database table push_subscriptions is empty!');
            return ['success' => 0, 'failed' => 0, 'errors' => ['No subscriptions found - database is empty']];
        }
        
        // Double-check: Verify all subscriptions belong to admin users
        $userModel = new UserModel();
        $validSubscriptions = [];
        $removedCount = 0;
        
        foreach ($subscriptions as $subscription) {
            $user = $userModel->find($subscription['user_id']);
            
            if ($user && in_array($user['role'], ['admin', 'hr', 'branch_admin'])) {
                $validSubscriptions[] = $subscription;
            } else {
                // Remove subscription for non-admin or deleted user
                log_message('warning', '⚠️ Removing invalid subscription ID: ' . $subscription['id'] . ' (User ID: ' . $subscription['user_id'] . ' is not admin)');
                $this->pushSubscriptionModel->delete($subscription['id']);
                $removedCount++;
            }
        }
        
        if ($removedCount > 0) {
            log_message('info', '🧹 Removed ' . $removedCount . ' invalid subscription(s)');
        }
        
        if (empty($validSubscriptions)) {
            log_message('warning', '⚠️⚠️⚠️ NO VALID ADMIN/HR SUBSCRIPTIONS FOUND AFTER VERIFICATION!');
            return ['success' => 0, 'failed' => 0, 'errors' => ['No valid Admin/HR subscriptions found']];
        }
        
        log_message('info', '✅ Proceeding to send notifications to ' . count($validSubscriptions) . ' Admin/HR users');
        
        // Use only valid subscriptions
        $subscriptions = $validSubscriptions;

        $webPush = $this->createWebPushClient();
        
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($subscriptions as $subscription) {
            try {
                $keys = json_decode($subscription['keys'], true);
                
                $pushSubscription = Subscription::create([
                    'endpoint' => $subscription['endpoint'],
                    'keys' => [
                        'p256dh' => $keys['p256dh'] ?? '',
                        'auth' => $keys['auth'] ?? ''
                    ]
                ]);

                $payload = json_encode([
                    'title' => $title,
                    'body' => $message,
                    'icon' => base_url(env('ImagePath') . 'assets/images/fab_logo.jpg'),
                    'badge' => base_url(env('ImagePath') . 'assets/images/fab_logo.jpg'),
                    'data' => $data,
                    'requireInteraction' => false,
                    'vibrate' => [200, 100, 200]
                ]);

                $webPush->queueNotification($pushSubscription, $payload);
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = [
                    'subscription_id' => $subscription['id'],
                    'error' => $e->getMessage()
                ];
                
                // If subscription is invalid, remove it
                if (strpos($e->getMessage(), '410') !== false || strpos($e->getMessage(), 'expired') !== false) {
                    $this->pushSubscriptionModel->delete($subscription['id']);
                }
            }
        }

        // Send all queued notifications
        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $successCount++;
                log_message('info', 'Push notification sent successfully to: ' . substr($report->getEndpoint(), 0, 50) . '...');
            } else {
                $failedCount++;
                $errorReason = $report->getReason();
                $errors[] = [
                    'endpoint' => $report->getEndpoint(),
                    'error' => $errorReason
                ];
                
                log_message('error', 'Push notification failed: ' . $errorReason . ' | Endpoint: ' . substr($report->getEndpoint(), 0, 50) . '...');
                
                // Remove invalid subscriptions
                if ($report->isSubscriptionExpired()) {
                    log_message('warning', 'Removing expired subscription: ' . substr($report->getEndpoint(), 0, 50) . '...');
                    $this->pushSubscriptionModel->where('endpoint', $report->getEndpoint())->delete();
                }
            }
        }

        log_message('info', 'Push notification summary - Success: ' . $successCount . ', Failed: ' . $failedCount);

        return [
            'success' => $successCount,
            'failed' => $failedCount,
            'errors' => $errors
        ];
    }

    /**
     * Send push notification to specific user
     */
    public function notifyUser($userId, $title, $message, $data = [])
    {
        $subscriptions = $this->pushSubscriptionModel->getUserSubscriptions($userId);
        
        if (empty($subscriptions)) {
            return ['success' => 0, 'failed' => 0, 'errors' => []];
        }

        $webPush = $this->createWebPushClient();
        
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($subscriptions as $subscription) {
            try {
                $keys = json_decode($subscription['keys'], true);
                
                $pushSubscription = Subscription::create([
                    'endpoint' => $subscription['endpoint'],
                    'keys' => [
                        'p256dh' => $keys['p256dh'] ?? '',
                        'auth' => $keys['auth'] ?? ''
                    ]
                ]);

                $payload = json_encode([
                    'title' => $title,
                    'body' => $message,
                    'icon' => base_url(env('ImagePath') . 'assets/images/fab_logo.jpg'),
                    'badge' => base_url(env('ImagePath') . 'assets/images/fab_logo.jpg'),
                    'data' => $data,
                    'requireInteraction' => false,
                    'vibrate' => [200, 100, 200]
                ]);

                $webPush->queueNotification($pushSubscription, $payload);
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = [
                    'subscription_id' => $subscription['id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        // Send all queued notifications
        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $successCount++;
            } else {
                $failedCount++;
                $errors[] = [
                    'endpoint' => $report->getEndpoint(),
                    'error' => $report->getReason()
                ];
                
                if ($report->isSubscriptionExpired()) {
                    $this->pushSubscriptionModel->where('endpoint', $report->getEndpoint())->delete();
                }
            }
        }

        return [
            'success' => $successCount,
            'failed' => $failedCount,
            'errors' => $errors
        ];
    }

    /**
     * Send push notification to all users (admin, hr, employee)
     */
    public function notifyAllUsers($title, $message, $data = [])
    {
        log_message('info', '🔔 ===== PUSH NOTIFICATION REQUEST (ALL USERS) =====');
        log_message('info', '🔔 Title: ' . $title);
        log_message('info', '🔔 Message: ' . $message);
        
        // Get all subscriptions (all users)
        $subscriptions = $this->pushSubscriptionModel->findAll();
        
        log_message('info', '🔔 Found ' . count($subscriptions) . ' subscription(s)');
        
        if (empty($subscriptions)) {
            log_message('warning', '⚠️⚠️⚠️ NO SUBSCRIPTIONS FOUND!');
            log_message('warning', '⚠️ Users need to login and enable push notifications');
            return ['success' => 0, 'failed' => 0, 'errors' => ['No subscriptions found']];
        }
        
        // Verify subscriptions belong to valid users
        $userModel = new UserModel();
        $validSubscriptions = [];
        $removedCount = 0;
        
        foreach ($subscriptions as $subscription) {
            $user = $userModel->find($subscription['user_id']);
            
            if ($user) {
                $validSubscriptions[] = $subscription;
            } else {
                // Remove subscription for deleted user
                log_message('warning', '⚠️ Removing invalid subscription ID: ' . $subscription['id'] . ' (User ID: ' . $subscription['user_id'] . ' not found)');
                $this->pushSubscriptionModel->delete($subscription['id']);
                $removedCount++;
            }
        }
        
        if ($removedCount > 0) {
            log_message('info', '🧹 Removed ' . $removedCount . ' invalid subscription(s)');
        }
        
        if (empty($validSubscriptions)) {
            log_message('warning', '⚠️⚠️⚠️ NO VALID SUBSCRIPTIONS FOUND AFTER VERIFICATION!');
            return ['success' => 0, 'failed' => 0, 'errors' => ['No valid subscriptions found']];
        }
        
        log_message('info', '✅ Proceeding to send notifications to ' . count($validSubscriptions) . ' user(s)');
        
        // Use only valid subscriptions
        $subscriptions = $validSubscriptions;

        $webPush = $this->createWebPushClient();
        
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($subscriptions as $subscription) {
            try {
                $keys = json_decode($subscription['keys'], true);
                
                $pushSubscription = Subscription::create([
                    'endpoint' => $subscription['endpoint'],
                    'keys' => [
                        'p256dh' => $keys['p256dh'] ?? '',
                        'auth' => $keys['auth'] ?? ''
                    ]
                ]);

                $payload = json_encode([
                    'title' => $title,
                    'body' => $message,
                    'icon' => base_url(env('ImagePath') . 'assets/images/fab_logo.jpg'),
                    'badge' => base_url(env('ImagePath') . 'assets/images/fab_logo.jpg'),
                    'data' => $data,
                    'requireInteraction' => false,
                    'vibrate' => [200, 100, 200]
                ]);

                $webPush->queueNotification($pushSubscription, $payload);
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = [
                    'subscription_id' => $subscription['id'],
                    'error' => $e->getMessage()
                ];
                
                // If subscription is invalid, remove it
                if (strpos($e->getMessage(), '410') !== false || strpos($e->getMessage(), 'expired') !== false) {
                    $this->pushSubscriptionModel->delete($subscription['id']);
                }
            }
        }

        // Send all queued notifications
        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $successCount++;
                log_message('info', 'Push notification sent successfully to: ' . substr($report->getEndpoint(), 0, 50) . '...');
            } else {
                $failedCount++;
                $errorReason = $report->getReason();
                $errors[] = [
                    'endpoint' => $report->getEndpoint(),
                    'error' => $errorReason
                ];
                
                log_message('error', 'Push notification failed: ' . $errorReason . ' | Endpoint: ' . substr($report->getEndpoint(), 0, 50) . '...');
                
                // Remove invalid subscriptions
                if ($report->isSubscriptionExpired()) {
                    log_message('warning', 'Removing expired subscription: ' . substr($report->getEndpoint(), 0, 50) . '...');
                    $this->pushSubscriptionModel->where('endpoint', $report->getEndpoint())->delete();
                }
            }
        }

        log_message('info', 'Push notification summary - Success: ' . $successCount . ', Failed: ' . $failedCount);

        return [
            'success' => $successCount,
            'failed' => $failedCount,
            'errors' => $errors
        ];
    }

    /**
     * Get VAPID public key for client
     */
    public function getPublicKey()
    {
        return $this->vapidPublicKey;
    }
}
