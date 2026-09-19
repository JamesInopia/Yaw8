<?php
# Small helpers that answer "who is the current user and what may they do?"
#
# The role and the suspension state are ALWAYS read from the database (once per
# request, then cached), never from the session or the JWT. That way promoting,
# demoting or suspending someone takes effect immediately instead of when their
# session/token expires.
class Auth {
    # Roles (user_account.role) that may use the admin area.
    # Compared case-insensitively and ignoring surrounding spaces.
    public const ADMIN_ROLES = ['admin', 'supreme overlord'];
    public const SUPREME_ROLE = 'supreme overlord';

    # Routes a suspended person is still allowed to reach (login / logout screens).
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

    # role + suspension state for one user (one DB query per user per request).
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
                # Most likely the suspension columns don't exist yet (migration not run).
                # Fall back to "role only" so the rest of the site keeps working.
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

    # The role stored in the database for a user id ('' if unknown).
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

    # Is whoever is browsing right now an Admin / Supreme Overlord?
    public static function isAdmin(): bool {
        return self::isAdminUser(self::currentUserId());
    }

    # Is whoever is browsing right now a Supreme Overlord?
    public static function isSupremeOverlord(): bool {
        return self::isSupremeOverlordUser(self::currentUserId());
    }

    # ── Suspension ─────────────────────────────────────────

    # null if the account is fine, otherwise ['until' => 'Y-m-d H:i:s'|null, 'indefinite' => bool].
    # A timed suspension whose end date has passed counts as "not suspended".
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

    # Called on every request (see public/index.php). If the logged-in user has
    # been suspended, their session is ended immediately:
    #   - page navigations are sent to the login page
    #   - fetch()/API calls get a JSON 403
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
