<?php
class AuthMiddleware{
    public static function requireAuth(){
        $config = require __DIR__ .'/../config/auth.php';
        $header = self::authorizationHeader();

        if (strncasecmp($header, 'Bearer ', 7) !== 0) {
            self::json(['message' => 'Bearer token required'], 401);
            exit;
        }

        $token = trim(str_ireplace('Bearer ', '', $header));

        $payload = Jwt::verify($token, $config['jwt_secret']);

        if(!$payload){
            self::json(['message' => 'Invalid or expired token'], 401);
            exit;
        }

        return $payload;
    }

    public static function authorizationHeader(){
            return $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    }

        private static function json(array $data, int $status = 200){
            http_response_code($status);
            header('Content-Type: application/json');
            echo json_encode($data);
        }
}