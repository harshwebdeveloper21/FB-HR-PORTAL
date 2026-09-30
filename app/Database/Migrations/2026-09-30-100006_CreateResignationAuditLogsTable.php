<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateResignationAuditLogsTable extends Migration {
    public function up() {
        $this->forge->addField([
            'id'             => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'resignation_id' => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'actor_id'       => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'action'         => ['type'=>'VARCHAR','constraint'=>100],
            'from_status'    => ['type'=>'VARCHAR','constraint'=>50,'null'=>true],
            'to_status'      => ['type'=>'VARCHAR','constraint'=>50,'null'=>true],
            'remarks'        => ['type'=>'TEXT','null'=>true],
            'created_at'     => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('resignation_id');
        $this->forge->createTable('resignation_audit_logs', true);
    }
    public function down() { $this->forge->dropTable('resignation_audit_logs', true); }
}
