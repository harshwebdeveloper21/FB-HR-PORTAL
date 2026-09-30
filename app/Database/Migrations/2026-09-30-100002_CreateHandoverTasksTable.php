<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateHandoverTasksTable extends Migration {
    public function up() {
        $this->forge->addField([
            'id'              => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'resignation_id'  => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'task'            => ['type'=>'VARCHAR','constraint'=>255],
            'description'     => ['type'=>'TEXT','null'=>true],
            'handover_to'     => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true],
            'due_date'        => ['type'=>'DATE','null'=>true],
            'status'          => ['type'=>'ENUM','constraint'=>['pending','accepted','completed'],'default'=>'pending'],
            'acceptor_remarks'=> ['type'=>'TEXT','null'=>true],
            'accepted_at'     => ['type'=>'DATETIME','null'=>true],
            'completed_at'    => ['type'=>'DATETIME','null'=>true],
            'created_at'      => ['type'=>'DATETIME','null'=>true],
            'updated_at'      => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('resignation_id');
        $this->forge->createTable('handover_tasks', true);
    }
    public function down() { $this->forge->dropTable('handover_tasks', true); }
}
