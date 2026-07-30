<?php
class User {
    private $userId;
    private $fullname;
    private $username;
    private $email;
    private $password;
    private $role;

    public function __construct($userId = '', $fullname = '', $username = '', $email = '', $password = '', $role = ''){
        $this->userId = $userId;
        $this->fullname = $fullname;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    # Getters and Setters for user ID, Username, and Password
    public function getId() { return $this->userId; }
    public function setId($userId) { $this->userId = $userId; }

    public function getFullname() {return $this->fullname;}
    public function setFullname($fullname) { $this->fullname = $fullname; }

    public function getUsername() {return $this->username; }
    public function setUsername($username) { $this->username = $username; }

    public function getEmail () {return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    public function getPassword() { return $this->password; }
    public function setPassword($password) { $this->password = $password; }

    public function getRole() {return $this->role; }
    public function setRole($role) { $this->role = $role; } 

    # Function that adds a user to the database
    public function addUser($fullname, $username, $email, $password, $role): bool {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('INSERT INTO user_account (fullname, username, email, password, role) VALUES (?, ?, ?, ?, ?)');
        return $stmt->execute([trim($fullname), trim($username), trim($email), trim($password), trim($role)]);
    }

    # Function that deletes user in the database
    public function deleteUser($id): bool {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('DELETE FROM user_account WHERE id = ?');
        
        return $stmt->execute([$id]);
    }

    # Functions that verify user credentials
    public function verifyUsername($username) : ?array {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM user_account WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function verifyEmail($email) : ?array {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM user_account WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }
}