<?php

namespace App\Models;

use CodeIgniter\Model;

class PushSubscriptionModel extends Model
{
    protected $table = 'push_subscriptions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'endpoint', 'keys', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';

    /**
     * Get all subscriptions for a user
     */
    public function getUserSubscriptions($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }

    /**
     * Get all admin subscriptions
     */
    public function getAdminSubscriptions()
    {
        $userModel = new UserModel();
        $admins = $userModel->whereIn('role', ['admin', 'hr', 'branch_admin'])->findAll();
        $adminIds = array_column($admins, 'id');
        
        if (empty($adminIds)) {
            return [];
        }
        
        return $this->whereIn('user_id', $adminIds)->findAll();
    }

    /**
     * Get all employee subscriptions (for checkout reminders)
     */
    public function getEmployeeSubscriptions()
    {
        $userModel = new UserModel();
        $employees = $userModel->whereIn('role', ['employee', 'hr', 'branch_admin', 'department_manager'])->findAll();
        $employeeIds = array_column($employees, 'id');
        
        if (empty($employeeIds)) {
            return [];
        }
        
        return $this->whereIn('user_id', $employeeIds)->findAll();
    }

    /**
     * Check if subscription exists
     */
    public function subscriptionExists($endpoint)
    {
        return $this->where('endpoint', $endpoint)->first() !== null;
    }
}

