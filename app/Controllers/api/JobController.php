<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use App\Models\JobModel;
use App\Models\JoblocationModel;
use App\Models\JobLocationAddressModel;
use App\Services\AuthService;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\DepartmentModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class JobController extends ResourceController
{
    private $jobModel;
    private $joblocationModel;
    private $jobLocationAddressModel;
    private $authService;

    public function __construct()
    {
        $this->jobModel = new JobModel();
        $this->joblocationModel = new JoblocationModel();
        $this->jobLocationAddressModel = new JobLocationAddressModel();
        $this->authService = new AuthService(service('request'));
    }
    public function creates()
    {
        $joblocationModel = new JoblocationModel();
        $cityModel = new \App\Models\CityModel();
        $countryModel = new \App\Models\CountryModel();
        $locations = $joblocationModel->findAll();
        $cities = $cityModel->findAll();
        $countries = $countryModel->findAll();
        $departmentModel = new DepartmentModel();
        $departments = $departmentModel->findAll();
        $joblocationModel = new JoblocationModel();
        $located = $joblocationModel->findAll();
        // $jobLocationAddressModel = new JobLocationAddressModel();
        // $address = $jobLocationAddressModel->findAll();
        return view('job/job', [
            'departments' => $departments,
            'locations' => $locations,
            'located' => $located,
            'cities' => $cities,
            'countries' => $countries,
        ]);
        //  return view('job/multistepjob', ['departments' => $departments, 'locations' => $locations]);
    }
    public function getAddressesByLocation()
    {
        $locationId = $this->request->getPost('location_id');

        $jobLocationAddressModel = new JobLocationAddressModel();
        $addresses = $jobLocationAddressModel->where('locations_id', $locationId)->findAll();

        return $this->response->setJSON($addresses);
    }
    public function getAddresses()
    {
        $locationId = $this->request->getPost('location_id');

        $jobLocationAddressModel = new JobLocationAddressModel();
        $addresses = $jobLocationAddressModel->where('locations_id', $locationId)->findAll();

        return $this->response->setJSON($addresses);
    }
    // Display job page (view)
    public function display()
    {
        return view('job/view');
    }
    public function applyjob()
    {
        return view('job/applyjob');
    }
    public function singlejob($id = null)
    {
        return view('job/display');
    }
    //Create job listing
    public function create()
    {
        // Authenticate the user
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Only admin and HR can create jobs
        if (!in_array($user->role, ['admin', 'hr'])) {
            return $this->failForbidden('Forbidden: You do not have access to this resource');
        }

        // Retrieve and validate input data
        $validationRules = [
            'job_title'    => 'required|min_length[3]|max_length[255]',
            // 'description'  => 'required',
            'department_id' => 'required|integer',
            'addresses_id' => 'required',
            // 'status'       => 'required|in_list[open,close]',
            'locations_id'     => 'required|max_length[255]',
            'age'          => 'required', // Age must be at least 18
            'job_type'     => 'required|in_list[full,part]',
            'experience'   => 'required|integer',
            'salary_range' => 'required|max_length[20]',
            // 'post_date'    => 'required|valid_date',
            'close_date'   => 'required|valid_date|check_close_date[post_date]', // Custom rule
            'gender'       => 'required|in_list[male,female,both]' // Validate gender
        ];

        $validationMessages = [
            'job_title' => [
                'required'   => 'job title field is required.',
                'min_length' => 'job title must be at least 3 characters long.',
                'max_length' => 'job title must not exceed 255 characters.',
            ],
            // 'description' => [
            //     'required' => 'The job description field is required.',
            // ],
            'department_id' => [
                'required' => 'department field is required.',
                'integer'  => 'department ID must be an integer.',
            ],
            'addresses_id' => [
                'required' => 'Address field is required.',

            ],
            // 'status' => [
            //     'required' => 'The status field is required.',
            //     'in_list'  => 'The status must be either "open" or "close".',
            // ],
            'locations_id' => [
                'required'   => 'location field is required.',
                'max_length' => 'location must not exceed 255 characters.',
            ],
            'age' => [
                'required'  => 'age field is required.',

            ],
            'job_type' => [
                'required' => 'job type field is required.',
                'in_list'  => 'job type must be either "full" or "part".',
            ],
            'experience' => [
                'required' => 'experience field is required.',
                'integer'  => 'experience must be a number.',
            ],
            'salary_range' => [
                'required'   => 'salary range field is required.',
                'max_length' => 'salary range must not exceed 20 characters.',
            ],

            // 'close_date' => [
            //     'required'   => 'close date field is required.',
            //     'valid_date' => 'close date must be a valid date in YYYY-MM-DD format.',
            // ],
            'close_date' => [
                'required'   => 'Close date is required.',
                'valid_date' => 'Close date must be a valid date in YYYY-MM-DD format.',
                'check_close_date' => 'Close date must be after today date.' // Custom message
            ],
            'gender' => [
                'required' => 'gender field is required.',
                'in_list'  => 'The gender must be either "male", "female", or "both".',
            ]
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ], 400);
        }

        // Get request data
        $data = $this->request->getPost();
        $data['created_by'] = $user->sub;
        if (!isset($data['status']) || empty($data['status'])) {
            $data['status'] = 'open';
        }
        if (!isset($data['post_date']) || empty($data['post_date'])) {
            $data['post_date'] = date('Y-m-d'); // Set today's date as default
        }

        // Insert into database
        if ($this->jobModel->insert($data)) {
            $notificationModel = new \App\Models\NotificationModel();
            $userModel = new \App\Models\UserModel();

            // Get sender details
            $sender = $userModel->find($user->sub);

            // Get all admins and HRs
            $recipients = $userModel->whereIn('role', ['admin', 'hr'])->findAll();

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
                        'username' => $sender['username'], // ✅ sender username
                        'type'     => 'job',
                        'message'  => 'New job posted: ' . $data['job_title']
                    ]),
                    'is_read' => 0
                ]);
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Job record added successfully'
            ], 201);
        }

        return $this->respond([
            'status' => 'error',
            'message' => 'Failed to add job record'
        ], 500);
    }

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

        $departmentId = $this->request->getGet('department_id'); // Get the department_id from query params

        $builder = $this->jobModel->select('jobs.id, jobs.job_title, jobs.job_type, department.department_name, jobs.post_date, jobs.salary_range')
            ->join('department', 'jobs.department_id = department.id');

        // Apply filter if department_id is provided
        if (!empty($departmentId)) {
            $builder->where('jobs.department_id', $departmentId);
        }

        $builder->orderBy('jobs.created_at', 'DESC');
        $jobs = $builder->findAll();

        return $this->respond(['status' => 'success', 'data' => $jobs]);
    }


    // Get a specific job by ID
    public function get($id = null)
    {
        // Check if the user is authorized
        if (!$this->authService->check()) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Initialize query
        $query = $this->jobModel->select('
        jobs.id, 
        jobs.job_title, 
        jobs.job_type, 
        department.department_name, 
        jobs.description,
        jobs.post_date, 
        jobs.salary_range, 
        jobs.close_date, 
        jobs.status, 
        job_location.job_location,
        job_location_addresses.address,
        job_location_addresses.postal_code,
        city.city_name,
        country.country_name,
        jobs.age,
        jobs.gender,  
        jobs.experience
    ')
            ->join('department', 'jobs.department_id = department.id', 'left')
            ->join('job_location', 'jobs.locations_id = job_location.location_id', 'left')
            ->join('job_location_addresses', 'jobs.addresses_id = job_location_addresses.address_id')
            ->join('city', 'job_location_addresses.city_id = city.id', 'left')
            ->join('country', 'job_location_addresses.country_id = country.id', 'left');

        // If an ID is provided, filter by ID; otherwise, get all jobs
        if ($id !== null) {
            $job = $query->where('jobs.id', $id)->first();

            if ($job) {
                return $this->respond(['status' => 'success', 'data' => $job]);
            }

            return $this->respond(['status' => 'error', 'message' => 'Job not found'], ResponseInterface::HTTP_NOT_FOUND);
        }

        // Fetch all jobs if no ID is provided
        $jobs = $query->findAll();

        return $this->respond(['status' => 'success', 'data' => $jobs]);
    }


    // Update a job listing
    public function update($id = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        if (!in_array($user->role, ['admin', 'hr'])) {
            return $this->failForbidden('Forbidden: You do not have access to this resource');
        }

        $data = $this->request->getPost();

        // Add gender validation
        $validationRules = [
            'job_title'    => 'required|min_length[3]|max_length[255]',
            'department_id' => 'required',
            'locations_id' => 'required|max_length[255]',
            'job_type'     => 'required|in_list[full,part]',
            'experience'   => 'required|integer',
            'close_date'   => 'required|valid_date',
            'gender'       => 'required|in_list[male,female,both]', // ✅ Add gender validation
        ];

        $validationMessages = [
            'job_title' => [
                'required'   => 'The job title field is required.',
                'min_length' => 'The job title must be at least 3 characters long.',
                'max_length' => 'The job title must not exceed 255 characters.',
            ],
            'department_id' => [
                'required' => 'The department field is required.',

            ],
            'locations_id' => [
                'required'   => 'The location field is required.',
                'max_length' => 'The location must not exceed 255 characters.',
            ],
            'job_type' => [
                'required' => 'The job type field is required.',
                'in_list'  => 'The job type must be either "full" or "part".',
            ],
            'experience' => [
                'required' => 'The experience field is required.',
                'integer'  => 'The experience must be a number.',
            ],
            'close_date' => [
                'required'   => 'The close date field is required.',
                'valid_date' => 'The close date must be a valid date in YYYY-MM-DD format.',
            ],
            'gender' => [
                'required' => 'The gender field is required.',
                'in_list'  => 'The gender must be either "male", "female", or "both".',
            ],
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ], 400);
        }
        $gender = isset($data['gender']) ? $data['gender'] : null;
        // Ensure gender is included in update data
        $updateData = [
            'job_title'    => $data['job_title'],
            'description' => $data['description'],
            'department_id' => $data['department_id'],
            'locations_id' => $data['locations_id'],
            'job_type'     => $data['job_type'],
            'experience'   => $data['experience'],
            'close_date'   => $data['close_date'],
            'age' => $data['age'],
            'salary_range' => $data['salary_range'],

            'gender'       => $gender, // ✅ Ensure gender is included
        ];

        if ($this->jobModel->update($id, $updateData)) {
            return $this->respond(['status' => 'success', 'message' => 'Job updated successfully']);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to update job'], 500);
    }

    // Delete a job
    public function delete($id = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        if ($user->role !== 'admin') {
            return $this->failForbidden('Forbidden: Only Admin can delete job records');
        }

        // Verify the job exists
        $job = $this->jobModel->find($id);
        if (!$job) {
            return $this->respond(['status' => 'error', 'message' => 'Job not found'], 404);
        }

        // Safely unlink candidates — set their job_id to 0 (do NOT delete them)
        $candidateModel = new \App\Models\CandidateModel();
        $candidateModel->where('job_id', $id)->set(['job_id' => 0])->update();

        // Safely unlink interviews — set their job_id to 0 (do NOT delete them)
        $interviewModel = new \App\Models\InterviewModel();
        $interviewModel->where('job_id', $id)->set(['job_id' => 0])->update();

        // Safely unlink onboarding records — set their job_id to 0 (do NOT delete them)
        $onboardingModel = new \App\Models\OnboardingModel();
        $onboardingModel->where('job_id', $id)->set(['job_id' => 0])->update();

        // Now safely delete the job
        if ($this->jobModel->delete($id)) {
            return $this->respond(['status' => 'success', 'message' => 'Job deleted successfully. Related candidates and records have been unlinked.']);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to delete job record'], 500);
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

        $record = $this->jobModel
            ->select('jobs.*,department.department_name')
            ->join('department', 'department.id = jobs.department_id', 'left')
            ->where('jobs.id', $id)
            ->first();
        if ($record) {
            return $this->respond(['status' => 'success', 'data' => $record]);
        }

        return $this->respond(['status' => 'error', 'message' => 'job type not found'], 404);
    }
    public function applyget()
    {
        if (!$this->authService->check()) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }
        // Fetch all jobs with related details
        $jobs = $this->jobModel->select('
            jobs.id, 
            jobs.job_title, 
            jobs.job_type, 
            department.department_name, 
            jobs.description,
            jobs.post_date, 
            jobs.salary_range, 
            jobs.close_date, 
            jobs.status, 
            job_location.job_location,
            job_location_addresses.address,
            job_location_addresses.postal_code,
            city.city_name,
            country.country_name,
            jobs.age,
            jobs.gender,  
            jobs.experience
        ')
            ->join('department', 'jobs.department_id = department.id', 'left')
            ->join('job_location', 'jobs.locations_id = job_location.location_id', 'left')
            ->join('job_location_addresses', 'jobs.addresses_id = job_location_addresses.address_id', 'left')
            ->join('city', 'job_location_addresses.city_id = city.id', 'left')
            ->join('country', 'job_location_addresses.country_id = country.id', 'left')
            ->findAll();

        // Check if jobs exist
        if (!$jobs) {
            return $this->respond(['status' => 'error', 'message' => 'No jobs found'], ResponseInterface::HTTP_NOT_FOUND);
        }

        return $this->respond(['status' => 'success', 'data' => $jobs]);
    }

    public function addAddress()
    {
        $addressModel = new JobLocationAddressModel();

        $data = [
            'locations_id' => $this->request->getPost('locations_id'),
            'address' => $this->request->getPost('address'),
            'state' => $this->request->getPost('state'),
            'city_id' => $this->request->getPost('city_id'),
            'country_id' => $this->request->getPost('country_id'),
            'postal_code' => $this->request->getPost('postal_code'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $insertId = $addressModel->insert($data);

        if ($insertId) {
            $data['address_id'] = $insertId;
            return $this->response->setJSON(['status' => 'success', 'data' => $data]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to add address']);
        }
    }
    public function add()
    {
        $locationModel = new JoblocationModel();

        // Validate input
        $validation = \Config\Services::validation();
        $validation->setRules([
            'job_location' => [
                'label' => 'Job Location',
                'rules' => 'required|min_length[2]|max_length[100]',
                'errors' => [
                    'required' => 'Job Location is required.',
                    'min_length' => 'Job Location must be at least 2 characters.',
                    'max_length' => 'Job Location must not exceed 100 characters.'
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            // Validation failed
            return $this->response->setJSON([
                'success' => false,
                'message' => $validation->getError('job_location') // Get the specific error
            ]);
        }

        // Passed validation, insert into database
        $data = [
            'job_location' => $this->request->getPost('job_location'),
        ];

        if ($locationModel->insert($data)) {
            $insertedId = $locationModel->insertID();

            return $this->response->setJSON([
                'success' => true,
                'location' => [
                    'id' => $insertedId,
                    'job_location' => $data['job_location']
                ]
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to add location. Please try again.'
            ]);
        }
    }

    /**
     * Export Jobs to styled Excel (.xlsx)
     */
    public function exportExcel()
    {
        $user = $this->authService->user();
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Unauthorized']);
        }

        $departmentId = $this->request->getGet('department_id');
        $status = $this->request->getGet('status');
        $search = $this->request->getGet('search');

        $builder = $this->jobModel->builder();
        $builder->select('jobs.*, department.department_name, job_location.job_location')
            ->join('department', 'department.id = jobs.department_id', 'left')
            ->join('job_location', 'job_location.location_id = jobs.locations_id', 'left');

        if (!empty($departmentId)) {
            $builder->where('jobs.department_id', (int)$departmentId);
        }
        if (!empty($status)) {
            $builder->where('jobs.status', $status);
        }
        if (!empty($search)) {
            $builder->groupStart()
                ->like('jobs.job_title', $search)
                ->orLike('jobs.job_type', $search)
                ->orLike('department.department_name', $search)
                ->orLike('job_location.job_location', $search)
                ->groupEnd();
        }

        $records = $builder->orderBy('jobs.id', 'DESC')->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Job Openings');

        $headers = [
            'A1' => 'S.No',
            'B1' => 'Job Title',
            'C1' => 'Department',
            'D1' => 'Location',
            'E1' => 'Job Type',
            'F1' => 'Experience',
            'G1' => 'Salary Range',
            'H1' => 'Gender Preference',
            'I1' => 'Age Requirement',
            'J1' => 'Post Date',
            'K1' => 'Close Date',
            'L1' => 'Status',
            'M1' => 'Description'
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E66136']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ];
        $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $rowNum = 2;
        $sno = 1;
        foreach ($records as $item) {
            $sheet->setCellValue('A' . $rowNum, $sno++);
            $sheet->setCellValue('B' . $rowNum, $item['job_title'] ?? '-');
            $sheet->setCellValue('C' . $rowNum, $item['department_name'] ?? '-');
            $sheet->setCellValue('D' . $rowNum, $item['job_location'] ?? '-');
            $sheet->setCellValue('E' . $rowNum, $item['job_type'] ?? '-');
            $sheet->setCellValue('F' . $rowNum, $item['experience'] ?? '-');
            $sheet->setCellValue('G' . $rowNum, $item['salary_range'] ?? '-');
            $sheet->setCellValue('H' . $rowNum, $item['gender'] ?? 'Any');
            $sheet->setCellValue('I' . $rowNum, $item['age'] ?? 'Any');
            $sheet->setCellValue('J' . $rowNum, !empty($item['post_date']) ? date('Y-m-d', strtotime($item['post_date'])) : '-');
            $sheet->setCellValue('K' . $rowNum, !empty($item['close_date']) ? date('Y-m-d', strtotime($item['close_date'])) : '-');
            $sheet->setCellValue('L' . $rowNum, ucfirst($item['status'] ?? 'Active'));
            $sheet->setCellValue('M' . $rowNum, strip_tags($item['description'] ?? '-'));

            $rowNum++;
        }

        $lastRow = $rowNum > 2 ? $rowNum - 1 : 2;
        $borderStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
        ];
        $sheet->getStyle('A1:M' . $lastRow)->applyFromArray($borderStyle);

        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        $filename = 'Job_Openings_' . date('Y_m_d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
