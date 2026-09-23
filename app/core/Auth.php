<?php
class Auth {
    # Defines what roles are in the system
    public const ADMIN_ROLES = ['admin', 'supreme overlord'];
    public const SUPREME_ROLE = 'supreme overlord';

    # Routes a suspended person
    private const AUTH_ROUTES = [
        'auth', 'auth/form', 'login', 'signup', 'logout',
        'auth/forgot-password', 'auth/verify-reset-code', 'auth/reset-password',
    ];

    private static array $stateCache = [];

    # The logged-in user's id from the PHP session, or null for a guest.
    public static function currentUserId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? null;
    }

    # role + suspension state for one user
    private static function state($userId): array {
        $empty = ['role' => '', 'suspended' => false, 'until' => null];

        if (empty($userId)) {
            return $empty;
        }

        if (!array_key_exists($userId, self::$stateCache)) {
            $userModel = new User();
            $row = null;

            try {
                $row = $userModel->getAuthState($userId);
            } catch (Throwable $e) {
                error_log('Auth::state failed: ' . $e->getMessage());

                try {
                    $plain = $userModel->getUserById($userId);
                    $row = $plain ? ['role' => $plain['role'] ?? '', 'suspendedNow' => 0, 'suspended_until' => null] : null;
                } catch (Throwable $e2) {
                    error_log('Auth::state fallback failed: ' . $e2->getMessage());
                }
            }

            self::$stateCache[$userId] = $row ? [
                'role'      => (string) ($row['role'] ?? ''),
                'suspended' => !empty($row['suspendedNow']),
                'until'     => $row['suspended_until'] ?? null,
            ] : $empty;
        }

        return self::$stateCache[$userId];
    }

    # ── Roles ──────────────────────────────────────────────

    # The role stored in the database
    public static function roleOf($userId): string {
        return self::state($userId)['role'];
    }

    # Admin OR Supreme Overlord
    public static function isAdminRole($role): bool {
        return in_array(strtolower(trim((string) $role)), self::ADMIN_ROLES, true);
    }

    public static function isSupremeOverlordRole($role): bool {
        return strtolower(trim((string) $role)) === self::SUPREME_ROLE;
    }

    public static function isAdminUser($userId): bool {
        return self::isAdminRole(self::roleOf($userId));
    }

    public static function isSupremeOverlordUser($userId): bool {
        return self::isSupremeOverlordRole(self::roleOf($userId));
    }

    # For Supreme Overlord
    public static function isAdmin(): bool {
        return self::isAdminUser(self::currentUserId());
    }

    public static function isSupremeOverlord(): bool {
        return self::isSupremeOverlordUser(self::currentUserId());
    }

    # ── Suspension ─────────────────────────────────────────
    # A timed suspension
    public static function suspensionOf($userId): ?array {
        $state = self::state($userId);

        if (!$state['suspended']) {
            return null;
        }

        return ['until' => $state['until'], 'indefinite' => $state['until'] === null];
    }

    # Human-readable text shown to a suspended person.
    public static function suspensionMessage(array $suspension): string {
        if ($suspension['indefinite']) {
            return 'Your account has been suspended. Only an administrator can lift this suspension.';
        }

        return 'Your account is suspended until ' . date('M j, Y, g:i A', strtotime($suspension['until'])) . '.';
    }

    public static function enforceSuspension(string $route): void {
        $userId = self::currentUserId();

        if (!$userId || in_array($route, self::AUTH_ROUTES, true)) {
            return;
        }

        $suspension = self::suspensionOf($userId);

        if ($suspension === null) {
            return;
        }

        $message = self::suspensionMessage($suspension);

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $isNavigation = ($_SERVER['HTTP_SEC_FETCH_MODE'] ?? 'navigate') === 'navigate';

        if ($isNavigation) {
            header('Location: ?url=auth&suspended=1');
            exit;
        }

        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'suspended' => true, 'message' => $message]);
        exit;
    }
}
