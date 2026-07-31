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
        $availableGenres = $gameModel->getAllGenres();

        // 3. Pass user and games to the view
        $this->view('profile/index', [
            'user' => $userData,
            'myGames' => $myGames,
            'availableGenres' => $availableGenres
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
        // Prevent accidental HTML/warning output from breaking JSON responses
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            // index.php already starts the session — starting it again here
            // triggers a PHP warning ("session_start(): Ignoring session_start()...")
            // which gets printed BEFORE this JSON, causing "Unexpected token '<'" on the client.
            $creatorUserId = $_SESSION['user_id'] ?? null;

            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $controls = $_POST['controls'] ?? '';
            $genresRaw = $_POST['genres'] ?? $_POST['genre'] ?? '[]';
            $genreNames = is_string($genresRaw) ? (json_decode($genresRaw, true) ?: []) : (array) $genresRaw;
            $status = $_POST['status'] ?? 'Draft';
            $collaborators = isset($_POST['collaborators']) ? json_decode($_POST['collaborators'], true) : [];

            // Genre is optional, only checking for user ID and Title
            if (!$creatorUserId || empty($title)) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields (Title). User must be logged in.']);
                exit;
            }

            // Handle Thumbnail Upload
            $thumbnailPath = null;
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $thumbnailPath = 'uploads/thumbnails/' . basename($_FILES['thumbnail']['name']);
                move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnailPath);
            }

            // Handle Game File Upload (Note: checking 'game_file' to match JS)
            $gameFilePath = null;
            if (isset($_FILES['game_file']) && $_FILES['game_file']['error'] === UPLOAD_ERR_OK) {
                $gameFilePath = 'uploads/games/' . basename($_FILES['game_file']['name']);
                move_uploaded_file($_FILES['game_file']['tmp_name'], $gameFilePath);
            }

            $gameModel = new Game();
            $newGameId = $gameModel->addGame($creatorUserId, $title, $description, $controls, $genreNames, $thumbnailPath, $gameFilePath, $status, $collaborators);

            if ($newGameId) {
                // Hand back the full saved row so the frontend can add it to
                // the "My Games" grid immediately, without a page refresh.
                $savedGame = $gameModel->getGameById($newGameId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Game submitted successfully.',
                    'game' => $savedGame,
                    // TEMPORARY DEBUG — remove once the cause is confirmed.
                    'debug_files' => $_FILES,
                    'debug_gameFilePath' => $gameFilePath,
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error while submitting game.']);
            }
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
        }
        exit;
    }

    public function editGame() {
        // Prevent accidental HTML/warning output from breaking JSON responses
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            // JS sends the game id as 'id', not 'game_id' — this was the mismatch
            // that made every edit fail with "Missing required fields (ID or Title)".
            $game_id = $_POST['id'] ?? $_POST['game_id'] ?? null;
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $controls = $_POST['controls'] ?? '';
            $genresRaw = $_POST['genres'] ?? $_POST['genre'] ?? '[]';
            $genreNames = is_string($genresRaw) ? (json_decode($genresRaw, true) ?: []) : (array) $genresRaw;
            $status = $_POST['status'] ?? 'Draft';

            // Genre is no longer required in this check
            if (!$game_id || empty($title)) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields (ID or Title).']);
                exit;
            }

            // Handle Thumbnail Upload (if a new one is provided)
            $thumbnailPath = null;
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $thumbnailPath = 'uploads/thumbnails/' . basename($_FILES['thumbnail']['name']);
                move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnailPath);
            }

            // Handle Game File Upload (if a new one is provided)
            $gameFilePath = null;
            if (isset($_FILES['game_file']) && $_FILES['game_file']['error'] === UPLOAD_ERR_OK) {
                $gameFilePath = 'uploads/games/' . basename($_FILES['game_file']['name']);
                move_uploaded_file($_FILES['game_file']['tmp_name'], $gameFilePath);
            }

            $gameModel = new Game();
            $success = $gameModel->editGame($game_id, $title, $description, $controls, $genreNames, $thumbnailPath, $gameFilePath, $status);

            if ($success) {
                // Hand back the full updated row so the frontend can patch the
                // existing card in place, without a page refresh.
                $savedGame = $gameModel->getGameById($game_id);
                echo json_encode(['success' => true, 'message' => 'Game updated successfully.', 'game' => $savedGame]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error while updating game.']);
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