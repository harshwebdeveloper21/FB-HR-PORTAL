<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateClearanceItemsTable extends Migration {
    public function up() {
        $this->forge->addField([
            'id'             => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'resignation_id' => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'department'     => ['type'=>'VARCHAR','constraint'=>100],
            'approver_id'    => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true],
            'status'         => ['type'=>'ENUM','constraint'=>['pending','approved','rejected'],'default'=>'pending'],
            'remarks'        => ['type'=>'TEXT','null'=>true],
            'approved_at'    => ['type'=>'DATETIME','null'=>true],
            'created_at'     => ['type'=>'DATETIME','null'=>true],
            'updated_at'     => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('resignation_id');
        $this->forge->createTable('clearance_items', true);
    }
    public function down() { $this->forge->dropTable('clearance_items', true); }
}
