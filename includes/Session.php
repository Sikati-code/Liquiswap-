<?php
/**
 * Session Management Class
 * Handles user sessions with database backing
 */

class Session {
    private Database $db;
    
    public function __construct() {
        $this->db = new Database();
        $this->init();
    }
    
    /**
     * Initialize session configuration
     */
    private function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure session
            ini_set('session.cookie_lifetime', SESSION_LIFETIME);
            ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
            
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'secure' => SESSION_COOKIE_SECURE,
                'httponly' => SESSION_COOKIE_HTTPONLY,
                'samesite' => SESSION_COOKIE_SAMESITE,
            ]);
            
            session_start();
        }
    }
    
    /**
     * Create new session for user
     */
    public function create(int $userId, array $userData = []): string {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        
        // Store in database
        $this->db->insert('sessions', [
            'user_id' => $userId,
            'token' => $token,
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'expires_at' => $expiresAt,
        ]);
        
        // Store in PHP session
        $_SESSION['user_id'] = $userId;
        $_SESSION['session_token'] = $token;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Merge user data
        foreach ($userData as $key => $value) {
            $_SESSION[$key] = $value;
        }
        
        // Set session cookie
        setcookie('session_id', session_id(), [
            'expires' => time() + SESSION_LIFETIME,
            'path' => '/',
            'secure' => SESSION_COOKIE_SECURE,
            'httponly' => SESSION_COOKIE_HTTPONLY,
            'samesite' => SESSION_COOKIE_SAMESITE,
        ]);
        
        return $token;
    }
    
    /**
     * Validate current session
     */
    public function validate(): bool {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
            return false;
        }
        
        // Check session in database
        $session = $this->db->fetchOne(
            "SELECT * FROM sessions WHERE token = :token AND expires_at > NOW()",
            ['token' => $_SESSION['session_token']]
        );
        
        if (!$session) {
            $this->destroy();
            return false;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        // Check for session hijacking
        if ($session['ip_address'] !== $this->getClientIp()) {
            // Log suspicious activity
            error_log("Session IP mismatch for user {$_SESSION['user_id']}");
        }
        
        return true;
    }
    
    /**
     * Get current user ID
     */
    public function getUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get session data
     */
    public function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Set session data
     */
    public function set(string $key, $value): void {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Remove session data
     */
    public function remove(string $key): void {
        unset($_SESSION[$key]);
    }
    
    /**
     * Destroy current session
     */
    public function destroy(): void {
        // Remove from database
        if (isset($_SESSION['session_token'])) {
            $this->db->delete('sessions', "token = :token", [
                'token' => $_SESSION['session_token']
            ]);
        }
        
        // Clear PHP session
        $_SESSION = [];
        
        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => SESSION_COOKIE_SECURE,
                'httponly' => SESSION_COOKIE_HTTPONLY,
                'samesite' => SESSION_COOKIE_SAMESITE,
            ]);
        }
        
        session_destroy();
    }
    
    /**
     * Regenerate session ID (prevents fixation attacks)
     */
    public function regenerate(): void {
        session_regenerate_id(true);
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool {
        return $this->validate();
    }
    
    /**
     * Require authentication
     */
    public function requireAuth(): void {
        if (!$this->isLoggedIn()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Authentication required',
                'redirect' => '/login'
            ]);
            exit;
        }
    }
    
    /**
     * Get client IP address
     */
    private function getClientIp(): string {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                   'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Clean expired sessions from database
     */
    public function cleanExpired(): int {
        return $this->db->delete('sessions', "expires_at < NOW()");
    }
    
    /**
     * Get active sessions for user
     */
    public function getUserSessions(int $userId): array {
        return $this->db->fetchAll(
            "SELECT id, ip_address, user_agent, created_at, expires_at 
             FROM sessions 
             WHERE user_id = :user_id AND expires_at > NOW()
             ORDER BY created_at DESC",
            ['user_id' => $userId]
        );
    }
    
    /**
     * Revoke specific session
     */
    public function revokeSession(int $sessionId, int $userId): bool {
        $result = $this->db->delete(
            'sessions',
            "id = :id AND user_id = :user_id",
            ['id' => $sessionId, 'user_id' => $userId]
        );
        return $result > 0;
    }
    
    /**
     * Revoke all sessions except current
     */
    public function revokeOtherSessions(int $userId): void {
        if (isset($_SESSION['session_token'])) {
            $this->db->delete(
                'sessions',
                "user_id = :user_id AND token != :token",
                ['user_id' => $userId, 'token' => $_SESSION['session_token']]
            );
        }
    }
}
