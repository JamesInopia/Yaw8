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

        if ($type === 'signup') {
            require $viewsPath . 'form-signup.php'; 
        } else {
            require $viewsPath . 'form-login.php';
        }
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

        // Return token and success status
        $this->json([
            'success' => true,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => (int) $this->authConfig['jwt_ttl']
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
            'expires_in' => (int) $this->authConfig['jwt_ttl']
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

    // FIXED: Renamed profie to profile
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

    private function jsonInput(): array {
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if (is_array($input)) {
            return $input;
        }

        return $_POST;
    }

    private function json(array $data, int $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}