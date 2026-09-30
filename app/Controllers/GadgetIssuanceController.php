<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GadgetIssuanceModel;

class GadgetIssuanceController extends BaseController
{
    private function ensureTableExists()
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        if (!$db->tableExists('gadget_issuances')) {
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'gadget_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'gadget_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'serial_number' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'model_number' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'issuance_date' => [
                    'type' => 'DATE',
                ],
                'return_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'Issued',
                ],
                'gadget_condition' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'default'    => 'Good / Normal',
                ],
                'damage_details' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'damage_cost' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'null'       => true,
                    'default'    => '0.00',
                ],
                'remarks' => [
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
            $forge->addKey('id', true);
            $forge->createTable('gadget_issuances', true);
        } else {
            if (!$db->fieldExists('gadget_condition', 'gadget_issuances')) {
                $forge->addColumn('gadget_issuances', [
                    'gadget_condition' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 100,
                        'null'       => true,
                        'default'    => 'Good / Normal',
                    ],
                    'damage_details' => [
                        'type' => 'TEXT',
                        'null' => true,
                    ],
                    'damage_cost' => [
                        'type'       => 'DECIMAL',
                        'constraint' => '10,2',
                        'null'       => true,
                        'default'    => '0.00',
                    ],
                ]);
            }
        }

        if (!$db->tableExists('gadget_types')) {
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $forge->addKey('id', true);
            $forge->addUniqueKey('name');
            $forge->createTable('gadget_types', true);

            $defaultTypes = [
                'Laptop',
                'Mobile / Smartphone',
                'Monitor / Display',
                'Tablet / iPad',
                'Accessory (Headset, Mouse, etc.)',
                'Peripheral / Hardware',
                'Other Asset'
            ];

            foreach ($defaultTypes as $dt) {
                $db->table('gadget_types')->ignore(true)->insert([
                    'name'       => $dt,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

   public function index()
    {
        $this->ensureTableExists();
        $db = \Config\Database::connect();

        // Get employees list for dropdown
        $employees = $db->table('users u')
            ->select('u.id, u.username, ui.firstname, ui.lastname, ui.employee_id')
            ->join('user_info ui', 'ui.user_id = u.id', 'left')
            ->where('u.is_deleted', 0)
            ->orderBy('ui.firstname', 'ASC')
            ->get()->getResultArray();

        return view('gadget_issuance/index', [
            'employees' => $employees
        ]);
    }

    public function getAll()
    {
        $this->ensureTableExists();
        $db = \Config\Database::connect();

        $builder = $db->table('gadget_issuances gi')
            ->select('gi.*, u.username, ui.firstname, ui.lastname, ui.employee_id as emp_code')
            ->join('users u', 'u.id = gi.user_id', 'left')
            ->join('user_info ui', 'ui.user_id = gi.user_id', 'left')
            ->orderBy('gi.id', 'DESC');

        $data = $builder->get()->getResultArray();

        foreach ($data as &$row) {
            $fullName = trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''));
            if (empty($fullName)) {
                $fullName = $row['username'] ?? ('User #' . $row['user_id']);
            }
            $row['employee_name'] = $fullName;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    public function getOne($id)
    {
        $this->ensureTableExists();
        $db = \Config\Database::connect();

        $row = $db->table('gadget_issuances gi')
            ->select('gi.*, u.username, ui.firstname, ui.lastname, ui.employee_id as emp_code')
            ->join('users u', 'u.id = gi.user_id', 'left')
            ->join('user_info ui', 'ui.user_id = gi.user_id', 'left')
            ->where('gi.id', $id)
            ->get()->getRowArray();

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Record not found']);
        }

        $fullName = trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''));
        if (empty($fullName)) {
            $fullName = $row['username'] ?? ('User #' . $row['user_id']);
        }
        $row['employee_name'] = $fullName;

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $row
        ]);
    }

    public function save()
    {
        $this->ensureTableExists();
        $model = new GadgetIssuanceModel();

        $id              = $this->request->getPost('id');
        $userId          = $this->request->getPost('user_id');
        $gadgetName      = $this->request->getPost('gadget_name');
        $gadgetType      = $this->request->getPost('gadget_type');
        $serialNumber    = $this->request->getPost('serial_number');
        $modelNumber     = $this->request->getPost('model_number');
        $issuanceDate    = $this->request->getPost('issuance_date');
        $returnDate      = $this->request->getPost('return_date') ?: null;
        $status          = $this->request->getPost('status') ?: 'Issued';
        $gadgetCondition = $this->request->getPost('gadget_condition') ?: 'Good / Normal';
        $damageDetails   = $this->request->getPost('damage_details');
        $damageCost      = $this->request->getPost('damage_cost') ?: 0.00;
        $remarks         = $this->request->getPost('remarks');

        if (empty($userId) || empty($gadgetName) || empty($issuanceDate)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Employee, Gadget Name, and Issuance Date are required.'
            ]);
        }

        if ($status === 'Returned' && empty($returnDate)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Return Date is required when status is set to Returned.'
            ]);
        }

        $data = [
            'user_id'          => $userId,
            'gadget_name'      => $gadgetName,
            'gadget_type'      => $gadgetType,
            'serial_number'    => $serialNumber,
            'model_number'     => $modelNumber,
            'issuance_date'    => $issuanceDate,
            'return_date'      => $returnDate,
            'status'           => $status,
            'gadget_condition' => $gadgetCondition,
            'damage_details'   => $damageDetails,
            'damage_cost'      => $damageCost,
            'remarks'          => $remarks
        ];

        if (!empty($id)) {
            $model->update($id, $data);
            $msg = 'Gadget issuance updated successfully!';
        } else {
            $model->insert($data);
            $msg = 'Gadget issuance created successfully!';
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $msg
        ]);
    }

    public function delete($id)
    {
        $this->ensureTableExists();
        $model = new GadgetIssuanceModel();

        if ($model->find($id)) {
            $model->delete($id);
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Gadget issuance record deleted successfully.'
            ]);
        }

        return $this->response->setStatusCode(404)->setJSON([
            'status'  => 'error',
            'message' => 'Record not found.'
        ]);
    }

    public function getTypes()
    {
        $this->ensureTableExists();
        $db = \Config\Database::connect();
        
        $dbTypes = [];
        if ($db->tableExists('gadget_types')) {
            $typeRows = $db->table('gadget_types')->select('name')->orderBy('id', 'ASC')->get()->getResultArray();
            $dbTypes = array_column($typeRows, 'name');
        }

        $giRows = $db->table('gadget_issuances')
            ->select('gadget_type as name')
            ->where('gadget_type IS NOT NULL')
            ->where('gadget_type !=', '')
            ->groupBy('gadget_type')
            ->get()->getResultArray();
        $giTypes = array_column($giRows, 'name');

        $defaults = [
            'Laptop',
            'Mobile / Smartphone',
            'Monitor / Display',
            'Tablet / iPad',
            'Accessory (Headset, Mouse, etc.)',
            'Peripheral / Hardware',
            'Other Asset'
        ];

        $merged = array_unique(array_merge($defaults, $dbTypes, $giTypes));

        return $this->response->setJSON([
            'status' => 'success',
            'types'  => array_values($merged)
        ]);
    }

    public function addType()
    {
        $this->ensureTableExists();
        $db = \Config\Database::connect();

        $name = trim($this->request->getPost('name') ?? '');
        if (empty($name)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Gadget type name is required.'
            ]);
        }

        $existing = $db->table('gadget_types')->where('LOWER(name)', strtolower($name))->get()->getRowArray();
        if (!$existing) {
            $db->table('gadget_types')->insert([
                'name'       => $name,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $name = $existing['name'];
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'name'    => $name,
            'message' => 'Gadget type saved to database successfully.'
        ]);
    }
}
