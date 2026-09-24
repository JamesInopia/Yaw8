<?php
class AuthMiddleware{
    public static function requireAuth(){
        $config = require __DIR__ .'/../config/auth.php';
        $header = self::authorizationHeader();
        $hasBearer = strncasecmp($header, 'Bearer ', 7) === 0;

        $payload = null;

        # 1) A valid JWT (kept for compatibility)
        if ($hasBearer) {
            $token = trim(substr($header, 7));
            $payload = Jwt::verify($token, $config['jwt_secret']);
        }

        # 2) Otherwise the PHP session. This is what the navbar and every page guard
        #    already treat as "logged in", and unlike the JWT (1 hour, stored in
        #    localStorage) it doesn't silently expire while the person is still browsing.
        if (!$payload) {
            $payload = self::payloadFromSession();
        }

        if (!$payload) {
            self::json(['message' => $hasBearer ? 'Invalid or expired token' : 'Bearer token required'], 401);
            exit;
        }

        # A suspended account can't use the API even if its token hasn't expired yet
        $suspension = Auth::suspensionOf($payload['sub'] ?? null);
        if ($suspension !== null) {
            self::json(['success' => false, 'suspended' => true, 'message' => Auth::suspensionMessage($suspension)], 403);
            exit;
        }

        return $payload;
    }

    # Builds a JWT-shaped payload from the logged-in PHP session, or null if there isn't one.
    # Only accepted for requests sent by our own JS (authHeaders() adds X-Requested-With) —
    # a form posted from another site can't set that header, so cookie-only requests are refused.
    private static function payloadFromSession(): ?array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (empty($userId)) {
            return null;
        }

        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
            return null;
        }

        $username = '';
        try {
            $row = (new User())->getUserById($userId);
            $username = (string) ($row['username'] ?? '');
        } catch (Throwable $e) {
            error_log('AuthMiddleware session lookup failed: ' . $e->getMessage());
        }

        return ['sub' => (int) $userId, 'username' => $username];
    }

    public static function authorizationHeader(){
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

        # Some Apache setups (XAMPP included) don't copy the header into $_SERVER
        if ($header === '' && function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                if (strcasecmp($name, 'Authorization') === 0) {
                    return $value;
                }
            }
        }

        return $header;
    }

    private static function json(array $data, int $status = 200){
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}