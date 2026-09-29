<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToCandidateTable extends Migration
{
    public function up()
    {
        $fields = [
            'date_of_birth' => ['type' => 'DATE', 'null' => true],
            'gender' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'current_address' => ['type' => 'TEXT', 'null' => true],
            'city' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        ];
        $this->forge->addColumn('candidate', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('candidate', ['date_of_birth', 'gender', 'current_address', 'city']);
    }
}
