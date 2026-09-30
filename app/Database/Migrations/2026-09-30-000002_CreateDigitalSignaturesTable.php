<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDigitalSignaturesTable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('digital_signatures')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'signee_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                ],
                'designation' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'null'       => true,
                ],
                'company_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'null'       => true,
                ],
                'signature_path' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'null'       => true,
                ],
                'stamp_path' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'null'       => true,
                ],
                'is_default' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->createTable('digital_signatures', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('digital_signatures', true);
    }
}
