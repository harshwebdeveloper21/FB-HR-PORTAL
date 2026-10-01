<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\TaskModel;
use App\Models\CommentModel;
use App\Models\SubtaskModel;
use App\Services\AuthService;
use App\Libraries\EmailService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TaskController extends ResourceController
{
    private $taskModel;
    private $subtaskModel;
    private $authService;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->subtaskModel = new SubtaskModel();
        $this->authService = new AuthService(service('request'));
    }

    public function getTasks()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $builder = $this->taskModel->builder();
        $builder->select('task.*')->join('users', 'users.id = task.user_id');

        if (in_array($user->role, ['admin', 'hr'], true)) {
            // Global roles can see all records.
        } elseif (in_array($user->role, ['branch_admin', 'department_manager'], true)) {
            $this->applyManagedScope($builder, $user);
        } elseif ($user->role === 'employee') {
            $builder->where('task.user_id', $user->sub);
        } else {
            return $this->failForbidden('Forbidden: Unauthorized role');
        }

        return $this->response->setJSON($builder->get()->getResultArray());
    }
    public function creates()
    {
        $userModel = new \App\Models\UserModel();
        $departmentModel = new \App\Models\DepartmentModel();
        $actor = $this->authService->check();
        if (!$actor) return redirect()->to('/login');

        // Get the role of the logged-in user
        $role = $actor->role;

        // Fetch employees with role 'employee'
        $employees = $this->managedEmployees($actor);

        // If the user is an admin, fetch both HR and employees for the dropdown
        $departments = $this->managedDepartments($actor);
        $branches = (new \App\Models\BranchModel())->getActiveBranches();

        return view('task/task', [
            'employees'          => $employees,
            'departments'        => $departments,
            'branches'           => $branches,
            'currentUserRole'    => $actor->role,
            'currentUserBranchId'=> $this->authService->getBranchId() ?? '',
        ]);
    }

    public function displays()
    {
        return view('task/view');
    }
    public function create()
    {
        // Check if user is authorized with a valid token
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Role-based access control (RBAC)
        if (!in_array($user->role, ['admin', 'hr', 'branch_admin', 'department_manager'], true)) {
            return $this->failForbidden('Forbidden: You do not have permission to create tasks');
        }

        // Prepare data for validation (include file)
        $data = [
            'user_id' => $this->request->getPost('user_id'),
            'task_title' => $this->request->getPost('task_title'),
            'department_id' => $this->request->getPost('department_id'),
            'assigned_date' => $this->request->getPost('assigned_date'),
            'due_date' => $this->request->getPost('due_date'),
            'document' => $this->request->getFile('document') // ✅ Include the file for validation
        ];
        if (!$this->canManageEmployee($user, (int)$data['user_id'])) {
            return $this->failForbidden('You can assign tasks only within your permitted scope.');
        }
        // Validation rules and messages
        $validationRules = [
            'user_id' => 'required|integer',
            'task_title' => 'required',
            'department_id' => 'required',
            'assigned_date' => 'required|valid_date[Y-m-d]',
            'task_status' => 'required|in_list[Pending,In-Progress,Completed]',
            'due_date' => 'required|valid_date[Y-m-d]|check_end_date[assigned_date]',
            'document' => 'uploaded[document]|max_size[document,2048]|ext_in[document,pdf,doc,docx,jpeg,jpg,png,webp,mp3,wav,mp4,avi]',
        ];

        $validationMessages = [
            'user_id' => ['required' => 'Name is required.', 'integer' => 'Name is required.'],
            'task_title' => ['required' => 'Task title is required.'],
            'department_id' => ['required' => 'Department is required.'],
            'assigned_date' => ['required' => 'Assigned date is required.', 'valid_date' => 'Invalid assigned date format.'],
            'due_date' => [
                'required' => 'Due date is required.',
                'valid_date' => 'Invalid due date format.',
                'check_end_date' => 'Due date must be after assigned date.'
            ],
            'task_status' => [
                'required' => 'Task status is required.',
                'in_list' => 'The task status field must be one of: Pending, In-Progress, Completed.',
            ],
            'document' => [
                'rules' => 'uploaded[document.0]|max_size[document,2048]|ext_in[document,pdf,doc,docx,jpeg,jpg,png,mp3,webp,wav,mp4,avi]',
                'errors' => [
                    'uploaded' => 'At least one document is required.',
                    'max_size' => 'Each file must not exceed 2MB.',
                    'ext_in' => 'Allowed file types: PDF, DOC, DOCX.',
                ]
            ]

        ];

        // Run validation
        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->respond([
                'status' => 'error',
                'message' => $this->validator->getErrors()
            ], 400);
        }

        // Prepare data for saving
        $saveData = $this->request->getPost();
        $saveData['department_id'] = $scopedDepartmentId;
        $saveData['created_by'] = $user->sub;

        if (!isset($saveData['task_status']) || empty($saveData['task_status'])) {
            $saveData['task_status'] = 'pending';
        }

        $documents = $this->request->getFileMultiple('document');

        $storedFiles = [];

        if ($documents && count($documents) > 0) {
            $filePath = FCPATH . 'upload/document/';
            if (!is_dir($filePath)) {
                mkdir($filePath, 0755, true);
            }

            foreach ($documents as $doc) {
                if ($doc->isValid() && !$doc->hasMoved()) {
                    $originalName = $doc->getClientName();
                    $newFileName = $doc->getRandomName();
                    $doc->move($filePath, $newFileName);

                    $storedFiles[] = [
                        'original' => $originalName,
                        'stored' => $newFileName
                    ];
                }
            }

            $saveData['document'] = json_encode($storedFiles); // Save array of files as JSON
        } else {
            $saveData['document'] = null;
        }

        // Insert into DB
        if ($this->taskModel->insert($saveData)) {
            $taskId = $this->taskModel->insertID();

            if ($taskId) {
                // Send email
                $emailService = new EmailService();
                $emailService->sendTaskEmail($taskId);

                // Send notifications
                $notificationModel = new \App\Models\NotificationModel();
                $userModel = new \App\Models\UserModel();

                $assignedEmployee = $userModel->find($saveData['user_id']);
                $creator = $userModel->find($user->sub);

                $recipients = $userModel
                    ->whereIn('role', ['admin', 'hr'])
                    ->orWhere('id', $saveData['user_id'])
                    ->findAll();

                foreach ($recipients as $recipient) {
                    $notificationModel->insert([
                        'sender_id' => $user->sub,
                        'recipient_id' => $recipient['id'],
                        'data' => json_encode([
                            'type' => 'task',
                            'username' => $creator['username'],
                            'employee' => $assignedEmployee['username'],
                            'user_id' => $saveData['user_id'],
                            'message' => 'New task assigned to ' . $assignedEmployee['username']
                        ]),
                        'is_read' => 0
                    ]);
                }
            }

            return $this->respond(['status' => 'success', 'message' => 'Task added successfully'], 201);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to add task'], 500);
    }

    public function getAll()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }
        $this->taskModel->select('task.*,task.created_at,user_info.profile_image ,users.username,task.task_title, task.assigned_date, task.due_date')
            ->join('users', 'users.id = task.user_id')
              ->join('user_info', 'user_info.user_id = task.user_id', 'left');


        // Role-based filtering
        if (in_array($user->role, ['admin', 'hr'], true)) {
            // Global roles can see all records.
            $records = $this->taskModel->orderBy('task.created_at', 'DESC')->findAll();
        } elseif (in_array($user->role, ['branch_admin', 'department_manager'], true)) {
            $this->applyManagedScope($this->taskModel, $user);
            $records = $this->taskModel->orderBy('task.created_at', 'DESC')->findAll();
        } elseif ($user->role === 'employee') {
            // Employee can only see their own records
            $records = $this->taskModel->where('task.user_id', $user->sub)->orderBy('task.created_at', 'DESC')->findAll();
        } else {
            return $this->failForbidden('Forbidden: Unauthorized role');
        }

        return $this->respond([
            'status' => 'success',
            'data' => $records
        ]);
    }
    public function getByEmployee($employeeId = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $task = $this->taskModel->find($employeeId);
        if (!$task) return $this->failNotFound('Task not found');
        if ($user->role === 'employee' && (int)$task['user_id'] !== (int)$user->sub) {
            return $this->failForbidden('Forbidden: You can only access your own tasks');
        }
        if (!in_array($user->role, ['admin', 'hr', 'employee'], true) && !$this->canManageEmployee($user, (int)$task['user_id'])) {
            return $this->failForbidden('Forbidden: You can only access tasks within your permitted scope');
        }

        $tasks = [$task];
        return $this->respond([
            'status' => 'success',
            'data' => $tasks
        ]);
    }

    public function update($id = null)
{
    $user = $this->authService->check();
    if (!$user) {
        return $this->failUnauthorized('Unauthorized: Token missing or invalid');
    }

    if (!in_array($user->role, ['admin', 'hr', 'branch_admin', 'department_manager'], true)) {
        return $this->failForbidden('Forbidden: You do not have permission to update tasks');
    }

    $data = $this->request->getPost();
    $existingTask = $this->taskModel->find($id);
    if (!$existingTask) {
        return $this->failNotFound('Task not found');
    }
    $targetUserId = (int)($data['user_id'] ?? $existingTask['user_id']);
    if (!$this->canManageEmployee($user, (int)$existingTask['user_id']) || !$this->canManageEmployee($user, $targetUserId)) {
        return $this->failForbidden('You can update tasks only within your permitted scope.');
    }
    $target = (new \App\Models\UserModel())->find($targetUserId);
    $data['department_id'] = $target['department_id'] ?? null;

    // Validation rules
    $validationRules = [
        'user_id' => [
            'rules' => 'required|integer',
            'errors' => [
                'required' => 'Name is required.',
                'integer' => 'Name must be a valid number.'
            ]
        ],
        'task_title' => [
            'rules' => 'required',
            'errors' => [
                'required' => 'Task title is required.'
            ]
        ],
        'department_id' => [
            'rules' => 'required',
            'errors' => [
                'required' => 'Department is required.'
            ]
        ],
        'task_status' => [
            'rules' => 'required',
            'errors' => [
                'required' => 'Task status is required.'
            ]
        ],
        'assigned_date' => [
            'rules' => 'required|valid_date[Y-m-d]',
            'errors' => [
                'required' => 'Assigned date is required.',
                'valid_date' => 'Assigned date must be in YYYY-MM-DD format.'
            ]
        ],
        'due_date' => [
            'rules' => 'required|valid_date|check_end_date[assigned_date]',
            'errors' => [
                'required' => 'Due date is required.',
                'valid_date' => 'Due date must be valid (YYYY-MM-DD).',
                'check_end_date' => 'Due date must be after the assigned date.'
            ]
        ],
    ];

    if (!$this->validate($validationRules)) {
        return $this->respond([
            'status' => 'error',
            'message' => $this->validator->getErrors()
        ], 400);
    }

    // Handle document upload
    $documents = $this->request->getFileMultiple('document');
    $existingDocument = $this->taskModel->find($id)['document'] ?? null;
    $existingDocument = $existingDocument ? json_decode($existingDocument, true) : [];

    $newDocs = [];
    $hasNewUpload = false;

    if (is_array($documents)) {
        foreach ($documents as $doc) {
            if ($doc->isValid() && !$doc->hasMoved()) {
                $filePath = FCPATH . 'upload/document/';
                if (!is_dir($filePath)) {
                    mkdir($filePath, 0755, true);
                }

                $originalName = $doc->getClientName();
                $newFileName = $doc->getRandomName();
                $doc->move($filePath, $newFileName);

                $newDocs[] = [
                    'original' => $originalName,
                    'stored'   => $newFileName,
                ];

                $hasNewUpload = true;
            }
        }
    }

    // Determine whether to keep or replace documents
    if ($hasNewUpload) {
        // Delete old files only if new ones are uploaded
        foreach ($existingDocument as $doc) {
            $oldPath = FCPATH . 'upload/document/' . $doc['stored'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $data['document'] = json_encode($newDocs);
    } else {
        $data['document'] = json_encode($existingDocument);
    }

    // Update the task
    if ($this->taskModel->update($id, $data)) {
        // Send task update email
        $emailService = new EmailService();
        $emailService->sendTaskEmail($id);

        return $this->respond([
            'status' => 'success',
            'message' => 'Task updated successfully'
        ]);
    }

    return $this->respond([
        'status' => 'error',
        'message' => 'Failed to update task'
    ], 500);
}

    public function delete($id = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        if (!in_array($user->role, ['admin', 'hr', 'branch_admin', 'department_manager'], true)) {
            return $this->failForbidden('Forbidden: You do not have permission to delete tasks');
        }

        // Fetch the task details
        $task = $this->taskModel->find($id);

        if (!$task) {
            return $this->failNotFound('Task not found');
        }
        if (!$this->canManageEmployee($user, (int)$task['user_id'])) {
            return $this->failForbidden('You can delete tasks only within your permitted scope.');
        }

        // Only allow deletion if the status is "completed"
        if ($task['task_status'] !== 'completed') {

            return $this->respond([
                'status' => 'error',
                'message' => 'Task is still in progress. Only completed tasks can be deleted.'
            ], 400);
        }

        // Delete task if status is completed
        if ($this->taskModel->delete($id)) {
            return $this->respond([
                'status' => 'success',
                'message' => 'Task deleted successfully'
            ]);
        }

        return $this->failServerError('Failed to delete task');
    }

    public function profilePage($taskId)
    {
        $commentModel = new CommentModel();
        $data['comments'] = $commentModel->getComments($taskId);
        $data['taskId'] = $taskId;

        return view('task/profile', $data);
    }
    
    public function getProfile($id = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $record = $this->taskModel
            ->select('
            task.*, 
            users.username as employee_name, 
            users.email as email, 
            user_info.employee_id, 
            user_info.profile_image, 
            user_info.department_id as user_department_id, 
            department.department_name, 
            designation.designation_name as designation_title, 
            created_by_user.username as created_by_username
        ') // Note the added commas for separating fields
            ->join('users', 'users.id = task.user_id')
            ->join('user_info', 'user_info.user_id = task.user_id', 'left')
            ->join('department', 'department.id = user_info.department_id', 'left')
            ->join('designation', 'designation.id = user_info.designation_id', 'left')
            ->join('users as created_by_user', 'created_by_user.id = task.created_by', 'left')
            ->where('task.id', $id)
            ->first();


        if (!$record) {
            return $this->failNotFound('task record not found');
        }
        if ($user->role === 'employee' && (int)$record['user_id'] !== (int)$user->sub) {
            return $this->failForbidden('You can access only your own task.');
        }
        if (!in_array($user->role, ['admin', 'hr', 'employee'], true) && !$this->canManageEmployee($user, (int)$record['user_id'])) {
            return $this->failForbidden('You can access only tasks within your permitted scope.');
        }
        $commentModel = new \App\Models\CommentModel();
        $comments = $commentModel
            ->select('comments.comment, comments.created_at, users.username, user_info.profile_image')
            ->join('users', 'users.id = comments.user_id')
            ->join('user_info', 'user_info.user_id = users.id', 'left')
            ->where('comments.task_id', $id)
            ->orderBy('comments.created_at', 'DESC')
            ->findAll();

        $documents = [];
        if (!empty($record['document'])) {
            $docArray = json_decode($record['document'], true);

            if (is_array($docArray)) {
                foreach ($docArray as $doc) {
                    $documents[] = [
                        'name' => $doc['original'] ?? basename($doc['stored']),
                        'url' => base_url('upload/document/' . $doc['stored']),
                    ];
                }
            } else {
                // fallback if stored as comma-separated
                $fallbackDocs = explode(',', $record['document']);
                foreach ($fallbackDocs as $doc) {
                    $documents[] = [
                        'name' => $this->generateReadableName($doc), // Custom function below
                        'url' => base_url('upload/document/' . $doc),
                    ];
                }
            }
        }
        $record['documents'] = $documents;

        // Fetch subtasks for this task
        $subtaskModel = new \App\Models\SubtaskModel();
        $subtasks = $subtaskModel->where('task_id', $id)->findAll();
        // Count completed and total subtasks
        $totalSubtasks = count($subtasks);
        $completedCount = 0;

        foreach ($subtasks as &$subtask) {
            if (strtolower($subtask['subtask_status']) === 'Completed') {
                $completedCount++;
            }

            $subtaskFiles = [];

            if (!empty($subtask['files'])) {
                $decodedFiles = json_decode($subtask['files'], true);

                if (is_array($decodedFiles)) {
                    foreach ($decodedFiles as $file) {
                        if (is_array($file)) {
                            $subtaskFiles[] = [
                                'name' => $file['original'] ?? basename($file['stored']),
                                'url' => base_url('uploads/subtasks/' . $file['stored']),
                            ];
                        } else {
                            // Fallback if $file is a string
                            $subtaskFiles[] = [
                                'name' => $this->generateReadableName($file),
                                'url' => base_url('uploads/subtasks/' . $file),
                            ];
                            // fallback if stored as comma-separated
                            $fallbackDocs = explode(',', $record['files']);
                            foreach ($fallbackDocs as $file) {
                                $subtaskFiles[] = [
                                    'name' => $this->generateReadableName($file), // Custom function below
                                    'url' => base_url('upload/document/' . $file),
                                ];
                            }
                        }
                    }
                }
            }

            $subtask['files'] = $subtaskFiles;
        }

        return $this->respond([
            'status' => 'success',
            'data' => $record,
            'comments' => $comments,
            'subtasks' => $subtasks,
            'subtask_summary' => [
                'completed' => $completedCount,
                'total' => $totalSubtasks
            ]
        ]);
    }
    private function generateReadableName($filename)
    {
        // Strip prefix & timestamp if possible
        $parts = explode('_', $filename, 2);
        return isset($parts[1]) ? $parts[1] : $filename;
    }

    public function addDepartment()
    {
        $deptController = new \App\Controllers\api\DepartmentController();
        $deptController->initController($this->request, $this->response, $this->logger);
        return $deptController->addDepartment();
    }
    public function updateStatus()
    {
        $user = $this->authService->check();
        if (!$user) return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        $json = $this->request->getJSON();
        $taskId = $json->id ?? null;
        $newStatus = $json->status ?? null;

        if (!$taskId || !$newStatus) {
            return $this->fail('Invalid data', 400);
        }

        $taskModel = new TaskModel();
        $task = $taskModel->find($taskId);

        if (!$task) {
            return $this->failNotFound('Task not found');
        }
        if ($user->role === 'employee' && (int)$task['user_id'] !== (int)$user->sub) {
            return $this->failForbidden('You can update only your own task status.');
        }
        if (!in_array($user->role, ['admin', 'hr', 'employee'], true) && !$this->canManageEmployee($user, (int)$task['user_id'])) {
            return $this->failForbidden('You can update tasks only within your permitted scope.');
        }

        $taskModel->update($taskId, ['task_status' => $newStatus]);

        return $this->respond([
            'status' => 'success',
            'message' => 'Task status updated successfully'
        ]);
    }

    /**
     * Export Tasks to styled Excel (.xlsx)
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
        $status = $this->request->getGet('status');

        $builder = $this->taskModel->builder();
        $builder->select('task.*, users.username as employee_name, ui.firstname, ui.lastname, ui.employee_id, ui.email, dep.department_name, creator.username as creator_name')
            ->join('users', 'users.id = task.user_id', 'left')
            ->join('user_info ui', 'ui.user_id = users.id', 'left')
            ->join('department dep', 'dep.id = task.department_id', 'left')
            ->join('users creator', 'creator.id = task.created_by', 'left');

        // Role-based filtering
        if (in_array($user->role, ['branch_admin', 'department_manager'], true)) {
            $this->applyManagedScope($builder, $user);
        } elseif ($user->role === 'employee') {
            $builder->where('task.user_id', $user->sub);
        }

        if (!empty($status)) {
            $builder->where('task.task_status', $status);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('task.task_title', $search)
                ->orLike('task.task_status', $search)
                ->orLike('users.username', $search)
                ->orLike('ui.firstname', $search)
                ->orLike('ui.lastname', $search)
                ->orLike('dep.department_name', $search)
                ->groupEnd();
        }

        $records = $builder->orderBy('task.created_at', 'DESC')->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Tasks');

        $headers = [
            'A1' => 'S.No',
            'B1' => 'Employee ID',
            'C1' => 'Assigned Employee',
            'D1' => 'Department',
            'E1' => 'Task Title',
            'F1' => 'Assigned Date',
            'G1' => 'Due Date',
            'H1' => 'Status',
            'I1' => 'Description',
            'J1' => 'Created By',
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
            $sheet->setCellValue('E' . $rowNum, $item['task_title'] ?? '-');
            $sheet->setCellValue('F' . $rowNum, $item['assigned_date'] ?? '-');
            $sheet->setCellValue('G' . $rowNum, $item['due_date'] ?? '-');
            $sheet->setCellValue('H' . $rowNum, ucfirst($item['task_status'] ?? '-'));
            $sheet->setCellValue('I' . $rowNum, strip_tags($item['description'] ?? '-'));
            $sheet->setCellValue('J' . $rowNum, $item['creator_name'] ?? '-');
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

        $filename = 'Tasks_' . date('Y_m_d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function managedEmployees(object $actor): array
    {
        $users = new \App\Models\UserModel();
        if (in_array($actor->role, ['admin', 'hr'], true)) return $users->whereIn('role', ['employee', 'department_manager'])->where('is_deleted', 0)->findAll();
        $actorRow = $users->find($actor->sub);
        $query = $users->where('is_deleted', 0)->where('branch_id', (int)($actorRow['branch_id'] ?? 0));
        if ($actor->role === 'branch_admin') return $query->whereIn('role', ['employee', 'department_manager'])->findAll();
        if ($actor->role === 'department_manager') return $query->where('role', 'employee')->where('department_id', (int)($actorRow['department_id'] ?? 0))->findAll();
        return [];
    }

    private function managedDepartments(object $actor): array
    {
        $departments = new \App\Models\DepartmentModel();
        if (in_array($actor->role, ['admin', 'hr'], true)) return $departments->findAll();
        $actorRow = (new \App\Models\UserModel())->find($actor->sub);
        if ($actor->role === 'branch_admin') return $departments->where('branch_id', (int)($actorRow['branch_id'] ?? 0))->findAll();
        if ($actor->role === 'department_manager') return $departments->where('id', (int)($actorRow['department_id'] ?? 0))->findAll();
        return [];
    }

    private function canManageEmployee(object $actor, int $employeeId): bool
    {
        if (in_array($actor->role, ['admin', 'hr'], true)) return true;
        $users = new \App\Models\UserModel(); $actorRow = $users->find($actor->sub); $target = $users->find($employeeId);
        if (!$actorRow || !$target) return false;
        if ($actor->role === 'branch_admin') return (int)$target['branch_id'] === (int)$actorRow['branch_id'] && in_array($target['role'], ['employee', 'department_manager'], true);
        if ($actor->role === 'department_manager') return $target['role'] === 'employee' && (int)$target['branch_id'] === (int)$actorRow['branch_id'] && (int)$target['department_id'] === (int)$actorRow['department_id'];
        return false;
    }

    private function applyManagedScope($builder, object $actor): void
    {
        $actorRow = (new \App\Models\UserModel())->find($actor->sub);
        $builder->where('users.branch_id', (int)($actorRow['branch_id'] ?? 0));
        if ($actor->role === 'branch_admin') $builder->whereIn('users.role', ['employee', 'department_manager']);
        else $builder->where('users.role', 'employee')->where('users.department_id', (int)($actorRow['department_id'] ?? 0));
    }
}
