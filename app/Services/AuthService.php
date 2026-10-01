<?php

namespace App\Services;

use Firebase\JWT\JWT;
use CodeIgniter\HTTP\RequestInterface;
use App\Models\UserModel;
use Firebase\JWT\Key;

class AuthService
{
    private const DEFAULT_JWT_SECRET = "YourSecureJWTSecretKeyForHRSystem2024!";
    private $key;
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
        $configuredKey = trim((string) (env("JWT_SECRET") ?? getenv("JWT_SECRET") ?? ""));
        $this->key = strlen($configuredKey) >= 32 ? $configuredKey : self::DEFAULT_JWT_SECRET;

        if ($configuredKey !== "" && strlen($configuredKey) < 32) {
            log_message("error", "JWT_SECRET is too short. It must be at least 32 characters.");
        }
    }

    public function attempt($email, $password)
    {
        $userModel = new UserModel();
        $user = $userModel->where("email", $email)->first();

        if (!$user || !password_verify($password, $user["password"])) {
            return false;
        }

        return $this->login($user);
    }

    public function login(array $user)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 86400; // Token valid for 24 hours
        $payload = [
            "iat" => $issuedAt,
            "exp" => $expirationTime,
            "sub" => $user["id"],
            "email" => $user["email"],
            "role" => $user["role"],
        ];

        $token = JWT::encode($payload, $this->key, "HS256");

        session()->set("user_token", $token);
        session()->set("user_id", $user["id"]);
        session()->set("branch_id", $user["branch_id"] ?? null);

        return $token;
    }

    public function check()
    {
        $token = $this->getBearerToken();

        if ($token) {
            try {
                $decoded = JWT::decode($token, new Key($this->key, "HS256"));
                if (!session()->has("user_id")) {
                    session()->set("user_id", $decoded->sub);
                }
                return (object) $decoded;
            } catch (\Firebase\JWT\ExpiredException $e) {
                log_message("debug", "JWT Expired, attempting remember-me fallback");
            } catch (\Exception $e) {
                log_message("error", "JWT Error: " . $e->getMessage());
            }
        }

        // Try remember-me fallback if session/JWT is invalid
        return $this->tryRememberMeLogin();
    }

    public function user()
    {
        $auth = $this->check();
        return $auth ?: null;
    }

    public function logout()
    {
        $this->forgetUser();
        session()->destroy();
        return true;
    }

    public function rememberUser($userId)
    {
        helper("text");
        $token = random_string("crypto", 64);
        $hash = hash("sha256", $token);
        $expiresAt = date("Y-m-d H:i:s", strtotime("+30 days"));

        $db = db_connect();
        $db->table("remember_tokens")->insert([
            "user_id" => $userId,
            "token_hash" => $hash,
            "expires_at" => $expiresAt,
        ]);

        $secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        setcookie("remember_me_token", $token, time() + 86400 * 30, "/", "", $secure, true);
    }

    public function forgetUser()
    {
        $token = $this->request->getCookie("remember_me_token");
        if ($token) {
            $db = db_connect();
            $db->table("remember_tokens")->where("token_hash", hash("sha256", $token))->delete();
        }

        $secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        setcookie("remember_me_token", "", time() - 3600, "/", "", $secure, true);
    }

    private function getBearerToken()
    {
        $authorizationHeader = $this->request->getHeaderLine("Authorization");
        if ($authorizationHeader) {
            $arr = explode(" ", $authorizationHeader);
            if (count($arr) == 2 && strtolower($arr[0]) === "bearer" && $arr[1] !== 'null' && $arr[1] !== 'undefined') {
                return $arr[1];
            }
        }

        if (session_status() === PHP_SESSION_ACTIVE || !headers_sent()) {
            return session()->get("user_token");
        }

        return null;
    }

    private function tryRememberMeLogin()
    {
        $token = $this->request->getCookie("remember_me_token");
        if (!$token) {
            return false;
        }

        $db = db_connect();
        $hash = hash("sha256", $token);
        $record = $db->table("remember_tokens")
            ->where("token_hash", $hash)
            ->where("expires_at >", date("Y-m-d H:i:s"))
            ->get()->getRow();

        if (!$record) {
            $this->forgetUser();
            return false;
        }

        $userModel = new UserModel();
        $user = $userModel->find($record->user_id);

        if (!$user || (!empty($user['is_deleted']) && $user['is_deleted'] == 1)) {
            $this->forgetUser();
            return false;
        }

        $userInfoModel = new \App\Models\UserInfoModel();
        $userInfo = $userInfoModel->where('user_id', $user['id'])->first();
        if ($userInfo && !empty($userInfo['status']) && strtolower(trim($userInfo['status'])) !== 'active') {
            $this->forgetUser();
            return false;
        }

        // Token Rotation: Delete old token and issue new one
        $db->table("remember_tokens")->where("token_hash", $hash)->delete();
        $this->rememberUser($user["id"]);

        // Generate new JWT and establish session
        $jwt = $this->login($user);

        return (object) [
            "sub" => $user["id"],
            "email" => $user["email"],
            "role" => $user["role"],
        ];
    }

    /**
     * Get the branch_id for the currently authenticated user.
     * Admin users have branch_id = NULL (can access all branches).
     * HR and employee must have a branch_id.
     *
     * We do NOT store branch_id in the JWT so that transfers take effect
     * immediately without requiring a new login.
     *
     * @return int|null  branch_id, or null for admin / unassigned
     */
    public function getBranchId(): ?int
    {
        $user = $this->check();
        if (!$user) {
            return null;
        }

        $userId = $user->sub ?? $user->id ?? null;
        if (!$userId) {
            return null;
        }

        $db   = db_connect();
        $row  = $db->table('users')
                   ->select('branch_id, role')
                   ->where('id', $userId)
                   ->get()->getRowArray();

        if (!$row) {
            return null;
        }

        // Admin and Global HR check session for active filter branch (null = full cross-branch access)
        if (in_array($row['role'], ['admin', 'hr'])) {
            $activeBranch = (session_status() === PHP_SESSION_ACTIVE || !headers_sent()) ? session()->get('admin_active_branch') : null;
            return !empty($activeBranch) ? (int)$activeBranch : null;
        }

        // Branch Admin, Department Manager, and Employee are strictly locked to their assigned branch
        return isset($row['branch_id']) ? (int)$row['branch_id'] : null;
    }
}
