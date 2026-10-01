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
    private $departmentModel;
    private $designationModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userInfoModel = new UserInfoModel();
        $this->candidateModel = new CandidateModel();
        $this->interviewModel = new InterviewModel();
        $this->departmentModel = new \App\Models\DepartmentModel();
        $this->designationModel = new \App\Models\DesignationModel();
        $this->authService = new AuthService(service('request'));
    }

    public function create($id = null)
    {
        $jobModel = new JobModel();
        $departmentModel = new \App\Models\DepartmentModel();
        $locationModel = new \App\Models\JoblocationModel();
        $countryModel = new \App\Models\CountryModel();
        $stateModel = new \App\Models\StateModel();
        $cityModel = new \App\Models\CityModel();

        $jobs = $jobModel->findAll();
        $departments = $departmentModel->findAll();
        $locations = $locationModel->findAll();
        $countries = $countryModel->orderBy('country_name', 'ASC')->findAll();
        $states = $stateModel->orderBy('state_name', 'ASC')->findAll();
        $cities = $cityModel->orderBy('city_name', 'ASC')->findAll();

        return view('candidate/candidate', [
            'jobs'        => $jobs,
            'departments' => $departments,
            'locations'   => $locations,
            'countries'   => $countries,
            'states'      => $states,
            'cities'      => $cities,
        ]);
    }

    public function display()
    {
        return view('candidate/view');
    }

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
            'email'          => 'required|valid_email|is_unique[candidate.email]',
            'phone_number'   => 'required',
            'resume'         => 'uploaded[resume]|max_size[resume,2048]|ext_in[resume,pdf,doc,docx]',
        ];

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
                'uploaded' => 'Resume is required. Please upload your resume.',
                'max_size' => 'Resume file size must not exceed 2MB.',
                'ext_in'   => 'Resume must be in PDF, DOC, or DOCX format.',
            ],
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $this->validator->getErrors()
            ], 400);
        }

        // Get the form data
        $data = $this->request->getPost();
        $data['job_date'] = date('Y-m-d');
        if (empty($data['job_id'])) {
            $data['job_id'] = 0; // Use 0 instead of null to avoid 'cannot be null' db errors
        }

        // Location fields from master modules
        $data['country_id'] = !empty($data['country_id']) ? (int)$data['country_id'] : null;
        $data['state_id']   = !empty($data['state_id']) ? (int)$data['state_id'] : null;
        $data['city_id']    = !empty($data['city_id']) ? (int)$data['city_id'] : null;

        if (!empty($data['city_id'])) {
            $cityRow = (new \App\Models\CityModel())->find($data['city_id']);
            if ($cityRow && !empty($cityRow['city_name'])) {
                $data['city'] = $cityRow['city_name'];
            }
        }

        // Handle required file upload
        $resume = $this->request->getFile('resume');
        if ($resume && $resume->isValid() && !$resume->hasMoved()) {
            $filePath = FCPATH . 'uploads/resumes/';
            if (!is_dir($filePath)) {
                mkdir($filePath, 0755, true);
            }
            $newFileName = $resume->getRandomName();
            $resume->move($filePath, $newFileName);
            $data['resume'] = 'uploads/resumes/' . $newFileName;
        } else {
            $data['resume'] = '';
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

        if (!in_array($user->role, ['admin', 'hr', 'branch_admin'], true)) {
            return $this->failForbidden('Forbidden: You do not have access to this resource');
        }

        $candidates = $this->candidateModel->select('
            candidate.id, candidate.job_id, candidate.candidate_name, candidate.email, 
            candidate.phone_number, candidate.resume, candidate.job_date, candidate.status, 
            candidate.city, candidate.city_id, candidate.country_id, candidate.state_id,
            jobs.job_title, jobs.department_id as job_department_id, department.department_name,
            MAX(u.role) as user_role, MAX(ui.employee_id) as current_emp_id, MAX(ui.department_id) as employee_department_id
        ')
            ->join('jobs', 'candidate.job_id = jobs.id', 'left')
            ->join('department', 'department.id = jobs.department_id', 'left')
            ->join('users u', 'u.email = candidate.email AND u.is_deleted = 0', 'left')
            ->join('user_info ui', 'ui.user_id = u.id', 'left')
            ->groupBy('candidate.id')
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
            candidate.status,
            candidate.resume, 
            candidate.notes,
            candidate.job_id,
            candidate.date_of_birth,
            candidate.gender,
            candidate.current_address,
            candidate.city,
            candidate.country_id,
            candidate.state_id,
            candidate.city_id,
            country.country_name,
            states.state_name,
            city.city_name,
            jobs.job_title,
            jobs.department_id as job_department_id,
            department.department_name,
            jobs.post_date, 
            jobs.status as job_status,
            MAX(u.role) as user_role,
            MAX(ui.employee_id) as current_emp_id,
            MAX(ui.department_id) as employee_department_id
        ')
            ->join('jobs', 'candidate.job_id = jobs.id', 'left')
            ->join('department', 'department.id = jobs.department_id', 'left')
            ->join('country', 'country.id = candidate.country_id', 'left')
            ->join('states', 'states.id = candidate.state_id', 'left')
            ->join('city', 'city.id = candidate.city_id', 'left')
            ->join('users u', 'u.email = candidate.email AND u.is_deleted = 0', 'left')
            ->join('user_info ui', 'ui.user_id = u.id', 'left')
            ->groupBy('candidate.id');

        // If an ID is provided, filter by ID; otherwise, get all jobs
        if ($id !== null) {
            $candidate = $query->where('candidate.id', $id)->first();

            if ($candidate) {
                // Construct full URL for resume file
                if (!empty($candidate['resume'])) {
                    $candidate['resume_url'] = base_url($candidate['resume']);
                }
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
            'email'          => 'required|valid_email',
            'phone_number'   => 'required',
        ];
        $resumeFile = $this->request->getFile('resume');
        if ($resumeFile && $resumeFile->isValid() && !$resumeFile->hasMoved()) {
            $validationRules['resume'] = 'max_size[resume,2048]|ext_in[resume,pdf,doc,docx]';
        }

        $validationMessages = [
            'candidate_name' => ['required' => 'Candidate name is required.'],
            'email'          => ['required' => 'Email is required.', 'valid_email' => 'Please enter a valid email address.'],
            'phone_number'   => [
                'required' => 'Phone number is required.',
            ],
            'resume' => [
                'max_size' => 'Resume file size must not exceed 2MB.',
                'ext_in'   => 'Resume must be in PDF, DOC, or DOCX format.',
            ],
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $this->validator->getErrors()
            ], 400);
        }

        // Get the form data
        $data = $this->request->getPost();

        // Fetch existing candidate record
        $candidate = $this->candidateModel->find($id);
        if (!$candidate) {
            return $this->respond(['status' => 'error', 'message' => 'Candidate not found'], ResponseInterface::HTTP_NOT_FOUND);
        }

        // Location fields from master modules
        $data['country_id'] = !empty($data['country_id']) ? (int)$data['country_id'] : null;
        $data['state_id']   = !empty($data['state_id']) ? (int)$data['state_id'] : null;
        $data['city_id']    = !empty($data['city_id']) ? (int)$data['city_id'] : null;

        if (!empty($data['city_id'])) {
            $cityRow = (new \App\Models\CityModel())->find($data['city_id']);
            if ($cityRow && !empty($cityRow['city_name'])) {
                $data['city'] = $cityRow['city_name'];
            }
        }

        // Handle optional file upload for resume
        $resume = $this->request->getFile('resume');
        if ($resume && $resume->isValid() && !$resume->hasMoved()) {
            $filePath = FCPATH . 'uploads/resumes/';
            if (!is_dir($filePath)) {
                mkdir($filePath, 0755, true);
            }
            $newFileName = $resume->getRandomName();
            $resume->move($filePath, $newFileName);
            $data['resume'] = 'uploads/resumes/' . $newFileName;
        } else {
            // Keep existing resume
            unset($data['resume']);
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
                'email'    => $data['email'],
            ];
            $this->userModel->update($userRecord['id'], $userUpdateData);
        }

        // Fetch related user_info record
        $userInfoRecord = $this->userInfoModel->where('email', $candidate['email'])->first();
        if ($userInfoRecord) {
            $userInfoUpdateData = [
                'firstname'      => $data['candidate_name'],
                'email'          => $data['email'],
                'contact_number' => $data['phone_number'],
                'job_id'         => $data['job_id'] ?? $userInfoRecord['job_id'],
                'resume'         => isset($data['resume']) ? $data['resume'] : $userInfoRecord['resume'],
                'address_1'      => $data['current_address'] ?? $userInfoRecord['address_1'],
                'gender'         => $data['gender'] ?? $userInfoRecord['gender'],
                'date_of_birth'  => $data['date_of_birth'] ?? $userInfoRecord['date_of_birth'],
                'country_id'     => $data['country_id'] ?? $userInfoRecord['country_id'],
                'state_id'       => $data['state_id'] ?? $userInfoRecord['state_id'],
                'city_id'        => $data['city_id'] ?? $userInfoRecord['city_id'],
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

        // Check if the user is an admin or hr
        if (!in_array($user->role, ['admin', 'hr'])) {
            return $this->failForbidden('Forbidden: Only Admin or HR can delete candidate records');
        }

        // Check if the candidate has any associated interviews and delete them if present
        $interviews = $this->interviewModel->where('candidate_id', $id)->findAll();
        if (!empty($interviews)) {
            $this->interviewModel->where('candidate_id', $id)->delete();
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

        // Admin, HR, or Branch Admin can access candidate records
        if (!in_array($user->role, ['admin', 'hr', 'branch_admin'], true)) {
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

    /**
     * Generate a guaranteed unique collision-free Employee ID
     */
    public function getGuaranteedUniqueEmployeeId(?string $preferredId = null): string
    {
        $db = \Config\Database::connect();
        $rows = $db->table('user_info')
            ->select('user_info.employee_id, user_info.user_id')
            ->join('users', 'users.id = user_info.user_id')
            ->where('users.is_deleted', 0)
            ->where('user_info.employee_id IS NOT NULL')
            ->where('user_info.employee_id !=', '')
            ->get()
            ->getResultArray();

        $existing = [];

        foreach ($rows as $r) {
            $raw = trim((string)($r['employee_id'] ?? ''));
            if ($raw === '' || $raw === '0') {
                continue;
            }
            $existing[strtolower($raw)] = true;

            if (preg_match('/(\d+)/', $raw, $m)) {
                $num = (int)$m[1];
                $existing['emp-' . str_pad($num, 3, '0', STR_PAD_LEFT)] = true;
                $existing['emp-' . $num] = true;
                $existing[(string)$num] = true;
            }
        }

        $userRows = $db->table('users')->select('id')->where('is_deleted', 0)->get()->getResultArray();
        foreach ($userRows as $u) {
            $uId = (int)$u['id'];
            $existing['emp-' . str_pad($uId, 3, '0', STR_PAD_LEFT)] = true;
            $existing['emp-' . $uId] = true;
            $existing[(string)$uId] = true;
        }

        if (!empty($preferredId)) {
            $cleanPref = strtolower(trim($preferredId));
            if (!isset($existing[$cleanPref])) {
                return trim($preferredId);
            }
        }

        $nextNum = 1;
        while (
            isset($existing['emp-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT)]) ||
            isset($existing['emp-' . $nextNum]) ||
            isset($existing[(string)$nextNum])
        ) {
            $nextNum++;
        }

        return 'EMP-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get candidate data and department/designation options for conversion modal
     * GET /api/candidate/convert-data/(:num)
     */
    public function getConvertData($candidateId = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized access');
        }

        if (!in_array($user->role, ['admin', 'hr', 'branch_admin'], true)) {
            return $this->failForbidden('Forbidden: Access denied');
        }

        $candidate = $this->candidateModel
            ->select('candidate.*, jobs.job_title, jobs.department_id as job_department_id')
            ->join('jobs', 'candidate.job_id = jobs.id', 'left')
            ->find($candidateId);

        if (!$candidate) {
            return $this->failNotFound('Candidate not found');
        }

        // Fetch departments with branch names
        $deptModel = new \App\Models\DepartmentModel();
        $deptBuilder = $deptModel->builder()
            ->select('department.*, branches.name as branch_name')
            ->join('branches', 'branches.id = department.branch_id', 'left')
            ->orderBy('department.department_name', 'ASC');

        if ($user->role === 'branch_admin') {
            $branchId = (int)$this->authService->getBranchId();
            if ($branchId) {
                $deptBuilder->where('department.branch_id', $branchId);
            }
        }

        $departments = $deptBuilder->get()->getResultArray();

        // Fetch all designations with department name
        $designationModel = new \App\Models\DesignationModel();
        $designations = $designationModel->select('designation.*, department.department_name')
            ->join('department', 'department.id = designation.department_id', 'left')
            ->orderBy('designation.designation_name', 'ASC')
            ->findAll();

        $suggestedEmpId = $this->getGuaranteedUniqueEmployeeId();

        $existingUser = $this->userModel->where('email', $candidate['email'])->where('is_deleted', 0)->first();
        $existingInfo = null;
        if ($existingUser) {
            $existingInfo = $this->userInfoModel->where('user_id', $existingUser['id'])->first();
        }

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'candidate'        => $candidate,
                'departments'      => $departments,
                'designations'     => $designations,
                'suggested_emp_id' => $suggestedEmpId,
                'default_date'     => date('Y-m-d'),
                'is_already_emp'   => ($existingUser && $existingUser['role'] === 'employee'),
                'existing_emp_id'  => $existingInfo['employee_id'] ?? null,
            ]
        ]);
    }

    /**
     * Convert Candidate to Employee
     * POST /api/candidate/convert-to-employee
     */
    public function convertToEmployee()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized access');
        }

        if (!in_array($user->role, ['admin', 'hr', 'branch_admin'], true)) {
            return $this->failForbidden('Forbidden: Only Admin, HR, or Branch Admin can convert candidates to employees.');
        }

        $candidateId = (int)$this->request->getPost('candidate_id');
        $candidate = $this->candidateModel->find($candidateId);
        if (!$candidate) {
            return $this->respond(['status' => 'error', 'message' => 'Candidate not found.'], 404);
        }

        $departmentId = (int)$this->request->getPost('department_id');
        if (empty($departmentId)) {
            return $this->respond(['status' => 'error', 'message' => 'Department is required.'], 400);
        }

        $deptModel = new \App\Models\DepartmentModel();
        $department = $deptModel->find($departmentId);
        if (!$department) {
            return $this->respond(['status' => 'error', 'message' => 'Selected department does not exist.'], 400);
        }

        // Branch admin constraint: department must belong to their branch
        if ($user->role === 'branch_admin') {
            $branchId = (int)$this->authService->getBranchId();
            if ((int)($department['branch_id'] ?? 0) !== $branchId) {
                return $this->respond(['status' => 'error', 'message' => 'You can only assign candidates to departments within your branch.'], 403);
            }
        } else {
            $branchId = !empty($department['branch_id']) ? (int)$department['branch_id'] : (int)$this->authService->getBranchId();
        }

        $designationId = !empty($this->request->getPost('designation_id')) ? (int)$this->request->getPost('designation_id') : null;
        $joiningDate   = !empty($this->request->getPost('joining_date')) ? $this->request->getPost('joining_date') : date('Y-m-d');
        $salary        = !empty($this->request->getPost('salary')) ? (float)$this->request->getPost('salary') : 0.00;
        $targetRole    = in_array($this->request->getPost('role'), ['employee', 'department_manager'], true) ? $this->request->getPost('role') : 'employee';
        $location      = $this->request->getPost('working_location') ?: 'On-Site';

        $preferredEmpId = trim((string)$this->request->getPost('employee_id'));
        $empId = $this->getGuaranteedUniqueEmployeeId($preferredEmpId);

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Users table
        $existingUser = $this->userModel->where('email', $candidate['email'])->where('is_deleted', 0)->first();
        if ($existingUser) {
            $userId = (int)$existingUser['id'];
            $userUpdate = [
                'role'          => $targetRole,
                'department_id' => $departmentId,
            ];
            if ($branchId) {
                $userUpdate['branch_id'] = $branchId;
            }
            $this->userModel->update($userId, $userUpdate);
        } else {
            $plainPassword = bin2hex(random_bytes(4));
            $passwordHash  = password_hash($plainPassword, PASSWORD_DEFAULT);
            $userId = $this->userModel->insert([
                'username'      => $candidate['candidate_name'],
                'email'         => $candidate['email'],
                'password'      => $passwordHash,
                'role'          => $targetRole,
                'department_id' => $departmentId,
                'branch_id'     => $branchId,
                'is_deleted'    => 0,
            ]);
        }

        // 2. User Info table
        $fullName = trim($candidate['candidate_name'] ?? '');
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';

        $existingInfo = $this->userInfoModel->where('user_id', $userId)->orWhere('email', $candidate['email'])->first();

        $infoData = [
            'user_id'          => $userId,
            'firstname'        => !empty($existingInfo['firstname']) ? $existingInfo['firstname'] : $firstName,
            'lastname'         => !empty($existingInfo['lastname']) ? $existingInfo['lastname'] : $lastName,
            'email'            => $candidate['email'],
            'contact_number'   => !empty($candidate['phone_number']) ? $candidate['phone_number'] : ($existingInfo['contact_number'] ?? ''),
            'employee_id'      => $empId,
            'department_id'    => $departmentId,
            'designation_id'   => $designationId ?: ($existingInfo['designation_id'] ?? null),
            'joining_date'     => $joiningDate,
            'salary'           => $salary ?: ($existingInfo['salary'] ?? 0.00),
            'role'             => $targetRole,
            'status'           => 'Active',
            'working_location' => $location,
            'date_of_birth'    => !empty($candidate['date_of_birth']) ? $candidate['date_of_birth'] : ($existingInfo['date_of_birth'] ?? null),
            'gender'           => !empty($candidate['gender']) ? $candidate['gender'] : ($existingInfo['gender'] ?? null),
            'address_1'        => !empty($candidate['current_address']) ? $candidate['current_address'] : ($existingInfo['address_1'] ?? null),
            'country_id'       => !empty($candidate['country_id']) ? $candidate['country_id'] : ($existingInfo['country_id'] ?? null),
            'state_id'         => !empty($candidate['state_id']) ? $candidate['state_id'] : ($existingInfo['state_id'] ?? null),
            'city_id'          => !empty($candidate['city_id']) ? $candidate['city_id'] : ($existingInfo['city_id'] ?? null),
            'resume'           => !empty($candidate['resume']) ? $candidate['resume'] : ($existingInfo['resume'] ?? null),
            'job_id'           => !empty($candidate['job_id']) ? $candidate['job_id'] : ($existingInfo['job_id'] ?? null),
        ];

        if ($existingInfo) {
            $this->userInfoModel->update($existingInfo['id'], $infoData);
        } else {
            $this->userInfoModel->insert($infoData);
        }

        // If targetRole is department_manager, link department's manager_id
        if ($targetRole === 'department_manager' && $departmentId) {
            $deptModel->update($departmentId, ['manager_id' => $userId]);
        }

        // 3. Mark candidate as Hired in candidate table
        $this->candidateModel->update($candidateId, [
            'status' => 'Hired',
        ]);

        // 4. Send notification to Admin and HR
        $notificationModel = new \App\Models\NotificationModel();
        $adminHrUsers = $this->userModel->whereIn('role', ['admin', 'hr'])->where('is_deleted', 0)->findAll();
        $deptName = $department['department_name'] ?? 'Department';
        foreach ($adminHrUsers as $adm) {
            $notificationModel->insert([
                'sender_id'    => $user->sub,
                'recipient_id' => $adm['id'],
                'data'         => json_encode([
                    'type'        => 'candidate_converted',
                    'username'    => $candidate['candidate_name'],
                    'message'     => "Candidate '{$candidate['candidate_name']}' has been converted to Employee ({$empId}) in {$deptName}.",
                    'employee_id' => $empId,
                    'user_id'     => $userId,
                ]),
                'is_read'      => 0,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->respond(['status' => 'error', 'message' => 'Database transaction failed. Could not convert candidate.'], 500);
        }

        return $this->respond([
            'status'      => 'success',
            'message'     => "Candidate '{$candidate['candidate_name']}' successfully converted to Employee ({$empId}) in {$deptName}!",
            'employee_id' => $empId,
            'user_id'     => $userId,
        ]);
    }
}
