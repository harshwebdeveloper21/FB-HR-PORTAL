<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCandidateExperiencesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'candidate_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'company' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'role' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'total_experience' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'last_salary' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'notice_period' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'reason_for_leaving' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('candidate_id');
        $this->forge->createTable('candidate_experiences');
    }

    public function down()
    {
        $this->forge->dropTable('candidate_experiences');
    }
}
