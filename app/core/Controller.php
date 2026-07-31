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

    protected function json($data, int $status = 200) {
        http_response_code($status);
        header("Content-Type: application/json");
        echo json_encode($data);
        exit;
    }

    # Reads a JSON request body into an array, falling back to $_POST for
    # regular form submissions (shared by any controller that accepts POST).
    protected function jsonInput(): array {
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if (is_array($input)) {
            return $input;
        }

        return $_POST;
    }

    # Returns the logged-in user's id from the PHP session, or null for a
    # guest. Unlike AuthMiddleware::requireAuth() (which reads the JWT
    # Bearer header — only present on fetch() calls) this reads the
    # session cookie, which IS present on a normal page navigation.
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