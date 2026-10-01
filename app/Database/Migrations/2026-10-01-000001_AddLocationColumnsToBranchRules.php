<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocationColumnsToBranchRules extends Migration
{
    public function up()
    {
        $fields = [
            'office_latitude' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'default'    => null,
                'after'      => 'enable_geofencing',
            ],
            'office_longitude' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'default'    => null,
                'after'      => 'office_latitude',
            ],
            'office_radius' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => 100,
                'after'      => 'office_longitude',
            ],
        ];

        if ($this->db->tableExists('branch_rules')) {
            $existingCols = $this->db->getFieldNames('branch_rules');
            $colsToAdd = [];
            foreach ($fields as $colName => $def) {
                if (!in_array($colName, $existingCols)) {
                    $colsToAdd[$colName] = $def;
                }
            }
            if (!empty($colsToAdd)) {
                $this->forge->addColumn('branch_rules', $colsToAdd);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('branch_rules')) {
            $existingCols = $this->db->getFieldNames('branch_rules');
            $colsToDrop = array_intersect(['office_latitude', 'office_longitude', 'office_radius'], $existingCols);
            if (!empty($colsToDrop)) {
                $this->forge->dropColumn('branch_rules', $colsToDrop);
            }
        }
    }
}
