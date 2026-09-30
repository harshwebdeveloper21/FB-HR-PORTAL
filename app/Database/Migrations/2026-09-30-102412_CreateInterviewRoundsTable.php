<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInterviewRoundsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'interview_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'interviewer_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'interview_round' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'interview_score' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'interview_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
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
        // We do not strictly enforce foreign key to avoid issues, but we index it
        $this->forge->addKey('interview_id');
        $this->forge->createTable('interview_rounds');
    }

    public function down()
    {
        $this->forge->dropTable('interview_rounds');
    }
}
