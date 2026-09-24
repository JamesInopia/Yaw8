<?php
class AuthController extends Controller {
    private UserService $userService;
    private array $authConfig;

    public function __construct() {
        $this->layout = 'main';
        $this->userService = new UserService();
        $this->authConfig = require __DIR__ . '/../config/auth.php'; 
    }

    # Function that takes user to the index
    public function index() {
        $this->view('auth/index', []);
    }

    public function getForm() {
        $type = $_GET['type'] ?? 'login';
        $viewsPath = dirname(__DIR__) . '/views/content/auth/';

        $map = [
            'login'          => 'form-login.php',
            'signup'         => 'form-signup.php',
            'forgot-email'   => 'form-forgot-email.php',
            'forgot-code'    => 'form-forgot-code.php',
            'reset-password' => 'form-reset-password.php',
        ];

        require $viewsPath . ($map[$type] ?? $map['login']);
    }

    public function signup() {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'POST method required'], 405);
            return;
        }

        $input = $this->jsonInput();
        $fullname = $input['fullname'] ?? '';
        $username = $input['username'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $confirmPassword = $input['confirmPassword'] ?? '';
        $agreeTerms = !empty($input['agreeTerms']);
        
        $result = $this->userService->addUser($fullname, $username, $email, $password, $confirmPassword, $agreeTerms);
        
        // If registration fails, return validation errors immediately
        if (empty($result['success'])) {
            $this->json($result, 400);
            return;
        }

        // --- AUTOMATIC LOGIN AFTER SIGNUP ---
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Retrieve the newly created user (or extract user details from result)
        $userId = $result['user']['id'] ?? $result['user']['userId'] ?? '';

        $userModelInstance = new User(
            $userId, 
            $fullname, 
            $username, 
            $email, 
            $password, 
            'Member'
        );

        // Generate JWT Token
        $token = Jwt::createForUser(
            $userModelInstance,
            $this->authConfig['jwt_secret'],
            (int) $this->authConfig['jwt_ttl']
        );

        $_SESSION['user_id'] = $userId;

        // Return token, user profile, and success status
        $this->json([
            'success' => true,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => (int) $this->authConfig['jwt_ttl'],
            'user' => [
                'id' => $userId,
                'name' => $fullname,
                'username' => $username,
                'email' => $email
            ]
        ], 201);
    }

    # Function that logs user in
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'POST method required'], 405);
            return;
        }

        $input = $this->jsonInput();
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->json(['success' => false, 'message' => 'Email and password are required.'], 422);
            return;
        }

        if (!$this->userService->emailExists($email)) {
            $this->json(['success' => false, 'message' => 'No account found with that email. Try signing up instead.'], 404);
            return;
        }
        
        $user = $this->userService->verifyPassword($email, $password);

        if (!$user) {
            $this->json(['success' => false, 'message' => 'Incorrect password. Please try again.'], 401);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Standardize extracting the ID field from database arrays
        $userId = $user['id'] ?? $user['userId'] ?? '';

        // Suspended accounts can't log in (a timed suspension ends by itself)
        $suspension = Auth::suspensionOf($userId);
        if ($suspension !== null) {
            $_SESSION = [];
            $this->json(['success' => false, 'suspended' => true, 'message' => Auth::suspensionMessage($suspension)], 403);
            return;
        }

        $userModelInstance = new User(
            $userId, 
            $user['fullname'] ?? '', 
            $user['username'] ?? '', 
            $user['email'] ?? '', 
            $user['password'] ?? '', 
            $user['role'] ?? 'Member'
        );

        $token = Jwt::createForUser(
            $userModelInstance,
            $this->authConfig['jwt_secret'],
            (int) $this->authConfig['jwt_ttl']
        );

        $_SESSION['user_id'] = $userId;

        $this->json([
            'success' => true, 
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => (int) $this->authConfig['jwt_ttl'],
            'user' => [
                'id' => $userId,
                'name' => $user['fullname'] ?? '',
                'username' => $user['username'] ?? '',
                'email' => $user['email'] ?? ''
            ]
        ]);
    }
    
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }

        session_destroy();   
        header('Location: ?url=auth');
        exit;
    }

    # Forgot password — step 1: send a 6-digit code to the given email
    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'POST method required'], 405);
            return;
        }

        $input = $this->jsonInput();
        $email = trim($input['email'] ?? '');

        if ($email === '') {
            $this->json(['success' => false, 'message' => 'Email is required.'], 422);
            return;
        }

        $result = $this->userService->sendPasswordResetCode($email);
        if (empty($result['success'])) {
            $this->json($result, 400);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['reset_email'] = $email;

        $this->json(['success' => true, 'message' => 'Code sent.']);
    }

    # Forgot password — step 2: check the 6-digit code
    public function verifyResetCode() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'POST method required'], 405);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $email = $_SESSION['reset_email'] ?? '';

        if ($email === '') {
            $this->json(['success' => false, 'message' => 'Please start the reset process again.'], 400);
            return;
        }

        $input = $this->jsonInput();
        $result = $this->userService->verifyPasswordResetCode($email, trim($input['code'] ?? ''));

        if (empty($result['success'])) {
            $this->json($result, 400);
            return;
        }

        $this->json(['success' => true]);
    }

    # Forgot password — step 3: set the new password
    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'POST method required'], 405);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $email = $_SESSION['reset_email'] ?? '';

        if ($email === '') {
            $this->json(['success' => false, 'message' => 'Please start the reset process again.'], 400);
            return;
        }

        $input = $this->jsonInput();
        $result = $this->userService->resetPassword(
            $email,
            $input['newPassword'] ?? '',
            $input['confirmPassword'] ?? ''
        );

        if (empty($result['success'])) {
            $this->json($result, 400);
            return;
        }

        unset($_SESSION['reset_email']);
        $this->json(['success' => true, 'message' => 'Password updated. You can now log in.']);
    }

    public function profile(): void {
        $payload = AuthMiddleware::requireAuth();

        $this->json([
            'success' => true,
            'user' => [
                'id' => $payload['sub'] ?? null,
                'username' => $payload['username'] ?? ''
            ]
        ]);
    }
}