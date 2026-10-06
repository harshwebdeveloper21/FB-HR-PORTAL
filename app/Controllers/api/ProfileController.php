<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserInfoModel;
use App\Models\UserModel;
use App\Models\DesignationModel;
use App\Models\DepartmentModel;
use App\Models\CityModel;
use App\Models\CountryModel;
use App\Models\TaskModel;
use App\Models\SubtaskModel;
use App\Models\CompanyLogoModel;
use App\Models\AccountDetailModel;
use App\Models\PerformanceModel;
use App\Models\AttendanceModel;
use App\Models\LeaveModel;
use App\Models\StateModel;
use App\Services\AuthService;

class ProfileController extends ResourceController
{
    private $userModel;
    private $userInfoModel;
    private $authService;
    private $designationModel;
    private $departmentModel;
    private $cityModel;
    private $countryModel;
    private $subtaskModel;
    private $taskModel;
    private $companyLogoModel;
    private $accountdetail;
    private $stateModel;
    private $perfomanceModel;
    private $attendenceModel;
    private $leaveModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userInfoModel = new UserInfoModel();
        $this->designationModel = new DesignationModel();
        $this->departmentModel = new DepartmentModel();
        $this->cityModel = new CityModel();
        $this->countryModel = new CountryModel();
        $this->stateModel = new StateModel();
        $this->companyLogoModel = new CompanyLogoModel();
        $this->accountdetail = new AccountDetailModel();
        $this->perfomanceModel = new PerformanceModel();
        $this->attendenceModel = new AttendanceModel();
        $this->leaveModel = new LeaveModel();
        $this->taskModel = new TaskModel();
        $this->subtaskModel = new SubtaskModel();
        $this->authService = new AuthService(service('request'));
    }

    public function getProfile()
    {
        // Authenticate the user
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Fetch user details from `users` table
        $userData = $this->userModel->find($user->sub);
        if (!$userData) {
            return $this->failNotFound('User not found in users table');
        }

        // Fetch additional user information from `user_info` table
        $userInfo = $this->userInfoModel->where('user_id', $user->sub)->first();
        if (!$userInfo) {
            $userInfo = [
                'firstname' => '', 'lastname' => '', 'email' => '', 'gender' => '',
                'date_of_birth' => '', 'address_1' => '', 'address_2' => '',
                'state_id' => '', 'postcode' => '', 'city_id' => '', 'country_id' => '',
                'contact_number' => '', 'designation_id' => '', 'department_id' => '',
                'joining_date' => '', 'working_location' => '', 'profile_image' => ''
            ];
        }

        // Fetch designation and department names
        $designation = $this->designationModel->find($userInfo['designation_id']);
        $department = $this->departmentModel->find($userInfo['department_id']);
        $city = $this->cityModel->find($userInfo['city_id']);
        $state = $this->stateModel->find($userInfo['state_id']);
        $country = $this->countryModel->find($userInfo['country_id']);

        // Fetch company logo and name from `company_logo` table
        $company = $this->companyLogoModel->first(); // Fetch first company record
        $account = $this->accountdetail->where('user_id', $user->sub)->first();
        // Fetch tasks assigned to the user
        $tasks = $this->taskModel->where('user_id', $user->sub)->findAll();

        // For each task, fetch subtasks
        foreach ($tasks as &$task) {
            $task['subtasks'] = $this->subtaskModel
                ->where('task_id', $task['id'])
                ->where('user_id', $user->sub) // Ensure it's the same user
                ->findAll();
        }
        $profileImageName = (!empty($userInfo['profile_image']) && $userInfo['profile_image'] !== '1789966027_54c5a38ccda20f7c2bac.jpg' && (file_exists(ROOTPATH . 'upload/' . $userInfo['profile_image']) || (defined('FCPATH') && file_exists(FCPATH . 'upload/' . $userInfo['profile_image'])))) ? $userInfo['profile_image'] : 'default_avatar.png';

        // Ensure keys exist to prevent undefined index errors
        $fullProfile = array_merge($userData, $userInfo, [
            'designation_name' => !empty($designation['designation_name']) ? $designation['designation_name'] : 'N/A',
            'department_name' => !empty($department['department_name']) ? $department['department_name'] : 'N/A',
            'city_name' => !empty($city['city_name']) ? $city['city_name'] : 'N/A',
            'state_name' => !empty($state['state_name']) ? $state['state_name'] : 'N/A',
            'country_name' => !empty($country['country_name']) ? $country['country_name'] : 'N/A',
            'company_name' => !empty($company['company_name']) ? $company['company_name'] : 'N/A',
            'logo_img' => !empty($company['logo_img']) ? $company['logo_img'] : 'upload/fab_logo.jpg', // Default fallback
            'pdf_logo' => !empty($company['pdf_logo']) ? $company['pdf_logo'] : '',
            'favicon_icon' => !empty($company['favicon_icon']) ? $company['favicon_icon'] : '',
            'profile_image' => $profileImageName,
            'profile_image_full_path' => base_url('upload/' . $profileImageName),
            'company_address' => !empty($company['company_address']) ? $company['company_address'] : 'N/A', // Added
            'company_phone' => !empty($company['company_phone']) ? $company['company_phone'] : 'N/A', // Added
            'company_email' => !empty($company['company_email']) ? $company['company_email'] : 'N/A', // Added
            'acc_number' => !empty($account['acc_number']) ? $account['acc_number'] : 'N/A', // Added
            'bank_name' => !empty($account['bank_name']) ? $account['bank_name'] : 'N/A', // Added
            'ifsc_code' => !empty($account['ifsc_code']) ? $account['ifsc_code'] : 'N/A', // Added
            'acc_in_name' => !empty($account['acc_in_name']) ? $account['acc_in_name'] : 'N/A', // Added
            'branch_name' => !empty($account['branch_name']) ? $account['branch_name'] : 'N/A', // Added
            'branch_code' => !empty($account['branch_code']) ? $account['branch_code'] : 'N/A', // Added
            'task' => $tasks
        ]);

        // Return the combined profile
        return $this->respond([
            'status' => 'success',
            'data' => $fullProfile
        ]);
    }

    public function uploadCompanyLogo()
    {
        $file = $this->request->getFile('logo');

        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('upload/', $newName);

            $this->companyLogoModel->insert([
                'company_name' => $this->request->getPost('company_name'),
                'logo_img' => $newName,
                'created_by' => session()->get('user_id')
            ]);

            return $this->respond(['status' => 'success', 'message' => 'Company logo uploaded successfully']);
        }
        else {
            return $this->failValidationErrors($file->getErrorString());
        }
    }

    public function editProfile()
    {
        // Authenticate the user
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Fetch user details from the `users` table
        $userData = $this->userModel->find($user->sub);
        if (!$userData) {
            return $this->failNotFound('User not found in users table');
        }

        // Fetch additional user information from the `user_info` table
        $userInfo = $this->userInfoModel->where('user_id', $user->sub)->first();
        if (!$userInfo) {
            $userInfo = [
                'firstname' => '', 'lastname' => '', 'email' => '', 'gender' => '',
                'date_of_birth' => '', 'address_1' => '', 'address_2' => '',
                'state_id' => '', 'postcode' => '', 'city_id' => '', 'country_id' => '',
                'contact_number' => '', 'designation_id' => '', 'department_id' => '',
                'joining_date' => '', 'working_location' => '', 'profile_image' => ''
            ];
        }

        // Fetch company details
        $company = $this->companyLogoModel->first(); // Fetch first company record

        // Merge all data
        $profileData = array_merge($userData, $userInfo, [
            'company_name' => $company ? $company['company_name'] : 'N/A',
            'logo_img' => $company ? $company['logo_img'] : 'upload/fab_logo.jpg',
            'pdf_logo' => ($company && !empty($company['pdf_logo'])) ? $company['pdf_logo'] : '',
            'favicon_icon' => ($company && !empty($company['favicon_icon'])) ? $company['favicon_icon'] : '',
            'company_address' => $company ? $company['company_address'] : 'N/A', // Added
            'company_phone' => $company ? $company['company_phone'] : 'N/A', // Added
            'company_email' => $company ? $company['company_email'] : 'N/A', // Added
        ]);

        return $this->respond([
            'status' => 'success',
            'data' => $profileData
        ]);
    }
    public function uploadLogo()
    {
        $file = $this->request->getFile('logo_img');

        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('upload/', $newName);

            // Update company logo in the database
            $this->companyLogoModel->update(1, [ // Assuming there's only one company record
                'logo_img' => $newName
            ]);

            return $this->respond(['status' => 'success', 'logo_img' => $newName]);
        }
        else {
            return $this->failValidationErrors($file->getErrorString());
        }
    }


    public function updateProfile()
    {
        // Authenticate the logged-in user (assumes token/session-based auth)
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Get logged-in user's ID
        $userId = $user->sub;

        // Validate incoming data
        $data = $this->request->getPost();

        $rules = [
            'firstname' => [
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'First name is required.',
                    'min_length' => 'First name must be at least 3 characters long.'
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email is required.',
                    'valid_email' => 'Please enter a valid email address.'
                ]
            ]
        ];

        // Check if the user is an admin before adding company-specific validation
        // if ($user->role === 'admin') {
        //     $rules['company_name'] = [
        //         'rules' => 'required|min_length[3]',
        //         'errors' => [
        //             'required' => 'Company name is required.',
        //             'min_length' => 'Company name must be at least 3 characters long.'
        //         ]
        //     ];
        //     $rules['company_address'] = [
        //         'rules' => 'required|min_length[5]',
        //         'errors' => [
        //             'required' => 'Company address is required.',
        //             'min_length' => 'Company address must be at least 5 characters long.'
        //         ]
        //     ];
        //     $rules['company_phone'] = [
        //         'rules' => 'required|regex_match[/^[0-9+\-\s()]+$/]',
        //         'errors' => [
        //             'required' => 'Company phone number is required.',
        //             'regex_match' => 'Enter a valid phone number.'
        //         ]
        //     ];
        //     $rules['company_email'] = [
        //         'rules' => 'required|valid_email',
        //         'errors' => [
        //             'required' => 'Company email is required.',
        //             'valid_email' => 'Enter a valid company email address.'
        //         ]
        //     ];
        // }


        // Handle profile image validation
        $profileImage = $this->request->getFile('profile_image');
        if ($profileImage && $profileImage->isValid()) {
            $rules['profile_image'] = [
                'rules' => 'uploaded[profile_image]|is_image[profile_image]|mime_in[profile_image,image/jpg,image/jpeg,image/webp,image/png]',
                'errors' => [
                    'uploaded' => 'Profile image is required.',
                    'is_image' => 'Invalid image file. Please upload a valid image.',
                    'mime_in' => 'Only JPG, JPEG, PNG, and WEBP formats are allowed.'
                ]
            ];
        }
        // Handle company logo validation
        $companyLogo = $this->request->getFile('logo_img');
        if ($companyLogo && $companyLogo->isValid()) {
            if ($user->role === 'admin' && $companyLogo && $companyLogo->isValid()) {
                $rules['logo_img'] = [
                    'rules' => 'uploaded[logo_img]|is_image[logo_img]|mime_in[logo_img,image/jpg,image/jpeg,image/webp,image/png]',
                    'errors' => [
                        'uploaded' => 'Company logo is required.',
                        'is_image' => 'Invalid image format.',
                        'mime_in' => 'Only JPG, JPEG, PNG, and WEBP formats are allowed.'
                    ]
                ];
            }
        }

        // Validate the input
        if (!$this->validate($rules)) {
            return $this->failValidationErrors([
                'message' => 'Validation failed. Please check the required fields.',
                'errors' => $this->validator->getErrors(), // Returns detailed errors
            ]);
        }

        // Check if user_info exists
        $userInfo = $this->userInfoModel->where('user_id', $userId)->first();
        if (!$userInfo) {
            $userInfo = ['profile_image' => null];
        }

        // Handle profile image upload if present
        $profileImageName = $userInfo['profile_image']; // Keep the current image by default
        if ($profileImage && $profileImage->isValid() && !$profileImage->hasMoved()) {
            // Generate a new random name for the uploaded image
            $newProfileImageName = $profileImage->getRandomName();
            $profileImage->move(FCPATH . 'upload/', $newProfileImageName);

            // Delete old image if it exists
            if (!empty($userInfo['profile_image']) && file_exists(FCPATH . 'upload/' . $userInfo['profile_image'])) {
                unlink(FCPATH . 'upload/' . $userInfo['profile_image']);
            }

            $profileImageName = $newProfileImageName;
        }

        // Fetch company record
        $company = $this->companyLogoModel->first();
        if (!$company) {
            return $this->failNotFound('Company record not found');
        }

        // Handle company logo upload if present
        $companyLogoName = $company['logo_img']; // Keep existing logo by default
        if ($companyLogo && $companyLogo->isValid() && !$companyLogo->hasMoved()) {
            // Generate a new random name for the uploaded logo
            $newCompanyLogoName = $companyLogo->getRandomName();
            $companyLogo->move(FCPATH . 'upload/', $newCompanyLogoName);

            // Delete old logo if it exists
            if (!empty($company['logo_img']) && file_exists(FCPATH . 'upload/' . $company['logo_img'])) {
                unlink(FCPATH . 'upload/' . $company['logo_img']);
            }

            $companyLogoName = $newCompanyLogoName;
        }

        // Update `users` table
        $this->userModel->update($userId, [
            'email' => $data['email'],
            'username' => $data['firstname'], // Full name
        ]);
        // print_r($data);die;
        $userInfoData = [
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'address_1' => $data['address_1'] ?? null,
            'address_2' => $data['address_2'] ?? null,
            'state_id' => $data['state_id'] ?? null,
            'postcode' => $data['postcode'] ?? null,
            'city_id' => $data['city_id'] ?? null,
            'country_id' => $data['country_id'] ?? null,
            'contact_number' => $data['contact_number'],
            'designation_id' => $data['designation_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'joining_date' => $data['joining_date'] ?? null,
            'working_location' => $data['working_location'] ?? null,
            'profile_image' => $profileImageName, // Updated image name
        ];

        if (!$this->userInfoModel->where('user_id', $userId)->first()) {
            $userInfoData['user_id'] = $userId;
            $userInfoData['role'] = $user->role ?? 'employee';
            $this->userInfoModel->insert($userInfoData);
        } else {
            $this->userInfoModel->where('user_id', $userId)->set($userInfoData)->update();
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Profile and company details updated successfully!',
        ]);
    }

    public function removeProfileImage()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $userId = $user->sub;


        $this->userInfoModel->where('user_id', $userId)->set([
            'profile_image' => null, // Updated image name
        ])->update();

        return $this->respond([
            'status' => 'success',
            'message' => 'Profile and company details updated successfully!',
        ]);
    }

    public function updatecompany()
    {
        // Authenticate the logged-in user (assumes token/session-based auth)
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        // Get logged-in user's ID
        $userId = $user->sub;

        // Validate incoming data
        $data = $this->request->getPost();



        // Check if the user is an admin before adding company-specific validation
        if ($user->role === 'admin') {
            $rules['company_name'] = [
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Company name is required.',
                    'min_length' => 'Company name must be at least 3 characters long.'
                ]
            ];
            $rules['company_address'] = [
                'rules' => 'required|min_length[5]',
                'errors' => [
                    'required' => 'Company address is required.',
                    'min_length' => 'Company address must be at least 5 characters long.'
                ]
            ];
            $rules['company_phone'] = [
                'rules' => 'required|regex_match[/^[0-9+\-\s()]+$/]',
                'errors' => [
                    'required' => 'Company phone number is required.',
                    'regex_match' => 'Enter a valid phone number.'
                ]
            ];
            $rules['company_email'] = [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Company email is required.',
                    'valid_email' => 'Enter a valid company email address.'
                ]
            ];
        }


        // Handle profile image validation

        // Handle company logo validation
        $companyLogo = $this->request->getFile('logo_img');
        if ($companyLogo && $companyLogo->isValid()) {
            if ($user->role === 'admin' && $companyLogo && $companyLogo->isValid()) {
                $rules['logo_img'] = [
                    'rules' => 'uploaded[logo_img]|is_image[logo_img]|mime_in[logo_img,image/jpg,image/jpeg,image/webp,image/png,image/x-icon,image/vnd.microsoft.icon]',
                    'errors' => [
                        'uploaded' => 'Company logo is required.',
                        'is_image' => 'Invalid image format.',
                        'mime_in' => 'Only JPG, JPEG, PNG, and WEBP formats are allowed.'
                    ]
                ];
            }
        }

        // Handle company favicon validation
        $companyFavicon = $this->request->getFile('favicon_icon');
        if ($companyFavicon && $companyFavicon->isValid()) {
            if ($user->role === 'admin') {
                $rules['favicon_icon'] = [
                    'rules' => 'uploaded[favicon_icon]|is_image[favicon_icon]|mime_in[favicon_icon,image/jpg,image/jpeg,image/webp,image/png,image/x-icon,image/vnd.microsoft.icon]',
                    'errors' => [
                        'uploaded' => 'Company favicon is required.',
                        'is_image' => 'Invalid favicon format.',
                        'mime_in' => 'Only ICO, JPG, JPEG, PNG, and WEBP formats are allowed.'
                    ]
                ];
            }
        }

        // Handle company PDF logo validation
        $companyPdfLogo = $this->request->getFile('pdf_logo');
        if ($companyPdfLogo && $companyPdfLogo->isValid()) {
            if ($user->role === 'admin') {
                $rules['pdf_logo'] = [
                    'rules' => 'uploaded[pdf_logo]|is_image[pdf_logo]|mime_in[pdf_logo,image/jpg,image/jpeg,image/webp,image/png]',
                    'errors' => [
                        'uploaded' => 'Company PDF logo is required.',
                        'is_image' => 'Invalid image format.',
                        'mime_in' => 'Only JPG, JPEG, PNG, and WEBP formats are allowed.'
                    ]
                ];
            }
        }

        // Validate the input
        if (!$this->validate($rules)) {
            return $this->failValidationErrors([
                'message' => 'Validation failed. Please check the required fields.',
                'errors' => $this->validator->getErrors(), // Returns detailed errors
            ]);
        }

        // Check if user_info exists

        // Handle profile image upload if present


        // Fetch company record
        $company = $this->companyLogoModel->first();
        if (!$company) {
            return $this->failNotFound('Company record not found');
        }

        // Handle company logo upload if present
        $companyLogoName = $company['logo_img']; // Keep existing logo by default
        if ($companyLogo && $companyLogo->isValid() && !$companyLogo->hasMoved()) {
            // Generate a new random name for the uploaded logo
            $newCompanyLogoName = $companyLogo->getRandomName();
            $companyLogo->move(FCPATH . 'upload/', $newCompanyLogoName);

            // Delete old logo if it exists
            if (!empty($company['logo_img']) && file_exists(FCPATH . 'upload/' . $company['logo_img'])) {
                unlink(FCPATH . 'upload/' . $company['logo_img']);
            }

            $companyLogoName = $newCompanyLogoName;
        }

        // Handle company favicon upload if present
        $companyFaviconName = $company['favicon_icon'] ?? null; // Keep existing favicon by default
        if ($companyFavicon && $companyFavicon->isValid() && !$companyFavicon->hasMoved()) {
            $newCompanyFaviconName = $companyFavicon->getRandomName();
            $companyFavicon->move(FCPATH . 'upload/', $newCompanyFaviconName);

            if (!empty($company['favicon_icon']) && file_exists(FCPATH . 'upload/' . $company['favicon_icon'])) {
                unlink(FCPATH . 'upload/' . $company['favicon_icon']);
            }

            $companyFaviconName = $newCompanyFaviconName;
        }

        // Handle company PDF logo upload if present
        $companyPdfLogoName = $company['pdf_logo'] ?? null; // Keep existing PDF logo by default
        if ($companyPdfLogo && $companyPdfLogo->isValid() && !$companyPdfLogo->hasMoved()) {
            $newCompanyPdfLogoName = $companyPdfLogo->getRandomName();
            $companyPdfLogo->move(FCPATH . 'upload/', $newCompanyPdfLogoName);

            if (!empty($company['pdf_logo']) && file_exists(FCPATH . 'upload/' . $company['pdf_logo'])) {
                unlink(FCPATH . 'upload/' . $company['pdf_logo']);
            }

            $companyPdfLogoName = $newCompanyPdfLogoName;
        }

        // Update `company_logo` table
        // Only update company details if user is admin
        if ($user->role === 'admin') {
            $this->companyLogoModel->update($company['id'], [
                'company_name' => $data['company_name'],
                'logo_img' => $companyLogoName,
                'pdf_logo' => $companyPdfLogoName,
                'favicon_icon' => $companyFaviconName,
                'company_address' => $data['company_address'],
                'company_phone' => $data['company_phone'],
                'company_email' => $data['company_email'],
            ]);
        }


        return $this->respond([
            'status' => 'success',
            'message' => 'Profile and company details updated successfully!',
        ]);
    }
}
