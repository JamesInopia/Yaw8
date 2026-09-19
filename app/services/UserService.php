<?php
class UserService {
    private User $userModel;
    private PasswordReset $passwordResetModel;

    public function __construct(){
        $this->userModel = new User();
        $this->passwordResetModel = new PasswordReset();
    }

    public function emailExists($email): bool {
        $user = $this->userModel->verifyEmail($email);
        return !empty($user); 
    }

    public function addUser($fullname, $username, $email, $password, $confirm_password, $agreeTerms): array {
        $fullname = trim($fullname);
        $username = trim($username);
        $email = trim($email);
        $password = trim($password);
        $confirm_password = trim($confirm_password);

        // If any required field is empty
        if ($fullname === '' || $username === '' || $email === '' || $password === '' || $confirm_password === '') {
            return ['success' => false, 'message' => 'Please fill in all required fields.'];
        }

        // If terms and agree button not ticked
        if (!$agreeTerms) {
            return ['success' => false, 'message' => 'Please agree to the Terms of Service and Privacy Policy.'];
        }

        // Email should end with @iacademy.edu.ph
        if (!str_ends_with(strtolower($email), '@iacademy.edu.ph')) {
            return ['success' => false, 'message' => 'Email must end with @iacademy.edu.ph'];
        }

        // Password should be at least 8 characters long
        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
        }

        // If password and confirm password do not match
        if ($password !== $confirm_password) {
            return ['success' => false, 'message' => 'Passwords do not match!'];
        }

        // Check duplicates
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'An account with that email already exists. Try logging in instead.'];
        }

        if ($this->userModel->verifyUsername($username)) {
            return ['success' => false, 'message' => 'User with the name ' . $username . ' already exists. Use a different username instead.'];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = 'Member';

        $createdUserId = $this->userModel->addUser($fullname, $username, $email, $hashedPassword, $role);
        
        if ($createdUserId) {
            return [
                'success' => true, 
                // <-- ADD THIS: Pass the user ID back to the controller
                'user' => ['id' => $createdUserId] 
            ];
        }

        return ['success' => false, 'message' => 'Database error during registration.'];
    }

    public function verifyPassword($email, $password){
        $user = $this->userModel->verifyEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }

    # Forgot password — step 1: generate a 6-digit code, store its hash, email it
    public function sendPasswordResetCode($email): array {
        $email = trim($email);

        if (!$this->emailExists($email)) {
            return ['success' => false, 'message' => 'No account found with that email.'];
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $codeHash = password_hash($code, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + 600); // valid for 10 minutes

        if (!$this->passwordResetModel->createCode($email, $codeHash, $expiresAt)) {
            return ['success' => false, 'message' => 'Could not start password reset. Try again.'];
        }

        if (!Mailer::sendResetCode($email, $code)) {
            return ['success' => false, 'message' => 'Could not send the email. Try again later.'];
        }

        return ['success' => true];
    }

    # Forgot password — step 2: check the 6-digit code the user typed
    public function verifyPasswordResetCode($email, $code): array {
        $row = $this->passwordResetModel->getActiveCode($email);

        if (!$row) {
            return ['success' => false, 'message' => 'That code expired. Please request a new one.'];
        }

        if (!password_verify($code, $row['code_hash'])) {
            return ['success' => false, 'message' => 'Incorrect code. Please try again.'];
        }

        $this->passwordResetModel->markVerified($row['id']);
        return ['success' => true];
    }

    # Forgot password — step 3: set the new password, but only if step 2 actually passed
    public function resetPassword($email, $newPassword, $confirmPassword): array {
        if (!$this->passwordResetModel->isVerified($email)) {
            return ['success' => false, 'message' => 'Please verify your code first.'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'Passwords do not match!'];
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!$this->userModel->updatePasswordByEmail($email, $hashed)) {
            return ['success' => false, 'message' => 'Database error while resetting password.'];
        }

        $this->passwordResetModel->deleteForEmail($email);
        return ['success' => true];
    }
}