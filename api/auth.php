<?php
/**
 * Authentication API
 * Handles login, register, logout, password management
 */

class AuthAPI {
    private Auth $auth;
    private Session $session;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->session = new Session();
    }
    
    public function handle(string $action, string $method, array $data, array $queryParams): array {
        switch ($action) {
            case 'register':
                return $this->register($data);
            case 'login':
                return $this->login($data);
            case 'logout':
                return $this->logout();
            case 'biometric':
                return $this->biometricLogin($data);
            case 'forgot-password':
                return $this->forgotPassword($data);
            case 'reset-password':
                return $this->resetPassword($data);
            case 'refresh':
                return $this->refreshToken();
            case 'check':
                return $this->checkAuth();
            default:
                return ['success' => false, 'error' => 'Invalid auth action'];
        }
    }
    
    private function register(array $data): array {
        $required = ['full_name', 'phone_number', 'password'];
        $missing = validateRequired($data, $required);
        
        if (!empty($missing)) {
            return ['success' => false, 'error' => 'Missing required fields', 'fields' => $missing];
        }
        
        return $this->auth->register($data);
    }
    
    private function login(array $data): array {
        $required = ['phone_number', 'password'];
        $missing = validateRequired($data, $required);
        
        if (!empty($missing)) {
            return ['success' => false, 'error' => 'Phone number and password required'];
        }
        
        $remember = $data['remember'] ?? false;
        return $this->auth->login($data['phone_number'], $data['password'], $remember);
    }
    
    private function logout(): array {
        $this->auth->logout();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    private function biometricLogin(array $data): array {
        if (!ENABLE_BIOMETRIC) {
            return ['success' => false, 'error' => 'Biometric login is disabled'];
        }
        
        if (empty($data['user_id'])) {
            return ['success' => false, 'error' => 'User ID required'];
        }
        
        return $this->auth->biometricLogin((int)$data['user_id']);
    }
    
    private function forgotPassword(array $data): array {
        if (empty($data['phone_number'])) {
            return ['success' => false, 'error' => 'Phone number required'];
        }
        
        return $this->auth->requestPasswordReset($data['phone_number']);
    }
    
    private function resetPassword(array $data): array {
        $required = ['token', 'new_password'];
        $missing = validateRequired($data, $required);
        
        if (!empty($missing)) {
            return ['success' => false, 'error' => 'Token and new password required'];
        }
        
        return $this->auth->resetPassword($data['token'], $data['new_password']);
    }
    
    private function refreshToken(): array {
        $token = JWT::extract();
        
        if (!$token) {
            return ['success' => false, 'error' => 'No token provided'];
        }
        
        $newToken = JWT::refresh($token);
        
        if (!$newToken) {
            return ['success' => false, 'error' => 'Invalid or expired token'];
        }
        
        JWT::setCookie($newToken);
        
        return ['success' => true, 'token' => $newToken];
    }
    
    private function checkAuth(): array {
        $user = $this->auth->getCurrentUser();
        
        if (!$user) {
            return ['success' => false, 'error' => 'Not authenticated'];
        }
        
        return [
            'success' => true,
            'authenticated' => true,
            'user' => [
                'id' => $user['id'],
                'uuid' => $user['uuid'],
                'full_name' => $user['full_name'],
                'phone_number' => $user['phone_number'],
                'email' => $user['email'],
                'role' => $user['role'],
                'trust_score' => $user['trust_score'],
                'total_swaps' => $user['total_swaps'],
                'success_rate' => $user['success_rate'],
                'member_since' => $user['member_since'],
                'biometric_enabled' => $user['biometric_enabled'],
                'two_factor_enabled' => $user['two_factor_enabled'],
            ]
        ];
    }
}
