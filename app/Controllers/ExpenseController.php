<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ExpenseModel;
use App\Models\ExpenseCategoryModel;
use App\Models\UserInfoModel;
use App\Services\AuthService;

class ExpenseController extends BaseController
{
    protected $expenseModel;
    protected $categoryModel;
    protected $userInfoModel;
    protected $authService;

    public function __construct()
    {
        $this->expenseModel = new ExpenseModel();
        $this->categoryModel = new ExpenseCategoryModel();
        $this->userInfoModel = new UserInfoModel();
        $this->authService = new AuthService(service('request'));
    }

    public function index()
    {
        $user = $this->authService->check();
        if (!$user) return redirect()->to('/login');

        $filters = $this->request->getGet();
        if ($user->role === 'employee') {
            $filters['user_id'] = $user->sub;
        }

        $expenses = $this->expenseModel->getExpensesWithDetails($filters);
        $categories = $this->categoryModel->findAll();

        // Dashboard stats
        $stats = [
            'total_month' => $this->expenseModel->where('MONTH(expense_date)', date('m'))->where('YEAR(expense_date)', date('Y'))->selectSum('amount')->get()->getRow()->amount ?? 0,
            'total_year' => $this->expenseModel->where('YEAR(expense_date)', date('Y'))->selectSum('amount')->get()->getRow()->amount ?? 0,
        ];

        // Category-wise data for chart
        $db = \Config\Database::connect();
        $chartData = $db->table('expenses e')
            ->select('ec.name, SUM(e.amount) as total')
            ->join('expense_categories ec', 'ec.id = e.category_id')
            ->groupBy('e.category_id')
            ->get()->getResultArray();

        return view('expense/index', [
            'expenses' => $expenses,
            'categories' => $categories,
            'stats' => $stats,
            'chartData' => $chartData,
            'filters' => $filters,
            'user' => $user
        ]);
    }

    public function create()
    {
        $user = $this->authService->check();
        if (!$user) return redirect()->to('/login');

        $categories = $this->categoryModel->findAll();
        $employees = $this->userInfoModel->findAll();

        return view('expense/create', [
            'categories' => $categories,
            'employees' => $employees,
            'user' => $user
        ]);
    }

    public function store()
    {
        $user = $this->authService->check();
        if (!$user) return redirect()->to('/login');

        $validation = \Config\Services::validation();
        $validation->setRules([
            'title' => 'required',
            'category_id' => 'required',
            'amount' => 'required|decimal',
            'expense_date' => 'required|valid_date',
            'payment_method' => 'required'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = $this->request->getPost();
        $data['created_by'] = $user->sub;
        $data['status'] = 'Approved';
        
        // Handle "Company" in paid_by
        if ($data['paid_by'] === 'Company') {
            $data['paid_by'] = null;
        }

        // Handle attachment
        $file = $this->request->getFile('attachment');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/expenses', $newName);
            $data['attachment'] = $newName;
        }

        $this->expenseModel->insert($data);

        // Fetch employee name
        $userInfo = $this->userInfoModel->where('user_id', $user->sub)->first();
        $empName = $userInfo ? trim(($userInfo['firstname'] ?? '') . ' ' . ($userInfo['lastname'] ?? '')) : 'An employee';

        // Notify Admins
        $notificationModel = new \App\Models\NotificationModel();
        $userModel = new \App\Models\UserModel();
        $admins = $userModel->whereIn('role', ['admin', 'hr'])->findAll();
        foreach ($admins as $admin) {
            $notificationModel->insert([
                'sender_id' => $user->sub,
                'recipient_id' => $admin['id'],
                'data' => json_encode([
                    'title' => 'New Expense Submitted',
                    'message' => $empName . " submitted a new expense: " . $data['title'],
                    'link' => '/expenses'
                ]),
                'is_read' => false
            ]);
        }

        return redirect()->to('/expenses')->with('success', 'Expense added successfully.');
    }

    public function edit($id)
    {
        $user = $this->authService->check();
        if (!$user) return redirect()->to('/login');

        $expense = $this->expenseModel->find($id);
        if (!$expense) return redirect()->to('/expenses')->with('error', 'Expense not found.');

        // Permission check
        if ($user->role === 'employee' && $expense['created_by'] != $user->sub) {
            return redirect()->to('/expenses')->with('error', 'Unauthorized.');
        }

        $categories = $this->categoryModel->findAll();
        $employees = $this->userInfoModel->findAll();

        return view('expense/edit', [
            'expense' => $expense,
            'categories' => $categories,
            'employees' => $employees,
            'user' => $user
        ]);
    }

    public function update($id)
    {
        $user = $this->authService->check();
        if (!$user) return redirect()->to('/login');

        $expense = $this->expenseModel->find($id);
        if (!$expense) return redirect()->to('/expenses')->with('error', 'Expense not found.');

        if ($user->role === 'employee' && $expense['created_by'] != $user->sub) {
            return redirect()->to('/expenses')->with('error', 'Unauthorized.');
        }

        $data = $this->request->getPost();
        if ($data['paid_by'] === 'Company') {
            $data['paid_by'] = null;
        }

        $file = $this->request->getFile('attachment');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/expenses', $newName);
            $data['attachment'] = $newName;
        }

        $this->expenseModel->update($id, $data);

        return redirect()->to('/expenses')->with('success', 'Expense updated successfully.');
    }

    public function delete($id)
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['admin', 'hr', 'branch_admin'])) {
            return redirect()->to('/expenses')->with('error', 'Unauthorized.');
        }

        $this->expenseModel->delete($id);
        return redirect()->to('/expenses')->with('success', 'Expense deleted successfully.');
    }

    public function approve($id)
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['admin', 'hr', 'branch_admin'])) {
            return redirect()->to('/expenses')->with('error', 'Unauthorized.');
        }

        $expense = $this->expenseModel->find($id);
        $this->expenseModel->update($id, ['status' => 'Approved']);

        // Notify Employee
        $notificationModel = new \App\Models\NotificationModel();
        $notificationModel->insert([
            'sender_id' => $user->sub,
            'recipient_id' => $expense['created_by'],
            'data' => json_encode([
                'title' => 'Expense Approved',
                'message' => "Your expense '" . $expense['title'] . "' has been approved.",
                'link' => '/expenses'
            ]),
            'is_read' => false
        ]);

        return redirect()->to('/expenses')->with('success', 'Expense approved.');
    }

    public function reject($id)
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['admin', 'hr', 'branch_admin'])) {
            return redirect()->to('/expenses')->with('error', 'Unauthorized.');
        }

        $expense = $this->expenseModel->find($id);
        $this->expenseModel->update($id, ['status' => 'Rejected']);

        // Notify Employee
        $notificationModel = new \App\Models\NotificationModel();
        $notificationModel->insert([
            'sender_id' => $user->sub,
            'recipient_id' => $expense['created_by'],
            'data' => json_encode([
                'title' => 'Expense Rejected',
                'message' => "Your expense '" . $expense['title'] . "' has been rejected.",
                'link' => '/expenses'
            ]),
            'is_read' => false
        ]);

        return redirect()->to('/expenses')->with('success', 'Expense rejected.');
    }

    public function categories()
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['admin', 'hr', 'branch_admin'])) {
            return redirect()->to('/expenses')->with('error', 'Unauthorized.');
        }

        $categories = $this->categoryModel->findAll();
        return view('expense/categories', ['categories' => $categories, 'user' => $user]);
    }

    public function storeCategory()
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['admin', 'hr', 'branch_admin'])) {
            return redirect()->to('/expenses')->with('error', 'Unauthorized.');
        }

        $name = $this->request->getPost('name');
        $id = $this->request->getPost('id');

        if ($id) {
            $this->categoryModel->update($id, ['name' => $name]);
        } else {
            $this->categoryModel->insert(['name' => $name]);
        }

        return redirect()->to('/expenses/categories')->with('success', 'Category saved successfully.');
    }

    public function deleteCategory($id)
    {
        $user = $this->authService->check();
        if (!$user || !in_array($user->role, ['admin', 'hr', 'branch_admin'])) {
            return redirect()->to('/expenses')->with('error', 'Unauthorized.');
        }

        $this->categoryModel->delete($id);
        return redirect()->to('/expenses')->with('success', 'Category deleted successfully.');
    }
}
