<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInterviewAssessmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'interview_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'job_title' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'department' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'interview_round' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'interviewer_name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'interview_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'interview_mode' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'ratings_data' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'overall_score' => [
                'type' => 'DECIMAL',
                'constraint' => '4,2',
                'null' => true,
            ],
            'feedback' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'expected_salary' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'notice_period' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'recommendation' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'next_step' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'final_remarks' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->createTable('interview_assessments');
    }

    public function down()
    {
        $this->forge->dropTable('interview_assessments');
    }
}
