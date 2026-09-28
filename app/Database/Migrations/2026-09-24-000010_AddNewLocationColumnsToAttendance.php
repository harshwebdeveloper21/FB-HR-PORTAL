<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNewLocationColumnsToAttendance extends Migration
{
    public function up() { if ($this->db->fieldExists('check_in_ip_address', 'attendance')) { return; } 
        $fields = [
            // - Check-In location fields -
            'check_in_ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
                'after'      => 'check_in_time',
            ],
            'check_in_latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
                'after'      => 'check_in_ip_address',
            ],
            'check_in_longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
                'after'      => 'check_in_latitude',
            ],
            'check_in_location_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'check_in_longitude',
            ],

            // - Check-Out location fields -
            'check_out_ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
                'after'      => 'check_out_time',
            ],
            'check_out_latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
                'after'      => 'check_out_ip_address',
            ],
            'check_out_longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
                'after'      => 'check_out_latitude',
            ],
            'check_out_location_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'check_out_longitude',
            ],
        ];

        $this->forge->addColumn('attendance', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('attendance', [
            'check_in_ip_address', 'check_in_latitude', 'check_in_longitude', 'check_in_location_name',
            'check_out_ip_address', 'check_out_latitude', 'check_out_longitude', 'check_out_location_name',
        ]);
    }
}
