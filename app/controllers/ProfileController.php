<?php
class ProfileController extends Controller {
    public function __construct() {
        $this->layout = 'main';
    }

    public function index() {
        $this->requireAuth();
        
        require_once __DIR__ . '/../models/Game.php';
        require_once __DIR__ . '/../models/User.php';
        
        $gameModel = new Game();
        $userModel = new User();
        
        // Grab whichever session key stores the user ID
        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'] ?? $_SESSION['id'] ?? 0; 
        
        // 1. Fetch user directly from DB by ID (or username fallback)
        $userData = null;
        if ($userId) {
            $userData = $userModel->getUserById($userId);
        } elseif (!empty($_SESSION['username'])) {
            $userData = $userModel->verifyUsername($_SESSION['username']);
        }
        
        // 2. Fetch user's games
        $myGames = $gameModel->getGamesByUser($userId);
        
        // 3. Pass user and games to the view
        $this->view('profile/index', [
            'user' => $userData,
            'myGames' => $myGames
        ]);
    }

    public function editProfile() {
        // Prevent accidental HTML output from breaking JSON responses
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            // 1. Gather Text Data (using $_POST directly)
            $fullname = trim($_POST['fullname'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $bio = trim($_POST['bio'] ?? '');

            // If these are empty, the POST data got lost (likely a redirect issue)
            if (empty($fullname) || empty($username)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Data missing from POST!',
                    'request_method' => $_SERVER['REQUEST_METHOD'],
                    'received_post_data' => $_POST
                ]);
                exit; 
            }

            // 2. Get the logged-in user's ID
            // Make sure these match exactly how you store user IDs in $_SESSION
            $userId = $_SESSION['user_id'] ?? $_SESSION['userId'] ?? 0;
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'User not authenticated. Please log in.']);
                exit;
            }

            // 3. Database Update
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();

            // Run the update query
            $isUpdated = $userModel->editUser($userId, $fullname, $username, $bio);

            if ($isUpdated) {
                $_SESSION['fullname'] = $fullname;
                $_SESSION['username'] = $username;
                $_SESSION['bio']      = $bio;

                echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update failed. Username might already be taken.']);
            }

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database/Server Error: ' . $e->getMessage()]);
        }
        
        exit;
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

            // Retrieve creator ID from session
            $userId = $_SESSION['user_id'] ?? $_SESSION['userId'] ?? 0;
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'User not authenticated. Please log in.']);
                return;
            }

            // Decode the JSON array of collaborators sent by JavaScript
            $collaboratorsRaw = $_POST['collaborators'] ?? '[]';
            $collaboratorUserIds = [];

            if (is_string($collaboratorsRaw)) {
                $decoded = json_decode($collaboratorsRaw, true);
                
                if (is_array($decoded) && !empty($decoded)) {
                    // Connect to DB to look up user IDs based on username or email
                    $pdo = Database::connect();
                    
                    // Prepare the statement checking both columns
                    $stmt = $pdo->prepare('SELECT userId FROM user_account WHERE username = ? OR email = ? LIMIT 1');
                    
                    foreach ($decoded as $collabInput) {
                        $identifier = trim($collabInput);
                        if (!empty($identifier)) {
                            // Pass the identifier twice: once for username, once for email
                            $stmt->execute([$identifier, $identifier]);
                            $foundUser = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            // If a matching user is found, store their integer ID
                            if ($foundUser && isset($foundUser['userId'])) {
                                $collaboratorUserIds[] = (int) $foundUser['userId'];
                            }
                        }
                    }
                }
            }

            // Pass $userId and the validated integer $collaboratorUserIds into addGame
            $isInserted = $gameModel->addGame(
                $userId,
                $title, 
                $description,
                $controls,
                $thumbnailPath, 
                $gameFilePath, 
                $status,
                $collaboratorUserIds
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

    public function deleteGame() {
        // Prevent accidental HTML output from breaking JSON responses
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                return;
            }

            $id = trim($_POST['id'] ?? '');

            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'Missing game id.']);
                return;
            }

            require_once __DIR__ . '/../models/Game.php';
            $gameModel = new Game();

            // Attempt to delete the game
            $isDeleted = $gameModel->deleteGame($id);

            if ($isDeleted) {
                echo json_encode(['success' => true, 'message' => 'Game deleted successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete game from database.']);
            }

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
        }
        exit;
    }
}