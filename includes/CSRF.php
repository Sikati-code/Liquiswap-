<?php
/**
 * CSRF Protection Class
 * Handles CSRF token generation and validation
 */

class CSRF {
    private static string $salt;
    private static int $tokenLifetime = 3600; // 1 hour
    
    /**
     * Initialize CSRF with configuration
     */
    public static function init(): void {
        self::$salt = CSRF_TOKEN_SALT;
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Generate a new CSRF token
     */
    public static function generate(string $formId = 'default'): string {
        self::init();
        
        $token = bin2hex(random_bytes(32));
        $timestamp = time();
        
        // Store in session
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        
        $_SESSION['csrf_tokens'][$formId] = [
            'token' => $token,
            'timestamp' => $timestamp,
        ];
        
        // Clean old tokens
        self::cleanOldTokens();
        
        return $token;
    }
    
    /**
     * Validate a CSRF token
     */
    public static function validate(string $token, string $formId = 'default'): bool {
        self::init();
        
        if (empty($token) || !isset($_SESSION['csrf_tokens'][$formId])) {
            return false;
        }
        
        $stored = $_SESSION['csrf_tokens'][$formId];
        
        // Check expiration
        if (time() - $stored['timestamp'] > self::$tokenLifetime) {
            unset($_SESSION['csrf_tokens'][$formId]);
            return false;
        }
        
        // Validate token
        if (!hash_equals($stored['token'], $token)) {
            return false;
        }
        
        // Token is valid - remove it (one-time use)
        unset($_SESSION['csrf_tokens'][$formId]);
        
        return true;
    }
    
    /**
     * Get CSRF token for form (generate if not exists)
     */
    public static function getToken(string $formId = 'default'): string {
        self::init();
        
        if (isset($_SESSION['csrf_tokens'][$formId])) {
            $stored = $_SESSION['csrf_tokens'][$formId];
            // Check if still valid
            if (time() - $stored['timestamp'] <= self::$tokenLifetime) {
                return $stored['token'];
            }
        }
        
        return self::generate($formId);
    }
    
    /**
     * Clean old/expired tokens from session
     */
    private static function cleanOldTokens(): void {
        if (!isset($_SESSION['csrf_tokens'])) {
            return;
        }
        
        $now = time();
        foreach ($_SESSION['csrf_tokens'] as $formId => $data) {
            if ($now - $data['timestamp'] > self::$tokenLifetime) {
                unset($_SESSION['csrf_tokens'][$formId]);
            }
        }
    }
    
    /**
     * Generate hidden input field HTML
     */
    public static function field(string $formId = 'default'): string {
        $token = self::getToken($formId);
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Check CSRF token from request
     */
    public static function checkRequest(string $formId = 'default'): bool {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return self::validate($token, $formId);
    }
    
    /**
     * Get token for AJAX requests (returns current or generates new)
     */
    public static function getAjaxToken(): array {
        return [
            'token' => self::getToken('ajax'),
            'header_name' => 'X-CSRF-Token'
        ];
    }
}

// Initialize CSRF on load
CSRF::init();
