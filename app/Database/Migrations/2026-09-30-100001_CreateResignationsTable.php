<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateResignationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'employee_id'           => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'resignation_date'      => ['type'=>'DATE'],
            'reason'                => ['type'=>'TEXT','null'=>true],
            'requested_lwd'         => ['type'=>'DATE','null'=>true],
            'final_lwd'             => ['type'=>'DATE','null'=>true],
            'notice_days'           => ['type'=>'INT','constraint'=>4,'default'=>30],
            'notice_shortfall_days' => ['type'=>'INT','constraint'=>4,'default'=>0],
            'notice_waived'         => ['type'=>'TINYINT','constraint'=>1,'default'=>0],
            'status'                => ['type'=>'ENUM','constraint'=>['submitted','manager_approved','manager_rejected','hr_approved','hr_rejected','withdrawn','notice_period','handover','clearance','fnf','relieved'],'default'=>'submitted'],
            'manager_id'            => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true],
            'manager_remarks'       => ['type'=>'TEXT','null'=>true],
            'manager_action_at'     => ['type'=>'DATETIME','null'=>true],
            'hr_id'                 => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true],
            'hr_remarks'            => ['type'=>'TEXT','null'=>true],
            'hr_action_at'          => ['type'=>'DATETIME','null'=>true],
            'created_at'            => ['type'=>'DATETIME','null'=>true],
            'updated_at'            => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('employee_id');
        $this->forge->addKey('status');
        $this->forge->createTable('resignations', true);
    }
    public function down() { $this->forge->dropTable('resignations', true); }
}
