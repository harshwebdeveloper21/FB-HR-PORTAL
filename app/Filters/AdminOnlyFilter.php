<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Services\AuthService;

/**
 * AdminOnlyFilter - blocks access for non-admin and non-HR users.
 * Applied to routes that only global admins and HR should reach (e.g. branch CRUD,
 * HR transfer permission toggle).
 */
class AdminOnlyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authService = new AuthService($request);
        $user = $authService->check();

        if (!$user) {
            // Not logged in
            if ($request->isAJAX() || str_starts_with($request->getUri()->getPath(), 'api/')) {
                return \Config\Services::response()
                    ->setStatusCode(401)
                    ->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
            }
            return redirect()->to('/login')->with('error', 'Login required.');
        }

        if ($user->role !== 'admin' && $user->role !== 'hr') {
            if ($request->isAJAX() || str_starts_with($request->getUri()->getPath(), 'api/')) {
                return \Config\Services::response()
                    ->setStatusCode(403)
                    ->setJSON(['status' => 'error', 'message' => 'Admin or HR access required.']);
            }
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to access this page.');
        }

        return null; // allow
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}
