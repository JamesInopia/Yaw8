<?php
class User {
    private $userId;
    private $fullname;
    private $username;
    private $email;
    private $password;
    private $role;
    private $bio;

    public function __construct($userId = '', $fullname = '', $username = '', $email = '', $password = '', $role = '', $bio = ''){
        $this->userId = $userId;
        $this->fullname = $fullname;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->bio = $bio;
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
    
    public function getBio() {return $this->bio; }
    public function setBio($bio) { $this->bio = $bio;}

    # Function that adds a user to the database
    public function addUser($fullname, $username, $email, $password, $role) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('INSERT INTO user_account (fullname, username, email, password, role) VALUES (?, ?, ?, ?, ?)');
        
        if ($stmt->execute([trim($fullname), trim($username), trim($email), trim($password), trim($role)])) {
            return $pdo->lastInsertId();
        }
        return false;
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

    # Function that edits/updates an existing user's profile
    public function editUser($id, $fullname, $username, $bio): bool {
        $pdo = Database::connect();
        
        $sql = 'UPDATE user_account SET fullname = ?, username = ?, bio = ? WHERE userId = ?';
        $stmt = $pdo->prepare($sql);
        
        return $stmt->execute([
            trim($fullname), 
            trim($username), 
            trim($bio), 
            $id
        ]);
    }

    # Function to update user email
    public function updateEmail($userId, $email): bool {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('UPDATE user_account SET email = ? WHERE userId = ?');
        return $stmt->execute([trim($email), $userId]);
    }

    # Function to update user password
    public function updatePassword($userId, $hashedPassword): bool {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('UPDATE user_account SET password = ? WHERE userId = ?');
        return $stmt->execute([$hashedPassword, $userId]);
    }

    # Function to update user password by email (used by the forgot-password flow,
    # where the user isn't logged in yet so there's no userId to key off of)
    public function updatePasswordByEmail($email, $hashedPassword): bool {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('UPDATE user_account SET password = ? WHERE email = ?');
        return $stmt->execute([$hashedPassword, $email]);
    }

    public function getUserById($userId) : ?array {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM user_account WHERE userId = ? LIMIT 1');
        $stmt->execute([$userId]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    # ───────────────────────────────────────────
    # AUTH STATE + ADMIN USER MANAGEMENT
    # ───────────────────────────────────────────

    # Role + whether the account is suspended RIGHT NOW (a timed suspension
    # whose end date has passed no longer counts). Uses the database clock for
    # both writing and comparing, so PHP/MySQL timezone differences can't matter.
    public function getAuthState($userId): ?array {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            'SELECT userId, role, suspended_until,
                    (suspended = 1 AND (suspended_until IS NULL OR suspended_until > NOW())) AS suspendedNow
             FROM user_account
             WHERE userId = ?
             LIMIT 1'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    # Accounts for the admin "Users" tab.
    #   $includeAdmins = false -> only non-admin accounts (what a normal Admin sees)
    #   $includeAdmins = true  -> also Admin accounts (Supreme Overlord only)
    # Supreme Overlord accounts and the person asking are never listed.
    # Rating = average of all ratings received on the games this user made.
    public function getUsersForAdmin(bool $includeAdmins, $excludeUserId): array {
        $params = [];
        $where = ['1 = 1'];

        if (!$includeAdmins) {
            $marks = implode(', ', array_fill(0, count(Auth::ADMIN_ROLES), '?'));
            $where[] = "LOWER(TRIM(COALESCE(u.role, ''))) NOT IN ($marks)";
            $params = array_merge($params, Auth::ADMIN_ROLES);
        } else {
            $where[] = "LOWER(TRIM(COALESCE(u.role, ''))) <> ?";
            $params[] = Auth::SUPREME_ROLE;
        }

        $where[] = 'u.userId <> ?';
        $params[] = (int) $excludeUserId;

        $pdo = Database::connect();
        $stmt = $pdo->prepare($this->adminUserSelect() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY u.userId ASC');
        $stmt->execute($params);

        return array_map([$this, 'shapeAdminUser'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    # One account in the same shape as the list (any role — callers check permissions).
    public function getUserForAdmin($userId): ?array {
        $pdo = Database::connect();
        $stmt = $pdo->prepare($this->adminUserSelect() . ' WHERE u.userId = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->shapeAdminUser($row) : null;
    }

    private function adminUserSelect(): string {
        return 'SELECT u.userId, u.fullname, u.username, u.email, u.role, u.suspended_until,
                    (u.suspended = 1 AND (u.suspended_until IS NULL OR u.suspended_until > NOW())) AS suspendedNow,
                    (SELECT AVG(r.rating)
                       FROM rating r
                       JOIN game_devs gd ON gd.gameId = r.gameId
                      WHERE gd.userId = u.userId) AS avgRating,
                    (SELECT COUNT(*)
                       FROM rating r
                       JOIN game_devs gd ON gd.gameId = r.gameId
                      WHERE gd.userId = u.userId) AS ratingCount
                FROM user_account u';
    }

    private function shapeAdminUser(array $row): array {
        $suspended = !empty($row['suspendedNow']);

        return [
            'userId'         => (int) $row['userId'],
            'name'           => $row['fullname'],
            'username'       => $row['username'],
            'email'          => $row['email'],
            'role'           => (string) $row['role'],
            'avgRating'      => round((float) $row['avgRating'], 1),
            'ratingCount'    => (int) $row['ratingCount'],
            'suspended'      => $suspended,
            'suspendedUntil' => $suspended ? $row['suspended_until'] : null,
            'indefinite'     => $suspended && $row['suspended_until'] === null,
        ];
    }

    # $days = null -> indefinite (only an admin can lift it); $days = N -> N days from now.
    public function suspend($userId, ?int $days): bool {
        $pdo = Database::connect();

        if ($days === null) {
            $stmt = $pdo->prepare('UPDATE user_account SET suspended = 1, suspended_until = NULL WHERE userId = ?');
            return $stmt->execute([$userId]);
        }

        $stmt = $pdo->prepare('UPDATE user_account SET suspended = 1, suspended_until = DATE_ADD(NOW(), INTERVAL ? DAY) WHERE userId = ?');
        return $stmt->execute([$days, $userId]);
    }

    public function unsuspend($userId): bool {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('UPDATE user_account SET suspended = 0, suspended_until = NULL WHERE userId = ?');

        return $stmt->execute([$userId]);
    }

    public function updateRole($userId, string $role): bool {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('UPDATE user_account SET role = ? WHERE userId = ?');

        return $stmt->execute([$role, $userId]);
    }

    # Total number of registered accounts (admin dashboard stat card)
    public function countAll(): int {
        $pdo = Database::connect();
        return (int) $pdo->query('SELECT COUNT(*) FROM user_account')->fetchColumn();
    }

    public function getAllUsers() {
        $pdo = Database::connect();
        $sql = 'SELECT u.userId AS id, u.fullname AS name, u.role, u.bio, 
                    COUNT(DISTINCT g.gameId) AS games,
                    AVG(r.rating) AS avgRating,
                    COUNT(r.rating) AS ratingCount
                FROM user_account u
                LEFT JOIN game_devs gd ON u.userId = gd.userId
                LEFT JOIN game g ON gd.gameId = g.gameId AND g.status = "published"
                LEFT JOIN rating r ON r.gameId = g.gameId
                GROUP BY u.userId';
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}