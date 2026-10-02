<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\InterviewModel;
use App\Models\CandidateModel;
use App\Models\JobModel;
use App\Models\UserModel;
use App\Services\AuthService;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\EmailService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InterviewController extends ResourceController
{
    private $interviewModel;
    private $authService;

    public function __construct()
    {
        $this->interviewModel = new InterviewModel();
        $this->authService = new AuthService(service('request'));
    }

    public function create()
    {
        // Validate user authorization
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }
        if (!in_array($user->role, ['admin', 'hr'])) {
            return $this->failForbidden('Forbidden: You do not have access to this resource');
        }


        // Validate input data
        $validationRules = [
            'full_name'      => 'required',
            'email'          => 'required|valid_email',
            'mobile_number'  => 'required',
            'schedule_date'  => 'permit_empty|valid_date', // Adjusted to not strictly require schedule_date since it's on step 6
        ];
        $validationMessages = [
            'full_name' => [
                'required' => 'Full name is required.',
            ],
            'email' => [
                'required' => 'Email is required.',
                'valid_email' => 'Please provide a valid email.',
            ],
            'mobile_number' => [
                'required' => 'Phone number is required.',
            ],
            // 'description' => [
            //     'required' => 'Description is required.',
            //     'string'   => 'Description must be a valid text.'
            // ],
            // 'status' => [
            //     'required' => 'The status field is required.',
            //     'string'   => 'The status must be a valid text.'
            // ],
            'schedule_date' => [
                'required'   => 'Schedule date field is required.',
                'valid_date' => 'Schedule date must be a valid date.',
                'after_created_at' => 'Schedule date must be after today.',
            ],
        ];


        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ], 400);
        }

        // Get form data
        $data = $this->request->getPost();
        if (!isset($data['status']) || empty($data['status'])) {
            if (!empty($data['selection_status'])) {
                $data['status'] = $data['selection_status'];
            } elseif (!empty($data['interview_status'])) {
                $data['status'] = $data['interview_status'];
            } else {
                $data['status'] = 'scheduled';
            }
        }
        $userInfoModel = new \App\Models\UserInfoModel();
        $candidateModel = new \App\Models\CandidateModel();
        
        $candidateName = $data['full_name'] ?? 'Unknown Candidate';

        // If candidate_id is provided, do the linked checks
        if (!empty($data['candidate_id'])) {
            // Fetch job_id from candidate table based on selected candidate_id
            $candidate = $candidateModel->select('id,job_id,email,candidate_name')->where('id', $data['candidate_id'])->first();

            if (!$candidate) {
                return $this->respond([
                    'status'  => 'error',
                    'message' => 'Invalid Candidate ID'
                ], 400);
            }
            $existingInterview = $this->interviewModel->where('candidate_id', $data['candidate_id'])
                ->where('job_id', $candidate['job_id'])
                ->whereIn('status', ['scheduled', 'completed']) // Check both statuses
                ->first();

            if ($existingInterview) {
                return $this->respond([
                    'status'  => 'error',
                    'message' => 'This candidate is already scheduled for an interview.'
                ], 400);
            }
            $userInfo = $userInfoModel->where('email', $candidate['email'])->first();
            if (!$userInfo) {
                return $this->respond([
                    'status'  => 'error',
                    'message' => 'No matching user found in userinfo table'
                ], 400);
            }

            $data['job_id'] = $candidate['job_id']; // Automatically set job_id
            $candidateName = $candidate['candidate_name'];
        } else {
            // Create a new candidate if none was selected
            $newCandidateId = $candidateModel->insert([
                'candidate_name' => $data['full_name'],
                'email'          => $data['email'],
                'phone_number'   => $data['mobile_number']
            ]);
            $data['candidate_id'] = $newCandidateId;
        }

        $data['created_by'] = $user->sub;

        // Extract multiple entries arrays
        $educations = $data['education'] ?? [];
        $experiences = $data['experience'] ?? [];
        $rounds = $data['rounds'] ?? [];
        $total_score = $data['total_score'] ?? 0;
        $data['interview_score'] = $total_score;
        unset($data['education'], $data['experience'], $data['rounds'], $data['total_score']);

        // Insert the interview entry
        if ($this->interviewModel->insert($data)) {

            if (!empty($data['candidate_id']) && isset($userInfo)) {
                $updated = $userInfoModel
                    ->where('id', $userInfo['id']) // Match the userinfo ID
                    ->set(['status' => 'scheduled'])
                    ->update();

                if (!$updated) {
                    return $this->respond([
                        'status'  => 'error',
                        'message' => 'Failed to update userinfo status to scheduled'
                    ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            // Save Educations
            $eduModel = new \App\Models\CandidateEducationModel();
            foreach ($educations as $edu) {
                if (empty($edu['degree']) && empty($edu['course']) && empty($edu['university']) && empty($edu['passing_year']) && empty($edu['percentage'])) {
                    continue;
                }
                $edu['candidate_id'] = $data['candidate_id'];
                $eduModel->insert($edu);
            }

            // Save Experiences
            $expModel = new \App\Models\CandidateExperienceModel();
            foreach ($experiences as $exp) {
                if (empty($exp['company']) && empty($exp['role']) && empty($exp['total_experience']) && empty($exp['last_salary']) && empty($exp['notice_period']) && empty($exp['reason_for_leaving'])) {
                    continue;
                }
                $exp['candidate_id'] = $data['candidate_id'];
                $expModel->insert($exp);
            }

            // Save Rounds
            $interviewId = $this->interviewModel->getInsertID();
            $roundModel = new \App\Models\InterviewRoundModel();
            foreach ($rounds as $round) {
                if (empty($round['interviewer_id'])) {
                    continue;
                }
                $round['interview_id'] = $interviewId;
                $roundModel->insert($round);
            }

            $notificationModel = new \App\Models\NotificationModel();
            $userModel = new \App\Models\UserModel();

            $sender = $userModel->find($user->sub);

            // Get Admin and HR users
            $recipients = $userModel->whereIn('role', ['admin', 'hr'])->findAll();

            foreach ($recipients as $recipient) {
                $notificationModel->insert([
                    'sender_id'    => $user->sub,
                    'recipient_id' => $recipient['id'],
                    'data'         => json_encode([
                        'message'  => 'New interview scheduled for candidate: ' . $candidateName,
                        'type'     => 'interview',
                        'username' => $sender['username'],
                        'candidate_id'  => $data['candidate_id'] ?? null,
                        'candidate_name' => $candidateName
                    ]),
                    'is_read' => 0
                ]);
            }

            //Send welcome email
            $emailService = new EmailService();
            $emailService->sendInterviewEmail($data);
            
            if (isset($data['convert_to_employee']) && $data['convert_to_employee'] == 1 && isset($data['branch_id']) && isset($data['department_id'])) {
                $this->_processEmployeeConversion($interviewId, $data['branch_id'], $data['department_id']);
            }

            return $this->respond([
                'status'  => 'success',
                'message' => 'Interview created successfully'
            ], ResponseInterface::HTTP_CREATED);
        }

        return $this->respond([
            'status'  => 'error',
            'message' => 'Failed to create Interview entry'
        ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
    }


    public function getAll()
    {
        // Validate user authorization
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }
        if (!in_array($user->role, ['admin', 'hr'])) {
            return $this->failForbidden('Forbidden: You do not have access to this resource');
        }
        // Retrieve onboarding entries
        $interviews = $this->interviewModel->select('interviews.id, COALESCE(jobs.job_title, interviews.position_applied_for) as job_title, interviews.status, interviews.selection_status, interviews.schedule_date , COALESCE(candidate.candidate_name, interviews.full_name) as candidate_name, interviews.convert_to_employee')
            ->join('candidate', 'interviews.candidate_id = candidate.id', 'left')
            ->join('jobs', 'interviews.job_id = jobs.id', 'left')
            ->orderBy('interviews.created_at', 'DESC')
            ->findAll();
        return $this->respond(['status' => 'success', 'data' => $interviews]);
    }

    public function get($id = null)
    {
        // Validate user authorization
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $record = $this->interviewModel->find($id);
        if ($record) {
            if (!empty($record['candidate_id'])) {
                $eduModel = new \App\Models\CandidateEducationModel();
                $expModel = new \App\Models\CandidateExperienceModel();
                $record['educations'] = $eduModel->where('candidate_id', $record['candidate_id'])->findAll();
                $record['experiences'] = $expModel->where('candidate_id', $record['candidate_id'])->findAll();
            } else {
                $record['educations'] = [];
                $record['experiences'] = [];
            }
            
            $roundModel = new \App\Models\InterviewRoundModel();
            $record['rounds'] = $roundModel->where('interview_id', $record['id'])->findAll();

            return $this->respond(['status' => 'success', 'data' => $record]);
        }

        return $this->respond(['status' => 'error', 'message' => 'interview not found'], ResponseInterface::HTTP_NOT_FOUND);
    }


    // Update Interview
    public function update($id = null)
    {
        // Validate user authorization
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Get input data for update
        $data = $this->request->getPost();

        // Validate input data
        if (empty($data) || !is_array($data)) {
            return $this->respond(['status' => 'error', 'message' => 'No data provided to update'], ResponseInterface::HTTP_BAD_REQUEST);
        }

        $educations = $data['education'] ?? [];
        $experiences = $data['experience'] ?? [];
        $rounds = $data['rounds'] ?? [];
        $total_score = isset($data['total_score']) ? $data['total_score'] : 0;
        $data['interview_score'] = $total_score;
        unset($data['education'], $data['experience'], $data['rounds'], $data['total_score']);

        // Filter out empty values but keep 0
        $data = array_filter($data, fn ($value) => $value !== '' && $value !== null);

        // Check if the Interview entry exists
        $entry = $this->interviewModel->find($id);
        if (!$entry) {
            return $this->respond(['status' => 'error', 'message' => 'Interview entry not found'], ResponseInterface::HTTP_NOT_FOUND);
        }

        // Update the Interview entry
        if ($this->interviewModel->update($id, $data)) {
            $candidate_id = $data['candidate_id'] ?? $entry['candidate_id'];
            
            if ($candidate_id) {
                $eduModel = new \App\Models\CandidateEducationModel();
                $eduModel->where('candidate_id', $candidate_id)->delete();
                foreach ($educations as $edu) {
                    if (!empty($edu['degree']) || !empty($edu['course']) || !empty($edu['university'])) {
                        $edu['candidate_id'] = $candidate_id;
                        $eduModel->insert($edu);
                    }
                }

                $expModel = new \App\Models\CandidateExperienceModel();
                $expModel->where('candidate_id', $candidate_id)->delete();
                foreach ($experiences as $exp) {
                    if (!empty($exp['company']) || !empty($exp['role'])) {
                        $exp['candidate_id'] = $candidate_id;
                        $expModel->insert($exp);
                    }
                }
            }

            $roundModel = new \App\Models\InterviewRoundModel();
            $roundModel->where('interview_id', $id)->delete();
            foreach ($rounds as $round) {
                if (!empty($round['interviewer_id'])) {
                    $round['interview_id'] = $id;
                    $roundModel->insert($round);
                }
            }

            //Send welcome email
            $emailService = new EmailService();
            $emailService->sendInterviewEmail($data);
            
            if (isset($data['convert_to_employee']) && $data['convert_to_employee'] == 1 && isset($data['branch_id']) && isset($data['department_id'])) {
                $this->_processEmployeeConversion($id, $data['branch_id'], $data['department_id']);
            }

            return $this->respond(['status' => 'success', 'message' => 'Interview entry updated successfully']);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to update Interview entry'], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
    }

    // Delete Interview
    public function delete($id = null)
    {
        // Validate user authorization
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }
        if (!in_array($user->role, ['admin', 'hr'])) {
            return $this->failForbidden('Forbidden: Only Admin or HR can delete interview records');
        }

        $interview = $this->interviewModel->find($id);

        if (!$interview) {
            return $this->failNotFound('Interview record not found');
        }

        $status = strtolower($interview['status']);

        // Both 'cancelled', 'scheduled', and 'completed' interviews may be deleted

        // Both 'cancelled' and 'completed' interviews may be deleted
        // (Frontend already shows a strong warning for completed interviews)
        if ($this->interviewModel->delete($id)) {
            return $this->respond(['status' => 'success', 'message' => 'Interview deleted successfully']);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to delete interview'], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
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

        $record = $this->interviewModel->find($id);
        if ($record) {
            if (!empty($record['candidate_id'])) {
                $eduModel = new \App\Models\CandidateEducationModel();
                $expModel = new \App\Models\CandidateExperienceModel();
                $record['educations'] = $eduModel->where('candidate_id', $record['candidate_id'])->findAll();
                $record['experiences'] = $expModel->where('candidate_id', $record['candidate_id'])->findAll();
            } else {
                $record['educations'] = [];
                $record['experiences'] = [];
            }
            return $this->respond(['status' => 'success', 'data' => $record]);
        }

        return $this->respond(['status' => 'error', 'message' => 'Interview type not found'], 404);
    }
    public function creates($id = null)
    {
        $interviewModel = new \App\Models\InterviewModel();
        $existingCandidateIds = $interviewModel->select('candidate_id')->where('candidate_id IS NOT NULL')->findAll();
        $existingIds = array_column($existingCandidateIds, 'candidate_id');

        if ($id !== null) {
            $currentInterview = $interviewModel->find($id);
            if ($currentInterview && $currentInterview['candidate_id']) {
                $existingIds = array_diff($existingIds, [$currentInterview['candidate_id']]);
            }
        }

        $candidateModel = new CandidateModel();
        if (!empty($existingIds)) {
            $candidates = $candidateModel->whereNotIn('id', $existingIds)->groupBy('email')->findAll();
        } else {
            $candidates = $candidateModel->groupBy('email')->findAll();
        }
        $userModel = new UserModel();
        $interviewers = $userModel->whereIn('role', ['admin', 'hr', 'employee'])->findAll();

        $jobModel = new \App\Models\JobModel();
        $jobs = $jobModel->findAll();

        $departmentModel = new \App\Models\DepartmentModel();
        $departments = $departmentModel->findAll();

        $branchModel = new \App\Models\BranchModel();
        $branches = $branchModel->findAll();

        return view('interview/interviews', [
            'candidates' => $candidates,
            'interviewers' => $interviewers,
            'jobs' => $jobs,
            'departments' => $departments,
            'branches' => $branches
        ]);
    }


    public function display()
    {
        $departmentModel = new \App\Models\DepartmentModel();
        $branchModel = new \App\Models\BranchModel();
        
        $data = [
            'departments' => $departmentModel->findAll(),
            'branches' => $branchModel->findAll(),
        ];
        return view('interview/view', $data);
    }

    // public function edits($id)
    // {
    //     $interviewModel = new \App\Models\InterviewModel(); 
    //     $interview = $interviewModel->find($id); 


    //     if (!$interview) {
    //         return redirect()->to('/interviews')->with('error', 'Interview not found');
    //     }

    //     return view('interview/interviews', ['interview' => $interview]);
    // }
    public function singlejob($id = null)
    {

        return view('interview/display');
    }
    public function getCandidateJob($candidate_id)
    {
        // Initialize CandidateModel and JobsModel
        $candidateModel = new \App\Models\CandidateModel();
        $jobModel = new \App\Models\JobModel(); // Make sure JobsModel is created

        // Fetch job_id from candidate table
        $candidate = $candidateModel->select('job_id')->where('id', $candidate_id)->first();

        if ($candidate && isset($candidate['job_id'])) {
            // Fetch job title from jobs table using job_id
            $job = $jobModel->select('job_title')->where('id', $candidate['job_id'])->first();

            if ($job && isset($job['job_title'])) {
                return $this->respond([
                    'status' => 'success',
                    'job_title' => $job['job_title'] // Return job title instead of job_id
                ]);
            }

            return $this->respond([
                'status'  => 'error',
                'message' => 'Job not found for the selected candidate'
            ], 404);
        }

        return $this->respond([
            'status'  => 'error',
            'message' => 'Candidate not found or job_id missing'
        ], 404);
    }
    public function updateStatus($id)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $status = $this->request->getJSON()->status ?? null;
        if (!in_array($status, ['scheduled', 'completed', 'cancelled'])) {
            return $this->failValidationErrors('Invalid status provided');
        }

        // Find interview record
        $interview = $this->interviewModel->find($id);
        if (!$interview) {
            return $this->failNotFound('Interview not found');
        }

        // Prevent update if already completed
        if ($interview['status'] === 'completed') {
            return $this->respond([
                'status'  => 'error',
                'message' => 'The interview has already been completed and cannot be changed.'
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        // Update the status
        if ($this->interviewModel->update($id, ['status' => $status])) {
            return $this->respond(['status' => 'success', 'message' => 'Interview status updated successfully']);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to update status'], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function updateConvertToEmployee($id)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $payload = $this->request->getJSON();
        $convertToEmployee = $payload->convert_to_employee ?? 0;
        $branchId = $payload->branch_id ?? null;
        $departmentId = $payload->department_id ?? null;
        
        $interview = $this->interviewModel->find($id);
        if (!$interview) {
            return $this->failNotFound('Interview not found');
        }

        // If converting to employee, update/create the user
        if ($convertToEmployee == 1) {
            if (!$branchId || !$departmentId) {
                return $this->respond(['status' => 'error', 'message' => 'Branch and Department are required to convert to employee'], 400);
            }
            $this->_processEmployeeConversion($id, $branchId, $departmentId);
        }

        if ($this->interviewModel->update($id, ['convert_to_employee' => $convertToEmployee])) {
            return $this->respond(['status' => 'success', 'message' => 'Convert to Employee updated successfully']);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to update convert status'], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function _processEmployeeConversion($interviewId, $branchId, $departmentId)
    {
        $interview = $this->interviewModel->find($interviewId);
        if (!$interview) return false;

        $userModel = new \App\Models\UserModel();
        $userInfoModel = new \App\Models\UserInfoModel();

        // Check if user already exists
        $existingUser = $userModel->where('email', $interview['email'])->first();
        
        $userId = null;
        if (!$existingUser) {
            // Insert into users
            $userData = [
                'username' => $interview['full_name'] ?? $interview['candidate_name'] ?? 'Employee',
                'email' => $interview['email'],
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'employee',
                'branch_id' => $branchId,
                'department_id' => $departmentId
            ];
            $userId = $userModel->insert($userData);
        } else {
            $userId = $existingUser['id'];
            // Update their role to employee
            $userModel->update($userId, [
                'role' => 'employee',
                'branch_id' => $branchId,
                'department_id' => $departmentId
            ]);
        }

        // Upsert into user_info
        $existingInfo = $userInfoModel->where('user_id', $userId)->first();
        $nameParts = explode(' ', ($interview['full_name'] ?? $interview['candidate_name'] ?? 'Employee'), 2);
        $userInfoData = [
            'user_id' => $userId,
            'firstname' => $nameParts[0] ?? '',
            'lastname' => $nameParts[1] ?? '',
            'email' => $interview['email'],
            'gender' => $interview['gender'] ?? '',
            'date_of_birth' => $interview['date_of_birth'] ?? null,
            'address_1' => $interview['current_address'] ?? '',
            'contact_number' => $interview['mobile_number'] ?? '',
            'department_id' => $departmentId,
            'joining_date' => $interview['joining_date'] ?? null,
            'job_id' => $interview['job_id'] ?? null,
            'salary' => $interview['offered_salary'] ?? null,
            'status' => 'active'
        ];
        
        if (!$existingInfo) {
            $userInfoModel->insert($userInfoData);
        } else {
            $userInfoModel->update($existingInfo['id'], $userInfoData);
        }
        
        return true;
    }

    /**
     * Export Interviews to styled Excel (.xlsx)
     */
    public function exportExcel()
    {
        $user = $this->authService->user();
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Unauthorized']);
        }

        $status = $this->request->getGet('status');
        $search = trim((string)$this->request->getGet('search'));

        $builder = $this->interviewModel->builder();
        $builder->select('interviews.*, candidate.candidate_name, candidate.email, candidate.phone_number, jobs.job_title, department.department_name')
            ->join('candidate', 'candidate.id = interviews.candidate_id', 'left')
            ->join('jobs', 'jobs.id = COALESCE(interviews.job_id, candidate.job_id)', 'left')
            ->join('department', 'department.id = jobs.department_id', 'left');

        if (!empty($status)) {
            $builder->where('interviews.status', $status);
        }
        if (!empty($search)) {
            $builder->groupStart()
                ->like('candidate.candidate_name', $search)
                ->orLike('candidate.email', $search)
                ->orLike('candidate.phone_number', $search)
                ->orLike('jobs.job_title', $search)
                ->orLike('department.department_name', $search)
                ->orLike('interviews.description', $search)
                ->groupEnd();
        }

        $records = $builder->orderBy('interviews.id', 'DESC')->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Interviews');

        $headers = [
            'A1' => 'S.No',
            'B1' => 'Candidate Name',
            'C1' => 'Email',
            'D1' => 'Phone Number',
            'E1' => 'Job Position',
            'F1' => 'Department',
            'G1' => 'Scheduled Date & Time',
            'H1' => 'Status',
            'I1' => 'Description / Remarks',
            'J1' => 'Created At'
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
            $sheet->setCellValue('G' . $rowNum, !empty($item['schedule_date']) ? date('Y-m-d H:i', strtotime($item['schedule_date'])) : '-');
            $sheet->setCellValue('H' . $rowNum, ucfirst($item['status'] ?? 'Scheduled'));
            $sheet->setCellValue('I' . $rowNum, strip_tags($item['description'] ?? '-'));
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

        $filename = 'Interviews_' . date('Y_m_d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
