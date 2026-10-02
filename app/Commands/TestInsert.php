<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\StaffTransferModel;

class TestInsert extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:insert';
    protected $description = 'Tests insertion into staff_transfers table';

    public function run(array $params)
    {
        $model = new StaffTransferModel();
        $userModel = new \App\Models\UserModel();
        $db = \Config\Database::connect();
        
        $db->transStart();
        
        $updateSuccess = $userModel->update(1, ['branch_id' => 2]);
        if ($updateSuccess === false) {
            CLI::write("User Update Failed!", 'red');
            CLI::write("Validation Errors: " . json_encode($userModel->errors()), 'red');
            CLI::write("DB Error: " . json_encode($db->error()), 'red');
        } else {
            CLI::write("User Update Success!", 'green');
        }
        
        $insertSuccess = $model->insert([
            'user_id'        => 1,
            'from_branch_id' => 1,
            'to_branch_id'   => 2,
            'transferred_by' => 1,
            'reason'         => 'Test insert via model',
            'effective_date' => '2026-10-02',
            'status'         => 'completed',
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        if ($insertSuccess === false) {
            CLI::write("Insert Failed!", 'red');
            CLI::write("Validation Errors: " . json_encode($model->errors()), 'red');
            CLI::write("DB Error: " . json_encode($db->error()), 'red');
        } else {
            CLI::write("Insert Success! ID: " . $insertSuccess, 'green');
        }
        
        $db->transRollback();
    }
}
