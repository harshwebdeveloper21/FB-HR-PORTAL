<?php
namespace App\Filters;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Services\AuthService;

class HrOrManagerFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = (new AuthService($request))->check();
        if (!$user) {
            if ($request->isAJAX()) return \Config\Services::response()->setStatusCode(401)->setJSON(["status"=>"error","message"=>"Unauthorized"]);
            return redirect()->to("/login")->with("error","Login required.");
        }
        $allowed = ["admin","hr","branch_admin","department_manager"];
        if (!in_array($user->role, $allowed)) {
            if ($request->isAJAX()) return \Config\Services::response()->setStatusCode(403)->setJSON(["status"=>"error","message"=>"Access denied."]);
            return redirect()->to("/dashboard")->with("error","You do not have permission.");
        }
        return null;
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
