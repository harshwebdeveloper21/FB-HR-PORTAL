<?php
namespace App\Controllers\Api;
use App\Models\OfferLetterTemplateModel;
use App\Services\AuthService;
use CodeIgniter\RESTful\ResourceController;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class OfferLetterTemplateController extends ResourceController
{
    protected $templateModel;
    private $authService;

    public function __construct()
    {
        $this->templateModel = new OfferLetterTemplateModel();
        $this->authService = new AuthService(service('request'));

        helper(['form']);
    }

    public function index()
    {
        return view('offer_templates/create');
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

        $fileImg = $this->request->getFile('template_img');
        if ($fileImg && $fileImg->isValid() && !$fileImg->hasMoved()) {
            $rules['template_img'] = 'is_image[template_img]|max_size[template_img,2048]|mime_in[template_img,image/jpg,image/jpeg,image/png,image/webp]';
        }

        $fileHeader = $this->request->getFile('template_header');
        if ($fileHeader && $fileHeader->isValid() && !$fileHeader->hasMoved()) {
            $rules['template_header'] = 'is_image[template_header]|max_size[template_header,2048]|mime_in[template_header,image/jpg,image/jpeg,image/png,image/webp]';
        }

        $fileFooter = $this->request->getFile('template_footer');
        if ($fileFooter && $fileFooter->isValid() && !$fileFooter->hasMoved()) {
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
            'template_img' => [
                'is_image' => 'Only valid image formats are allowed.',
                'max_size' => 'The image size must not exceed 2MB.',
                'mime_in' => 'Only JPG, JPEG, PNG, and WEBP formats are allowed.',
            ],
            'template_header' => [
                'is_image' => 'Header must be a valid image format.',
                'max_size' => 'Header image size must not exceed 2MB.',
                'mime_in' => 'Only JPG, JPEG, PNG, and WEBP formats are allowed.',
            ],
            'template_footer' => [
                'is_image' => 'Footer must be a valid image format.',
                'max_size' => 'Footer image size must not exceed 2MB.',
                'mime_in' => 'Only JPG, JPEG, PNG, and WEBP formats are allowed.',
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        $model = new \App\Models\OfferLetterTemplateModel();
        $id = $this->request->getPost('id');

        // Prepare pages array
        $pages = $this->request->getPost('pages');
        if (!is_array($pages) || empty($pages)) {
            $pages = [];
            $p1 = $this->request->getPost('content');
            $p2 = $this->request->getPost('content_page2');
            if ($p1 !== null) $pages[] = $p1;
            if (!empty($p2)) $pages[] = $p2;
        }
        $pages = array_values($pages);

        // Prepare data including created_by
        $data = [
            'title' => $this->request->getPost('title'),
            'content' => $pages[0] ?? '',
            'content_page2' => $pages[1] ?? null,
            'content_pages' => json_encode($pages),
            'created_by' => $user->sub,
        ];

        // Handle image upload
        if ($fileImg && $fileImg->isValid() && !$fileImg->hasMoved()) {
            $newName = $fileImg->getRandomName();
            $fileImg->move(FCPATH . 'upload/', $newName);
            $data['template_img'] = $newName;
        }

        // Handle header image upload
        if ($fileHeader && $fileHeader->isValid() && !$fileHeader->hasMoved()) {
            $headerName = $fileHeader->getRandomName();
            $fileHeader->move(FCPATH . 'upload/', $headerName);
            $data['template_header'] = $headerName;
        }

        // Handle footer image upload
        if ($fileFooter && $fileFooter->isValid() && !$fileFooter->hasMoved()) {
            $footerName = $fileFooter->getRandomName();
            $fileFooter->move(FCPATH . 'upload/', $footerName);
            $data['template_footer'] = $footerName;
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

        return view('offer_templates/view');
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
        return view('offer_templates/edit_page', ['id' => $id]);
    }

    public function getTemplate($id)
    {
        $model = new \App\Models\OfferLetterTemplateModel();
        $template = $model->find($id);

        if ($template) {
            $headerExists = !empty($template['template_header']) && file_exists(FCPATH . 'upload/' . $template['template_header']);
            $footerExists = !empty($template['template_footer']) && file_exists(FCPATH . 'upload/' . $template['template_footer']);
            $imgExists = !empty($template['template_img']) && file_exists(FCPATH . 'upload/' . $template['template_img']);

            $template['template_header_url'] = $headerExists ? base_url('upload/' . $template['template_header']) : '';
            $template['template_footer_url'] = $footerExists ? base_url('upload/' . $template['template_footer']) : '';
            $template['template_img_url'] = $imgExists ? base_url('upload/' . $template['template_img']) : '';

            // Extract all pages
            $pages = [];
            if (!empty($template['content_pages'])) {
                $decoded = json_decode($template['content_pages'], true);
                if (is_array($decoded) && count($decoded) > 0) {
                    $pages = $decoded;
                }
            }
            if (empty($pages)) {
                if (!empty($template['content'])) $pages[] = $template['content'];
                if (!empty($template['content_page2'])) $pages[] = $template['content_page2'];
            }
            if (empty($pages)) {
                $pages = [''];
            }
            $template['pages'] = $pages;

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
        ];

        // Content validation: check pages array or content field
        $pages = $this->request->getPost('pages');
        if (!is_array($pages) || empty($pages)) {
            $rules['content'] = 'required|min_length[10]';
        }

        $fileImg = $this->request->getFile('template_img');
        if ($fileImg && $fileImg->isValid() && !$fileImg->hasMoved()) {
            $rules['template_img'] = 'is_image[template_img]|max_size[template_img,2048]|mime_in[template_img,image/jpg,image/jpeg,image/png,image/webp]';
        }

        $fileHeader = $this->request->getFile('template_header');
        if ($fileHeader && $fileHeader->isValid() && !$fileHeader->hasMoved()) {
            $rules['template_header'] = 'is_image[template_header]|max_size[template_header,2048]|mime_in[template_header,image/jpg,image/jpeg,image/png,image/webp]';
        }

        $fileFooter = $this->request->getFile('template_footer');
        if ($fileFooter && $fileFooter->isValid() && !$fileFooter->hasMoved()) {
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
            'template_img' => [
                'is_image' => 'Only valid image formats are allowed.',
                'max_size' => 'The image size must not exceed 2MB.',
                'mime_in' => 'Only JPG, JPEG, PNG, and WEBP formats are allowed.',
            ],
            'template_header' => [
                'is_image' => 'Header must be a valid image format.',
                'max_size' => 'Header image size must not exceed 2MB.',
                'mime_in' => 'Only JPG, JPEG, PNG, and WEBP formats are allowed.',
            ],
            'template_footer' => [
                'is_image' => 'Footer must be a valid image format.',
                'max_size' => 'Footer image size must not exceed 2MB.',
                'mime_in' => 'Only JPG, JPEG, PNG, and WEBP formats are allowed.',
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        $model = new \App\Models\OfferLetterTemplateModel();
        $template = $model->find($id);

        if (!$template) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Template not found']);
        }

        if (!is_array($pages) || empty($pages)) {
            $pages = [];
            $p1 = $this->request->getPost('content');
            $p2 = $this->request->getPost('content_page2');
            if ($p1 !== null) $pages[] = $p1;
            if (!empty($p2)) $pages[] = $p2;
        }
        $pages = array_values($pages);

        $data = [
            'title' => $this->request->getPost('title'),
            'content' => $pages[0] ?? '',
            'content_page2' => $pages[1] ?? null,
            'content_pages' => json_encode($pages),
        ];

        // Save image if uploaded
        if ($fileImg && $fileImg->isValid() && !$fileImg->hasMoved()) {
            $newName = $fileImg->getRandomName();
            $fileImg->move(FCPATH . 'upload/', $newName);
            $data['template_img'] = $newName;
        }

        // Save header if uploaded
        if ($fileHeader && $fileHeader->isValid() && !$fileHeader->hasMoved()) {
            $headerName = $fileHeader->getRandomName();
            $fileHeader->move(FCPATH . 'upload/', $headerName);
            $data['template_header'] = $headerName;
        }

        // Save footer if uploaded
        if ($fileFooter && $fileFooter->isValid() && !$fileFooter->hasMoved()) {
            $footerName = $fileFooter->getRandomName();
            $fileFooter->move(FCPATH . 'upload/', $footerName);
            $data['template_footer'] = $footerName;
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
        $model = new \App\Models\OfferLetterTemplateModel();
        $template = $model->find($id);

        if (!$template) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Template not found.'
            ]);
        }

        $sigData = $this->getDigitalSignatureData();

        // Fix image paths in content (convert relative to absolute)
        $template['content'] = str_replace(
            ['../upload/', 'src="upload/'],
            [base_url('upload/') . '/', 'src="' . base_url('upload/') . '/'],
            $template['content']
        );

        // Strip any remaining data-bullet-char attributes from existing DB data for the view
        if (!empty($template['content_pages'])) {
            $template['content_pages'] = preg_replace('/ data-bullet-char="[^"]*"/', '', $template['content_pages']);
            $template['content_pages'] = preg_replace('/ data-bullet-char=\\\\"[^\\\\"]*\\\\\"/', '', $template['content_pages']);
            // Replace signature placeholders for browser preview
            $template['content_pages'] = str_replace(
                ['{{digital_signature}}', '{{stamp}}', '{{company_stamp}}', '{{signature_and_stamp}}', '{{signature_stamp_block}}'],
                [$sigData['digital_signature'], $sigData['stamp'], $sigData['stamp'], $sigData['signature_and_stamp'], $sigData['signature_and_stamp']],
                $template['content_pages']
            );
        }
        if (!empty($template['content'])) {
            $template['content'] = preg_replace('/ data-bullet-char="[^"]*"/', '', $template['content']);
            $template['content'] = str_replace(
                ['{{digital_signature}}', '{{stamp}}', '{{company_stamp}}', '{{signature_and_stamp}}', '{{signature_stamp_block}}'],
                [$sigData['digital_signature'], $sigData['stamp'], $sigData['stamp'], $sigData['signature_and_stamp'], $sigData['signature_and_stamp']],
                $template['content']
            );
        }
        
        return view('offer_templates/template_view', [
            'templates' => $template,
            'sigData'   => $sigData
        ]);
    }

    function parseTemplate($templateContent, $data)
    {
        // Clean up whitespace for PDF output
        $templateContent = str_replace(["\r", "\t"], '', $templateContent);

        // Replace &nbsp; HTML entities with regular spaces
        $templateContent = str_replace('&nbsp;', ' ', $templateContent);

        $templateContent = str_replace("\xc2\xa0", ' ', $templateContent);

        // Collapse multiple consecutive spaces
        $templateContent = preg_replace('/[ \t]{2,}/', ' ', $templateContent);

        $templateContent = str_replace(['–', '—', '−', '&ndash;', '&mdash;'], '-', $templateContent);

        $templateContent = preg_replace(
            '/(<span[^>]+class="[^"]*custom-bullet-char[^"]*"[^>]+style=")([^"]*?)\bwidth\s*:\s*\d+px\s*;?\s*([^"]*")/i',
            '$1$2$3',
            $templateContent
        );

        // Remove <code> and <tt> tags wrapping placeholders (e.g. <code>{{job_title}}</code>)
        $templateContent = preg_replace('/<code>\s*(\{\{\s*[a-zA-Z0-9_-]+\s*\}\})\s*<\/code>/i', '$1', $templateContent);
        $templateContent = preg_replace('/<tt>\s*(\{\{\s*[a-zA-Z0-9_-]+\s*\}\})\s*<\/tt>/i', '$1', $templateContent);

        // Auto-inject signature and stamp above signer name if not explicitly placed in template
        if (!empty($data['signature_and_stamp']) && strpos($templateContent, '{{digital_signature}}') === false && strpos($templateContent, '{{signature_and_stamp}}') === false) {
            if (preg_match('/(\{\{\s*signer_name\s*\}\})/i', $templateContent)) {
                $templateContent = preg_replace('/(\{\{\s*signer_name\s*\}\})/i', '{{signature_and_stamp}}<br>$1', $templateContent, 1);
            } elseif (preg_match('/(\{\{\s*created_by\s*\}\})/i', $templateContent)) {
                $templateContent = preg_replace('/(\{\{\s*created_by\s*\}\})/i', '{{signature_and_stamp}}<br>$1', $templateContent, 1);
            }
        }

        // Replace all placeholders
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $cleanKey = trim($key, '{} ');
                $valStr = (string) $value;
                $templateContent = str_replace('{{' . $cleanKey . '}}', $valStr, $templateContent);
                $templateContent = str_replace('{{ ' . $cleanKey . ' }}', $valStr, $templateContent);
                $templateContent = str_replace('{' . $cleanKey . '}', $valStr, $templateContent);
            }
        }

        // Clean any remaining code tags around replaced values
        $templateContent = preg_replace('/<code>(.*?)<\/code>/i', '$1', $templateContent);

        // Remove any strikethrough tags (<s>, <strike>, <del>) and text-decoration: line-through styles
        $templateContent = preg_replace('/<\/?(s|strike|del)\b[^>]*>/i', '', $templateContent);
        $templateContent = preg_replace('/text-decoration\s*:\s*line-through;?/i', '', $templateContent);

        $templateContent = preg_replace(
            '/(<span\b[^>]+\bcustom-bullet-char\b[^>]*?)\s+style="[^"]*"([^>]*>)/i',
            '$1$2',
            $templateContent
        );

        $templateContent = preg_replace(
            '/(<span\b[^>]+\bcustom-bullet-char\b[^>]*>[^<]*<\/span>)(.*?)(<\/li>)/is',
            '$1<span class="custom-bullet-text">$2</span>$3',
            $templateContent
        );

        return $templateContent;
    }

    public function generateOfferLetter($candidateId, $templateId)
    {
        $templateModel = new \App\Models\OfferLetterTemplateModel();
        $candidateModel = new \App\Models\CandidateModel();
        $jobModel = new \App\Models\JobModel();
        $departmentModel = new \App\Models\DepartmentModel();
        $companyModel = new \App\Models\CompanyLogoModel();
        $userModel = new \App\Models\UserModel();
        $userInfoModel = new \App\Models\UserInfoModel();
        $onboardingModel = new \App\Models\OnboardingModel();

        $template = $templateModel->find($templateId);
        if (!$template) {
            return $this->response->setStatusCode(404)->setBody('Template not found.');
        }

        $company = $companyModel->first();
        $candidate = (!empty($candidateId) && $candidateId !== 'sample' && $candidateId != 0) ? $candidateModel->find($candidateId) : null;
        $onboarding = $candidate ? $onboardingModel->where('candidate_id', $candidateId)->first() : null;

        $job = ($candidate && !empty($candidate['job_id'])) 
            ? $jobModel->find($candidate['job_id']) 
            : (($onboarding && !empty($onboarding['job_id'])) ? $jobModel->find($onboarding['job_id']) : null);

        $departmentId = ($onboarding && !empty($onboarding['department_id'])) 
            ? $onboarding['department_id'] 
            : ($job['department_id'] ?? null);
        $department = $departmentId ? $departmentModel->find($departmentId) : null;

        $creator = !empty($template['created_by']) ? $userModel->find($template['created_by']) : null;
        $userInfo = $candidate ? $userInfoModel->where('user_id', $candidate['id'])->first() : null;

        // Extract documents submitted — always render as a numbered ordered list
        $rawDocu = !empty($onboarding['docu_submitted']) ? $onboarding['docu_submitted'] : '';

        if (!empty($rawDocu)) {
            // Strip HTML tags to get plain text, then split into lines
            $plainDocu = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</li>'], "\n", $rawDocu));
            $plainDocu = html_entity_decode($plainDocu, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $lines = array_filter(array_map('trim', explode("\n", $plainDocu)));

            $listItems = '';
            foreach ($lines as $line) {
                // Strip any leading numbering like "1." "1)" "1 -" etc.
                $line = preg_replace('/^\d+[\.\)\-\s]+\s*/', '', trim($line));
                if ($line !== '') {
                    $listItems .= '<li>' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</li>';
                }
            }

            if (!empty($listItems)) {
                $docuSubmitted = '<ol style="margin:4px 0 4px 18px; padding-left:4px;">' . $listItems . '</ol>';
            } else {
                $docuSubmitted = nl2br(htmlspecialchars($rawDocu));
            }
        } else {
            // Default fallback documents as a numbered list
            $docuSubmitted = '<ol style="margin:4px 0 4px 18px; padding-left:4px;">'
                . '<li>Class 10<sup>th</sup> Marksheet (Original)</li>'
                . '<li>ID Proof (Aadhaar Card xerox)</li>'
                . '<li>Address Proof (Electricity Bill Xerox)</li>'
                . '</ol>';
        }

        // Extract dates with fallback
        $rawStartDate = !empty($onboarding['start_date'])
            ? $onboarding['start_date']
            : (!empty($userInfo['joining_date'])
                ? $userInfo['joining_date']
                : (!empty($job['post_date']) ? $job['post_date'] : date('Y-m-d')));
        $startDate = date('F j, Y', strtotime($rawStartDate));
        $startDateOrdinal = date('jS F Y', strtotime($rawStartDate));

        $salary = $userInfo['salary'] ?? '';

        // ✅ Safely prepare logo
        $logoSrc = '';
        $logoPath = '';
        if (!empty($template['template_img']) && file_exists(FCPATH . 'upload/' . $template['template_img'])) {
            $logoPath = FCPATH . 'upload/' . $template['template_img'];
        } elseif (!empty($company['logo_img']) && file_exists(FCPATH . 'upload/' . $company['logo_img'])) {
            $logoPath = FCPATH . 'upload/' . $company['logo_img'];
        }

        if (!empty($logoPath) && file_exists($logoPath)) {
            $mimeType = mime_content_type($logoPath) ?: 'image/png';
            $logoSrc = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($logoPath));
            $logoImgTag = '<img src="' . $logoSrc . '" height="70" class="offer-letter-logo" style="max-height: 70px; max-width: 220px;">';
        } else {
            $logoImgTag = '';
        }

        // Header image banner (if uploaded as a file)
        $headerImgSrc = '';
        if (!empty($template['template_header']) && file_exists(FCPATH . 'upload/' . $template['template_header'])) {
            $hPath = FCPATH . 'upload/' . $template['template_header'];
            $hMime = mime_content_type($hPath) ?: 'image/png';
            $headerImgSrc = 'data:' . $hMime . ';base64,' . base64_encode(file_get_contents($hPath));
        }

        // Footer image banner (if uploaded as a file)
        $footerImgSrc = '';
        if (!empty($template['template_footer']) && file_exists(FCPATH . 'upload/' . $template['template_footer'])) {
            $fPath = FCPATH . 'upload/' . $template['template_footer'];
            $fMime = mime_content_type($fPath) ?: 'image/png';
            $footerImgSrc = 'data:' . $fMime . ';base64,' . base64_encode(file_get_contents($fPath));
        }

        // Header text: prefer template_header if text, else default to standard company location
        $templateHeader = (!empty($template['template_header']) && !preg_match('/\.(png|jpe?g|gif|webp)$/i', trim($template['template_header'])))
            ? trim($template['template_header'])
            : 'Fablead Developers Technolab, Surat , Gujarat , India';

        // Candidate / fallback data matching the reference layout
        $candidateName = $candidate['candidate_name'] ?? 'Drashti Shah';
        $candidateEmail = $candidate['email'] ?? '';
        $candidatePhone = $candidate['phone_number'] ?? '';
        $jobTitle = $job['job_title'] ?? 'Trainee Digital Marketing SEO Executive';
        $departmentName = $department['department_name'] ?? 'Digital Marketing';
        $createdByName = !empty($creator['firstname']) ? trim($creator['firstname'] . ' ' . ($creator['lastname'] ?? '')) : 'Raj Singh';
        $creatorEmail = $creator['email'] ?? ($company['company_email'] ?? 'hr@fableadtechnolabs.com');
        $creatorDesignation = $creator['designation'] ?? 'Co-Founder & CEO/CTO';
        $formattedSalary = !empty($salary) ? $salary : '2-month max will be Training Period then after, your position will be Trainee Digital Marketing SEO Executive and salary Based on your performance';

        $sigData = $this->getDigitalSignatureData();

        $data = [
            'logo_img'              => $logoImgTag,
            'logo_src'              => $logoSrc,
            'header_img_src'        => $headerImgSrc,
            'footer_img_src'        => $footerImgSrc,
            'template_title'        => 'JOINING LETTER',
            'template_header'       => $templateHeader,
            'company_name'          => $company['company_name'] ?? 'Fablead Developers Technolab',
            'company_address'       => !empty($company['company_address']) ? $company['company_address'] : 'Fablead Developers Technolab, Surat , Gujarat , India',
            'company_phone'         => $company['company_phone'] ?? '9909910855',
            'company_email'         => $company['company_email'] ?? 'info@fableadtechnolabs.com',
            'today_date'            => date('F j, Y'),
            'current_date'          => date('F j, Y'),
            'candidate_name'        => $candidateName,
            'employee_name'         => $candidateName,
            'email'                 => $candidateEmail,
            'candidate_email'       => $candidateEmail,
            'employee_email'        => $candidateEmail,
            'phone_number'          => $candidatePhone,
            'candidate_phone'       => $candidatePhone,
            'employee_phone'        => $candidatePhone,
            'job_title'             => $jobTitle,
            'designation'           => $jobTitle,
            'position'              => $jobTitle,
            'start_date'            => $startDate,
            'joining_date'          => $startDate,
            'start_date_ordinal'    => $startDateOrdinal,
            'department_name'       => $departmentName,
            'department'            => $departmentName,
            'created_by'            => $createdByName,
            'signer_name'           => $createdByName,
            'creator_email'         => $creatorEmail,
            'creator_designation'   => $creatorDesignation,
            'signer_designation'    => $creatorDesignation,
            'salary'                => $formattedSalary,
            'salary_terms'          => $formattedSalary,
            'docu_submitted'        => $docuSubmitted,
            'documents_submitted'   => $docuSubmitted,
            'submitted_documents'   => $docuSubmitted,
            'reporting_to'          => 'Simran Goswami',
            'supervisor'            => 'Simran Goswami',
            'working_hours'         => '09:30 AM till 06:15 PM (Monday to Friday)',
            'digital_signature'     => $sigData['digital_signature'],
            'digital_signature_img' => $sigData['digital_signature_img'],
            'digital_signature_src' => $sigData['digital_signature_src'],
            'stamp'                 => $sigData['stamp'],
            'company_stamp'         => $sigData['company_stamp'],
            'stamp_img'             => $sigData['stamp_img'],
            'stamp_src'             => $sigData['stamp_src'],
            'signature_and_stamp'   => $sigData['signature_and_stamp'],
            'signature_stamp_block' => $sigData['signature_stamp_block'],
        ];

        // Extract all pages
        $pages = [];
        if (!empty($template['content_pages'])) {
            $decoded = json_decode($template['content_pages'], true);
            if (is_array($decoded) && count($decoded) > 0) {
                $pages = $decoded;
            }
        }
        if (empty($pages)) {
            if (!empty($template['content'])) $pages[] = $template['content'];
            if (!empty($template['content_page2'])) $pages[] = $template['content_page2'];
        }
        if (empty($pages)) {
            $pages = [''];
        }

        $parsedPages = [];
        foreach ($pages as $p) {
            // Strip any remaining data-bullet-char attributes from existing DB data
            $cleanedHtml = preg_replace('/ data-bullet-char="[^"]*"/', '', $p);
            $cleanedHtml = preg_replace('/ data-bullet-char=\\\\"[^\\\\"]*\\\\\"/', '', $cleanedHtml);
            
            $parsedPages[] = $this->parseTemplate($cleanedHtml, $data);
        }

        $finalHtml = view('offer_templates/offer_letter_preview', array_merge($data, [
            'parsed_pages' => $parsedPages,
            'content' => $parsedPages[0] ?? '',
            'content_page2' => $parsedPages[1] ?? '',
        ]));

        // Generate PDF with Dompdf configured for Times New Roman font and clean layout
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Times-Roman');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($finalHtml, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeTitle = 'Joining_Letter';
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $candidateName);
        $filename = "{$safeTitle}_{$safeName}.pdf";

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Preview sample PDF for a template
     */
    public function previewSamplePdf($templateId)
    {
        return $this->generateOfferLetter(0, $templateId);
    }

    public function getOfferTemplate($id)
    {
        $model = new \App\Models\OfferLetterTemplateModel();
        $template = $model->find($id);

        if (!$template) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Template not found.'
            ])->setStatusCode(404);
        }

        $headerExists = !empty($template['template_header']) && file_exists(FCPATH . 'upload/' . $template['template_header']);
        $footerExists = !empty($template['template_footer']) && file_exists(FCPATH . 'upload/' . $template['template_footer']);
        $imgExists = !empty($template['template_img']) && file_exists(FCPATH . 'upload/' . $template['template_img']);

        $pages = [];
        if (!empty($template['content_pages'])) {
            $decoded = json_decode($template['content_pages'], true);
            if (is_array($decoded) && count($decoded) > 0) {
                $pages = $decoded;
            }
        }
        if (empty($pages)) {
            if (!empty($template['content'])) $pages[] = $template['content'];
            if (!empty($template['content_page2'])) $pages[] = $template['content_page2'];
        }
        if (empty($pages)) {
            $pages = [''];
        }

        return $this->response->setJSON([
            'status' => true,
            'data' => [
                'title' => $template['title'],
                'template_header' => $headerExists ? base_url('upload/' . $template['template_header']) : '',
                'template_footer' => $footerExists ? base_url('upload/' . $template['template_footer']) : '',
                'template_header_url' => $headerExists ? base_url('upload/' . $template['template_header']) : '',
                'template_footer_url' => $footerExists ? base_url('upload/' . $template['template_footer']) : '',
                'template_header_file' => $headerExists ? $template['template_header'] : '',
                'template_footer_file' => $footerExists ? $template['template_footer'] : '',
                'content' => $template['content'],
                'content_page2' => $template['content_page2'] ?? '',
                'content_pages' => $template['content_pages'] ?? '',
                'pages' => $pages,
                'template_img' => $imgExists ? base_url('upload/' . $template['template_img']) : '',
                'template_img_url' => $imgExists ? base_url('upload/' . $template['template_img']) : '',
            ]
        ]);
    }

    /**
     * Export Offer Letter Templates to styled Excel (.xlsx)
     */
    public function exportExcel()
    {
        $user = $this->authService->user();
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Unauthorized']);
        }

        $search = $this->request->getGet('search');

        $builder = $this->templateModel->builder();
        $builder->select('offer_letter_templates.*, user_info.firstname, user_info.lastname')
            ->join('user_info', 'user_info.user_id = offer_letter_templates.created_by', 'left');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('offer_letter_templates.title', $search)
                ->orLike('offer_letter_templates.content', $search)
                ->groupEnd();
        }

        $records = $builder->orderBy('offer_letter_templates.id', 'DESC')->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Offer Templates');

        $headers = [
            'A1' => 'S.No',
            'B1' => 'Template ID',
            'C1' => 'Template Title',
            'D1' => 'Template Header',
            'E1' => 'Content Preview',
            'F1' => 'Created By',
            'G1' => 'Created Date'
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E66136']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $rowNum = 2;
        $sno = 1;
        foreach ($records as $item) {
            $creator = trim(($item['firstname'] ?? '') . ' ' . ($item['lastname'] ?? '')) ?: 'Admin';
            $preview = strip_tags($item['content'] ?? '');
            if (mb_strlen($preview) > 150) {
                $preview = mb_substr($preview, 0, 147) . '...';
            }

            $sheet->setCellValue('A' . $rowNum, $sno++);
            $sheet->setCellValue('B' . $rowNum, 'TMPL-' . sprintf('%03d', $item['id']));
            $sheet->setCellValue('C' . $rowNum, $item['title'] ?? '-');
            $sheet->setCellValue('D' . $rowNum, $item['template_header'] ?? '-');
            $sheet->setCellValue('E' . $rowNum, $preview);
            $sheet->setCellValue('F' . $rowNum, $creator);
            $sheet->setCellValue('G' . $rowNum, !empty($item['created_at']) ? date('Y-m-d H:i', strtotime($item['created_at'])) : '-');

            $rowNum++;
        }

        $lastRow = $rowNum > 2 ? $rowNum - 1 : 2;
        $borderStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
        ];
        $sheet->getStyle('A1:G' . $lastRow)->applyFromArray($borderStyle);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        $filename = 'Offer_Templates_' . date('Y_m_d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
