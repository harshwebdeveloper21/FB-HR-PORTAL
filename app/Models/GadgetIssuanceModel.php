<?php

namespace App\Models;

use CodeIgniter\Model;

class GadgetIssuanceModel extends Model
{
    protected $table            = 'gadget_issuances';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'gadget_name',
        'gadget_type',
        'serial_number',
        'model_number',
        'issuance_date',
        'return_date',
        'status',
        'gadget_condition',
        'damage_details',
        'damage_cost',
        'remarks'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
