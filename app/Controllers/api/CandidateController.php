<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use App\Services\AuthService;
use App\Models\CandidateModel;
use App\Models\JobModel;
use App\Models\UserInfoModel;
use App\Models\UserModel;
use App\Models\InterviewModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\EmailService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CandidateController extends ResourceController
{
    private $candidateModel;
    private $authService;
    private $userInfoModel;
    private $userModel;
    private $interviewModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userInfoModel = new UserInfoModel();
        $this->candidateModel = new CandidateModel();
        $this->interviewModel = new InterviewModel();
        $this->authService = new AuthService(service('request'));
    }

    public function create($id = null)
    {
        $jobModel = new JobModel();
        $departmentModel = new \App\Models\DepartmentModel();
        $locationModel = new \App\Models\JoblocationModel();

        $jobs = $jobModel->findAll();
        $departments = $departmentModel->findAll();
        $locations = $locationModel->findAll();

        return view('candidate/candidate', [
            'jobs' => $jobs,
            'departments' => $departments,
            'locations' => $locations
        ]);
    }

    public function display()
    {
        return view('candidate/view');
    }
    // public function CandidateApply()
    // {
    //     $jobModel = new JobModel();
    //     $jobs = $jobModel->findAll();

    //     return view('candidate/candidate_apply', ['jobs' => $jobs]);
    // }

    public function creates()
    {
        // Check if the user is authorized with a valid token
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Validate input data
        $validationRules = [
            'candidate_name' => 'required',
            'email' => 'required|valid_email|is_unique[candidate.email]',
            'phone_number' => 'required',
        ];
        $resumeFile = $this->request->getFile('resume');
        if ($resumeFile && $resumeFile->isValid() && !$resumeFile->hasMoved()) {
            $validationRules['resume'] = 'max_size[resume,2048]|ext_in[resume,pdf,doc,docx]';
        }
        $validationMessages = [
            'candidate_name' => [
                'required' => 'Candidate name is required.',
            ],
            'email' => [
                'required'    => 'Email is required.',
                'valid_email' => 'Please enter a valid email address.',
                'is_unique'   => 'This email has already been used to apply.'
            ],

            'phone_number' => [
                'required' => 'Phone number is required.',
            ],
            'resume' => [
                'max_size' => 'Resume file size must not exceed 2MB.',
                'ext_in'   => 'Resume must be in PDF, DOC, or DOCX format.',
            ],
            // 'notes' => [
            //     'required' => 'The notes field is required.',
            // ],
        ];


        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ], 400);
        }

        // Get the form data
        $data = $this->request->getPost();
        $data['job_date'] = date('Y-m-d');
        if (empty($data['job_id'])) {
            $data['job_id'] = 0; // Use 0 instead of null to avoid 'cannot be null' db errors
        }
        // Handle optional file upload
        $resume = $this->request->getFile('resume');
        if ($resume && $resume->isValid() && !$resume->hasMoved()) {
            // Set the file path for the public folder
            $filePath = FCPATH . 'uploads/resumes/';

            $newFileName = $resume->getRandomName();
            $resume->move($filePath, $newFileName);

            // Add file name (relative path) to data
            $data['resume'] = 'uploads/resumes/' . $newFileName;  // Store relative path for the resume
        } else {
            // If no file is uploaded, set `resume` to NULL
            $data['resume'] = null;
        }

        // Add user ID to track who created the candidate
        $data['created_by'] = $user->sub;

        // Generate the next employee_id for the candidate
        $userInfoModel = new \App\Models\UserInfoModel();
        $lastEmployee = $userInfoModel->orderBy('employee_id', 'DESC')->first();
        $newEmployeeId = 1000; // Default if no employees exist
        if ($lastEmployee && !empty($lastEmployee['employee_id'])) {
            $lastIdStr = (string)$lastEmployee['employee_id'];
            if (is_numeric($lastIdStr)) {
                $newEmployeeId = $lastIdStr + 1;
            } elseif (preg_match('/(\d+)$/', $lastIdStr, $matches)) {
                $num = (int)$matches[1] + 1;
                $prefix = substr($lastIdStr, 0, -strlen($matches[1]));
                $newEmployeeId = $prefix . sprintf('%0' . strlen($matches[1]) . 'd', $num);
            } else {
                $newEmployeeId = $lastIdStr . '-1';
            }
        }

        $userData = [
            'username' => $data['candidate_name'],
            'email' => $data['email'],
            'role' => 'candidate',

        ];

        $userId = $this->userModel->insert($userData);

        if (!$userId) {
            return $this->respond(['status' => 'error', 'message' => 'Failed to create user'], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Insert into `user_info` table
        $userInfoData = [
            'user_id' => $userId,
            'firstname' => $data['candidate_name'],
            'email' => $data['email'],
            'contact_number' => $data['phone_number'],
            'status' => 'candidate',
            'resume' => $data['resume'],
            'job_id' => $data['job_id'],
            'employee_id' => $newEmployeeId, // Set the newly generated employee ID
        ];
        $userInfoInserted = $this->userInfoModel->insert($userInfoData);
        //Send welcome email
        $emailService = new EmailService();
        $emailService->sendCandidateWelcomeEmail($data);
        if (!$userInfoInserted) {
            return $this->respond(['status' => 'error', 'message' => 'Failed to insert user info'], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        // Insert the candidate into the database
        $candidateId = $this->candidateModel->insert($data);
        if ($candidateId) {
            // ✅ Notify Admins, HRs, and the candidate
            $notificationModel = new \App\Models\NotificationModel();
            $userModel = new \App\Models\UserModel();

            // Get all admins and HRs
            $recipients = $userModel->whereIn('role', ['admin', 'hr'])->findAll();

            // Add the candidate themselves as a recipient
            $recipients[] = ['id' => $userId]; // Candidate's user ID

            // Avoid duplicate recipients if candidate is also HR/Admin
            $uniqueRecipients = [];
            foreach ($recipients as $recipient) {
                $uniqueRecipients[$recipient['id']] = $recipient;
            }

            // Send notifications
            foreach ($uniqueRecipients as $recipient) {
                $notificationModel->insert([
                    'sender_id'    => $user->sub,
                    'recipient_id' => $recipient['id'],
                    'data'         => json_encode([
                        'username' => $data['candidate_name'],
                        'type'     => 'candidate',
                        'message'  => 'New candidate applied: ' . $data['candidate_name']
                    ]),
                    'is_read' => 0
                ]);
            }

            $candidateData = [
                'id' => $candidateId,
                'candidate_name' => $data['candidate_name'],
                'job_id' => $data['job_id'] ?? null,
                'email' => $data['email'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
            ];
            return $this->respond([
                'status' => 'success',
                'message' => 'Candidate created successfully',
                'data' => $candidateData
            ], ResponseInterface::HTTP_CREATED);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to create candidate'], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
    }

    // Get all candidates
    public function getAll()
    {
        // Check if the user is authorized
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        if (!in_array($user->role, ['admin', 'hr'])) {
            return $this->failForbidden('Forbidden: You do not have access to this resource');
        }

        $candidates = $this->candidateModel->select('candidate.id, candidate.job_id, candidate.candidate_name, candidate.email, candidate.phone_number, candidate.resume, candidate.job_date, candidate.status, jobs.job_title')
            ->join('jobs', 'candidate.job_id = jobs.id', 'left')
            ->orderBy('candidate.created_at', 'DESC')
            ->findAll();
        return $this->respond(['status' => 'success', 'data' => $candidates]);
    }

    // Get a specific candidate by ID
    public function get($id = null)
    {
        // Check if the user is authorized
        if (!$this->authService->check()) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $query = $this->candidateModel->select('
        candidate.id, 
        candidate.candidate_name,
        candidate.email, 
        candidate.phone_number, 
        candidate.status AS candidate_status, 
        candidate.resume, 
       candidate.notes,
        jobs.job_title,
        jobs.post_date, 
        jobs.status,
       ')
            ->join('jobs', 'candidate.job_id = jobs.id');

        // If an ID is provided, filter by ID; otherwise, get all jobs
        if ($id !== null) {
            $candidate = $query->where('candidate.id', $id)->first();

            if ($candidate) {
                return $this->respond(['status' => 'success', 'data' => $candidate]);
            }
            if ($candidate) {
                // Construct full URL for resume file
                $candidate['resume_url'] = base_url($candidate['resume']);

                return $this->respond(['status' => 'success', 'data' => $candidate]);
            }

            return $this->respond(['status' => 'error', 'message' => 'Candidate not found'], ResponseInterface::HTTP_NOT_FOUND);
        }
    }

    public function update($id = null)
    {
        // Check if the user is authorized with a valid token
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Validate input data
        $validationRules = [
            'candidate_name' => 'required',
            'email' => 'required|valid_email',
            'phone_number' => 'required',
        ];
        $validationMessages = [
            'candidate_name' => ['required' => 'Candidate name is required.'],
            'email' => ['required' => 'Email is required.', 'valid_email' => 'Please enter a valid email address.'],
            'phone_number' => [
                'required' => 'Phone number is required.',
            ],
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ], 400);
        }

        // Get the form data
        $data = $this->request->getPost();

        // Fetch existing candidate record
        $candidate = $this->candidateModel->find($id);
        if (!$candidate) {
            return $this->respond(['status' => 'error', 'message' => 'Candidate not found'], ResponseInterface::HTTP_NOT_FOUND);
        }

        // Handle optional file upload for resume
        $resume = $this->request->getFile('resume');
        if ($resume && $resume->isValid() && !$resume->hasMoved()) {
            $filePath = FCPATH . 'uploads/resumes/';
            $newFileName = $resume->getRandomName();
            $resume->move($filePath, $newFileName);
            $data['resume'] = 'uploads/resumes/' . $newFileName;
        }

        // Add updated_by field
        $data['updated_by'] = $user->sub;

        // Update candidate record
        $this->candidateModel->update($id, $data);

        // Fetch related user record
        $userRecord = $this->userModel->where('email', $candidate['email'])->first();
        if ($userRecord) {
            $userUpdateData = [
                'username' => $data['candidate_name'],
                'email' => $data['email'],
            ];
            $this->userModel->update($userRecord['id'], $userUpdateData);
        }

        // Fetch related user_info record
        $userInfoRecord = $this->userInfoModel->where('email', $candidate['email'])->first();
        if ($userInfoRecord) {
            $userInfoUpdateData = [
                'firstname' => $data['candidate_name'],
                'email' => $data['email'],
                'contact_number' => $data['phone_number'],
                'job_id' => $data['job_id'],
                'resume' => isset($data['resume']) ? $data['resume'] : $userInfoRecord['resume'],
            ];
            $this->userInfoModel->update($userInfoRecord['id'], $userInfoUpdateData);
        }

        // Send notification email
        $emailService = new EmailService();
        $emailService->sendCandidateWelcomeEmail($data);

        return $this->respond(['status' => 'success', 'message' => 'Candidate updated successfully']);
    }

    // Delete a candidate
    public function delete($id = null)
    {
        // Check if the user is authenticated
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Check if the user is an admin
        if ($user->role !== 'admin') {
            return $this->failForbidden('Forbidden: Only Admin can delete candidate records');
        }

        // Check if the candidate has any associated interviews
        $interviews = $this->interviewModel->where('candidate_id', $id)->findAll();
        if (!empty($interviews)) {
            return $this->failForbidden('This candidate has associated interviews and cannot be deleted');
        }

        // Attempt to delete the candidate record
        if ($this->candidateModel->delete($id)) {
            return $this->respond(['status' => 'success', 'message' => 'Candidate deleted successfully']);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to delete candidate record'], 500);
    }

    public function getById($id = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Only Admin and HR can access leave records
        if (!in_array($user->role, ['admin', 'hr'])) {
            return $this->failForbidden('Forbidden: You do not have access to this resource');
        }

        $record = $this->candidateModel->find($id);
        if ($record) {
            // Assuming the resume file is stored in 'uploads/resumes/' and the stored path is relative
            if (!empty($record['resume'])) {
                // Return the full URL for the resume, combining base URL and resume path
                $record['resume'] = base_url($record['resume']); // Ensure base_url() uses the correct path to the file
            }

            return $this->respond(['status' => 'success', 'data' => $record]);
        }

        return $this->respond(['status' => 'error', 'message' => 'Candidate not found'], 404);
    }
    public function singlejob($id = null)
    {

        return view('candidate/display');
    }

    public function downloadResume($id)
    {
        $candidateModel = new CandidateModel();
        $candidate = $candidateModel->find($id);

        if (!$candidate || empty($candidate['resume'])) {
            return $this->failNotFound('Resume not found.');
        }

        $filePath = FCPATH . $candidate['resume'];  // ✅ Use FCPATH instead of ROOTPATH

        if (!file_exists($filePath)) {
            return $this->failNotFound('File does not exist.');
        }

        // Force file download
        return $this->response->download($filePath, null)->setFileName(basename($filePath));
    }

    /**
     * Export Candidates to styled Excel (.xlsx)
     */
    public function exportExcel()
    {
        $user = $this->authService->user();
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Unauthorized']);
        }

        $jobId = $this->request->getGet('job_id');
        $status = $this->request->getGet('status');
        $search = $this->request->getGet('search');

        $builder = $this->candidateModel->builder();
        $builder->select('candidate.*, jobs.job_title, department.department_name')
            ->join('jobs', 'jobs.id = candidate.job_id', 'left')
            ->join('department', 'department.id = jobs.department_id', 'left');

        if (!empty($jobId)) {
            $builder->where('candidate.job_id', (int)$jobId);
        }
        if (!empty($status)) {
            $builder->where('candidate.status', $status);
        }
        if (!empty($search)) {
            $builder->groupStart()
                ->like('candidate.candidate_name', $search)
                ->orLike('candidate.email', $search)
                ->orLike('candidate.phone_number', $search)
                ->orLike('jobs.job_title', $search)
                ->groupEnd();
        }

        $records = $builder->orderBy('candidate.id', 'DESC')->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Candidates');

        $headers = [
            'A1' => 'S.No',
            'B1' => 'Candidate Name',
            'C1' => 'Email',
            'D1' => 'Phone Number',
            'E1' => 'Applied Job Position',
            'F1' => 'Department',
            'G1' => 'Application Date',
            'H1' => 'Status',
            'I1' => 'Notes / Remarks',
            'J1' => 'Created Date'
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E66136']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $rowNum = 2;
        $sno = 1;
        foreach ($records as $item) {
            $sheet->setCellValue('A' . $rowNum, $sno++);
            $sheet->setCellValue('B' . $rowNum, $item['candidate_name'] ?? '-');
            $sheet->setCellValue('C' . $rowNum, $item['email'] ?? '-');
            $sheet->setCellValueExplicit('D' . $rowNum, (string)($item['phone_number'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowNum, $item['job_title'] ?? '-');
            $sheet->setCellValue('F' . $rowNum, $item['department_name'] ?? '-');
            $sheet->setCellValue('G' . $rowNum, !empty($item['job_date']) ? date('Y-m-d', strtotime($item['job_date'])) : '-');
            $sheet->setCellValue('H' . $rowNum, ucfirst($item['status'] ?? 'Applied'));
            $sheet->setCellValue('I' . $rowNum, strip_tags($item['notes'] ?? '-'));
            $sheet->setCellValue('J' . $rowNum, !empty($item['created_at']) ? date('Y-m-d H:i', strtotime($item['created_at'])) : '-');

            $rowNum++;
        }

        $lastRow = $rowNum > 2 ? $rowNum - 1 : 2;
        $borderStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
        ];
        $sheet->getStyle('A1:J' . $lastRow)->applyFromArray($borderStyle);

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        $filename = 'Candidates_' . date('Y_m_d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
