<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table = 'department';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'branch_id', 'department_name', 'manager_id', 'created_at',
    ];
    protected $useTimestamps = true;
}
