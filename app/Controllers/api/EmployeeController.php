<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use App\Models\UserInfoModel;
use App\Models\CityModel;
use App\Models\CountryModel;
use App\Models\StateModel;
use App\Models\DesignationModel;
use App\Models\DepartmentModel;
use App\Models\NotificationModel;
use App\Services\AuthService;
use App\Services\HierarchyService;
use App\Libraries\EmailService;
use CodeIgniter\Config\Services;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EmployeeController extends ResourceController
{
    protected $authService;
    // protected $email;
    protected $emailService;
    protected $cityModel;
    protected $countryModel;
    protected $stateModel;
    protected $departmentModel;
    protected $designationModel;
    protected $userModel;
    protected $userInfoModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->authService = Services::auth($this->request); // Inject the request object
        // $this->email = \Config\Services::email();
        $this->emailService = new EmailService(); // Initialize EmailService
        $this->cityModel = new CityModel();
        $this->countryModel = new CountryModel();
        $this->stateModel = new StateModel();
        $this->departmentModel = new DepartmentModel();
        $this->designationModel = new DesignationModel();
        $this->userModel = new UserModel();
        $this->userInfoModel = new UserInfoModel();
        $this->notificationModel = new NotificationModel();
    }

    public function creates($id = null)
    {
        // Fetch cities, countries, departments, designations, and branches
        $cities = $this->cityModel->findAll();
        $countries = $this->countryModel->findAll();
        $state = $this->stateModel->findAll();
        $departments = $this->departmentModel->orderBy('department_name', 'ASC')->findAll();
        $designations = $this->designationModel->findAll();
        $branchModel = new \App\Models\BranchModel();
        $branches = $branchModel->where('status', 'active')->orderBy('name', 'ASC')->findAll();

        // If $id is provided, it's for an existing employee, fetch their data
        if ($id) {

            $user = $this->userModel->find($id);
            $userInfo = $this->userInfoModel->where('user_id', $id)->first();

            $currentUser = $this->authService->check();
            return view('employee/employee', [
                'cities' => $cities,
                'countries' => $countries,
                'state' => $state,
                'departments' => $departments,
                'designations' => $designations,
                'branches' => $branches,
                'user' => $user,
                'userInfo' => $userInfo,
                'currentUserRole' => $currentUser ? $currentUser->role : 'employee',
            ]);
        } else {
            $currentUser = $this->authService->check();
            return view('employee/employee', [
                'cities' => $cities,
                'countries' => $countries,
                'state' => $state,
                'departments' => $departments,
                'designations' => $designations,
                'branches' => $branches,
                'currentUserRole' => $currentUser ? $currentUser->role : 'employee',
            ]);
        }
    }

    public function get_user_details($id = null)
    {
        // Fetch supporting data
        $cities = $this->cityModel->findAll();
        $countries = $this->countryModel->findAll();
        $state = $this->stateModel->findAll();
        $departments = $this->departmentModel->findAll();
        $designations = $this->designationModel->findAll();

        // If ID is provided, return user-specific details
        if ($id !== null) {
            $user = $this->userModel->find($id);
            $userInfo = $this->userInfoModel->where('user_id', $id)->first();

            if (!$user) {
                return $this->respond([
                    'status' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            return $this->respond([
                'status' => true,
                'message' => 'User details fetched successfully.',
                'cities' => $cities,
                'countries' => $countries,
                'state' => $state,
                'departments' => $departments,
                'designations' => $designations,
                'user' => $user,
                'userInfo' => $userInfo,
            ]);
        }

        // If no ID is provided, return only master data
        return $this->respond([
            'status' => true,
            'message' => 'Master data fetched successfully.',
            'cities' => $cities,
            'countries' => $countries,
            'state' => $state,
            'departments' => $departments,
            'designations' => $designations,
        ]);
    }

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

            // Match digits
            if (preg_match('/(\d+)/', $raw, $m)) {
                $num = (int)$m[1];
                $existing['emp-' . str_pad($num, 3, '0', STR_PAD_LEFT)] = true;
                $existing['emp-' . $num] = true;
                $existing[(string)$num] = true;
            }
        }

        // Also add users.id of active users so fallback IDs don't collide
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

        // Find the lowest positive integer sequence available
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

    public function lastEmployeeId()
    {
        $formattedId = $this->getGuaranteedUniqueEmployeeId();
        preg_match('/(\d+)/', $formattedId, $m);
        $nextEmployeeId = isset($m[1]) ? (int)$m[1] : 1;

        return $this->respond([
            'status'           => true,
            'employee_id'      => max($nextEmployeeId - 1, 0),
            'next_employee_id' => $nextEmployeeId,
            'formatted_id'     => $formattedId
        ]);
    }

    public function display()
    {
        return view('employee/view');
    }

    public function liveRequest()
    {
        return view('employee/employee_live_request');
    }

    public function validateStep()
    {
        $validation = \Config\Services::validation();
        $step = $this->request->getPost('current_step');
        $userId = $this->request->getPost('user_id');
        if (!empty($userId)) {
            $passwordRules = 'permit_empty|min_length[6]';
            $passwordErrors = [
                'min_length' => 'Password must be at least 6 characters long.'
            ];
        } else {
            $passwordRules = 'required|min_length[6]';
            $passwordErrors = [
                'required' => 'Password is required.',
                'min_length' => 'Password must be at least 6 characters long.'
            ];
        }
        // Define validation rules matching required fields
        $rules = [
            1 => [
                'firstname' => [
                    'rules' => 'required|min_length[3]',
                    'errors' => [
                        'required' => 'First name is required.',
                        'min_length' => 'First name must be at least 3 characters long.'
                    ]
                ],
                'lastname' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Last name is required.'
                    ]
                ],
                'email' => [
                    'rules' => 'required|valid_email',
                    'errors' => [
                        'required' => 'Email is required.',
                        'valid_email' => 'Please enter a valid email address.',
                        'is_unique' => 'This email is already registered.'
                    ]
                ],
                'password' => [
                    'rules' => $passwordRules,
                    'errors' => $passwordErrors
                ]
            ],
            2 => [
                'address_1' => [
                    'rules' => 'permit_empty'
                ],
                'city_id' => [
                    'rules' => 'permit_empty'
                ],
                'country_id' => [
                    'rules' => 'permit_empty'
                ],
                'state_id' => [
                    'rules' => 'permit_empty'
                ],
                'postcode' => [
                    'rules' => 'permit_empty|max_length[8]',
                    'errors' => [
                        'max_length' => 'Postcode cannot exceed 8 characters.'
                    ]
                ],
                'contact_number' => [
                    'rules' => 'permit_empty|numeric',
                    'errors' => [
                        'numeric' => 'Contact number must contain only numbers.'
                    ]
                ],
            ],
            3 => [
                'employee_id' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Employee ID is required.'
                    ]
                ],
                'department_id' => [
                    'rules' => 'required',
                    'errors' => ['required' => 'Department is required.']
                ],
                'joining_date' => [
                    'rules' => 'permit_empty|valid_date',
                    'errors' => [
                        'valid_date' => 'Please enter a valid joining date.'
                    ]
                ],
                'salary' => [
                    'rules' => 'required|decimal|greater_than[0]',
                    'errors' => [
                        'required' => 'Salary amount is required.',
                        'decimal' => 'Salary must be a valid decimal number.',
                        'greater_than' => 'Salary must be greater than zero.'
                    ]
                ]
            ]
        ];
        if (empty($userId)) {
            $rules[1]['email']['rules'] .= '|is_unique[users.email]';
        } else {
            $rules[1]['email']['rules'] .= '|is_unique[users.email,id,' . $userId . ']';
        }

        // Validate step
        if (!$validation->setRules($rules[$step])->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'status' => false,
                'errors' => $validation->getErrors()
            ]);
        }

        // Check employee_id uniqueness directly for step 3 against active users
        if ($step == 3) {
            $empId = trim((string)$this->request->getPost('employee_id'));
            if (!empty($empId)) {
                $db = \Config\Database::connect();
                // Simple direct check: does any OTHER active user already have this employee_id?
                $sql = "SELECT COUNT(*) as cnt FROM user_info
                        INNER JOIN users ON users.id = user_info.user_id
                        WHERE users.is_deleted = 0
                        AND LOWER(TRIM(user_info.employee_id)) = LOWER(TRIM(?))
                        AND user_info.user_id != ?";
                $bindings = [$empId, (int)($userId ?? 0)];
                $query = $db->query($sql, $bindings);
                $cnt = (int)($query->getRow()->cnt ?? 0);
                if ($cnt > 0) {
                    return $this->response->setJSON([
                        'status' => false,
                        'errors' => ['employee_id' => 'This Employee ID (' . htmlspecialchars($empId) . ') is already assigned to another employee.']
                    ]);
                }
            }
        }

        return $this->response->setJSON(['status' => true]);
    }

    public function create()
    {
        // Authorize before proceeding with the update
        $user = $this->authorize();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized access');
        }

        $data = $this->request->getPost();

        // Default gender if not provided
        $gender = $data['gender'] ?? 'male';

        // Check if the email is already taken
        $existingUser = $this->userModel->where('email', $data['email'])->where('is_deleted', 0)->first();
        if ($existingUser) {
            return $this->failValidationErrors(['This email is already registered. Please use a different one.']);
        }

        // Ensure employee_id is set and unique among active users
        $empId = isset($data['employee_id']) ? trim($data['employee_id']) : '';
        if (empty($empId)) {
            $empId = $this->getGuaranteedUniqueEmployeeId();
            $data['employee_id'] = $empId;
        } else {
            // Check if this employee_id is taken by another active user
            $db = \Config\Database::connect();
            $sql = "SELECT COUNT(*) as cnt FROM user_info
                    INNER JOIN users ON users.id = user_info.user_id
                    WHERE users.is_deleted = 0
                    AND LOWER(TRIM(user_info.employee_id)) = LOWER(TRIM(?))";
            $query = $db->query($sql, [$empId]);
            $cnt = (int)($query->getRow()->cnt ?? 0);
            if ($cnt > 0) {
                // Auto-resolve to next unique ID so employee creation is always smooth
                $empId = $this->getGuaranteedUniqueEmployeeId();
                $data['employee_id'] = $empId;
            }
        }

        // Hash password or generate
        $plainPassword = isset($data['password']) ? $data['password'] : bin2hex(random_bytes(4));
        $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);

        $targetRole = $data['role'] ?? 'employee';
        if ($targetRole === 'hr') {
            $hierarchyService = new HierarchyService();
            if (!$hierarchyService->canCreateHr()) {
                return $this->failValidationErrors(['Only one HR account is allowed across all branches in the entire system.']);
            }
        }

        $departmentId = !empty($data['department_id']) ? (int)$data['department_id'] : null;

        $db = \Config\Database::connect();
        $db->transStart();

        $userId = $this->userModel->insert([
            'email'         => $data['email'],
            'username'      => $data['firstname'] . ' ' . $data['lastname'],
            'password'      => $passwordHash,
            'role'          => $targetRole,
            'department_id' => $departmentId,
        ]);

        if (!$userId) {
            $db->transRollback();
            return $this->failServerError('Failed to create user.');
        }

        // ── Branch ID assignment ──────────────────────────────────────────────
        $creatorUser    = $this->authService->check();
        $creatorRole    = $creatorUser ? ($creatorUser->role ?? 'employee') : 'employee';
        $assignBranchId = null;

        if ($creatorRole === 'branch_admin') {
            $assignBranchId = (new AuthService(service('request')))->getBranchId();
        } elseif ($creatorRole === 'admin' || $creatorRole === 'hr') {
            $assignBranchId = !empty($data['branch_id']) ? (int)$data['branch_id'] : (new AuthService(service('request')))->getBranchId();
        }

        if ($assignBranchId) {
            $this->userModel->update($userId, ['branch_id' => $assignBranchId, 'department_id' => $departmentId]);
        }

        // If newly created user is a department manager, link them as manager in department table
        if ($targetRole === 'department_manager' && !empty($departmentId)) {
            $this->departmentModel->update($departmentId, ['manager_id' => $userId]);
        }

        // Handle File Upload - Profile Image
        $profileImage = $this->request->getFile('profile_image');
        if ($profileImage && $profileImage->isValid() && !$profileImage->hasMoved()) {
            $profileImageName = $profileImage->getRandomName();
            $profileImage->move(FCPATH . 'upload/', $profileImageName);
        }

        // Handle File Upload - Face Photo for biometric attendance
        $facePhoto = $this->request->getFile('face_photo');
        $facePhotoName = null;
        if ($facePhoto && $facePhoto->isValid() && !$facePhoto->hasMoved()) {
            // Create faces directory if not exists
            if (!is_dir(FCPATH . 'upload/faces/')) {
                mkdir(FCPATH . 'upload/faces/', 0755, true);
            }
            $facePhotoName = 'face_' . $facePhoto->getRandomName();
            $facePhoto->move(FCPATH . 'upload/faces/', $facePhotoName);
        }

        // Prepare user info data
        $userInfoData = [
            'user_id' => $userId,
            'firstname' => $data['firstname'] ?? '',
            'lastname' => $data['lastname'] ?? '',
            'email' => $data['email'] ?? '',
            'gender' => $data['gender'] ?? '',
            'date_of_birth' => $data['date_of_birth'] ?? '',
            'address_1' => $data['address_1'] ?? '',
            'address_2' => $data['address_2'] ?? '',
            'state_id' => $data['state_id'] ?? '',
            'postcode' => $data['postcode'] ?? '',
            'city_id' => $data['city_id'] ?? '',
            'country_id' => $data['country_id'] ?? '',
            'contact_number' => $data['contact_number'] ?? '',
            'employee_id' => isset($data['employee_id']) ? trim($data['employee_id']) : '',
            'designation_id' => $data['designation_id'] ?? '',
            'department_id' => $departmentId,
            'joining_date' => !empty($data['joining_date']) ? $data['joining_date'] : null,
            'working_location' => $data['working_location'] ?? '',
            'role' => $targetRole,
            'salary' => $data['salary'] ?? '',
        ];

        if (isset($profileImageName)) {
            $userInfoData['profile_image'] = $profileImageName;
        }

        if ($facePhotoName) {
            $userInfoData['face_photo'] = $facePhotoName;
        }

        $userInfo = $this->userInfoModel->insert($userInfoData);

        // âœ… Notify Admins or HRs
        $notificationModel = new \App\Models\NotificationModel();
        $userModel = new \App\Models\UserModel();

        $admins = $userModel->whereIn('role', ['admin', 'hr'])->findAll();
        foreach ($admins as $admin) {
            $notificationModel->insert([
                'sender_id' => $user->sub,
                'recipient_id' => $admin['id'],
                'data' => json_encode([
                    'username' => $data['firstname'] . ' ' . $data['lastname'],
                    'role' => $data['role'],
                    'type' => 'employee'
                ]),
                'is_read' => 0
            ]);
        }

        // - Birthday Notifications
        $today = date('m-d');
        $userInfoModel = new \App\Models\UserInfoModel();

        $birthdayUsers = $userInfoModel
            ->select('user_id, firstname, lastname')
            ->where("DATE_FORMAT(date_of_birth, '%m-%d')", $today)
            ->findAll();

        $adminHRs = $userModel->whereIn('role', ['admin', 'hr'])->findAll();

        foreach ($birthdayUsers as $birthdayUser) {
            $employeeId = $birthdayUser['user_id'];
            $fullName = $birthdayUser['firstname'] . ' ' . $birthdayUser['lastname'];

            $allRecipients = $adminHRs;
            $allRecipients[] = ['id' => $employeeId];

            $uniqueRecipients = [];
            foreach ($allRecipients as $recipient) {
                $uniqueRecipients[$recipient['id']] = $recipient;
            }

            foreach ($uniqueRecipients as $recipient) {
                $notificationModel->insert([
                    'sender_id' => $employeeId,
                    'recipient_id' => $recipient['id'],
                    'data' => json_encode([
                        'username' => $fullName,
                        'type' => 'birthday'
                    ]),
                    'is_read' => 0
                ]);
            }
        }

        if (!$userInfo) {
            $db->transRollback();
            return $this->failServerError('Failed to create user info.');
        }

        // Send login credentials
        $emailService = new EmailService();
        $emailResult = $emailService->sendEmail($userId, $plainPassword);

        if (!$emailResult['status']) {
            $db->transRollback();
            return $this->failServerError('User creation failed due to email error.');
        }

        // Initialize employee leaves if provided
        if (isset($data['remaining_paid_leave']) || isset($data['remaining_sick_leave'])) {
            $employeeLeaveModel = new \App\Models\EmployeeLeaveModel();
            $employeeLeaveModel->insert([
                'employee_id' => $userId,
                'paid_leave' => $data['remaining_paid_leave'] ?? 0,
                'casual_leave' => $data['remaining_sick_leave'] ?? 0
            ]);
        }

        $db->transComplete();

        return $this->respondCreated(['message' => 'Employee created successfully!']);
    }

    // Update Employee
    public function update($id = null)
    {
        // Authorize before proceeding with the update
        $user = $this->authorize();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized access');
        }

        // Validate incoming data
        $data = $this->request->getPost();

        // Start with basic validation rules
        $rules = [
            'firstname' => 'required|min_length[3]',
        ];

        // Only add profile image validation if a new image is uploaded
        $profileImage = $this->request->getFile('profile_image');

        // Check for additional validation errors
        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Check if the user exists

        $existingUser = $this->userModel->find($id);
        if (!$existingUser) {
            return $this->failNotFound('User not found');
        }

        // Check if a new password is provided
        $newPassword = $data['password'] ?? null;
        $passwordChanged = false;

        if (!empty($newPassword)) {
            if (!password_get_info($newPassword)['algo']) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $originalPassword = $newPassword; // Store the original password
            } else {
                $hashedPassword = $newPassword;
                $originalPassword = null; // No need to store, as it's already hashed
            }
            $passwordChanged = true;
        } else {
            $hashedPassword = $existingUser['password'];
            $originalPassword = null;
        }


        // Check if user_info exists before updating

        $userInfo = $this->userInfoModel->where('user_id', $id)->first();
        if (!$userInfo) {
            return $this->failNotFound('User info not found for update');
        }

        // Handle profile image update (only if a new image is uploaded)
        $profileImageName = $userInfo['profile_image']; // Keep the old image by default
        if ($profileImage && $profileImage->isValid() && !$profileImage->hasMoved()) {
            // Generate new image name
            $newImageName = $profileImage->getRandomName();

            // Move the uploaded image
            $profileImage->move(FCPATH . 'upload/', $newImageName);

            // Delete old image if it exists
            if (!empty($userInfo['profile_image']) && file_exists(FCPATH . 'upload/' . $userInfo['profile_image'])) {
                unlink(FCPATH . 'upload/' . $userInfo['profile_image']);
            }

            $profileImageName = $newImageName; // Set new image name
        }

        // Handle face photo update (for biometric attendance)
        $facePhoto = $this->request->getFile('face_photo');
        $facePhotoName = $userInfo['face_photo'] ?? null; // Keep the old face photo by default
        if ($facePhoto && $facePhoto->isValid() && !$facePhoto->hasMoved()) {
            $newFacePhotoName = 'face_' . $facePhoto->getRandomName();

            // Create faces directory if not exists
            if (!is_dir(FCPATH . 'upload/faces/')) {
                mkdir(FCPATH . 'upload/faces/', 0755, true);
            }

            $facePhoto->move(FCPATH . 'upload/faces/', $newFacePhotoName);

            // Delete old face photo if exists
            if (!empty($userInfo['face_photo']) && file_exists(FCPATH . 'upload/faces/' . $userInfo['face_photo'])) {
                unlink(FCPATH . 'upload/faces/' . $userInfo['face_photo']);
            }

            $facePhotoName = $newFacePhotoName;
        }

        // Use null coalescing operator (??) to prevent undefined key errors
        $designation_id = $data['designation_id'] ?? null;
        $department_id = $data['department_id'] ?? null;
        $employee_id = isset($data['employee_id']) ? trim($data['employee_id']) : null;
        $role = $data['role'] ?? $existingUser['role']; // Keep old role if not provided

        // Check if employee_id already belongs to another active user
        if (!empty($employee_id)) {
            $db = \Config\Database::connect();
            $sql = "SELECT COUNT(*) as cnt FROM user_info
                    INNER JOIN users ON users.id = user_info.user_id
                    WHERE users.is_deleted = 0
                    AND LOWER(TRIM(user_info.employee_id)) = LOWER(TRIM(?))
                    AND user_info.user_id != ?";
            $query = $db->query($sql, [$employee_id, (int)$id]);
            $cnt = (int)($query->getRow()->cnt ?? 0);
            if ($cnt > 0) {
                return $this->failValidationErrors(['employee_id' => 'This Employee ID is already assigned to another employee.']);
            }
        } else {
            // If no employee_id posted, keep the existing one
            $employee_id = $userInfo['employee_id'] ?? null;
        }

        if ($role === 'hr') {
            $hierarchyService = new HierarchyService();
            if (!$hierarchyService->canCreateHr($id)) {
                return $this->failValidationErrors(['Only one HR account is allowed across all branches in the entire system.']);
            }
        }

        $creatorUser = $this->authService->check();
        $creatorRole = $creatorUser ? ($creatorUser->role ?? 'employee') : 'employee';

        $userUpdateData = [
            'email'         => $data['email'],
            'username'      => ($data['firstname'] ?? '') . ' ' . ($data['lastname'] ?? ''),
            'role'          => $role,
            'password'      => $hashedPassword,
            'department_id' => !empty($department_id) ? (int)$department_id : null,
        ];

        if (in_array($creatorRole, ['admin', 'hr']) && !empty($data['branch_id'])) {
            $userUpdateData['branch_id'] = (int)$data['branch_id'];
        }

        // Update the users table
        $this->userModel->update($id, $userUpdateData);

        // If updated to department manager, update department manager_id
        if ($role === 'department_manager' && !empty($department_id)) {
            $this->departmentModel->update((int)$department_id, ['manager_id' => (int)$id]);
        }


        // Prepare gender field (or null if not set)
        $gender = isset($data['gender']) ? $data['gender'] : null;


        // Update the user_info table
        $this->userInfoModel->where('user_id', $id)->set([
            'firstname' => $data['firstname'] ?? '',
            'lastname' => $data['lastname'] ?? '',
            'email' => $data['email'] ?? '',
            'gender' => $gender,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'address_1' => $data['address_1'] ?? '',
            'address_2' => $data['address_2'] ?? '',
            'state_id' => $data['state_id'] ?? '',
            'postcode' => $data['postcode'] ?? '',
            'city_id' => $data['city_id'] ?? null,
            'country_id' => $data['country_id'] ?? null,
            'contact_number' => $data['contact_number'] ?? '',
            'employee_id' => $employee_id,
            'designation_id' => $designation_id,
            'department_id' => $department_id,
            'joining_date' => !empty($data['joining_date']) ? $data['joining_date'] : null,
            'working_location' => $data['working_location'] ?? '',
            'role' => $role,
            'status' => $data['status'] ?? 'Active',
            'status_reason' => $data['status_reason'] ?? null,
            'last_working_day' => !empty($data['last_working_day']) ? $data['last_working_day'] : null,
            'profile_image' => $profileImageName, // Update profile image only if changed
            'face_photo' => $facePhotoName, // Update face photo for biometric attendance
            'salary' => $data['salary'] ?? '',
        ])->update();

        // Update employee leaves if provided
        if (isset($data['remaining_paid_leave']) || isset($data['remaining_sick_leave'])) {
            $employeeLeaveModel = new \App\Models\EmployeeLeaveModel();
            $leaveData = [];
            if (isset($data['remaining_paid_leave'])) {
                $leaveData['paid_leave'] = $data['remaining_paid_leave'];
            }
            if (isset($data['remaining_sick_leave'])) {
                $leaveData['casual_leave'] = $data['remaining_sick_leave']; // Wait, the model uses 'casual_leave' for sick leave? Or sick_leave? Let me check the db column.
            }
            // wait, PayrollController maps remaining_casual_leaves to casual_leave. So sick leave is casual_leave. Let me just set both.
            
            $existingLeave = $employeeLeaveModel->where('employee_id', $id)->first();
            if ($existingLeave) {
                $employeeLeaveModel->update($existingLeave['id'], [
                    'paid_leave' => $data['remaining_paid_leave'] ?? $existingLeave['paid_leave'],
                    'casual_leave' => $data['remaining_sick_leave'] ?? $existingLeave['casual_leave'] // mapping sick leave to casual_leave based on existing code
                ]);
            } else {
                $employeeLeaveModel->insert([
                    'employee_id' => $id,
                    'paid_leave' => $data['remaining_paid_leave'] ?? 0,
                    'casual_leave' => $data['remaining_sick_leave'] ?? 0
                ]);
            }
        }

        $emailService = new EmailService();
        // Send email if password is changed
        if ($passwordChanged && $originalPassword) {
            $emailService->sendEmail($id, $originalPassword);
        }

        return $this->respond(['message' => 'Employee updated successfully!']);
    }

    public function index()
    {
        $user = $this->authService->check(); // Get logged-in user

        if (!$user) {
            return $this->failUnauthorized('Unauthorized access');
        }

        $role = $user->role;
        $departmentId = $this->request->getGet('department_id');

        // Build query with join
        $builder = $this->userModel
            ->select('users.*, user_info.employee_id, user_info.status, user_info.status_reason, user_info.last_working_day, user_info.firstname, user_info.lastname, user_info.profile_image, user_info.joining_date, user_info.id as user_info_id, user_info.salary, user_info.last_increment_date, user_info.last_increment_amount, department.department_name, users.department_id, employee_leaves.paid_leave, employee_leaves.casual_leave')
            ->join('user_info', 'user_info.user_id = users.id')
            ->join('department', 'department.id = users.department_id', 'left')
            ->join('employee_leaves', 'employee_leaves.employee_id = users.id', 'left');

        // Role-based filtering
        if ($role === 'admin') {
            $builder->whereIn('users.role', ['employee', 'hr', 'branch_admin', 'department_manager', 'admin']);
            
            // Apply global branch filter if set in session
            $filterBranchId = $this->authService->getBranchId();
            if (!empty($filterBranchId)) {
                $builder->where('users.branch_id', (int)$filterBranchId);
            }
        } elseif ($role === 'hr') {
            // Global HR: Company-wide (or filtered by active branch switcher)
            $builder->whereIn('users.role', ['employee', 'branch_admin', 'department_manager']);
            $filterBranchId = $this->authService->getBranchId();
            if (!empty($filterBranchId)) {
                $builder->where('users.branch_id', (int)$filterBranchId);
            }
        } elseif ($role === 'branch_admin') {
            // Branch Admin: Employees and Department Managers in their branch
            $builder->whereIn('users.role', ['employee', 'department_manager']);
            $branchId = (int)$this->authService->getBranchId();
            $builder->where('users.branch_id', $branchId);
        } elseif ($role === 'department_manager') {
            // Department Manager: Employees in their branch & department
            $dmUser = $this->userModel->find($user->sub);
            $builder->where('users.role', 'employee');
            if (!empty($dmUser['branch_id'])) {
                $builder->where('users.branch_id', (int)$dmUser['branch_id']);
            }
            if (!empty($dmUser['department_id'])) {
                $builder->where('users.department_id', (int)$dmUser['department_id']);
            }
        } else {
            return $this->failForbidden('You do not have permission to view employees');
        }

        // Department filtering
        if (!empty($departmentId)) {
            $builder->where('users.department_id', (int)$departmentId);
        }

        // Role filtering
        $roleFilter = $this->request->getGet('role');
        if (!empty($roleFilter)) {
            $builder->where('users.role', $roleFilter);
        }

        $builder->where('users.is_deleted', 0);

        // Get view type - 'active', 'inactive', 'resigned', 'fired', 'all'
        $viewType = strtolower($this->request->getGet('view') ?? 'active');

        if ($viewType === 'resigned') {
            $builder->where("LOWER(user_info.status)", 'resigned');
        } elseif ($viewType === 'fired' || $viewType === 'removed') {
            $builder->where("LOWER(user_info.status) IN ('fired', 'removed')");
        } elseif ($viewType === 'inactive') {
            $builder->where("LOWER(user_info.status)", 'inactive');
        } elseif ($viewType === 'all_inactive') {
            $builder->where("LOWER(user_info.status) IN ('inactive', 'resigned', 'fired', 'removed')");
        } elseif ($viewType === 'all') {
            // No status filter: return all active and inactive employees
        } else {
            // Default 'active'
            $builder->where("(LOWER(user_info.status) NOT IN ('inactive', 'resigned', 'fired', 'removed') OR user_info.status IS NULL)");
            $builder->where("(user_info.last_working_day IS NULL OR user_info.last_working_day >= CURDATE())");
        }

        $builder->orderBy('users.id', 'DESC');


        $results = $builder->findAll();

        $employees = [];

        foreach ($results as $row) {
            $empIdDisplay = !empty($row['employee_id']) ? $row['employee_id'] : ('EMP-' . sprintf('%03d', $row['id']));
            $rawStatus = trim($row['status'] ?? '');
            $statusNormalized = (!empty($rawStatus) && strtolower($rawStatus) !== 'null') ? ucfirst(strtolower($rawStatus)) : 'Active';
            $lastWorkingDay = (strtolower($statusNormalized) === 'active') ? null : $row['last_working_day'];

            $employees[] = [
                'user' => [
                    'id' => $row['id'],
                    'email' => $row['email'],
                    'role' => $row['role'],
                    'password' => $row['password'],
                ],
                'user_info' => [
                    'id' => $row['user_info_id'],
                    'employee_id' => $empIdDisplay,
                    'firstname' => $row['firstname'],
                    'lastname' => $row['lastname'],
                    'joining_date' => $row['joining_date'],
                    'remaining_paid_leave' => (float) ($row['paid_leave'] ?? 0),
                    'remaining_sick_leave' => (float) ($row['casual_leave'] ?? 0),
                    'department_id' => $row['department_id'],
                    'department_name' => $row['department_name'],
                    'salary' => (float) ($row['salary'] ?? 0),
                    'status' => $statusNormalized,
                    'status_reason' => $row['status_reason'] ?? '',
                    'last_working_day' => $lastWorkingDay,
                    'last_increment_date' => $row['last_increment_date'] ?? 'N/A',
                    'last_increment_amount' => (float) ($row['last_increment_amount'] ?? 0),
                    'profile_image_url' => getUserProfileImage($row['profile_image'] ?? null),
                ]
            ];
        }

        return $this->respond([
            'status' => true,
            'employees' => $employees
        ]);
    }


    public function show($id = null)
    {


        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->failNotFound('User not found');
        }
        // **Do NOT return hashed password in response**
        unset($user['password']);

        $userInfo = $this->userInfoModel->where('user_id', $id)->first();
        if ($userInfo) {
            if (empty($userInfo['employee_id'])) {
                $userInfo['employee_id'] = 'EMP-' . sprintf('%03d', $user['id']);
            }
        }

        // Append full image path if profile_image exists
        if ($userInfo) {
            $userInfo['profile_image'] = getUserProfileImage($userInfo['profile_image'] ?? null);
        }

        // Append full face photo path if exists
        if ($userInfo && !empty($userInfo['face_photo'])) {
            $userInfo['face_photo'] = base_url('upload/faces/' . $userInfo['face_photo']);
        } else {
            $userInfo['face_photo'] = null;
        }

        // Fetch leave balances for display (dynamic fetch)
        $employeeLeaveModel = new \App\Models\EmployeeLeaveModel();
        $leaveBalance = $employeeLeaveModel->where('employee_id', $id)->first();
        if ($leaveBalance) {
            $userInfo['remaining_paid_leave'] = $leaveBalance['paid_leave'] ?? 0;
            $userInfo['remaining_sick_leave'] = $leaveBalance['casual_leave'] ?? 0;
        } else {
            $userInfo['remaining_paid_leave'] = 0; // Default values
            $userInfo['remaining_sick_leave'] = 0;
        }

        if ($userInfo) {
            $userInfo['role'] = $user['role'] ?? ($userInfo['role'] ?? 'employee');
            if (empty($userInfo['department_id']) && !empty($user['department_id'])) {
                $userInfo['department_id'] = $user['department_id'];
            }
        }

        return $this->respond([
            'user' => $user,
            'user_info' => $userInfo
        ]);
    }

    // Add JWT authorization to protected routes
    private function authorize()
    {
        $user = $this->authService->check();
        if (!$user) {
            log_message('error', 'User is not authorized. JWT token missing or invalid.');
            return false; // Return false if user is not authorized
        }
        return $user;
    }

    public function delete($id = null)
    {
        // Authenticate user
        $user = $this->authorize();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized access');
        }

        // Only Admin or HR can delete employees
        if (!in_array($user->role, ['admin', 'hr'])) {
            return $this->failForbidden('Forbidden: Only Admin or HR can delete employee records');
        }

        $db = \Config\Database::connect();

        // Verify employee exists and is not already deleted
        $existingUser = $db->table('users')->where('id', $id)->where('is_deleted', 0)->get()->getRowArray();
        if (!$existingUser) {
            return $this->failNotFound('Employee not found');
        }

        // - Start a transaction so everything succeeds or nothing changes -
        $db->transStart();

        // 1. Attendance records
        $db->table('attendance')->where('user_id', $id)->delete();

        // 2. Leave records
        $db->table('leaves')->where('user_id', $id)->delete();

        // 3. Payroll records
        $db->table('payroll')->where('user_id', $id)->delete();

        // 4. Performance records
        $db->table('performance')->where('user_id', $id)->delete();

        // 5. Tasks assigned to or created by this employee
        $db->table('task')->where('user_id', $id)->delete();

        // 6. Sub-tasks
        $db->table('subtasks')->where('user_id', $id)->delete();

        // 7. Training records
        $db->table('training')->where('user_id', $id)->delete();

        // 8. Comments
        $db->table('comments')->where('user_id', $id)->delete();

        // 9. Bank / account details
        $db->table('account_detail')->where('user_id', $id)->delete();

        // 10. Notifications (both sent and received)
        $db->table('notifications')->where('sender_id', $id)->orWhere('recipient_id', $id)->delete();

        // 11. Employee of the Month records
        $db->table('employee_of_month_certificates')->where('user_id', $id)->delete();

        // 12. Push notification subscriptions
        $db->table('push_subscriptions')->where('user_id', $id)->delete();

        // 13. Remember tokens (sessions)
        $db->table('remember_tokens')->where('user_id', $id)->delete();

        // 14. Employee reports
        $db->table('empreport')->where('user_id', $id)->delete();

        // 15. User info (profile)
        $db->table('user_info')->where('user_id', $id)->delete();

        // 16. Finally - delete the user account itself
        $db->table('users')->where('id', $id)->delete();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Failed to delete employee. Transaction rolled back.');
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Employee and all related data have been permanently deleted.'
        ]);
    }

    public function profile($id)
    {
        $user = $this->authService->user();
        $userId = $user->sub;
        // Pass employee ID to the view
        return view('employee/profile', ['id' => $id, 'employeeId' => $userId,]);
    }


    /**
     * profileview($id)
     *
     * Route: /employee/profile/view/{users.id}
     *
     * Receives the users.id (user_id), finds the matching
     * user_info row, and renders the profile view using user_info.id.
     */
    public function profileview($id)
    {
        // Look up user_info by users.id (user_id column)
        $userInfo = $this->userInfoModel
            ->select('id')
            ->where('user_id', $id)
            ->first();

        if (!$userInfo) {
            return $this->failNotFound('Employee profile not found for user ID: ' . $id);
        }

        $userInfoId   = $userInfo['id'];                   // user_info.id â€” what the profile view needs
        $loggedInUser = $this->authService->user();
        $employeeId   = $loggedInUser ? $loggedInUser->sub : null;

        return view('employee/profile', [
            'id'         => $userInfoId,  // user_info.id
            'employeeId' => $employeeId,  // logged-in user's own users.id
        ]);
    }

    public function details($id)
    {
        // Fetch user info with joins
        $user = $this->userInfoModel
            ->select('
            users.email, users.username, users.id AS user_id,
            user_info.id, user_info.profile_image, user_info.firstname, user_info.lastname,
            user_info.date_of_birth, user_info.gender, user_info.contact_number,
            user_info.address_1, user_info.address_2, user_info.city_id, user_info.state_id,
            user_info.country_id, user_info.designation_id, user_info.department_id,
            user_info.postcode, user_info.employee_id, user_info.joining_date,
            user_info.working_location, user_info.role, user_info.salary,
            user_info.status, user_info.last_working_day,
            account_detail.acc_number, account_detail.bank_name, account_detail.ifsc_code,
            account_detail.acc_in_name, account_detail.branch_name, account_detail.branch_code
        ')
            ->join('users', 'users.id = user_info.user_id', 'left')
            ->join('account_detail', 'account_detail.user_id = user_info.user_id', 'left')
            ->where('user_info.id', $id)
            ->first();

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        // Load related names from reference tables
        $user['city_name'] = $this->cityModel->find($user['city_id'])['city_name'] ?? 'N/A';
        $user['state_name'] = $this->stateModel->find($user['state_id'])['state_name'] ?? 'N/A';
        $user['country_name'] = $this->countryModel->find($user['country_id'])['country_name'] ?? 'N/A';
        $user['designation_name'] = $this->designationModel->find($user['designation_id'])['designation_name'] ?? 'N/A';
        $user['department_name'] = $this->departmentModel->find($user['department_id'])['department_name'] ?? 'N/A';

        $city = $this->cityModel->findAll();
        $state = $this->stateModel->findAll();
        $country = $this->countryModel->findAll();
        $designation = $this->designationModel->findAll();
        $department = $this->departmentModel->findAll();
        // Handle default profile image
        $user['profile_image'] = !empty($user['profile_image'])
            ? base_url('upload/' . $user['profile_image'])
            : base_url(env('ImagePath') . 'upload/1789966027_54c5a38ccda20f7c2bac.jpg');

        return $this->response->setJSON([
            'success' => true,
            'data' => $user,
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'designation' => $designation,
            'department' => $department,
        ]);
    }

    public function change_password_user()
    {
        $request = service('request');
        $input = json_decode($request->getBody(), true);

        $userId = $input['user_id'] ?? null;
        $newPassword = $input['password'] ?? null;
        $confirmPassword = $input['confirm_password'] ?? null;

        if (!$userId || !$newPassword || !$confirmPassword) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User ID, password, and confirm password are required.'
            ]);
        }

        if ($newPassword !== $confirmPassword) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Password and Confirm Password do not match.'
            ]);
        }

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found.'
            ]);
        }

        $userModel->update($userId, [
            'password' => $hashedPassword
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Password updated successfully.'
        ]);
    }

    public function change_image()
    {
        $id = $this->request->getPost('id');
        $profileImage = $this->request->getFile('profile_image');
        $userInfo = $this->userInfoModel->where('user_id', $id)->first();
        if (!$userInfo) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found.'
            ]);
        }

        $profileImageName = $userInfo['profile_image'];

        if ($profileImage && $profileImage->isValid() && !$profileImage->hasMoved()) {
            $newImageName = $profileImage->getRandomName();
            $profileImage->move(FCPATH . 'upload/', $newImageName);

            if (!empty($profileImageName) && file_exists(FCPATH . 'upload/' . $profileImageName)) {
                unlink(FCPATH . 'upload/' . $profileImageName);
            }

            $profileImageName = $newImageName;
        }

        $this->userInfoModel->where('user_id', $id)->set([
            'profile_image' => $profileImageName,
        ])->update();

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Profile image updated successfully.'
        ]);
    }
    public function save_overview()
    {
        $input = $this->request->getPost();

        // Validation rules
        $rules = [
            'id' => 'required|is_natural_no_zero',
            'firstname' => 'required|min_length[2]',
            'lastname' => 'permit_empty|min_length[2]',
            'email' => 'required|valid_email',
            'gender' => 'required|in_list[male,female,other]',
            'dob' => 'required|valid_date[Y-m-d]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $userId = $input['id'];
        $email = $input['email'];
        $firstname = $input['firstname'];
        $lastname = $input['lastname'];
        $gender = $input['gender'];
        $dob = $input['dob'];

        // Load models
        $userModel = new \App\Models\UserModel();
        $userInfoModel = new \App\Models\UserInfoModel();

        // Begin DB transaction
        $db = \Config\Database::connect();
        $db->transStart();

        // Update `users` table
        $userModel->update($userId, [
            'email' => $email,
            'username' => $firstname,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Update `user_info` table
        $userInfoModel->where('user_id', $userId)->set([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'gender' => $gender,
            'date_of_birth' => $dob,
            'updated_at' => date('Y-m-d H:i:s')
        ])->update();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update profile'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Profile updated successfully'
        ]);
    }
    public function get_user_address_data($userId)
    {

        $userInfoModel = new \App\Models\UserInfoModel();
        $cityModel = new \App\Models\CityModel();
        $stateModel = new \App\Models\StateModel();
        $countryModel = new \App\Models\CountryModel();
        $designationModel = new \App\Models\DesignationModel();
        $departmentModel = new \App\Models\DepartmentModel();

        $user = $userInfoModel->where('user_id', $userId)->first();

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'user' => $user,
            'city' => $cityModel->findAll(),
            'state' => $stateModel->findAll(),
            'country' => $countryModel->findAll(),
            'designation' => $designationModel->findAll(),
            'department' => $departmentModel->findAll()
        ]);
    }
    public function update_user_address()
    {
        $data = $this->request->getPost();

        $rules = [
            'user_id' => 'required|integer',
            'address_1' => 'required|min_length[3]',
            'address_2' => 'permit_empty',
            'country_id' => 'required|integer',
            'state_id' => 'required|integer',
            'city_id' => 'required|integer',
            'postcode' => 'required',
            'contact_number' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $userId = $data['user_id'];

        $userInfoModel = new \App\Models\UserInfoModel();
        $user = $userInfoModel->where('user_id', $userId)->first();

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        // Update address fields
        $userInfoModel->where('user_id', $userId)->set([
            'address_1' => $data['address_1'],
            'address_2' => $data['address_2'],
            'country_id' => $data['country_id'],
            'state_id' => $data['state_id'],
            'city_id' => $data['city_id'],
            'postcode' => $data['postcode'],
            'contact_number' => $data['contact_number'],
            'updated_at' => date('Y-m-d H:i:s')
        ])->update();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Address updated successfully'
        ]);
    }
    public function get_user_bank_data($userId)
    {
        $model = new \App\Models\AccountDetailModel();
        $bank = $model->select('user_id, bank_name, acc_number, acc_in_name, branch_name, branch_code')
            ->where('user_id', $userId)
            ->first();

        // Even if not found, return blank structure so frontend modal can open
        if (!$bank) {
            $bank = [
                'user_id' => $userId,
                'bank_name' => '',
                'acc_number' => '',
                'acc_in_name' => '',
                'branch_name' => '',
                'branch_code' => ''
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $bank
        ]);
    }


    public function update_user_bank_data()
    {
        $data = $this->request->getPost();

        $rules = [
            'user_id' => [
                'rules' => 'required|integer',
                'errors' => [
                    'required' => 'User ID is required.',
                    'integer' => 'Invalid User ID.',
                ],
            ],
            'bank_name' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'The Bank Name field is required.',
                ],
            ],
            'acc_number' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'The Account Number field is required.',
                    'numeric' => 'The Account Number must contain only digits.',
                ],
            ],
            'acc_in_name' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'The Account Holder Name field is required.',
                ],
            ],
            'branch_name' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'The Branch Name field is required.',
                ],
            ],
            'branch_code' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'The Branch Code field is required.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $model = new \App\Models\AccountDetailModel();

        // Check if bank data already exists
        $existing = $model->where('user_id', $data['user_id'])->first();

        $bankData = [
            'user_id' => $data['user_id'],
            'bank_name' => $data['bank_name'],
            'acc_number' => $data['acc_number'],
            'acc_in_name' => $data['acc_in_name'],
            'branch_name' => $data['branch_name'],
            'branch_code' => $data['branch_code'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            // Update existing record
            $model->where('user_id', $data['user_id'])->update(null, $bankData);
            $message = 'Bank info updated successfully';
        } else {
            // Insert new record
            $bankData['created_at'] = date('Y-m-d H:i:s');
            $model->insert($bankData);
            $message = 'Bank info inserted successfully';
        }

        return $this->response->setJSON(['success' => true, 'message' => $message]);
    }

    public function update_user_job_data()
    {
        $data = $this->request->getPost();

        $rules = [
            'user_id' => 'required|integer',
            'employee_id' => 'required',
            'department_id' => 'required|integer',
            'designation_id' => 'required|integer',
            'joining_date' => 'permit_empty|valid_date',
            'working_location' => 'required',
            'postcode' => 'required',
            'salary' => 'required|numeric',
            'role' => 'required|in_list[admin,employee,hr]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Clean employee_id and check uniqueness
        $cleanedEmpId = trim(str_replace('EMP#', '', (string)$data['employee_id']));
        $data['employee_id'] = $cleanedEmpId;

        $model = new \App\Models\UserInfoModel();

        $existing = $model->where('user_id', $data['user_id'])->first();
        if (!$existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'User not found']);
        }

        if (!empty($cleanedEmpId)) {
            $duplicate = $model->where('employee_id', $cleanedEmpId)
                ->where('user_id !=', $data['user_id'])
                ->first();
            if ($duplicate) {
                return $this->response->setJSON([
                    'success' => false,
                    'errors'  => ['employee_id' => 'This Employee ID is already assigned to another employee.']
                ]);
            }
        }

        $model->where('user_id', $data['user_id'])->set([
            'employee_id' => $data['employee_id'],
            'department_id' => $data['department_id'],
            'designation_id' => $data['designation_id'],
            'joining_date' => !empty($data['joining_date']) ? $data['joining_date'] : null,
            'working_location' => $data['working_location'],
            'postcode' => $data['postcode'],
            'salary' => $data['salary'],
            'role' => $data['role'],
            'updated_at' => date('Y-m-d H:i:s')
        ])->update();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Job details updated successfully'
        ]);
    }
    public function incrementSalary()
    {
        $user = $this->authorize();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized access');
        }

        $userId          = $this->request->getPost('user_id');
        $incrementAmount = $this->request->getPost('increment_amount');
        $incrementDate   = $this->request->getPost('increment_date');
        $isHistoryOnly   = filter_var($this->request->getPost('is_history_only'), FILTER_VALIDATE_BOOLEAN)
                           || $this->request->getPost('is_history_only') === '1'
                           || $this->request->getPost('is_history_only') === 1;
        $customPrevSalary = $this->request->getPost('previous_salary');

        if (!$userId || !$incrementAmount || (float)$incrementAmount <= 0 || !$incrementDate) {
            return $this->failValidationErrors('Invalid increment data provided.');
        }

        $userInfo = $this->userInfoModel->where('user_id', $userId)->first();
        if (!$userInfo) {
            return $this->failNotFound('Employee info not found');
        }

        // Restrict active salary increment for inactive or resigned employees (history-only entries are permitted)
        if (!$isHistoryOnly && in_array(strtolower($userInfo['status'] ?? ''), ['inactive', 'resigned', 'fired', 'removed'])) {
            return $this->failValidationErrors('Salary increment cannot be added for inactive or resigned employees.');
        }

        $db = \Config\Database::connect();

        // Calculate previous and new salary
        if ($isHistoryOnly && $customPrevSalary !== null && $customPrevSalary !== '') {
            $previousSalary = (float)$customPrevSalary;
        } else {
            $previousSalary = (float)($userInfo['salary'] ?? 0);
        }
        $newSalary = $previousSalary + (float)$incrementAmount;
        $now       = date('Y-m-d H:i:s');

        // ---- Idempotency Guard ----
        // Prevent duplicate inserts if the same request fires twice within 30 seconds
        $thirtySecondsAgo = date('Y-m-d H:i:s', strtotime('-30 seconds'));
        $existing = $db->table('salary_increment_history')
            ->where('employee_id', $userId)
            ->where('increment_amount', (float)$incrementAmount)
            ->where('effective_from_date', $incrementDate)
            ->where('created_at >=', $thirtySecondsAgo)
            ->countAllResults();

        if ($existing > 0) {
            return $this->respond([
                'status'  => 'success',
                'message' => 'Record already saved (duplicate request ignored).'
            ]);
        }

        $db->transStart();

        if (!$isHistoryOnly) {
            // ---- Regular Increment: updates employee's active salary and last increment info ----
            $existingJson = $userInfo['last_increment_date'] ?? null;
            $historyArray = [];

            if (!empty($existingJson)) {
                $decoded = json_decode($existingJson, true);
                if (is_array($decoded)) {
                    $historyArray = $decoded;
                }
            }

            // Prepend new entry so latest is first
            array_unshift($historyArray, [
                'increment_amount' => (float)$incrementAmount,
                'previous_salary'  => $previousSalary,
                'new_salary'       => $newSalary,
                'effective_date'   => $incrementDate,
                'created_at'       => $now,
            ]);

            // Save updated salary + JSON history to user_info
            $this->userInfoModel->where('user_id', $userId)->set([
                'salary'                => $newSalary,
                'last_increment_date'   => json_encode($historyArray),
                'last_increment_amount' => (float)$incrementAmount,
            ])->update();
        } else {
            // ---- History Only: DO NOT touch user_info.salary or last_increment_amount! ----
            $existingJson = $userInfo['last_increment_date'] ?? null;
            if (!empty($existingJson)) {
                $decoded = json_decode($existingJson, true);
                if (is_array($decoded)) {
                    $decoded[] = [
                        'increment_amount' => (float)$incrementAmount,
                        'previous_salary'  => $previousSalary,
                        'new_salary'       => $newSalary,
                        'effective_date'   => $incrementDate,
                        'created_at'       => $now,
                        'history_only'     => true,
                    ];
                    $this->userInfoModel->where('user_id', $userId)->set([
                        'last_increment_date' => json_encode($decoded),
                    ])->update();
                }
            }
        }

        // Always insert into relational history table for records and display
        $db->table('salary_increment_history')->insert([
            'employee_id'         => $userId,
            'increment_amount'    => (float)$incrementAmount,
            'previous_salary'     => $previousSalary,
            'new_salary'          => $newSalary,
            'effective_from_date' => $incrementDate,
            'updated_by'          => $user->sub,
            'created_at'          => $now,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Failed to save salary increment.');
        }

        if ($isHistoryOnly) {
            return $this->respond([
                'status'  => 'success',
                'message' => 'Salary increment history record added successfully! Current salary remains unchanged.'
            ]);
        }

        return $this->respond([
            'status'  => 'success',
            'message' => 'Salary incremented successfully! New salary: ₹' . number_format($newSalary, 2)
        ]);
    }

    public function getIncrementHistory($userInfoId = null)
    {
        $authUser = $this->authorize();
        if (!$authUser) {
            return $this->failUnauthorized('Unauthorized access');
        }

        if (!$userInfoId) {
            return $this->failValidationErrors('Employee ID is required.');
        }

        // The profile page passes user_info.id - resolve it to users.id
        $userInfo = $this->userInfoModel->find($userInfoId);
        if (!$userInfo) {
            return $this->failNotFound('Employee not found.');
        }
        $usersId = $userInfo['user_id']; // users.id â€” stored as employee_id in history

        $db = \Config\Database::connect();

        // Primary source: relational salary_increment_history table (chronological: latest effective date first)
        $records = $db->table('salary_increment_history as h')
            ->select('h.id, h.increment_amount, h.previous_salary, h.new_salary, h.effective_from_date, h.created_at')
            ->where('h.employee_id', $usersId)
            ->orderBy('h.effective_from_date', 'DESC')
            ->orderBy('h.id', 'DESC')
            ->get()
            ->getResultArray();

        // Fallback: if relational table has no rows, parse JSON from user_info.last_increment_date
        if (empty($records) && !empty($userInfo['last_increment_date'])) {
            $decoded = json_decode($userInfo['last_increment_date'], true);
            if (is_array($decoded)) {
                // JSON is already prepended (latest first); map to expected field names
                $records = array_map(function ($entry, $idx) {
                    return [
                        'id'                  => $idx + 1,
                        'increment_amount'    => $entry['increment_amount'] ?? 0,
                        'previous_salary'     => $entry['previous_salary']  ?? 0,
                        'new_salary'          => $entry['new_salary']       ?? 0,
                        'effective_from_date' => $entry['effective_date']   ?? '',
                        'created_at'          => $entry['created_at']       ?? '',
                    ];
                }, $decoded, array_keys($decoded));
            }
        }

        return $this->respond([
            'status'         => 'success',
            'current_salary' => (float)($userInfo['salary'] ?? 0),
            'history'        => $records
        ]);
    }

    /**
     * getIncrementHistoryByUserId($userId)
     *
     * Route: GET /api/employee/increment-history-user/{userId}
     *
     * Accepts users.id (the id used in the employee list view) and returns
     * the full salary increment history for that employee.
     */
    public function getIncrementHistoryByUserId($userId = null)
    {
        $authUser = $this->authorize();
        if (!$authUser) {
            return $this->failUnauthorized('Unauthorized access');
        }

        if (!$userId) {
            return $this->failValidationErrors('User ID is required.');
        }

        // Resolve users.id - user_info row
        $userInfo = $this->userInfoModel->where('user_id', $userId)->first();
        if (!$userInfo) {
            return $this->failNotFound('Employee not found.');
        }

        $db = \Config\Database::connect();

        // Fetch employee name for display
        $userModel = new \App\Models\UserModel();
        $userRow   = $userModel->find($userId);
        $firstName = $userInfo['firstname'] ?? '';
        $lastName  = $userInfo['lastname']  ?? '';
        $empName   = trim("$firstName $lastName") ?: ($userRow['username'] ?? 'Employee');

        // Primary source: relational salary_increment_history table (descending by effective date)
        $records = $db->table('salary_increment_history as h')
            ->select('h.id, h.increment_amount, h.previous_salary, h.new_salary, h.effective_from_date, h.created_at')
            ->where('h.employee_id', $userId)
            ->orderBy('h.effective_from_date', 'DESC')
            ->orderBy('h.id', 'DESC')
            ->get()
            ->getResultArray();

        // Fallback: if relational table has no rows, parse JSON from user_info.last_increment_date
        if (empty($records) && !empty($userInfo['last_increment_date'])) {
            $decoded = json_decode($userInfo['last_increment_date'], true);
            if (is_array($decoded)) {
                $records = array_map(function ($entry, $idx) {
                    return [
                        'id'                  => $idx + 1,
                        'increment_amount'    => $entry['increment_amount'] ?? 0,
                        'previous_salary'     => $entry['previous_salary']  ?? 0,
                        'new_salary'          => $entry['new_salary']       ?? 0,
                        'effective_from_date' => $entry['effective_date']   ?? '',
                        'created_at'          => $entry['created_at']       ?? '',
                    ];
                }, $decoded, array_keys($decoded));
            }
        }

        return $this->respond([
            'status'         => 'success',
            'current_salary' => (float)($userInfo['salary'] ?? 0),
            'employee_name'  => $empName,
            'history'        => $records
        ]);
    }

    public function updateStatus()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized access');
        }

        $userId = $this->request->getPost('user_id');
        $status = trim($this->request->getPost('status') ?? '');
        $reason = trim($this->request->getPost('status_reason') ?? '');
        $lastWorkingDay = trim($this->request->getPost('last_working_day') ?? '');

        if (empty($userId) || empty($status)) {
            return $this->respond(['status' => 'error', 'message' => 'User ID and status are required.'], 400);
        }

        $userInfo = $this->userInfoModel->where('user_id', $userId)->first();
        if (!$userInfo) {
            return $this->respond(['status' => 'error', 'message' => 'Employee record not found.'], 404);
        }

        $statusNormalized = ucfirst(strtolower($status));
        $updateData = [
            'status' => $statusNormalized,
        ];

        if (strtolower($status) === 'active') {
            // When employee is set to Active, clear last_working_day and status_reason
            $updateData['last_working_day'] = null;
            $updateData['status_reason'] = null;
        } else {
            // Inactive, Resigned, Fired, Removed
            $updateData['status_reason'] = !empty($reason) ? $reason : null;
            if (!empty($lastWorkingDay)) {
                $updateData['last_working_day'] = $lastWorkingDay;
            } elseif (empty($userInfo['last_working_day'])) {
                $updateData['last_working_day'] = date('Y-m-d');
            }
        }

        $this->userInfoModel->where('user_id', $userId)->set($updateData)->update();

        return $this->respond([
            'status' => 'success',
            'message' => 'Employee status updated to ' . $statusNormalized . ' successfully!'
        ]);
    }

    public function leaveHistoryMonthly($userId = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized access');
        }

        if (empty($userId)) {
            return $this->respond(['status' => false, 'message' => 'User ID required.'], 400);
        }

        $userInfo = $this->userInfoModel->where('user_id', $userId)->first();
        if (!$userInfo) {
            return $this->respond(['status' => false, 'message' => 'Employee record not found.'], 404);
        }

        $leaveModel = new \App\Models\LeaveModel();
        $employeeLeaveModel = new \App\Models\EmployeeLeaveModel();

        $empLeave = $employeeLeaveModel->where('employee_id', $userId)->first();

        $leaves = $leaveModel->where('user_id', $userId)
            ->orderBy('start_date', 'DESC')
            ->findAll();

        $monthlyData = [];
        foreach ($leaves as $leave) {
            $monthYear = date('M Y', strtotime($leave['start_date'] ?? $leave['created_at']));
            if (!isset($monthlyData[$monthYear])) {
                $monthlyData[$monthYear] = [
                    'month_year' => $monthYear,
                    'paid_used' => 0,
                    'sick_used' => 0,
                    'total_days' => 0,
                    'records' => []
                ];
            }

            $days = (float)($leave['total_days'] ?? 1);
            $leaveType = strtolower($leave['leave_type'] ?? '');

            if (strpos($leaveType, 'sick') !== false || strpos($leaveType, 'casual') !== false) {
                $monthlyData[$monthYear]['sick_used'] += $days;
            } else {
                $monthlyData[$monthYear]['paid_used'] += $days;
            }
            $monthlyData[$monthYear]['total_days'] += $days;
            $monthlyData[$monthYear]['records'][] = [
                'leave_type' => $leave['leave_type'] ?? 'N/A',
                'start_date' => $leave['start_date'] ?? '',
                'end_date'   => $leave['end_date'] ?? '',
                'total_days' => $days,
                'status'     => $leave['status'] ?? 'Approved',
                'reason'     => $leave['reason'] ?? '',
            ];
        }

        return $this->respond([
            'status' => true,
            'employee_name' => trim(($userInfo['firstname'] ?? '') . ' ' . ($userInfo['lastname'] ?? '')),
            'remaining_paid_leave' => (float)($empLeave['paid_leave'] ?? 0),
            'remaining_sick_leave' => (float)($empLeave['casual_leave'] ?? 0),
            'monthly_history' => array_values($monthlyData),
            'all_leaves' => $leaves
        ]);
    }

    /**
     * Export all employee fields to Excel (.xlsx)
     */
    public function exportExcel()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized access');
        }

        $role = $user->role;
        if (!in_array($role, ['admin', 'hr'])) {
            return $this->failForbidden('You do not have permission to export employees');
        }

        $departmentId = $this->request->getGet('department_id');
        $viewType     = $this->request->getGet('view') ?? 'active';
        $month        = $this->request->getGet('month');
        $year         = $this->request->getGet('year');

        // Build query to fetch all employee details
        $builder = $this->userModel
            ->select('
                users.id as user_table_id,
                users.email,
                users.role,
                user_info.id as user_info_id,
                user_info.employee_id,
                user_info.firstname,
                user_info.lastname,
                user_info.gender,
                user_info.date_of_birth,
                user_info.contact_number,
                user_info.address_1,
                user_info.address_2,
                user_info.postcode,
                user_info.working_location,
                user_info.joining_date,
                user_info.salary,
                user_info.status,
                user_info.status_reason,
                user_info.last_working_day,
                user_info.last_increment_date,
                user_info.last_increment_amount,
                department.department_name,
                designation.designation_name,
                city.city_name,
                states.state_name,
                country.country_name,
                employee_leaves.paid_leave,
                employee_leaves.casual_leave,
                account_detail.bank_name,
                account_detail.acc_number,
                account_detail.ifsc_code,
                account_detail.branch_name
            ')
            ->join('user_info', 'user_info.user_id = users.id')
            ->join('department', 'department.id = user_info.department_id', 'left')
            ->join('designation', 'designation.id = user_info.designation_id', 'left')
            ->join('city', 'city.id = user_info.city_id', 'left')
            ->join('states', 'states.id = user_info.state_id', 'left')
            ->join('country', 'country.id = user_info.country_id', 'left')
            ->join('employee_leaves', 'employee_leaves.employee_id = users.id', 'left')
            ->join('account_detail', 'account_detail.user_id = users.id', 'left');

        if ($role === 'admin') {
            $builder->whereIn('users.role', ['employee', 'hr', 'branch_admin', 'department_manager']);
            $filterBranchId = $this->authService->getBranchId();
            if (!empty($filterBranchId)) {
                $builder->where('users.branch_id', (int)$filterBranchId);
            }
        } elseif ($role === 'hr') {
            $builder->whereIn('users.role', ['employee', 'branch_admin', 'department_manager']);
            $filterBranchId = $this->authService->getBranchId();
            if (!empty($filterBranchId)) {
                $builder->where('users.branch_id', (int)$filterBranchId);
            }
        } elseif ($role === 'branch_admin') {
            $builder->whereIn('users.role', ['employee', 'department_manager']);
            $branchId = (int)$this->authService->getBranchId();
            $builder->where('users.branch_id', $branchId);
        } elseif ($role === 'department_manager') {
            $dmUser = $this->userModel->find($user->sub);
            $builder->where('users.role', 'employee');
            if (!empty($dmUser['branch_id'])) {
                $builder->where('users.branch_id', (int)$dmUser['branch_id']);
            }
            if (!empty($dmUser['department_id'])) {
                $builder->where('user_info.department_id', (int)$dmUser['department_id']);
            }
        } else {
            $builder->where('users.role', 'employee');
            $builder->where('users.id', $user->sub);
        }

        if (!empty($departmentId)) {
            $builder->where('user_info.department_id', $departmentId);
        }

        $roleFilter = $this->request->getGet('role');
        if (!empty($roleFilter)) {
            $builder->where('users.role', $roleFilter);
        }

        $builder->where('users.is_deleted', 0);

        $viewType = strtolower($viewType);
        if ($viewType === 'resigned') {
            $builder->where("LOWER(user_info.status)", 'resigned');
        } elseif ($viewType === 'fired' || $viewType === 'removed') {
            $builder->where("LOWER(user_info.status) IN ('fired', 'removed')");
        } elseif ($viewType === 'inactive') {
            $builder->where("LOWER(user_info.status)", 'inactive');
        } elseif ($viewType === 'all_inactive') {
            $builder->where("LOWER(user_info.status) IN ('inactive', 'resigned', 'fired', 'removed')");
        } elseif ($viewType === 'all') {
            // No status filter
        } else {
            // Default 'active'
            $builder->where("(LOWER(user_info.status) NOT IN ('inactive', 'resigned', 'fired', 'removed') OR user_info.status IS NULL)");
            $builder->where("(user_info.last_working_day IS NULL OR user_info.last_working_day >= CURDATE())");
        }

        if (!empty($year)) {
            $builder->where("YEAR(user_info.joining_date)", $year);
        }
        if (!empty($month)) {
            $builder->where("MONTH(user_info.joining_date)", $month);
        }

        $builder->orderBy('users.id', 'ASC');
        $employees = $builder->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Employees');

        // Define column headers
        $headers = [
            'A1'  => 'S.No',
            'B1'  => 'Emp ID',
            'C1'  => 'First Name',
            'D1'  => 'Last Name',
            'E1'  => 'Full Name',
            'F1'  => 'Email',
            'G1'  => 'Contact Number',
            'H1'  => 'Role',
            'I1'  => 'Department',
            'J1'  => 'Designation',
            'K1'  => 'Status',
            'L1'  => 'Status Reason / Comment',
            'M1'  => 'Joining Date',
            'N1'  => 'Last Working Day',
            'O1'  => 'Monthly Salary (INR)',
            'P1'  => 'Rem. Paid Leave',
            'Q1'  => 'Rem. Sick Leave',
            'R1'  => 'Gender',
            'S1'  => 'Date of Birth',
            'T1'  => 'Working Location',
            'U1'  => 'Address 1',
            'V1'  => 'Address 2',
            'W1'  => 'City',
            'X1'  => 'State',
            'Y1'  => 'Country',
            'Z1'  => 'Postcode',
            'AA1' => 'Last Increment Date',
            'AB1' => 'Last Increment Amount (INR)',
            'AC1' => 'Bank Name',
            'AD1' => 'Account Number',
            'AE1' => 'IFSC Code',
            'AF1' => 'Branch Name',
        ];

        // Populate header cells
        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        // Header styling
        $headerStyle = [
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E66136'], // Brand orange
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ];
        $sheet->getStyle('A1:AF1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Fill data rows
        $rowNum = 2;
        $sno = 1;

        foreach ($employees as $emp) {
            $empIdCode = !empty($emp['employee_id']) ? $emp['employee_id'] : ('EMP-' . sprintf('%03d', $emp['user_table_id']));
            $fullName  = trim(($emp['firstname'] ?? '') . ' ' . ($emp['lastname'] ?? ''));
            $status    = !empty($emp['status']) ? $emp['status'] : 'Active';

            $sheet->setCellValue('A' . $rowNum, $sno++);
            $sheet->setCellValue('B' . $rowNum, $empIdCode);
            $sheet->setCellValue('C' . $rowNum, $emp['firstname'] ?? '');
            $sheet->setCellValue('D' . $rowNum, $emp['lastname'] ?? '');
            $sheet->setCellValue('E' . $rowNum, $fullName);
            $sheet->setCellValue('F' . $rowNum, $emp['email'] ?? '');
            $sheet->setCellValueExplicit('G' . $rowNum, (string)($emp['contact_number'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('H' . $rowNum, ucfirst($emp['role'] ?? 'employee'));
            $sheet->setCellValue('I' . $rowNum, $emp['department_name'] ?? '-');
            $sheet->setCellValue('J' . $rowNum, $emp['designation_name'] ?? '-');
            $sheet->setCellValue('K' . $rowNum, $status);
            $sheet->setCellValue('L' . $rowNum, $emp['status_reason'] ?? '');
            $sheet->setCellValue('M' . $rowNum, !empty($emp['joining_date']) && $emp['joining_date'] !== '0000-00-00' ? $emp['joining_date'] : '-');
            $sheet->setCellValue('N' . $rowNum, !empty($emp['last_working_day']) ? $emp['last_working_day'] : '-');
            $sheet->setCellValue('O' . $rowNum, (float)($emp['salary'] ?? 0));
            $sheet->setCellValue('P' . $rowNum, (float)($emp['paid_leave'] ?? 0));
            $sheet->setCellValue('Q' . $rowNum, (float)($emp['casual_leave'] ?? 0));
            $sheet->setCellValue('R' . $rowNum, ucfirst($emp['gender'] ?? ''));
            $sheet->setCellValue('S' . $rowNum, !empty($emp['date_of_birth']) && $emp['date_of_birth'] !== '0000-00-00' ? $emp['date_of_birth'] : '-');
            $sheet->setCellValue('T' . $rowNum, ucfirst($emp['working_location'] ?? ''));
            $sheet->setCellValue('U' . $rowNum, $emp['address_1'] ?? '');
            $sheet->setCellValue('V' . $rowNum, $emp['address_2'] ?? '');
            $sheet->setCellValue('W' . $rowNum, $emp['city_name'] ?? '');
            $sheet->setCellValue('X' . $rowNum, $emp['state_name'] ?? '');
            $sheet->setCellValue('Y' . $rowNum, $emp['country_name'] ?? '');
            $sheet->setCellValue('Z' . $rowNum, $emp['postcode'] ?? '');
            $sheet->setCellValue('AA' . $rowNum, !empty($emp['last_increment_date']) ? $emp['last_increment_date'] : '-');
            $sheet->setCellValue('AB' . $rowNum, (float)($emp['last_increment_amount'] ?? 0));
            $sheet->setCellValue('AC' . $rowNum, $emp['bank_name'] ?? '-');
            $sheet->setCellValueExplicit('AD' . $rowNum, (string)($emp['acc_number'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('AE' . $rowNum, $emp['ifsc_code'] ?? '-');
            $sheet->setCellValue('AF' . $rowNum, $emp['branch_name'] ?? '-');

            // Format salary & increment as currency numbers
            $sheet->getStyle('O' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('AB' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');

            // Center align specific columns
            $sheet->getStyle('A' . $rowNum . ':B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $rowNum . ':K' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('M' . $rowNum . ':N' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('P' . $rowNum . ':T' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('AA' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getRowDimension($rowNum)->setRowHeight(20);
            $rowNum++;
        }

        $lastRow = $rowNum > 2 ? $rowNum - 1 : 2;

        // Apply grid borders
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'E0E0E0'],
                ],
            ],
        ];
        $sheet->getStyle('A1:AF' . $lastRow)->applyFromArray($borderStyle);

        // Auto size all columns
        foreach (range('A', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('AA')->setAutoSize(true);
        $sheet->getColumnDimension('AB')->setAutoSize(true);
        $sheet->getColumnDimension('AC')->setAutoSize(true);
        $sheet->getColumnDimension('AD')->setAutoSize(true);
        $sheet->getColumnDimension('AE')->setAutoSize(true);
        $sheet->getColumnDimension('AF')->setAutoSize(true);

        $filename = 'Employees_' . ucfirst($viewType) . '_' . date('Y_m_d_His') . '.xlsx';

        // Clear output buffer to prevent corrupted binary excel file
        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
