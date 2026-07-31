<?php
    class Jwt {
        private static function base64UrlEncode (string $data): string{
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }

        private static function base64UrlDecode (string $data){
            $padding = 4 - (strlen($data) % 4);

            if ($padding < 4){
                $data .= str_repeat('=', $padding);
            }

            return base64_decode(strtr($data, '-_', '+/'), true);
        }

        public static function createForUser(User $user, string $secret, int $ttl = 3600) : string {
            return self::encode([
                'sub' => (int) $user->getId(),
                'username' => $user->getUsername()
            ], $secret, $ttl);
        }

        public static function encode (array $payload, string $secret, int $ttl = 3600) : string {
            $header = ['alg' => 'HS256', 'typ' => 'JWT'];

            $payload['iat'] = time();
            $payload['exp'] = time() + $ttl;

            $encodedHeader = self::base64UrlEncode(json_encode($header));
            $encodedPayload = self::base64UrlEncode(json_encode($payload));

            $signature = hash_hmac(
                'sha256',
                $encodedHeader .'.'. $encodedPayload,
                $secret,
                true
            );

            return $encodedHeader .'.'. $encodedPayload .'.'. self::base64UrlEncode($signature);
        }

        public static function verify (string $token, string $secret){
            $parts = explode('.', $token);
            if (count($parts) !== 3) return false;

            [$h, $p, $signature] = $parts;

            $expectedSignature = self::base64UrlEncode(hash_hmac('sha256', $h .'.'. $p, $secret, true));

            if(!hash_equals($expectedSignature, $signature)) return false;

            $payload = json_decode(self::base64UrlDecode($p), true);
            if (!is_array($payload)) return false;
            if (($payload['exp'] ?? 0) < time()) return false;

            return $payload;
        }
    }
?>