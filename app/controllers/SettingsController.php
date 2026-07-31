<?php
class SettingsController extends Controller {
    // Only school emails are allowed for this account's email field
    private const REQUIRED_EMAIL_DOMAIN = '@iacademy.edu.ph';

    public function __construct(){
        $this->layout = 'main';
    }

    public function index(){
        $this->requireAuth();
        // Pass user session data to the view so JS can grab it or PHP can render it
        $this->view('settings/index', ['user' => $_SESSION['user'] ?? null]);
    }

    # Returns the logged-in user's info as JSON, using the same PHP session
    # the rest of the app already relies on (no JWT bearer token involved).
    public function me() {
        $this->requireAuth();
        header('Content-Type: application/json');

        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'] ?? null;
        $userModel = new User();
        $userData = $userId ? $userModel->getUserById($userId) : null;

        if (!$userData) {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            return;
        }

        echo json_encode([
            'success' => true,
            'user' => [
                'fullname' => $userData['fullname'] ?? '',
                'username' => $userData['username'] ?? '',
                'email'    => $userData['email'] ?? ''
            ]
        ]);
    }

    # Lightweight pre-check used BEFORE the confirmation modal is shown.
    # Verifies the email format/domain and the current password, but makes
    # no changes to the account yet.
    public function verifyEmailChange() {
        $this->requireAuth();
        header('Content-Type: application/json');

        $email = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['currentPassword'] ?? '';
        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'] ?? null;

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to do this.']);
            return;
        }

        if (empty($currentPassword)) {
            echo json_encode(['success' => false, 'message' => 'Please enter your current password to confirm this change.']);
            return;
        }

        if (!$this->isValidSchoolEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Email must be a valid ' . self::REQUIRED_EMAIL_DOMAIN . ' address.']);
            return;
        }

        $userModel = new User();
        $userData = $userModel->getUserById($userId);

        if (!$userData || !password_verify($currentPassword, $userData['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
            return;
        }

        $existing = $userModel->verifyEmail($email);
        if ($existing && (string)($existing['userId'] ?? '') !== (string)$userId) {
            echo json_encode(['success' => false, 'message' => 'That email is already in use by another account.']);
            return;
        }

        // Everything checks out — safe for the frontend to show the "are you sure?" modal
        echo json_encode(['success' => true]);
    }

    public function updateEmail() {
        $this->requireAuth();
        header('Content-Type: application/json');

        $email = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['currentPassword'] ?? '';
        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'] ?? null;

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to do this.']);
            return;
        }

        if (empty($currentPassword)) {
            echo json_encode(['success' => false, 'message' => 'Please enter your current password to confirm this change.']);
            return;
        }

        if (!$this->isValidSchoolEmail($email)) {
            echo json_encode(['success' => false, 'message' => 'Email must be a valid ' . self::REQUIRED_EMAIL_DOMAIN . ' address.']);
            return;
        }

        $userModel = new User();
        $userData = $userModel->getUserById($userId);

        // Verify the password BEFORE touching the email, per requirement (b)
        if (!$userData || !password_verify($currentPassword, $userData['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
            return;
        }

        // Make sure no other account already owns this email
        $existing = $userModel->verifyEmail($email);
        if ($existing && (string)($existing['userId'] ?? '') !== (string)$userId) {
            echo json_encode(['success' => false, 'message' => 'That email is already in use by another account.']);
            return;
        }

        if ($userModel->updateEmail($userId, $email)) {
            // Email changed successfully. The frontend will force a logout
            // immediately after this response so the user has to log back
            // in with their new email, for security.
            echo json_encode([
                'success' => true,
                'message' => 'Email updated successfully. Logging you out...',
                'forceLogout' => true
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update email.']);
        }
    }

    public function updatePassword() {
        $this->requireAuth();
        header('Content-Type: application/json');

        $current = $_POST['currentPassword'] ?? '';
        $new = $_POST['newPassword'] ?? '';
        $confirm = $_POST['confirmNewPassword'] ?? '';
        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'] ?? null;

        if (empty($current) || empty($new) || empty($confirm)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            return;
        }

        if ($new !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
            return;
        }

        if (strlen($new) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
            return;
        }

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to do this.']);
            return;
        }

        $userModel = new User();
        $userData = $userModel->getUserById($userId);

        if ($userData && password_verify($current, $userData['password'])) {
            $hashedPassword = password_hash($new, PASSWORD_DEFAULT);
            if ($userModel->updatePassword($userId, $hashedPassword)) {
                echo json_encode(['success' => true, 'message' => 'Password updated!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
        }
    }

    # Checks the email is well-formed AND ends with the required school domain
    private function isValidSchoolEmail(string $email): bool {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return strcasecmp(substr($email, -strlen(self::REQUIRED_EMAIL_DOMAIN)), self::REQUIRED_EMAIL_DOMAIN) === 0;
    }
}
