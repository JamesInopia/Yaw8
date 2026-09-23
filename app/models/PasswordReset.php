<?php
class PasswordReset {
    # Wipes any older codes for this email, then stores the fresh one
    public function createCode($email, $codeHash, $expiresAt): bool {
        $pdo = Database::connect();
        $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);

        $stmt = $pdo->prepare('INSERT INTO password_resets (email, code_hash, expires_at) VALUES (?, ?, ?)');
        return $stmt->execute([$email, $codeHash, $expiresAt]);
    }

    # Fetches the latest unexpired code row for this email
    public function getActiveCode($email): ?array {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            'SELECT * FROM password_resets WHERE email = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function markVerified($id): bool {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('UPDATE password_resets SET verified = 1 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    # Confirms this email passed
    public function isVerified($email): bool {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            'SELECT id FROM password_resets WHERE email = ? AND verified = 1 AND expires_at > NOW() ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$email]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteForEmail($email): bool {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = ?');
        return $stmt->execute([$email]);
    }
}
