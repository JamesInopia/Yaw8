<?php
class UserService {
    private User $userModel;

    public function __construct(){
        $this->userModel = new User();
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
}