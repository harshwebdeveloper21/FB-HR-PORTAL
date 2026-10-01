<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use App\Models\TrainingModel;
use App\Services\AuthService;
use CodeIgniter\RESTful\ResourceController;
use App\Libraries\EmailService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TrainingController extends ResourceController
{
    private $trainingModel;
    private $authService;

    public function __construct()
    {
        $this->trainingModel = new TrainingModel();
        $this->authService = new AuthService(service('request'));
    }

    public function create()
    {
        $userModel = new \App\Models\UserModel();
        $departmentModel = new \App\Models\DepartmentModel();
        $actor = $this->authService->check();
        if (!$actor) {
            return redirect()->to('/login');
        }

        $employees = $this->managedEmployees($actor);
        $departments = $this->managedDepartments($actor);
        $branches = (new \App\Models\BranchModel())->getActiveBranches();

        return view('training/training', [
            'employees'           => $employees,
            'departments'         => $departments,
            'branches'            => $branches,
            'currentUserRole'     => $actor->role,
            'currentUserBranchId' => $this->authService->getBranchId() ?? '',
        ]);
    }

    public function display()
    {
        return view('training/view');
    }
    public function profilePage()
    {
        return view('training/profile');
    }
    public function creates()
    {
        // Check if user is authorized with a valid token
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Role-based access control (RBAC)
        if (!in_array($user->role, ['admin', 'hr', 'branch_admin', 'department_manager'], true)) {
            return $this->failForbidden('Forbidden: You do not have permission to create training records');
        }

        // Validate input data
        $data = $this->request->getPost();
        if (!$this->canManageEmployee($user, (int)($data['user_id'] ?? 0))) {
            return $this->failForbidden('You can assign training only within your permitted scope.');
        }
        $target = (new \App\Models\UserModel())->find((int)$data['user_id']);
        $data['department_id'] = $target['department_id'] ?? null;
        if (!$this->validate([
            'training_title' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Training title is required.'
                ]
            ],
            'user_id' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Employee name is required.'
                ]
            ],
            'department_id' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Department selection is required.'
                ]
            ],
            // 'description' => [
            //     'rules' => 'required',
            //     'errors' => [
            //         'required' => 'Description of the training is required.'
            //     ]
            // ],
            'start_date' => [
                'rules' => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required' => 'Start date is required.',
                    'valid_date' => 'Please enter a valid start date (YYYY-MM-DD).'
                ]
            ],
            'end_date' => [
                'rules' => 'required|valid_date[Y-m-d]|check_end_date[start_date]',
                'errors' => [
                    'required' => 'End date is required.',
                    'valid_date' => 'Please enter a valid end date (YYYY-MM-DD).',
                    'check_end_date' => 'End date must be after the start date.'
                ]
            ],
            'location' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Training location is required.'
                ]
            ],
        ])) {
            return $this->respond(['status' => 'error', 'message' => $this->validator->getErrors()], 400);
        }


        if ($this->trainingModel->insert($data)) {

            $trainingId = $this->trainingModel->insertID();

            // Send notification after insert
            $notificationModel = new \App\Models\NotificationModel();
            $userModel = new \App\Models\UserModel();
            $employee = $userModel->find($data['user_id']);
            $sender = $userModel->find($user->sub);

            // Notify admin, HR, and the selected employee
            $recipients = $userModel
                ->whereIn('role', ['admin', 'hr'])
                ->orWhere('id', $data['user_id']) // Include employee
                ->findAll();

            foreach ($recipients as $recipient) {
                $notificationModel->insert([
                    'sender_id'    => $user->sub,
                    'recipient_id' => $recipient['id'],
                    'data'         => json_encode([
                        'type'     => 'training',
                        'username' => $sender['username'],
                        'employee' => $employee['username'],
                        'message'  => 'New training assigned to ' . $employee['username'],
                        'user_id'  => $data['user_id']
                    ]),
                    'is_read' => 0
                ]);
            }

            // Optional: Send email
            if ($trainingId) {
                $emailService = new EmailService();
                $emailService->sendTrainingEmail($trainingId);
            }

            return $this->respond(['status' => 'success', 'message' => 'Training added successfully'], 201);
        }


        return $this->respond(['status' => 'error', 'message' => 'Failed to add training record'], 500);
    }

    // Get all performance records (Admin only)
    public function getAll()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $this->trainingModel->select('training.*,training.created_at, training.training_title, users.username as employee_name, training.start_date, training.end_date, training.location,user_info.profile_image')
            ->join('users', 'users.id = training.user_id')
             ->join('user_info', 'user_info.user_id = training.user_id');

        // Role-based filtering
        if (in_array($user->role, ['admin', 'hr'], true)) {
            // Global roles can see all records.
            $records = $this->trainingModel->orderBy('created_at', 'DESC')->findAll();
        } elseif (in_array($user->role, ['branch_admin', 'department_manager'], true)) {
            $this->applyManagedScope($this->trainingModel, $user);
            $records = $this->trainingModel->orderBy('training.created_at', 'DESC')->findAll();
        } elseif ($user->role === 'employee') {
            // Employee can only see their own records
            $records = $this->trainingModel->where('training.user_id', $user->sub)->orderBy('created_at', 'DESC')->findAll();
        } else {
            return $this->failForbidden('Forbidden: Unauthorized role');
        }


        return $this->respond(['status' => 'success', 'data' => $records]);
    }

    // Get performance records by employee (Admin, HR, Employee)
    public function getByEmployee($employeeId = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $record = $this->trainingModel->find($employeeId);
        if (!$record) return $this->failNotFound('Training record not found');
        if ($user->role === 'employee' && (int)$record['user_id'] !== (int)$user->sub) {
            return $this->failForbidden('Forbidden: You can only access your own training records');
        }
        if (!in_array($user->role, ['admin', 'hr', 'employee'], true) && !$this->canManageEmployee($user, (int)$record['user_id'])) {
            return $this->failForbidden('Forbidden: You can only access training within your permitted scope');
        }

        $records = [$record];

        return $this->respond(['status' => 'success', 'data' => $records]);
    }


    // Update performance record (Admin or HR can update)
    public function update($id = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        if (!in_array($user->role, ['admin', 'hr', 'branch_admin', 'department_manager'], true)) {
            return $this->failForbidden('Forbidden: You do not have permission to update training records');
        }

        // Get input data
        $data = $this->request->getPost();

        $existingRecord = $this->trainingModel->find($id);
        if (!$existingRecord) {
            return $this->respond(['status' => 'error', 'message' => 'Training record not found'], 404);
        }
        $targetUserId = (int)($data['user_id'] ?? $existingRecord['user_id']);
        if (!$this->canManageEmployee($user, (int)$existingRecord['user_id']) || !$this->canManageEmployee($user, $targetUserId)) {
            return $this->failForbidden('You can update training only within your permitted scope.');
        }
        $target = (new \App\Models\UserModel())->find($targetUserId);
        $data['department_id'] = $target['department_id'] ?? null;

        // Validate input data before updating
        if (!$this->validate([
            'training_title' => [
                'rules' => 'required',
                'errors' => ['required' => 'Training title is required.']
            ],
            'user_id' => [
                'rules' => 'required',
                'errors' => ['required' => 'Employee name is required.']
            ],
            'department_id' => [
                'rules' => 'required',
                'errors' => ['required' => 'Department selection is required.']
            ],
            // 'description' => [
            //     'rules' => 'required',
            //     'errors' => ['required' => 'Description of the training is required.']
            // ],
            'start_date' => [
                'rules' => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required' => 'Start date is required.',
                    'valid_date' => 'Please enter a valid start date (YYYY-MM-DD).'
                ]
            ],
            'end_date' => [
                'rules' => 'required|valid_date[Y-m-d]|check_end_date[start_date]',
                'errors' => [
                    'required' => 'End date is required.',
                    'valid_date' => 'Please enter a valid end date (YYYY-MM-DD).',
                    'check_end_date' => 'End date must be after the start date.'
                ]
            ],
            'location' => [
                'rules' => 'required',
                'errors' => ['required' => 'Training location is required.']
            ],
        ])) {
            return $this->respond(['status' => 'error', 'message' => $this->validator->getErrors()], 400);
        }

        // Update training record in database
        if ($this->trainingModel->update($id, $data)) {
            $emailService = new EmailService();
            $emailService->sendTrainingEmail($id);
            return $this->respond(['status' => 'success', 'message' => 'Training record updated successfully']);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to update training record'], 500);
    }


    // Delete performance record (Admin or HR can delete)
    public function delete($id = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        if (!in_array($user->role, ['admin', 'hr', 'branch_admin', 'department_manager'], true)) {
            return $this->failForbidden('Forbidden: You do not have permission to delete training records');
        }

        $record = $this->trainingModel->find($id);
        if (!$record || !$this->canManageEmployee($user, (int)$record['user_id'])) {
            return $this->failForbidden('You can delete training only within your permitted scope.');
        }

        if ($this->trainingModel->delete($id)) {
            return $this->respond(['status' => 'success', 'message' => 'training record deleted successfully']);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to delete training record'], 500);
    }
    public function getProfile($id = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Fetch training details along with user and designation info
        // $record = $this->trainingModel
        //     ->select('training.*, users.username as employee_name, department.department_name')
        //     ->join('users', 'users.id = training.user_id')
        //     ->join('department', 'department.id = training.department_id')
        //     ->where('training.id', $id)
        //     ->first();
        $record = $this->trainingModel
        ->select('training.*, 
                  users.username as employee_name, 
                  ui.email, ui.employee_id, 
                  des.designation_name, 
                  dep.department_name')
        ->join('users', 'users.id = training.user_id','left')
        ->join('user_info ui', 'ui.user_id = users.id','left')
        ->join('designation des', 'des.id = ui.designation_id','left')
        ->join('department dep', 'dep.id = ui.department_id','left')
        ->where('training.id', $id)
        ->first();
        if (!$record) {
            return $this->failNotFound('training record not found');
        }
        if ($user->role === 'employee' && (int)$record['user_id'] !== (int)$user->sub) {
            return $this->failForbidden('You can access only your own training record.');
        }
        if (!in_array($user->role, ['admin', 'hr', 'employee'], true) && !$this->canManageEmployee($user, (int)$record['user_id'])) {
            return $this->failForbidden('You can access only training within your permitted scope.');
        }

        return $this->respond(['status' => 'success', 'data' => $record]);
    }
    public function addDepartment()
    {
        $deptController = new \App\Controllers\api\DepartmentController();
        $deptController->initController($this->request, $this->response, $this->logger);
        return $deptController->addDepartment();
    }

    /**
     * Export Trainings to styled Excel (.xlsx)
     */
    public function exportExcel()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Unauthorized: Token missing or invalid']);
        }
        if (!in_array($user->role, ['admin', 'hr', 'branch_admin', 'department_manager', 'employee'], true)) {
            return $this->failForbidden('Forbidden: Unauthorized role');
        }

        $search = $this->request->getGet('search');
        $builder->select('training.*, users.username as employee_name, ui.firstname, ui.lastname, ui.employee_id, ui.email, dep.department_name, des.designation_name')
            ->join('users', 'users.id = training.user_id', 'left')
            ->join('user_info ui', 'ui.user_id = users.id', 'left')
            ->join('department dep', 'dep.id = training.department_id', 'left')
            ->join('designation des', 'des.id = ui.designation_id', 'left');

        // Role-based filtering
        if (in_array($user->role, ['branch_admin', 'department_manager'], true)) {
            $this->applyManagedScope($builder, $user);
        } elseif ($user->role === 'employee') {
            $builder->where('training.user_id', $user->sub);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('training.training_title', $search)
                ->orLike('training.location', $search)
                ->orLike('users.username', $search)
                ->orLike('ui.firstname', $search)
                ->orLike('ui.lastname', $search)
                ->orLike('dep.department_name', $search)
                ->groupEnd();
        }

        $records = $builder->orderBy('training.created_at', 'DESC')->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Trainings');

        $headers = [
            'A1' => 'S.No',
            'B1' => 'Employee ID',
            'C1' => 'Employee Name',
            'D1' => 'Department',
            'E1' => 'Designation',
            'F1' => 'Training Title',
            'G1' => 'Start Date',
            'H1' => 'End Date',
            'I1' => 'Location',
            'J1' => 'Description',
            'K1' => 'Created Date'
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E66136']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ];
        $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $rowNum = 2;
        $sno = 1;
        foreach ($records as $item) {
            $name = trim(($item['firstname'] ?? '') . ' ' . ($item['lastname'] ?? '')) ?: ($item['employee_name'] ?? 'N/A');

            $sheet->setCellValue('A' . $rowNum, $sno++);
            $sheet->setCellValueExplicit('B' . $rowNum, $item['employee_id'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $rowNum, $name);
            $sheet->setCellValue('D' . $rowNum, $item['department_name'] ?? '-');
            $sheet->setCellValue('E' . $rowNum, $item['designation_name'] ?? '-');
            $sheet->setCellValue('F' . $rowNum, $item['training_title'] ?? '-');
            $sheet->setCellValue('G' . $rowNum, $item['start_date'] ?? '-');
            $sheet->setCellValue('H' . $rowNum, $item['end_date'] ?? '-');
            $sheet->setCellValue('I' . $rowNum, $item['location'] ?? '-');
            $sheet->setCellValue('J' . $rowNum, strip_tags($item['description'] ?? '-'));
            $sheet->setCellValue('K' . $rowNum, !empty($item['created_at']) ? date('Y-m-d H:i', strtotime($item['created_at'])) : '-');

            $rowNum++;
        }

        $lastRow = $rowNum > 2 ? $rowNum - 1 : 2;
        $borderStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
        ];
        $sheet->getStyle('A1:K' . $lastRow)->applyFromArray($borderStyle);

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        $filename = 'Trainings_' . date('Y_m_d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function managedEmployees(object $actor): array
    {
        $model = new \App\Models\UserModel();
        if (in_array($actor->role, ['admin', 'hr'], true)) {
            return $model->whereIn('role', ['employee', 'department_manager'])->where('is_deleted', 0)->findAll();
        }
        $actorRow = $model->find($actor->sub);
        $query = $model->where('is_deleted', 0)->where('branch_id', (int)($actorRow['branch_id'] ?? 0));
        if ($actor->role === 'branch_admin') {
            return $query->whereIn('role', ['employee', 'department_manager'])->findAll();
        }
        if ($actor->role === 'department_manager') {
            return $query->where('department_id', (int)($actorRow['department_id'] ?? 0))->where('role', 'employee')->findAll();
        }
        return [];
    }

    private function managedDepartments(object $actor): array
    {
        $model = new \App\Models\DepartmentModel();
        if (in_array($actor->role, ['admin', 'hr'], true)) return $model->findAll();
        $actorRow = (new \App\Models\UserModel())->find($actor->sub);
        if ($actor->role === 'branch_admin') return $model->where('branch_id', (int)($actorRow['branch_id'] ?? 0))->findAll();
        if ($actor->role === 'department_manager') return $model->where('id', (int)($actorRow['department_id'] ?? 0))->findAll();
        return [];
    }

    private function canManageEmployee(object $actor, int $employeeId): bool
    {
        if (in_array($actor->role, ['admin', 'hr'], true)) return true;
        $users = new \App\Models\UserModel();
        $actorRow = $users->find($actor->sub);
        $target = $users->find($employeeId);
        if (!$actorRow || !$target) return false;
        if ($actor->role === 'branch_admin') return (int)$target['branch_id'] === (int)$actorRow['branch_id'] && in_array($target['role'], ['employee', 'department_manager'], true);
        if ($actor->role === 'department_manager') return $target['role'] === 'employee' && (int)$target['branch_id'] === (int)$actorRow['branch_id'] && (int)$target['department_id'] === (int)$actorRow['department_id'];
        return false;
    }

    private function applyManagedScope($builder, object $actor): void
    {
        $actorRow = (new \App\Models\UserModel())->find($actor->sub);
        $builder->where('users.branch_id', (int)($actorRow['branch_id'] ?? 0));
        if ($actor->role === 'branch_admin') {
            $builder->whereIn('users.role', ['employee', 'department_manager']);
        } else {
            $builder->where('users.role', 'employee')->where('users.department_id', (int)($actorRow['department_id'] ?? 0));
        }
    }
}
