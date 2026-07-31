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