<?php
$content = file_get_contents('app/Controllers/api/InterviewController.php');

$search = "    public function updateConvertToEmployee(\$id)
    {
        \$user = \$this->authService->check();
        if (!\$user) {
            return \$this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        \$convertToEmployee = \$this->request->getJSON()->convert_to_employee ?? 0;
        
        \$interview = \$this->interviewModel->find(\$id);
        if (!\$interview) {
            return \$this->failNotFound('Interview not found');
        }

        if (\$this->interviewModel->update(\$id, ['convert_to_employee' => \$convertToEmployee])) {
            return \$this->respond(['status' => 'success', 'message' => 'Convert to Employee updated successfully']);
        }

        return \$this->respond(['status' => 'error', 'message' => 'Failed to update convert status'], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
    }";

$replace = "    public function updateConvertToEmployee(\$id)
    {
        \$user = \$this->authService->check();
        if (!\$user) {
            return \$this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        \$payload = \$this->request->getJSON();
        \$convertToEmployee = \$payload->convert_to_employee ?? 0;
        \$branchId = \$payload->branch_id ?? null;
        \$departmentId = \$payload->department_id ?? null;
        
        \$interview = \$this->interviewModel->find(\$id);
        if (!\$interview) {
            return \$this->failNotFound('Interview not found');
        }

        // If converting to employee, create the user if not exists
        if (\$convertToEmployee == 1) {
            if (!\$branchId || !\$departmentId) {
                return \$this->respond(['status' => 'error', 'message' => 'Branch and Department are required to convert to employee'], 400);
            }

            \$userModel = new \\App\\Models\\UserModel();
            \$userInfoModel = new \\App\\Models\\UserInfoModel();

            // Check if user already exists
            \$existingUser = \$userModel->where('email', \$interview['email'])->first();
            if (!\$existingUser) {
                \$userData = [
                    'username' => \$interview['full_name'],
                    'email' => \$interview['email'],
                    'password' => password_hash('123456', PASSWORD_DEFAULT),
                    'role' => 'employee',
                    'branch_id' => \$branchId,
                    'department_id' => \$departmentId
                ];
                \$userId = \$userModel->insert(\$userData);

                \$nameParts = explode(' ', \$interview['full_name'], 2);
                \$userInfoData = [
                    'user_id' => \$userId,
                    'firstname' => \$nameParts[0] ?? '',
                    'lastname' => \$nameParts[1] ?? '',
                    'email' => \$interview['email'],
                    'gender' => \$interview['gender'],
                    'date_of_birth' => \$interview['date_of_birth'],
                    'address_1' => \$interview['current_address'],
                    'contact_number' => \$interview['mobile_number'],
                    'department_id' => \$departmentId,
                    'joining_date' => \$interview['joining_date'],
                    'job_id' => \$interview['job_id'],
                    'salary' => \$interview['offered_salary'],
                    'status' => 'active'
                ];
                \$userInfoModel->insert(\$userInfoData);
            }
        }

        if (\$this->interviewModel->update(\$id, ['convert_to_employee' => \$convertToEmployee])) {
            return \$this->respond(['status' => 'success', 'message' => 'Convert to Employee updated successfully']);
        }

        return \$this->respond(['status' => 'error', 'message' => 'Failed to update convert status'], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
    }";

file_put_contents('app/Controllers/api/InterviewController.php', str_replace($search, $replace, $content));
echo "InterviewController updated\n";
