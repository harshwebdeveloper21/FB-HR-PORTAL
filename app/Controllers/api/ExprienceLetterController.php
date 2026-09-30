<?php

namespace App\Controllers\Api;

use App\Models\ExprienceLetterModel;
use App\Services\AuthService;
use App\Models\UserInfoModel;
use App\Models\ExperienceModel;
use App\Models\DepartmentModel;
use App\Models\DesignationModel;
use App\Models\CompanyLogoModel;
use CodeIgniter\RESTful\ResourceController;
use Dompdf\Dompdf;
use Dompdf\Options;

class ExprienceLetterController extends ResourceController
{
    protected $templateModel;
    protected $userinfoModel;
    private $authService;

    public function __construct()
    {
        $this->templateModel = new ExprienceLetterModel();
        $this->userinfoModel = new UserInfoModel();
        $this->authService = new AuthService(service('request'));

        helper(['form']);
    }

    public function index()
    {
        return view('exprience_templetes/create');
    }
    public function saveTemplate()
    {
        // Validate user authorization
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $validation = \Config\Services::validation();

        $rules = [
            'title' => 'required|min_length[3]',
            'content' => 'required|min_length[10]',
        ];

        // Optional header and footer validation
        $headerFile = $this->request->getFile('template_header');
        if ($headerFile && $headerFile->isValid() && !$headerFile->hasMoved()) {
            $rules['template_header'] = 'is_image[template_header]|max_size[template_header,2048]|mime_in[template_header,image/jpg,image/jpeg,image/png,image/webp]';
        }
        $footerFile = $this->request->getFile('template_footer');
        if ($footerFile && $footerFile->isValid() && !$footerFile->hasMoved()) {
            $rules['template_footer'] = 'is_image[template_footer]|max_size[template_footer,2048]|mime_in[template_footer,image/jpg,image/jpeg,image/png,image/webp]';
        }

        $messages = [
            'title' => [
                'required' => 'Title is required.',
                'min_length' => 'Title must be at least 3 characters.',
            ],
            'content' => [
                'required' => 'Content is required.',
                'min_length' => 'Content must be at least 10 characters long.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        $model = new \App\Models\ExprienceLetterModel();
        $id = $this->request->getPost('id');

        // Prepare data including created_by
        $data = [
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'created_by' => $user->sub,
        ];

        // Handle header image upload
        if ($headerFile && $headerFile->isValid() && !$headerFile->hasMoved()) {
            $newName = $headerFile->getRandomName();
            $headerFile->move(FCPATH . 'upload/templates/', $newName);
            $data['template_header'] = $newName;
        }

        // Handle footer image upload
        if ($footerFile && $footerFile->isValid() && !$footerFile->hasMoved()) {
            $newName = $footerFile->getRandomName();
            $footerFile->move(FCPATH . 'upload/templates/', $newName);
            $data['template_footer'] = $newName;
        }

        if ($id) {
            $model->update($id, $data);
            return $this->response->setJSON(['status' => 'updated']);
        } else {
            $model->insert($data);
            return $this->response->setJSON(['status' => 'created']);
        }
    }
    public function view()
    {

        return view('exprience_templetes/view');
    }
    public function display()
    {
        $userModel = new \App\Models\UserModel();
        $employee = $userModel->whereIn('role', ['hr', 'employee'])->findAll();
        $exprienceModel = new \App\Models\ExprienceLetterModel();
        $templates = $exprienceModel->findAll();

        return view('exprience_templetes/view_letter', [
            'employee'  => $employee,
            'templates' => $templates,
        ]);
    }
    public function listTemplates()
    {
        $templates = $this->templateModel->orderBy('created_at', 'DESC')->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $templates
        ]);
    }
    public function delete($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid template ID.'
            ])->setStatusCode(400);
        }

        $template = $this->templateModel->find($id);

        if (!$template) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Template not found.'
            ])->setStatusCode(404);
        }

        $this->templateModel->delete($id);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Template deleted successfully.'
        ]);
    }
    public function EditPage($id)
    {
        return view('exprience_templetes/edit_page', ['id' => $id]);
    }

    public function getTemplate($id)
    {
        $model = new \App\Models\ExprienceLetterModel();
        $template = $model->find($id);

        if ($template) {
            return $this->response->setJSON(['status' => 'success', 'data' => $template]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Template not found']);
        }
    }
    public function updateTemplate($id)
    {
        $validation = \Config\Services::validation();

        $rules = [
            'title' => 'required|min_length[3]',
            'content' => 'required|min_length[10]',
        ];

        $headerFile = $this->request->getFile('template_header');
        if ($headerFile && $headerFile->isValid() && !$headerFile->hasMoved()) {
            $rules['template_header'] = 'is_image[template_header]|max_size[template_header,2048]|mime_in[template_header,image/jpg,image/jpeg,image/png,image/webp]';
        }
        $footerFile = $this->request->getFile('template_footer');
        if ($footerFile && $footerFile->isValid() && !$footerFile->hasMoved()) {
            $rules['template_footer'] = 'is_image[template_footer]|max_size[template_footer,2048]|mime_in[template_footer,image/jpg,image/jpeg,image/png,image/webp]';
        }

        $messages = [
            'title' => [
                'required' => 'Title is required.',
                'min_length' => 'Title must be at least 3 characters.',
            ],
            'content' => [
                'required' => 'Content is required.',
                'min_length' => 'Content must be at least 10 characters long.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        $model = new \App\Models\ExprienceLetterModel();
        $template = $model->find($id);

        if (!$template) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Template not found']);
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
        ];

        if ($headerFile && $headerFile->isValid() && !$headerFile->hasMoved()) {
            $newName = $headerFile->getRandomName();
            $headerFile->move(FCPATH . 'upload/templates/', $newName);
            $data['template_header'] = $newName;
        }

        if ($footerFile && $footerFile->isValid() && !$footerFile->hasMoved()) {
            $newName = $footerFile->getRandomName();
            $footerFile->move(FCPATH . 'upload/templates/', $newName);
            $data['template_footer'] = $newName;
        }

        $model->update($id, $data);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Template updated successfully']);
    }

    private function getDigitalSignatureData()
    {
        $db = \Config\Database::connect();
        $defaultSig = null;
        if ($db->tableExists('digital_signatures')) {
            $defaultSig = $db->table('digital_signatures')->where('is_default', 1)->get()->getRowArray();
            if (!$defaultSig) {
                $defaultSig = $db->table('digital_signatures')->orderBy('id', 'DESC')->get()->getRowArray();
            }
        }

        $digitalSigSrc = '';
        $stampSrc = '';

        if ($defaultSig) {
            if (!empty($defaultSig['signature_path']) && file_exists(FCPATH . $defaultSig['signature_path'])) {
                $sigPath = FCPATH . $defaultSig['signature_path'];
                $sigMime = @mime_content_type($sigPath) ?: 'image/png';
                $digitalSigSrc = 'data:' . $sigMime . ';base64,' . base64_encode(file_get_contents($sigPath));
            }
            if (!empty($defaultSig['stamp_path']) && file_exists(FCPATH . $defaultSig['stamp_path'])) {
                $stPath = FCPATH . $defaultSig['stamp_path'];
                $stMime = @mime_content_type($stPath) ?: 'image/png';
                $stampSrc = 'data:' . $stMime . ';base64,' . base64_encode(file_get_contents($stPath));
            }
        }

        $digitalSigImgTag = !empty($digitalSigSrc)
            ? '<img src="' . $digitalSigSrc . '" class="digital-signature-img" style="max-height: 55px; max-width: 160px; object-fit: contain; vertical-align: middle; display: inline-block;" alt="Digital Signature">'
            : '';

        $stampImgTag = !empty($stampSrc)
            ? '<img src="' . $stampSrc . '" class="company-stamp-img" style="max-height: 85px; max-width: 85px; object-fit: contain; vertical-align: middle; display: inline-block;" alt="Company Stamp">'
            : '';

        $signatureStampBlock = '';
        if (!empty($digitalSigImgTag) && !empty($stampImgTag)) {
            $signatureStampBlock = '<table class="sig-stamp-table" style="border: none; border-collapse: collapse; margin-bottom: 6px; display: inline-table;"><tr>'
                . '<td style="border: none; padding: 0 15px 0 0; vertical-align: bottom;">' . $digitalSigImgTag . '</td>'
                . '<td style="border: none; padding: 0; vertical-align: bottom;">' . $stampImgTag . '</td>'
                . '</tr></table>';
        } elseif (!empty($digitalSigImgTag)) {
            $signatureStampBlock = '<div class="sig-block" style="margin-bottom: 6px;">' . $digitalSigImgTag . '</div>';
        } elseif (!empty($stampImgTag)) {
            $signatureStampBlock = '<div class="stamp-block" style="margin-bottom: 6px;">' . $stampImgTag . '</div>';
        }

        return [
            'raw_sig'               => $defaultSig,
            'digital_signature'     => $digitalSigImgTag,
            'digital_signature_img' => $digitalSigImgTag,
            'digital_signature_src' => $digitalSigSrc,
            'stamp'                 => $stampImgTag,
            'company_stamp'         => $stampImgTag,
            'stamp_img'             => $stampImgTag,
            'stamp_src'             => $stampSrc,
            'signature_and_stamp'   => $signatureStampBlock,
            'signature_stamp_block' => $signatureStampBlock,
        ];
    }

    public function templateView($id)
    {
        $model = new \App\Models\ExprienceLetterModel();
        $template = $model->find($id);

        if (!$template) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Template not found.'
            ]);
        }

        $companyModel = new \App\Models\CompanyLogoModel();
        $company = $companyModel->first() ?? [];
        $sigData = $this->getDigitalSignatureData();

        // Fix image paths in content (convert relative to absolute)
        $template['content'] = str_replace(
            ['../upload/', 'src="upload/'],
            [base_url('upload/') . '/', 'src="' . base_url('upload/') . '/'],
            $template['content']
        );

        $template['content'] = str_replace(
            ['{{digital_signature}}', '{{stamp}}', '{{company_stamp}}', '{{signature_and_stamp}}', '{{signature_stamp_block}}'],
            [$sigData['digital_signature'], $sigData['stamp'], $sigData['stamp'], $sigData['signature_and_stamp'], $sigData['signature_and_stamp']],
            $template['content']
        );

        return view('exprience_templetes/template_view', [
            'templates' => $template,
            'company'   => $company,
            'sigData'   => $sigData
        ]);
    }

    public function addemployeePage()
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }
        $userModel = new \App\Models\UserModel();
        $employee = $userModel->whereIn('role', ['hr', 'employee'])->findAll();

        $exprienceModel = new \App\Models\ExprienceLetterModel();
        $template = $exprienceModel->findAll();
        return view('exprience_templetes/add_exp_emp', ['employee' => $employee, 'template' => $template]);
    }

    protected function parseTemplate($templateContent, $data)
    {
        $templateContent = str_replace(["\r", "\t"], '', $templateContent);
        $templateContent = preg_replace('/(&nbsp;|\xC2\xA0){2,}/u', ' ', $templateContent);
        $templateContent = preg_replace('/[ \t]{2,}/', ' ', $templateContent);
        $templateContent = str_replace(['–', '—', '−', '&ndash;', '&mdash;'], '-', $templateContent);

        $templateContent = preg_replace('/<code>\s*(\{\{\s*[a-zA-Z0-9_-]+\s*\}\})\s*<\/code>/i', '$1', $templateContent);
        $templateContent = preg_replace('/<tt>\s*(\{\{\s*[a-zA-Z0-9_-]+\s*\}\})\s*<\/tt>/i', '$1', $templateContent);

        // Auto-inject signature and stamp above signer name if not explicitly placed in template
        if (!empty($data['signature_and_stamp']) && strpos($templateContent, '{{digital_signature}}') === false && strpos($templateContent, '{{signature_and_stamp}}') === false) {
            if (preg_match('/(\{\{\s*signer_name\s*\}\})/i', $templateContent)) {
                $templateContent = preg_replace('/(\{\{\s*signer_name\s*\}\})/i', '{{signature_and_stamp}}<br>$1', $templateContent, 1);
            } elseif (preg_match('/(\{\{\s*created_by\s*\}\})/i', $templateContent)) {
                $templateContent = preg_replace('/(\{\{\s*created_by\s*\}\})/i', '{{signature_and_stamp}}<br>$1', $templateContent, 1);
            } elseif (preg_match('/(<strong>\s*Raj\s+Singh,?\s*<\/strong>)/i', $templateContent)) {
                $templateContent = preg_replace('/(<strong>\s*Raj\s+Singh,?\s*<\/strong>)/i', '{{signature_and_stamp}}<br>$1', $templateContent, 1);
            }
        }

        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $cleanKey = trim($key, '{} ');
                $valStr = (string) $value;
                $templateContent = str_replace('{{' . $cleanKey . '}}', $valStr, $templateContent);
                $templateContent = str_replace('{{ ' . $cleanKey . ' }}', $valStr, $templateContent);
                $templateContent = str_replace('{' . $cleanKey . '}', $valStr, $templateContent);
            }
        }

        $templateContent = preg_replace('/<code>(.*?)<\/code>/i', '$1', $templateContent);
        return $templateContent;
    }

    public function generateExperienceLetter($insert = true)
    {
        $employeeId = $this->request->getPost('employee_id');
        $templateId = $this->request->getPost('template_id');
        $fromDate = $this->request->getPost('from_date');
        $toDate = $this->request->getPost('to_date');
        $loggedInUserId = (session_status() === PHP_SESSION_ACTIVE && function_exists('session') && session()->has('user_id')) ? session()->get('user_id') : 1;

        $userModel = new \App\Models\UserInfoModel();
        $templateModel = new \App\Models\ExprienceLetterModel();
        $companyModel = new \App\Models\CompanyLogoModel();
        $designationModel = new \App\Models\DesignationModel();
        $departmentModel = new \App\Models\DepartmentModel();
        $generatedLetterModel = new \App\Models\ExprienceModel();

        $existing = $generatedLetterModel
            ->where('employee_id', $employeeId)
            ->first();

        if ($existing && $insert) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Experience letter has already been generated for this employee for the selected period.'
            ])->setStatusCode(400);
        }

        if ($insert && !$existing) {
            $generatedLetterModel->insert([
                'employee_id' => $employeeId,
                'template_id' => $templateId,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'generated_by' => $loggedInUserId
            ]);
        }

        $employee = $userModel->where('user_id', $employeeId)->first();
        if (!$employee) {
            $employee = $userModel->find($employeeId);
        }

        $template = $templateModel->find($templateId);
        $company = $companyModel->first();
        $todate = $generatedLetterModel
            ->where('employee_id', $employeeId)
            ->where('template_id', $templateId)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$employee || !$template || !$company) {
            return redirect()->back()->with('error', 'Invalid employee, template, or company data.');
        }

        $logoSrc = '';
        if ($company && !empty($company['logo_img'])) {
            $companyLogoPath = FCPATH . 'upload/' . $company['logo_img'];
            if (file_exists($companyLogoPath)) {
                $mime = mime_content_type($companyLogoPath) ?: 'image/png';
                $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($companyLogoPath));
            }
        }

        $headerImgSrc = '';
        if (!empty($template['template_header']) && file_exists(FCPATH . 'upload/templates/' . $template['template_header'])) {
            $hdrPath = FCPATH . 'upload/templates/' . $template['template_header'];
            $mime = mime_content_type($hdrPath) ?: 'image/png';
            $headerImgSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($hdrPath));
        }

        $footerImgSrc = '';
        if (!empty($template['template_footer']) && file_exists(FCPATH . 'upload/templates/' . $template['template_footer'])) {
            $ftrPath = FCPATH . 'upload/templates/' . $template['template_footer'];
            $mime = mime_content_type($ftrPath) ?: 'image/png';
            $footerImgSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($ftrPath));
        }

        $designation = !empty($employee['designation_id']) ? $designationModel->find($employee['designation_id']) : null;
        $department = !empty($employee['department_id']) ? $departmentModel->find($employee['department_id']) : null;
        $designationName = $designation['designation_name'] ?? 'N/A';
        $departmentName = $department['department_name'] ?? 'N/A';
        $creator = !empty($template['created_by']) ? $userModel->find($template['created_by']) : null;

        $fromDateRaw = !empty($fromDate) ? $fromDate : (!empty($employee['joining_date']) ? $employee['joining_date'] : date('Y-m-d'));
        $toDateRaw = !empty($toDate) ? $toDate : (!empty($todate['to_date']) ? $todate['to_date'] : date('Y-m-d'));

        $fromDateFormatted = date('jS F Y', strtotime($fromDateRaw));
        $toDateFormatted = date('jS M Y', strtotime($toDateRaw));

        $gender = strtolower($employee['gender'] ?? 'male');
        $salutation = ($gender === 'female') ? 'Ms.' : 'Mr.';
        $his_her = ($gender === 'female') ? 'her' : 'his';
        $he_she = ($gender === 'female') ? 'She' : 'He';
        $him_her = ($gender === 'female') ? 'her' : 'him';
        $employeeFullName = trim(($employee['firstname'] ?? '') . ' ' . ($employee['lastname'] ?? ''));

        $placeholders = [
            'employee_name'       => $employeeFullName,
            'candidate_name'      => $employeeFullName,
            'salutation'          => $salutation,
            'title_employee_name' => $salutation . ' ' . $employeeFullName,
            'designation'         => $designationName,
            'job_title'           => $designationName,
            'position'            => $designationName,
            'department'          => $departmentName,
            'department_name'     => $departmentName,
            'address_1'           => $employee['address_1'] ?? '',
            'address'             => $employee['address_1'] ?? '',
            'employee_address'    => $employee['address_1'] ?? '',
            'from_date'           => $fromDateFormatted,
            'start_date'          => $fromDateFormatted,
            'joining_date'        => $fromDateFormatted,
            'to_date'             => $toDateFormatted,
            'end_date'            => $toDateFormatted,
            'leaving_date'        => $toDateFormatted,
            'his_her'             => $his_her,
            'his_her_cap'         => ucfirst($his_her),
            'he_she'              => $he_she,
            'he_she_lower'        => strtolower($he_she),
            'him_her'             => $him_her,
            'email'               => $employee['email'] ?? '',
            'employee_email'      => $employee['email'] ?? '',
            'phone_number'        => $employee['phone_number'] ?? '',
            'employee_phone'      => $employee['phone_number'] ?? '',
            'current_date'        => date('F d, Y'),
            'today_date'          => date('F d, Y'),
            'company_name'        => $company['company_name'] ?? 'Fablead Developers Technolab',
            'company_address'     => !empty($company['company_address']) ? $company['company_address'] : 'Fablead Developers Technolab, Surat , Gujarat , India',
            'company_email'       => $company['company_email'] ?? '',
            'company_phone'       => $company['company_phone'] ?? '',
            'created_by'          => $creator['firstname'] ?? 'Raj Singh',
            'signer_name'         => 'Raj Singh',
            'signer_designation'  => 'Co-Founder / CTO / CEO',
            'creator_designation' => 'Co-Founder / CTO / CEO',
        ];

        $sigData = $this->getDigitalSignatureData();
        $placeholders['digital_signature']     = $sigData['digital_signature'];
        $placeholders['digital_signature_img'] = $sigData['digital_signature_img'];
        $placeholders['digital_signature_src'] = $sigData['digital_signature_src'];
        $placeholders['stamp']                 = $sigData['stamp'];
        $placeholders['company_stamp']         = $sigData['company_stamp'];
        $placeholders['stamp_img']             = $sigData['stamp_img'];
        $placeholders['stamp_src']             = $sigData['stamp_src'];
        $placeholders['signature_and_stamp']   = $sigData['signature_and_stamp'];
        $placeholders['signature_stamp_block'] = $sigData['signature_stamp_block'];

        $parsedContent = $this->parseTemplate($template['content'], $placeholders);

        $html = view('exprience_templetes/experience_letter_preview', [
            'title'           => $template['title'] ?? 'Experience Letter',
            'content'         => $parsedContent,
            'logo_src'        => $logoSrc,
            'header_img_src'  => $headerImgSrc,
            'footer_img_src'  => $footerImgSrc,
            'company_name'    => $company['company_name'] ?? 'Fablead Developers Technolab',
            'company_address' => 'Fablead Developers Technolab, Surat , Gujarat , India',
        ]);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Times-Roman');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeName = str_replace(' ', '_', $employeeFullName);
        $currentDateTime = date('Ymd_His');
        $filename = "experience_letter_{$safeName}_{$currentDateTime}.pdf";

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output())
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function previewSamplePdf($templateId)
    {
        $model = new \App\Models\ExprienceLetterModel();
        $template = $model->find($templateId);
        if (!$template) {
            return $this->response->setStatusCode(404)->setBody('Template not found');
        }

        $companyModel = new \App\Models\CompanyLogoModel();
        $company = $companyModel->first() ?? [];

        $logoSrc = '';
        if (!empty($company['logo_img'])) {
            $companyLogoPath = FCPATH . 'upload/' . $company['logo_img'];
            if (file_exists($companyLogoPath)) {
                $mime = mime_content_type($companyLogoPath) ?: 'image/png';
                $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($companyLogoPath));
            }
        }

        $headerImgSrc = '';
        if (!empty($template['template_header']) && file_exists(FCPATH . 'upload/templates/' . $template['template_header'])) {
            $hdrPath = FCPATH . 'upload/templates/' . $template['template_header'];
            $mime = mime_content_type($hdrPath) ?: 'image/png';
            $headerImgSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($hdrPath));
        }

        $footerImgSrc = '';
        if (!empty($template['template_footer']) && file_exists(FCPATH . 'upload/templates/' . $template['template_footer'])) {
            $ftrPath = FCPATH . 'upload/templates/' . $template['template_footer'];
            $mime = mime_content_type($ftrPath) ?: 'image/png';
            $footerImgSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($ftrPath));
        }

        // Demo sample data matching reference PDF Experience-letter-aryan_7188.pdf
        $placeholders = [
            'employee_name'       => 'Aryan Patel',
            'candidate_name'      => 'Aryan Patel',
            'salutation'          => 'Mr.',
            'title_employee_name' => 'Mr. Aryan Patel',
            'designation'         => 'Junior Web Developer',
            'job_title'           => 'Junior Web Developer',
            'position'            => 'Junior Web Developer',
            'department'          => 'Web Development',
            'department_name'     => 'Web Development',
            'address_1'           => 'Surat, Gujarat, India',
            'address'             => 'Surat, Gujarat, India',
            'employee_address'    => 'Surat, Gujarat, India',
            'from_date'           => '3rd June 2024',
            'start_date'          => '3rd June 2024',
            'joining_date'        => '3rd June 2024',
            'to_date'             => '10th Dec 2025',
            'end_date'            => '10th Dec 2025',
            'leaving_date'        => '10th Dec 2025',
            'his_her'             => 'his',
            'his_her_cap'         => 'His',
            'he_she'              => 'He',
            'he_she_lower'        => 'he',
            'him_her'             => 'him',
            'email'               => 'aryan.patel@example.com',
            'employee_email'      => 'aryan.patel@example.com',
            'phone_number'        => '9876543210',
            'employee_phone'      => '9876543210',
            'current_date'        => date('F d, Y'),
            'today_date'          => date('F d, Y'),
            'company_name'        => $company['company_name'] ?? 'Fablead Developers Technolab',
            'company_address'     => !empty($company['company_address']) ? $company['company_address'] : 'Fablead Developers Technolab, Surat , Gujarat , India',
            'company_email'       => $company['company_email'] ?? 'info@fableadtechnolabs.com',
            'company_phone'       => $company['company_phone'] ?? '9909910855',
            'created_by'          => 'Raj Singh',
            'signer_name'         => 'Raj Singh',
            'signer_designation'  => 'Co-Founder / CTO / CEO',
            'creator_designation' => 'Co-Founder / CTO / CEO',
        ];

        $sigData = $this->getDigitalSignatureData();
        $placeholders['digital_signature']     = $sigData['digital_signature'];
        $placeholders['digital_signature_img'] = $sigData['digital_signature_img'];
        $placeholders['digital_signature_src'] = $sigData['digital_signature_src'];
        $placeholders['stamp']                 = $sigData['stamp'];
        $placeholders['company_stamp']         = $sigData['company_stamp'];
        $placeholders['stamp_img']             = $sigData['stamp_img'];
        $placeholders['stamp_src']             = $sigData['stamp_src'];
        $placeholders['signature_and_stamp']   = $sigData['signature_and_stamp'];
        $placeholders['signature_stamp_block'] = $sigData['signature_stamp_block'];

        $parsedContent = $this->parseTemplate($template['content'], $placeholders);

        $html = view('exprience_templetes/experience_letter_preview', [
            'title'           => $template['title'] ?? 'Experience Letter',
            'content'         => $parsedContent,
            'logo_src'        => $logoSrc,
            'header_img_src'  => $headerImgSrc,
            'footer_img_src'  => $footerImgSrc,
            'company_name'    => $company['company_name'] ?? 'Fablead Developers Technolab',
            'company_address' => 'Fablead Developers Technolab, Surat , Gujarat , India',
        ]);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Times-Roman');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output())
            ->setHeader('Content-Disposition', 'inline; filename="Experience_Letter_Preview.pdf"');
    }

    public function getJoiningDate($id)
    {
        $userModel = new \App\Models\UserInfoModel();
        $employee = $userModel->where('user_id', $id)->first();
        if (!$employee) {
            $employee = $userModel->find($id);
        }

        $res = $this->response ?? service('response');
        if ($employee && !empty($employee['joining_date'])) {
            return $res->setJSON(['joining_date' => $employee['joining_date']]);
        } else {
            return $res->setJSON(['joining_date' => null]);
        }
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
        $generatedLetterModel = new \App\Models\ExprienceModel();
        $jobs = $generatedLetterModel->select('exprience.id, exprience.employee_id, exprience.template_id, user_info.firstname, user_info.lastname, exprience.from_date, exprience_letter_templetes.title, exprience.to_date')
            ->join('user_info', 'user_info.user_id = exprience.employee_id')
            ->join('exprience_letter_templetes', 'exprience_letter_templetes.id = exprience.template_id')
            ->orderBy('exprience.created_at', 'DESC')
            ->findAll();

        return $this->respond(['status' => 'success', 'data' => $jobs]);
    }

    public function getGeneratedLetter($id)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $res = $this->response ?? service('response');
        $generatedModel = new \App\Models\ExprienceModel();
        $letter = $generatedModel->find($id);

        if (!$letter) {
            return $res->setJSON(['status' => 'error', 'message' => 'Generated letter not found'])->setStatusCode(404);
        }

        return $res->setJSON(['status' => 'success', 'data' => $letter]);
    }

    public function updateGeneratedLetter($id)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        $res = $this->response ?? service('response');
        $generatedModel = new \App\Models\ExprienceModel();
        $letter = $generatedModel->find($id);

        if (!$letter) {
            return $res->setJSON(['status' => 'error', 'message' => 'Generated letter not found'])->setStatusCode(404);
        }

        $req = $this->request ?? service('request');
        $employeeId = $req->getPost('employee_id');
        $templateId = $req->getPost('template_id');
        $fromDate = $req->getPost('from_date');
        $toDate = $req->getPost('to_date');

        if (empty($employeeId) || empty($templateId) || empty($fromDate) || empty($toDate)) {
            return $res->setJSON(['status' => 'error', 'message' => 'All fields are required.'])->setStatusCode(422);
        }

        if (strtotime($fromDate) > strtotime($toDate)) {
            return $res->setJSON(['status' => 'error', 'message' => 'From Date cannot be later than To Date.'])->setStatusCode(422);
        }

        $generatedModel->update($id, [
            'employee_id' => $employeeId,
            'template_id' => $templateId,
            'from_date'   => $fromDate,
            'to_date'     => $toDate,
        ]);

        return $res->setJSON(['status' => 'success', 'message' => 'Generated experience letter updated successfully.']);
    }
    public function downloadExperienceLetter($generatedLetterId)
    {
        $generatedModel = new \App\Models\ExprienceModel();
        $data = $generatedModel->find($generatedLetterId);



        if (!$data) {
            return redirect()->back()->with('error', 'Letter not found.');
        }

        // Reuse your existing logic
        $this->request->setGlobal('post', [
            'employee_id' => $data['employee_id'],
            'template_id' => $data['template_id'],
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date']
        ]);

        return $this->generateExperienceLetter(false); // 👈 calls the original method
    }
    public function deleteletter($id = null)
    {
        $user = $this->authService->check();
        if (!$user) {
            return $this->failUnauthorized('Unauthorized: Token missing or invalid');
        }

        if ($user->role !== 'admin') {
            return $this->failForbidden('Forbidden: Only Admin can delete job records');
        }

        // Check if there are candidates applied for this job
        $generatedModel = new \App\Models\ExprienceModel();
        // Proceed with job deletion if no candidates are linked
        if ($generatedModel->delete($id)) {
            return $this->respond(['status' => 'success', 'message' => 'exprience letter deleted successfully']);
        }

        return $this->respond(['status' => 'error', 'message' => 'Failed to delete exprience record'], 500);
    }
    public function getTemplateById($id)
    {
        $model = new \App\Models\ExprienceLetterModel();
        $template = $model->find($id);

        if (!$template) {
            return $this->response->setJSON(['status' => false, 'message' => 'Template not found'])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'status' => true,
            'data' => [
                'id' => $template['id'],
                'title' => $template['title'],
                'content' => $template['content'],
                'template_header' => !empty($template['template_header']) ? base_url('upload/templates/' . $template['template_header']) : null,
                'template_footer' => !empty($template['template_footer']) ? base_url('upload/templates/' . $template['template_footer']) : null,
            ]
        ]);
    }
}
