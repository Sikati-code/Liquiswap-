<?php
/**
 * Settings API
 * Handles user settings and preferences
 */

class SettingsAPI {
    private Database $db;
    private Auth $auth;
    private Session $session;
    
    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth();
        $this->session = new Session();
    }
    
    public function handle(string $action, string $method, array $data, array $queryParams): array {
        // Require authentication for all settings endpoints
        $user = $this->auth->getCurrentUser();
        if (!$user) {
            http_response_code(401);
            return ['success' => false, 'error' => 'Authentication required'];
        }
        
        switch ($action) {
            case '':
            case 'get':
                return $this->getSettings($user['id']);
            case 'update':
                return $this->updateSettings($user['id'], $data);
            case 'change-password':
                return $this->changePassword($user['id'], $data);
            case 'biometric':
                return $this->toggleBiometric($user['id'], $data);
            case '2fa':
                return $this->toggle2FA($user['id'], $data);
            case 'theme':
                return $this->updateTheme($user['id'], $data);
            case 'language':
                return $this->updateLanguage($user['id'], $data);
            case 'notifications':
                return $this->updateNotifications($user['id'], $data);
            case 'sessions':
                return $this->getActiveSessions($user['id']);
            case 'revoke-session':
                return isset($data['session_id']) ? 
                    $this->revokeSession($user['id'], (int)$data['session_id']) : 
                    ['success' => false, 'error' => 'Session ID required'];
            default:
                return ['success' => false, 'error' => 'Invalid settings action'];
        }
    }
    
    /**
     * Get user settings
     */
    private function getSettings(int $userId): array {
        $user = $this->db->fetchOne(
            "SELECT id, full_name, email, phone_number, biometric_enabled, 
                    two_factor_enabled, member_since, last_login
             FROM users WHERE id = :id",
            ['id' => $userId]
        );
        
        // Get wallet count
        $walletCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM wallets WHERE user_id = :user_id",
            ['user_id' => $userId]
        );
        
        // Default settings (in production, these would come from a settings table)
        $settings = [
            'account' => [
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'phone_number' => $user['phone_number'],
                'member_since' => $user['member_since'],
                'last_login' => $user['last_login'],
            ],
            'preferences' => [
                'dark_mode' => true, // Default to dark mode
                'language' => 'en',
                'notifications_enabled' => true,
                'sound_effects' => true,
                'haptic_feedback' => true,
            ],
            'security' => [
                'biometric_enabled' => (bool)$user['biometric_enabled'],
                'two_factor_enabled' => (bool)$user['two_factor_enabled'],
                'linked_wallets' => (int)$walletCount['count'],
            ],
            'privacy' => [
                'show_balance' => true,
                'share_activity' => false,
            ],
        ];
        
        return ['success' => true, 'data' => $settings];
    }
    
    /**
     * Update user settings
     */
    private function updateSettings(int $userId, array $data): array {
        $allowed = ['full_name', 'email', 'preferences'];
        $updateData = [];
        
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            return ['success' => false, 'error' => 'No valid settings to update'];
        }
        
        // Update session data
        if (isset($data['preferences'])) {
            $prefs = $data['preferences'];
            if (isset($prefs['language'])) {
                $this->session->set('language', $prefs['language']);
            }
            if (isset($prefs['dark_mode'])) {
                $this->session->set('dark_mode', $prefs['dark_mode']);
            }
        }
        
        return ['success' => true, 'message' => 'Settings updated successfully'];
    }
    
    /**
     * Change password
     */
    private function changePassword(int $userId, array $data): array {
        $required = ['current_password', 'new_password'];
        $missing = validateRequired($data, $required);
        
        if (!empty($missing)) {
            return ['success' => false, 'error' => 'Current password and new password required'];
        }
        
        $result = $this->auth->changePassword($userId, $data['current_password'], $data['new_password']);
        
        if ($result['success']) {
            // Revoke other sessions
            $this->session->revokeOtherSessions($userId);
        }
        
        return $result;
    }
    
    /**
     * Toggle biometric login
     */
    private function toggleBiometric(int $userId, array $data): array {
        if (!ENABLE_BIOMETRIC) {
            return ['success' => false, 'error' => 'Biometric authentication is disabled'];
        }
        
        if (!isset($data['enabled'])) {
            return ['success' => false, 'error' => 'Enabled status required'];
        }
        
        $enabled = (bool)$data['enabled'];
        return $this->auth->toggleBiometric($userId, $enabled);
    }
    
    /**
     * Toggle 2FA
     */
    private function toggle2FA(int $userId, array $data): array {
        if (!ENABLE_2FA) {
            return ['success' => false, 'error' => 'Two-factor authentication is disabled'];
        }
        
        if (!isset($data['enabled'])) {
            return ['success' => false, 'error' => 'Enabled status required'];
        }
        
        $enabled = (bool)$data['enabled'];
        
        if ($enabled) {
            // Generate 2FA secret
            $secret = $this->generate2FASecret();
            
            $this->db->update('users', [
                'two_factor_enabled' => true,
                'two_factor_secret' => $secret
            ], "id = :id", ['id' => $userId]);
            
            return [
                'success' => true,
                'enabled' => true,
                'secret' => $secret,
                'qr_code' => $this->generateQRCodeUrl($secret, $userId),
                'message' => '2FA enabled. Scan QR code with authenticator app.'
            ];
        } else {
            $this->db->update('users', [
                'two_factor_enabled' => false,
                'two_factor_secret' => null
            ], "id = :id", ['id' => $userId]);
            
            return [
                'success' => true,
                'enabled' => false,
                'message' => '2FA disabled successfully'
            ];
        }
    }
    
    /**
     * Update theme preference
     */
    private function updateTheme(int $userId, array $data): array {
        if (!isset($data['dark_mode'])) {
            return ['success' => false, 'error' => 'Dark mode preference required'];
        }
        
        $darkMode = (bool)$data['dark_mode'];
        $this->session->set('dark_mode', $darkMode);
        
        return [
            'success' => true,
            'dark_mode' => $darkMode,
            'message' => $darkMode ? 'Dark mode enabled' : 'Light mode enabled'
        ];
    }
    
    /**
     * Update language preference
     */
    private function updateLanguage(int $userId, array $data): array {
        if (empty($data['language'])) {
            return ['success' => false, 'error' => 'Language code required'];
        }
        
        $validLanguages = ['en', 'fr'];
        $language = strtolower($data['language']);
        
        if (!in_array($language, $validLanguages)) {
            return ['success' => false, 'error' => 'Invalid language'];
        }
        
        $this->session->set('language', $language);
        
        return [
            'success' => true,
            'language' => $language,
            'message' => $language === 'fr' ? 'Langue changée en Français' : 'Language changed to English'
        ];
    }
    
    /**
     * Update notification preferences
     */
    private function updateNotifications(int $userId, array $data): array {
        $settings = [];
        
        if (isset($data['enabled'])) {
            $settings['notifications_enabled'] = (bool)$data['enabled'];
        }
        if (isset($data['transaction_alerts'])) {
            $settings['transaction_alerts'] = (bool)$data['transaction_alerts'];
        }
        if (isset($data['promotional'])) {
            $settings['promotional_notifications'] = (bool)$data['promotional'];
        }
        if (isset($data['security_alerts'])) {
            $settings['security_alerts'] = (bool)$data['security_alerts'];
        }
        
        return [
            'success' => true,
            'settings' => $settings,
            'message' => 'Notification preferences updated'
        ];
    }
    
    /**
     * Get active sessions
     */
    private function getActiveSessions(int $userId): array {
        $sessions = $this->session->getUserSessions($userId);
        
        $currentToken = $this->session->get('session_token');
        
        $formatted = array_map(function($s) use ($currentToken) {
            return [
                'id' => $s['id'],
                'ip_address' => $s['ip_address'],
                'user_agent' => $this->parseUserAgent($s['user_agent']),
                'created_at' => $s['created_at'],
                'expires_at' => $s['expires_at'],
                'is_current' => $s['token'] === $currentToken
            ];
        }, $sessions);
        
        return [
            'success' => true,
            'data' => [
                'sessions' => $formatted,
                'total' => count($formatted)
            ]
        ];
    }
    
    /**
     * Revoke a session
     */
    private function revokeSession(int $userId, int $sessionId): array {
        $result = $this->session->revokeSession($sessionId, $userId);
        
        if (!$result) {
            return ['success' => false, 'error' => 'Session not found or cannot be revoked'];
        }
        
        return [
            'success' => true,
            'message' => 'Session revoked successfully'
        ];
    }
    
    /**
     * Generate 2FA secret
     */
    private function generate2FASecret(): string {
        // In production, use a proper TOTP library
        return base64_encode(random_bytes(16));
    }
    
    /**
     * Generate QR code URL for 2FA
     */
    private function generateQRCodeUrl(string $secret, int $userId): string {
        $user = $this->db->fetchOne("SELECT email FROM users WHERE id = :id", ['id' => $userId]);
        $email = $user['email'] ?? 'user@liquiswap.com';
        
        $label = urlencode(APP_NAME . ':' . $email);
        $issuer = urlencode(APP_NAME);
        
        return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}";
    }
    
    /**
     * Parse user agent string
     */
    private function parseUserAgent(string $ua): array {
        $browser = 'Unknown';
        $os = 'Unknown';
        $device = 'Unknown';
        
        if (strpos($ua, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($ua, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($ua, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($ua, 'Edge') !== false) {
            $browser = 'Edge';
        }
        
        if (strpos($ua, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($ua, 'Mac') !== false) {
            $os = 'macOS';
        } elseif (strpos($ua, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($ua, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($ua, 'iPhone') !== false || strpos($ua, 'iPad') !== false) {
            $os = 'iOS';
        }
        
        return [
            'browser' => $browser,
            'os' => $os,
            'device' => $device,
            'raw' => substr($ua, 0, 100) // Truncated
        ];
    }
}
