<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateFnfSettlementsTable extends Migration {
    public function up() {
        $this->forge->addField([
            'id'                  => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'resignation_id'      => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'total_earnings'      => ['type'=>'DECIMAL','constraint'=>'12,2','default'=>'0.00'],
            'total_deductions'    => ['type'=>'DECIMAL','constraint'=>'12,2','default'=>'0.00'],
            'net_payable'         => ['type'=>'DECIMAL','constraint'=>'12,2','default'=>'0.00'],
            'status'              => ['type'=>'ENUM','constraint'=>['draft','hr_prepared','finance_approved','paid'],'default'=>'draft'],
            'finance_approver_id' => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true],
            'finance_remarks'     => ['type'=>'TEXT','null'=>true],
            'paid_date'           => ['type'=>'DATE','null'=>true],
            'payment_ref'         => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'hr_prepared_by'      => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true],
            'created_at'          => ['type'=>'DATETIME','null'=>true],
            'updated_at'          => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('resignation_id');
        $this->forge->createTable('fnf_settlements', true);
    }
    public function down() { $this->forge->dropTable('fnf_settlements', true); }
}
