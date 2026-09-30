<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateFnfItemsTable extends Migration {
    public function up() {
        $this->forge->addField([
            'id'         => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'fnf_id'     => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'type'       => ['type'=>'ENUM','constraint'=>['earning','deduction'],'default'=>'earning'],
            'title'      => ['type'=>'VARCHAR','constraint'=>255],
            'amount'     => ['type'=>'DECIMAL','constraint'=>'12,2','default'=>'0.00'],
            'created_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('fnf_id');
        $this->forge->createTable('fnf_items', true);
    }
    public function down() { $this->forge->dropTable('fnf_items', true); }
}
