<?php

namespace App\Controllers\Api;

use App\Models\AnnouncementModel;
use App\Models\UserModel;
use App\Models\DepartmentModel;
use App\Services\AuthService;
use CodeIgniter\RESTful\ResourceController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AnnouncementController extends ResourceController
{
    protected $announcementModel;
    protected $authService;
    protected $userModel;

    protected $notificationModel;

    public function __construct()
    {
        $this->announcementModel = new AnnouncementModel();
        $this->authService = new AuthService(service('request'));
        $this->userModel = new UserModel();
        $this->notificationModel = new \App\Models\NotificationModel();
    }

    /**
     * User View: Listing Page
     */
    public function index()
    {
        if (!$this->authService->check()) {
            return redirect()->to('/login');
        }

        $user = $this->authService->user();
        $userId = $user->sub;
        $userRole = $user->role;

        $type = $this->request->getGet('type');
        $date = $this->request->getGet('date');

        $uRow = $this->userModel->find($userId);
        $myBranchId = $uRow['branch_id'] ?? 0;

        $query = $this->announcementModel
            ->select('announcements.*')
            ->join('users AS creator', 'creator.id = announcements.created_by', 'left')
            ->where('announcements.status', 'Active')
            ->where('announcements.is_deleted', 0);

        // Filter by target audience
        $query->groupStart()
                ->where('announcements.target_audience', 'All Users')
                ->orGroupStart()
                    ->where('announcements.target_audience', 'Specific Role')
                    ->where("FIND_IN_SET('$userRole', announcements.target_roles) >", 0)
                ->groupEnd()
                ->orGroupStart()
                    ->where('announcements.target_audience', 'Specific Users')
                    ->where("FIND_IN_SET('$userId', announcements.target_users) >", 0)
                ->groupEnd()
            ->groupEnd();

        // Enforce branch admin isolation
        $query->groupStart()
                ->where('creator.role !=', 'branch_admin')
                ->orWhere('creator.branch_id', $myBranchId)
                ->orWhere('creator.id IS NULL')
            ->groupEnd();

        if ($type) {
            $query->where('announcements.type', $type);
        }

        if ($date) {
            $query->where('announcements.start_date <=', $date)
                  ->where('announcements.end_date >=', $date);
        }

        $announcements = $query->orderBy('created_at', 'DESC')->findAll();
        
        // Get read status
        $readStatus = $this->announcementModel->getReadStatus($userId);
        $readIds = array_column($readStatus, 'announcement_id');

        return view('announcements/index', [
            'announcements' => $announcements,
            'readIds' => $readIds,
            'role' => $userRole
        ]);
    }

    /**
     * Admin View: Management Page
     */
    public function adminIndex()
    {
        if (!$this->authService->check()) {
            return redirect()->to('/login');
        }

        $user = $this->authService->user();
        if (!in_array($user->role, ['admin', 'hr', 'branch_admin'])) {
            return redirect()->to('/dashboard');
        }

        $query = $this->announcementModel->where('is_deleted', 0);
        if ($user->role === 'branch_admin') {
            $query->where('created_by', $user->sub);
        }
        $announcements = $query->orderBy('created_at', 'DESC')
            ->findAll();

        return view('announcements/admin_index', [
            'announcements' => $announcements,
            'role' => $user->role
        ]);
    }

    /**
     * Admin View: Create Form
     */
    public function create()
    {
        if (!$this->authService->check()) {
            return redirect()->to('/login');
        }

        $user = $this->authService->user();
        if (!in_array($user->role, ['admin', 'hr', 'branch_admin'])) {
            return redirect()->to('/dashboard');
        }

        $usersQuery = $this->userModel->where('is_deleted', 0);
        if ($user->role === 'branch_admin') {
            $uRow = $this->userModel->find($user->sub);
            $usersQuery->where('branch_id', $uRow['branch_id']);
        }
        $users = $usersQuery->findAll();
        
        return view('announcements/create', [
            'users' => $users,
            'role' => $user->role
        ]);
    }

    /**
     * Admin Action: Store
     */
    public function store()
    {
        if (!$this->authService->check()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $user = $this->authService->user();
        if (!$user || !in_array($user->role, ['admin', 'hr', 'branch_admin'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied'])->setStatusCode(403);
        }
        
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'type' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'target_audience' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['status' => 'error', 'messages' => $this->validator->getErrors()]);
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'type' => $this->request->getPost('type'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'target_audience' => $this->request->getPost('target_audience'),
            'status' => $this->request->getPost('status') ?? 'Active',
            'created_by' => $user->sub,
            'is_deleted' => 0
        ];

        if ($data['target_audience'] == 'Specific Role') {
            $roles = $this->request->getPost('target_roles');
            $data['target_roles'] = is_array($roles) ? implode(',', $roles) : $roles;
            $data['target_users'] = null;
        } elseif ($data['target_audience'] == 'Specific Users') {
            $users = $this->request->getPost('target_users');
            $data['target_users'] = is_array($users) ? implode(',', $users) : $users;
            $data['target_roles'] = null;
        } else {
            $data['target_roles'] = null;
            $data['target_users'] = null;
        }

        // File Upload
        $file = $this->request->getFile('attachment');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            if ($file->move(FCPATH . 'uploads/announcements', $newName)) {
                $data['attachment'] = $newName;
            }
        }

        try {
            $announcementId = $this->announcementModel->insert($data);
            if ($announcementId) {
                $this->sendAnnouncementNotifications($data, $announcementId);
            }
            return $this->response->setJSON(['status' => 'success', 'message' => 'Announcement created successfully']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    /**
     * Helper to send notifications for new announcements
     */
    private function sendAnnouncementNotifications($announcementData, $announcementId)
    {
        $targetAudience = $announcementData['target_audience'];
        $title = $announcementData['title'];
        $senderId = $announcementData['created_by'];

        // Get sender's info
        $sender = $this->userModel->find($senderId);
        $senderUsername = $sender['username'] ?? 'Admin/HR';
        $senderRole = $sender ? $sender['role'] : '';

        $recipients = [];

        if ($targetAudience === 'All Users') {
            $recipients = $this->userModel->where('is_deleted', 0)->findAll();
        } elseif ($targetAudience === 'Specific Role') {
            $roles = explode(',', $announcementData['target_roles'] ?? '');
            if (!empty($roles)) {
                $recipients = $this->userModel->whereIn('role', $roles)->where('is_deleted', 0)->findAll();
            }
        } elseif ($targetAudience === 'Specific Users') {
            $userIds = explode(',', $announcementData['target_users'] ?? '');
            if (!empty($userIds)) {
                $recipients = $this->userModel->whereIn('id', $userIds)->where('is_deleted', 0)->findAll();
            }
        }

        $senderBranchId = $sender['branch_id'] ?? 0;

        foreach ($recipients as $recipient) {
            // Apply exclusion rules
            if ($senderRole === 'admin') {
                // When Admin creates: NOT sent to Admin, but sent to HR and all other employees
                if ($recipient['role'] === 'admin') {
                    continue;
                }
            } elseif ($senderRole === 'hr') {
                // When HR creates: NOT sent to the HR who created it. Sent to all others (Admin, other HRs, and employees)
                if ($recipient['id'] == $senderId) {
                    continue;
                }
            } elseif ($senderRole === 'branch_admin') {
                // Branch admin announcements only notify users in the same branch
                if (($recipient['branch_id'] ?? 0) != $senderBranchId) {
                    continue;
                }
            }

            $this->notificationModel->insert([
                'sender_id'    => $senderId,
                'recipient_id' => $recipient['id'],
                'data'         => json_encode([
                    'type'            => 'announcement',
                    'title'           => $title,
                    'message'         => "New Announcement: $title",
                    'username'        => $senderUsername,
                    'announcement_id' => $announcementId
                ]),
                'is_read' => 0
            ]);
        }
    }

    /**
     * Admin View: Edit Form
     */
    public function edit($id = null)
    {
        if (!$this->authService->check()) {
            return redirect()->to('/login');
        }

        $user = $this->authService->user();
        if (!$user || !in_array($user->role, ['admin', 'hr', 'branch_admin'])) {
            return redirect()->to('/dashboard');
        }

        $announcement = $this->announcementModel->find($id);
        if (!$announcement) {
            return redirect()->to('/announcements/admin')->with('error', 'Announcement not found');
        }

        if ($user->role === 'branch_admin' && $announcement['created_by'] != $user->sub) {
            return redirect()->to('/announcements/admin')->with('error', 'Permission denied');
        }

        $usersQuery = $this->userModel->where('is_deleted', 0);
        if ($user->role === 'branch_admin') {
            $uRow = $this->userModel->find($user->sub);
            $usersQuery->where('branch_id', $uRow['branch_id']);
        }
        $users = $usersQuery->findAll();

        return view('announcements/edit', [
            'announcement' => $announcement,
            'users' => $users,
            'role' => $user ? $user->role : null
        ]);
    }

    /**
     * Admin Action: Update
     */
    public function update($id = null)
    {
        if (!$this->authService->check()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $user = $this->authService->user();
        if (!$user || !in_array($user->role, ['admin', 'hr', 'branch_admin'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied'])->setStatusCode(403);
        }

        $announcement = $this->announcementModel->find($id);
        if ($user->role === 'branch_admin' && $announcement && $announcement['created_by'] != $user->sub) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied'])->setStatusCode(403);
        }

        $rules = [
            'title' => 'required',
            'description' => 'required',
            'type' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'target_audience' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['status' => 'error', 'messages' => $this->validator->getErrors()]);
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'type' => $this->request->getPost('type'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'target_audience' => $this->request->getPost('target_audience'),
            'status' => $this->request->getPost('status') ?? 'Active',
        ];

        if ($data['target_audience'] == 'Specific Role') {
            $roles = $this->request->getPost('target_roles');
            $data['target_roles'] = is_array($roles) ? implode(',', $roles) : $roles;
            $data['target_users'] = null;
        } elseif ($data['target_audience'] == 'Specific Users') {
            $users = $this->request->getPost('target_users');
            $data['target_users'] = is_array($users) ? implode(',', $users) : $users;
            $data['target_roles'] = null;
        } else {
            $data['target_roles'] = null;
            $data['target_users'] = null;
        }

        // File Upload
        $file = $this->request->getFile('attachment');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            if ($file->move(FCPATH . 'uploads/announcements', $newName)) {
                // Delete old file
                $old = $this->announcementModel->find($id);
                if ($old && $old['attachment'] && file_exists(FCPATH . 'uploads/announcements/' . $old['attachment'])) {
                    @unlink(FCPATH . 'uploads/announcements/' . $old['attachment']);
                }
                $data['attachment'] = $newName;
            }
        }

        try {
            $this->announcementModel->update($id, $data);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Announcement updated successfully']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    /**
     * Admin Action: Delete
     */
    public function delete($id = null)
    {
        $user = $this->authService->user();
        $role = session()->get('role');
        $userRole = ($user && !empty($user->role)) ? $user->role : $role;

        if (!$this->authService->check() && empty($role)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        if (!in_array($userRole, ['admin', 'hr', 'branch_admin'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied']);
        }

        if (empty($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid announcement ID']);
        }

        if ($userRole === 'branch_admin') {
            $ann = $this->announcementModel->find($id);
            if ($ann && $ann['created_by'] != $user->sub) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied']);
            }
        }

        $this->announcementModel->update($id, ['is_deleted' => 1]);

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Announcement deleted successfully',
            'csrfHash' => csrf_hash()
        ]);
    }

    /**
     * User Action: Mark as Read
     */
    public function markRead($id = null)
    {
        if (!$this->authService->check()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $user = $this->authService->user();
        $this->announcementModel->markAsRead($id, $user->sub);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Marked as read']);
    }

    /**
     * Export Announcements to styled Excel (.xlsx)
     */
    public function exportExcel()
    {
        $user = $this->authService->user();
        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON(['message' => 'Unauthorized']);
        }

        $type = $this->request->getGet('type');
        $status = $this->request->getGet('status');
        $search = $this->request->getGet('search');

        $builder = $this->announcementModel->builder();
        $builder->select('announcements.*, user_info.firstname, user_info.lastname')
            ->join('user_info', 'user_info.user_id = announcements.created_by', 'left')
            ->where('announcements.is_deleted', 0);

        if (!empty($type)) {
            $builder->where('announcements.type', $type);
        }
        if (!empty($status)) {
            $builder->where('announcements.status', $status);
        }
        if (!empty($search)) {
            $builder->groupStart()
                ->like('announcements.title', $search)
                ->orLike('announcements.description', $search)
                ->orLike('announcements.type', $search)
                ->groupEnd();
        }

        $records = $builder->orderBy('announcements.id', 'DESC')->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Announcements');

        $headers = [
            'A1' => 'S.No',
            'B1' => 'Title',
            'C1' => 'Type',
            'D1' => 'Description',
            'E1' => 'Target Audience',
            'F1' => 'Target Roles',
            'G1' => 'Start Date',
            'H1' => 'End Date',
            'I1' => 'Status',
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
            $creator = trim(($item['firstname'] ?? '') . ' ' . ($item['lastname'] ?? '')) ?: 'Admin';

            $sheet->setCellValue('A' . $rowNum, $sno++);
            $sheet->setCellValue('B' . $rowNum, $item['title'] ?? '-');
            $sheet->setCellValue('C' . $rowNum, $item['type'] ?? 'General');
            $sheet->setCellValue('D' . $rowNum, strip_tags($item['description'] ?? '-'));
            $sheet->setCellValue('E' . $rowNum, $item['target_audience'] ?? 'All Users');
            $sheet->setCellValue('F' . $rowNum, $item['target_roles'] ?? 'All');
            $sheet->setCellValue('G' . $rowNum, !empty($item['start_date']) ? date('Y-m-d', strtotime($item['start_date'])) : '-');
            $sheet->setCellValue('H' . $rowNum, !empty($item['end_date']) ? date('Y-m-d', strtotime($item['end_date'])) : '-');
            $sheet->setCellValue('I' . $rowNum, $item['status'] ?? 'Active');
            $sheet->setCellValue('J' . $rowNum, $creator);
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

        $filename = 'Announcements_' . date('Y_m_d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
