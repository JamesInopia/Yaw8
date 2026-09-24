<?php
class Controller {
    protected string $layout = 'main';

    # If not logged in, redirects user to login page
    protected function requireAuth(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: ?url=auth');
            exit;
        }
    }

    # Page guard for the admin area. Guests are sent to the login page;
    protected function requireAdminPage(): void {
        $this->requireAuth();

        if (!Auth::isAdmin()) {
            header('Location: ?url=home');
            exit;
        }
    }

    # API guard for the admin area
    protected function requireAdminApi(): int {
        $payload = AuthMiddleware::requireAuth();
        $userId = $payload['sub'] ?? null;

        if (!$userId || !Auth::isAdminUser($userId)) {
            $this->json(['success' => false, 'message' => 'Admin access required.'], 403);
        }

        return (int) $userId;
    }

    protected function json($data, int $status = 200) {
        http_response_code($status);
        header("Content-Type: application/json");
        echo json_encode($data);
        exit;
    }

    # Regular form submissions
    protected function jsonInput(): array {
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if (is_array($input)) {
            return $input;
        }

        return $_POST;
    }

    # Returns the logged-in user's id from the PHP session
    protected function currentUserIdOrNull() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? null;
    }

    # Renders entire page layout with content
    public function view(string $view, array $data = []): void {
        extract($data);

        $viewFile = dirname(__DIR__) . '/views/content/' . $view . '.php';
        $layoutFile = dirname(__DIR__) . '/views/layouts/' . $this->layout . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$viewFile}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    # Renders partial component view without layout
    public function renderPartial(string $view, array $data = []): void {
        extract($data);
        $file = dirname(__DIR__) . '/views/' . $view . '.php';
        
        if (file_exists($file)) {
            require $file;
        }
    }
}