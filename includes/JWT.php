<?php
/**
 * JWT Token Handler Class
 * Manages JSON Web Tokens for authentication
 */

class JWT {
    private static string $secret;
    private static int $expiry;
    
    /**
     * Initialize JWT with configuration
     */
    public static function init(): void {
        self::$secret = JWT_SECRET;
        self::$expiry = JWT_EXPIRY;
    }
    
    /**
     * Generate a new JWT token
     */
    public static function generate(array $payload): string {
        self::init();
        
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $time = time();
        
        $payload['iat'] = $time;
        $payload['exp'] = $time + self::$expiry;
        $payload['jti'] = bin2hex(random_bytes(16)); // Unique token ID
        
        $payloadJson = json_encode($payload);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payloadJson));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::$secret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    /**
     * Validate and decode a JWT token
     */
    public static function validate(string $token): ?array {
        self::init();
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        
        list($base64Header, $base64Payload, $base64Signature) = $parts;
        
        // Verify signature
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::$secret, true);
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        if (!hash_equals($expectedSignature, $base64Signature)) {
            return null;
        }
        
        // Decode payload
        $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Payload));
        $payload = json_decode($payloadJson, true);
        
        if (!$payload || !isset($payload['exp'])) {
            return null;
        }
        
        // Check expiration
        if ($payload['exp'] < time()) {
            return null;
        }
        
        return $payload;
    }
    
    /**
     * Extract token from request headers or cookies
     */
    public static function extract(): ?string {
        // Check Authorization header
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (strpos($auth, 'Bearer ') === 0) {
                return substr($auth, 7);
            }
        }
        
        // Check cookie
        if (isset($_COOKIE['jwt_token'])) {
            return $_COOKIE['jwt_token'];
        }
        
        return null;
    }
    
    /**
     * Set JWT token as HTTP-only cookie
     */
    public static function setCookie(string $token, int $expiry = null): void {
        $expiry = $expiry ?? self::$expiry;
        $expires = time() + $expiry;
        
        setcookie('jwt_token', $token, [
            'expires' => $expires,
            'path' => '/',
            'secure' => SESSION_COOKIE_SECURE,
            'httponly' => SESSION_COOKIE_HTTPONLY,
            'samesite' => SESSION_COOKIE_SAMESITE,
        ]);
    }
    
    /**
     * Clear JWT cookie
     */
    public static function clearCookie(): void {
        setcookie('jwt_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => SESSION_COOKIE_SECURE,
            'httponly' => SESSION_COOKIE_HTTPONLY,
            'samesite' => SESSION_COOKIE_SAMESITE,
        ]);
    }
    
    /**
     * Refresh token with new expiration
     */
    public static function refresh(string $token): ?string {
        $payload = self::validate($token);
        if (!$payload) {
            return null;
        }
        
        // Remove existing timestamp fields
        unset($payload['iat'], $payload['exp'], $payload['jti']);
        
        return self::generate($payload);
    }
}

// Initialize JWT on load
JWT::init();
