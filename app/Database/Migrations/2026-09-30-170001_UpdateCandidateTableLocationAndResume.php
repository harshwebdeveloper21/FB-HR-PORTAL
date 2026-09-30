<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateCandidateTableLocationAndResume extends Migration
{
    public function up()
    {
        $fields = [
            'country_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
                'after'      => 'city',
            ],
            'state_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
                'after'      => 'country_id',
            ],
            'city_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
                'after'      => 'state_id',
            ],
        ];

        // Add columns if they do not exist
        $existingFields = $this->db->getFieldNames('candidate');
        $toAdd = [];
        foreach ($fields as $colName => $colDef) {
            if (!in_array($colName, $existingFields, true)) {
                $toAdd[$colName] = $colDef;
            }
        }
        if (!empty($toAdd)) {
            $this->forge->addColumn('candidate', $toAdd);
        }

        // Make resume column nullable to prevent fatal 500 DB crashes if null is passed
        $modifyFields = [
            'resume' => [
                'name'       => 'resume',
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
        ];
        $this->forge->modifyColumn('candidate', $modifyFields);
    }

    public function down()
    {
        $cols = ['country_id', 'state_id', 'city_id'];
        $existingFields = $this->db->getFieldNames('candidate');
        $toDrop = array_intersect($cols, $existingFields);
        if (!empty($toDrop)) {
            $this->forge->dropColumn('candidate', $toDrop);
        }
    }
}
