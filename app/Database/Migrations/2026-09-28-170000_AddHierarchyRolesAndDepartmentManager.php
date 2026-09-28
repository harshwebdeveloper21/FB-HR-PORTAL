<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHierarchyRolesAndDepartmentManager extends Migration
{
    public function up()
    {
        // 1. Update users.role ENUM to include 'branch_admin' and 'department_manager'
        // 'admin' represents Super Admin, 'hr' is the Global HR (1 for all branches)
        $this->db->query("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'hr', 'branch_admin', 'department_manager', 'employee', 'candidate') NOT NULL DEFAULT 'employee'");

        // 2. Add department_id to users if it doesn't already exist
        $fields = $this->db->getFieldNames('users');
        if (!in_array('department_id', $fields)) {
            $this->forge->addColumn('users', [
                'department_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'branch_id',
                ],
            ]);
            $this->db->query('ALTER TABLE `users` ADD INDEX `idx_user_branch_dept` (`branch_id`, `department_id`)');
        }

        // 3. Add branch_id and manager_id to department table if not present
        $deptFields = $this->db->getFieldNames('department');
        $deptColumnsToAdd = [];
        if (!in_array('branch_id', $deptFields)) {
            $deptColumnsToAdd['branch_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'id',
            ];
        }
        if (!in_array('manager_id', $deptFields)) {
            $deptColumnsToAdd['manager_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'department_name',
            ];
        }
        if (!empty($deptColumnsToAdd)) {
            $this->forge->addColumn('department', $deptColumnsToAdd);
            if (isset($deptColumnsToAdd['branch_id'])) {
                $this->db->query('ALTER TABLE `department` ADD INDEX `idx_dept_branch` (`branch_id`)');
            }
            if (isset($deptColumnsToAdd['manager_id'])) {
                $this->db->query('ALTER TABLE `department` ADD INDEX `idx_dept_manager` (`manager_id`)');
            }
        }

        // 4. Add approval hierarchy columns to leaves table
        $leaveFields = $this->db->getFieldNames('leaves');
        $leaveColumnsToAdd = [];
        if (!in_array('manager_approval_status', $leaveFields)) {
            $leaveColumnsToAdd['manager_approval_status'] = [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default'    => 'pending',
                'after'      => 'status',
            ];
        }
        if (!in_array('manager_id', $leaveFields)) {
            $leaveColumnsToAdd['manager_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'manager_approval_status',
            ];
        }
        if (!in_array('manager_remarks', $leaveFields)) {
            $leaveColumnsToAdd['manager_remarks'] = [
                'type'       => 'TEXT',
                'null'       => true,
                'default'    => null,
                'after'      => 'manager_id',
            ];
        }
        if (!in_array('approved_by', $leaveFields)) {
            $leaveColumnsToAdd['approved_by'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'manager_remarks',
            ];
        }
        if (!empty($leaveColumnsToAdd)) {
            $this->forge->addColumn('leaves', $leaveColumnsToAdd);
        }
    }

    public function down()
    {
        // Revert columns if needed
        $fields = $this->db->getFieldNames('users');
        if (in_array('department_id', $fields)) {
            $this->forge->dropColumn('users', 'department_id');
        }
        $deptFields = $this->db->getFieldNames('department');
        if (in_array('branch_id', $deptFields)) {
            $this->forge->dropColumn('department', 'branch_id');
        }
        if (in_array('manager_id', $deptFields)) {
            $this->forge->dropColumn('department', 'manager_id');
        }
        $leaveFields = $this->db->getFieldNames('leaves');
        if (in_array('manager_approval_status', $leaveFields)) {
            $this->forge->dropColumn('leaves', ['manager_approval_status', 'manager_id', 'manager_remarks', 'approved_by']);
        }
        $this->db->query("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'hr', 'employee', 'candidate') NOT NULL DEFAULT 'employee'");
    }
}
