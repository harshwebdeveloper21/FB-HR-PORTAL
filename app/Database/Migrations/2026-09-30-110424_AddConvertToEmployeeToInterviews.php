<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConvertToEmployeeToInterviews extends Migration
{
    public function up()
    {
        $this->forge->addColumn('interviews', [
            'convert_to_employee' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('interviews', 'convert_to_employee');
    }
}
