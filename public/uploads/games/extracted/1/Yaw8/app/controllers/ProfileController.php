<?php
class ProfileController extends Controller {
    public function __construct() {
        $this->layout = 'main';
    }

    public function index() {
        $this->requireAuth();
        $this->view('profile/index', []);
    }

    public function addGame() {
        // Prevent accidental HTML output from breaking JSON responses
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                return;
            }

            // 1. Gather Text Data
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $controls = trim($_POST['controls'] ?? '');
            $status = 'review';

            if (empty($title) || empty($description) || empty($controls)) {
                echo json_encode(['success' => false, 'message' => 'Title, description, and controls are required.']);
                return;
            }

            // 2. Define Upload Directories (Relative to app root)
            $publicDir = dirname(__DIR__, 2) . '/public';
            $gameDir = $publicDir . '/uploads/games/';
            $thumbDir = $publicDir . '/uploads/thumbnails/';
            
            if (!is_dir($gameDir)) mkdir($gameDir, 0777, true);
            if (!is_dir($thumbDir)) mkdir($thumbDir, 0777, true);

            $gameFilePath = '';
            $thumbnailPath = '';

            // 3. Process Game .zip File
            if (isset($_FILES['game_file']) && $_FILES['game_file']['error'] === UPLOAD_ERR_OK) {
                $gameFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', basename($_FILES['game_file']['name']));
                $targetGamePath = $gameDir . $gameFileName;
                
                if (move_uploaded_file($_FILES['game_file']['tmp_name'], $targetGamePath)) {
                    $gameFilePath = '/uploads/games/' . $gameFileName; 
                }
            }

            // 4. Process Thumbnail
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $thumbFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', basename($_FILES['thumbnail']['name']));
                $targetThumbPath = $thumbDir . $thumbFileName;
                
                if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetThumbPath)) {
                    $thumbnailPath = '/uploads/thumbnails/' . $thumbFileName;
                }
            }

            // 5. Database Insertion
            require_once __DIR__ . '/../models/Game.php';
            $gameModel = new Game();
            
            $isInserted = $gameModel->addGame(
                $title, 
                $description,
                $controls,
                $thumbnailPath, 
                $gameFilePath, 
                $status
            );

            if ($isInserted) {
                echo json_encode(['success' => true, 'message' => 'Game uploaded successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database insertion failed. Check table constraints.']);
            }

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
        }
        exit;
    }

    public function editGame() {
        // Prevent accidental HTML output from breaking JSON responses
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                return;
            }

            // 1. Gather Text Data
            $id = trim($_POST['id'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $controls = trim($_POST['controls'] ?? '');
            $status = trim($_POST['status'] ?? '') ?: 'review';

            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'Missing game id.']);
                return;
            }

            if (empty($title) || empty($description) || empty($controls)) {
                echo json_encode(['success' => false, 'message' => 'Title, description, and controls are required.']);
                return;
            }

            // 2. Define Upload Directories (Relative to app root)
            $publicDir = dirname(__DIR__, 2) . '/public';
            $gameDir = $publicDir . '/uploads/games/';
            $thumbDir = $publicDir . '/uploads/thumbnails/';

            if (!is_dir($gameDir)) mkdir($gameDir, 0777, true);
            if (!is_dir($thumbDir)) mkdir($thumbDir, 0777, true);

            $gameFilePath = '';
            $thumbnailPath = '';

            // 3. Process Replacement Game .zip File (optional)
            if (isset($_FILES['game_file']) && $_FILES['game_file']['error'] === UPLOAD_ERR_OK) {
                $gameFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', basename($_FILES['game_file']['name']));
                $targetGamePath = $gameDir . $gameFileName;

                if (move_uploaded_file($_FILES['game_file']['tmp_name'], $targetGamePath)) {
                    $gameFilePath = '/uploads/games/' . $gameFileName;
                }
            }

            // 4. Process Replacement Thumbnail (optional)
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $thumbFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', basename($_FILES['thumbnail']['name']));
                $targetThumbPath = $thumbDir . $thumbFileName;

                if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetThumbPath)) {
                    $thumbnailPath = '/uploads/thumbnails/' . $thumbFileName;
                }
            }

            // 5. Database Update
            require_once __DIR__ . '/../models/Game.php';
            $gameModel = new Game();

            $isUpdated = $gameModel->editGame(
                $id,
                $title,
                $description,
                $controls,
                $status,
                $thumbnailPath,
                $gameFilePath
            );

            if ($isUpdated) {
                echo json_encode(['success' => true, 'message' => 'Game updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update failed. Check table constraints.']);
            }

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
        }
        exit;
    }
}