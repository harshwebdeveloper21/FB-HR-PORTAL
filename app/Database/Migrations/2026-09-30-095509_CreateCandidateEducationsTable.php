<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCandidateEducationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'candidate_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'degree' => ['type' => 'VARCHAR', 'constraint' => 255],
            'course' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'university' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'passing_year' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'percentage' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('candidate_id');
        $this->forge->createTable('candidate_educations');
    }

    public function down()
    {
        $this->forge->dropTable('candidate_educations');
    }
}
