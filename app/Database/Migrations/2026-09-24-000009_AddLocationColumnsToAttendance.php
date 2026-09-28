<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocationColumnsToAttendance extends Migration
{
    public function up() { if ($this->db->fieldExists('ip_address', 'attendance')) { return; } 
        $fields = [
            // - Check-In location fields -
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'check_in_time',
            ],
            'device_info' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'ip_address',
            ],
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
                'after'      => 'device_info',
            ],
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
                'after'      => 'latitude',
            ],
            'location_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => true,
                'after'      => 'longitude',
            ],

            // - Check-Out location fields -
            'checkout_ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'check_out_time',
            ],
            'checkout_device_info' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'checkout_ip_address',
            ],
            'checkout_latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
                'after'      => 'checkout_device_info',
            ],
            'checkout_longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
                'after'      => 'checkout_latitude',
            ],
            'checkout_location_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => true,
                'after'      => 'checkout_longitude',
            ],
        ];

        $this->forge->addColumn('attendance', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('attendance', [
            'ip_address', 'device_info', 'latitude', 'longitude', 'location_address',
            'checkout_ip_address', 'checkout_device_info', 'checkout_latitude', 'checkout_longitude', 'checkout_location_address',
        ]);
    }
}
