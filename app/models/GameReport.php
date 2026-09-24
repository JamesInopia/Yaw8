<?php
# Reports that users have made against a game
class GameReport {
    public const STATUS_OPEN = 'open';
    public const STATUS_RESOLVED = 'resolved';

    # Files a new report submitted by a user against a game
    public function create($gameId, $reporterId, $reason, $details = null): int {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            'INSERT INTO game_report (gameId, reporterId, reason, details, status, createdAt)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $gameId,
            $reporterId,
            ($reason !== null && $reason !== '') ? $reason : 'Other',
            ($details !== null && $details !== '') ? $details : null,
            self::STATUS_OPEN,
        ]);

        return (int) $pdo->lastInsertId();
    }

    # All reports for one game — open ones first, newest first within each group
    public function getByGame($gameId): array {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            'SELECT r.reportId, r.gameId, r.reason, r.details, r.status,
                    r.createdAt, r.resolvedAt,
                    reporter.username AS reporterUsername,
                    resolver.username AS resolvedByUsername
             FROM game_report r
             LEFT JOIN user_account reporter ON reporter.userId = r.reporterId
             LEFT JOIN user_account resolver ON resolver.userId = r.resolvedBy
             WHERE r.gameId = ?
             ORDER BY (r.status = "open") DESC, r.createdAt DESC, r.reportId DESC'
        );
        $stmt->execute([$gameId]);

        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($reports as &$report) {
            $report['reportId'] = (int) $report['reportId'];
            $report['gameId'] = (int) $report['gameId'];
        }
        unset($report);

        return $reports;
    }

    public function countOpenByGame($gameId): int {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM game_report WHERE gameId = ? AND status = "open"');
        $stmt->execute([$gameId]);

        return (int) $stmt->fetchColumn();
    }

    public function find($reportId): ?array {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT reportId, gameId, status FROM game_report WHERE reportId = ? LIMIT 1');
        $stmt->execute([$reportId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    # Marks one report as resolved
    public function resolve($reportId, $adminId): bool {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            'UPDATE game_report
             SET status = "resolved", resolvedAt = NOW(), resolvedBy = ?
             WHERE reportId = ? AND status = "open"'
        );
        $stmt->execute([$adminId, $reportId]);

        return $stmt->rowCount() > 0;
    }

    # Marks every open report on a game as resolved. Returns how many changed
    public function resolveAllForGame($gameId, $adminId): int {
        $pdo = Database::connect();
        $stmt = $pdo->prepare(
            'UPDATE game_report
             SET status = "resolved", resolvedAt = NOW(), resolvedBy = ?
             WHERE gameId = ? AND status = "open"'
        );
        $stmt->execute([$adminId, $gameId]);

        return $stmt->rowCount();
    }
}
