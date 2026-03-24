<?php
/**
 * User & Wallet API
 * Handles user profile and wallet management
 */

class UserAPI {
    private Database $db;
    private Auth $auth;
    private Session $session;
    
    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth();
        $this->session = new Session();
    }
    
    public function handle(string $action, ?string $id, string $method, array $data, array $queryParams): array {
        // Require authentication for all user endpoints
        $user = $this->auth->getCurrentUser();
        if (!$user && $action !== 'profile-public') {
            http_response_code(401);
            return ['success' => false, 'error' => 'Authentication required'];
        }
        
        switch ($action) {
            case 'profile':
                return $method === 'GET' ? $this->getProfile($user['id']) : $this->updateProfile($user['id'], $data);
            case 'wallets':
                return $this->handleWallets($user['id'], $id, $method, $data);
            case 'trust-score':
                return $this->getTrustScore($user['id']);
            case 'stats':
                return $this->getStats($user['id']);
            case 'recent-contacts':
                return $this->getRecentContacts($user['id']);
            default:
                return ['success' => false, 'error' => 'Invalid user action'];
        }
    }
    
    private function getProfile(int $userId): array {
        $user = $this->db->fetchOne(
            "SELECT id, uuid, full_name, email, phone_number, role, trust_score,
                    total_swaps, success_rate, member_since, last_login,
                    biometric_enabled, two_factor_enabled, created_at
             FROM users WHERE id = :id",
            ['id' => $userId]
        );
        
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        return ['success' => true, 'data' => $user];
    }
    
    private function updateProfile(int $userId, array $data): array {
        $allowedFields = ['full_name', 'email'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = sanitize($data[$field]);
            }
        }
        
        if (empty($updateData)) {
            return ['success' => false, 'error' => 'No valid fields to update'];
        }
        
        $this->db->update('users', $updateData, "id = :id", ['id' => $userId]);
        
        return ['success' => true, 'message' => 'Profile updated successfully'];
    }
    
    private function handleWallets(int $userId, ?string $id, string $method, array $data): array {
        switch ($method) {
            case 'GET':
                return $this->getWallets($userId);
            case 'POST':
                return $this->addWallet($userId, $data);
            case 'PUT':
                return $id ? $this->updateWallet($userId, (int)$id, $data) : 
                       ['success' => false, 'error' => 'Wallet ID required'];
            case 'DELETE':
                return $id ? $this->deleteWallet($userId, (int)$id) : 
                       ['success' => false, 'error' => 'Wallet ID required'];
            default:
                return ['success' => false, 'error' => 'Method not allowed'];
        }
    }
    
    private function getWallets(int $userId): array {
        $wallets = $this->db->fetchAll(
            "SELECT id, provider, account_identifier, balance, is_primary, is_verified, created_at
             FROM wallets WHERE user_id = :user_id ORDER BY is_primary DESC, provider",
            ['user_id' => $userId]
        );
        
        // Calculate total balance
        $totalBalance = array_sum(array_column($wallets, 'balance'));
        
        return [
            'success' => true,
            'data' => [
                'wallets' => $wallets,
                'total_balance' => $totalBalance,
                'currency' => CURRENCY_CODE
            ]
        ];
    }
    
    private function addWallet(int $userId, array $data): array {
        $required = ['provider', 'account_identifier'];
        $missing = validateRequired($data, $required);
        
        if (!empty($missing)) {
            return ['success' => false, 'error' => 'Provider and account identifier required'];
        }
        
        $validProviders = ['MTN', 'ORANGE', 'BANK', 'CASH'];
        if (!in_array($data['provider'], $validProviders)) {
            return ['success' => false, 'error' => 'Invalid provider'];
        }
        
        try {
            $walletId = $this->db->insert('wallets', [
                'user_id' => $userId,
                'provider' => $data['provider'],
                'account_identifier' => sanitize($data['account_identifier']),
                'balance' => $data['balance'] ?? 0.00,
                'is_primary' => $data['is_primary'] ?? false,
                'is_verified' => false,
            ]);
            
            return ['success' => true, 'wallet_id' => $walletId, 'message' => 'Wallet added successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Wallet already exists or invalid data'];
        }
    }
    
    private function updateWallet(int $userId, int $walletId, array $data): array {
        $allowedFields = ['account_identifier', 'is_primary', 'is_verified'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            return ['success' => false, 'error' => 'No valid fields to update'];
        }
        
        // If setting as primary, unset other primary wallets
        if (!empty($updateData['is_primary']) && $updateData['is_primary']) {
            $this->db->update(
                'wallets',
                ['is_primary' => false],
                "user_id = :user_id AND id != :id",
                ['user_id' => $userId, 'id' => $walletId]
            );
        }
        
        $updated = $this->db->update(
            'wallets',
            $updateData,
            "id = :id AND user_id = :user_id",
            ['id' => $walletId, 'user_id' => $userId]
        );
        
        if ($updated === 0) {
            return ['success' => false, 'error' => 'Wallet not found or no changes made'];
        }
        
        return ['success' => true, 'message' => 'Wallet updated successfully'];
    }
    
    private function deleteWallet(int $userId, int $walletId): array {
        $deleted = $this->db->delete(
            'wallets',
            "id = :id AND user_id = :user_id AND is_primary = false",
            ['id' => $walletId, 'user_id' => $userId]
        );
        
        if ($deleted === 0) {
            return ['success' => false, 'error' => 'Cannot delete primary wallet or wallet not found'];
        }
        
        return ['success' => true, 'message' => 'Wallet deleted successfully'];
    }
    
    private function getTrustScore(int $userId): array {
        $user = $this->db->fetchOne(
            "SELECT trust_score, total_swaps, success_rate FROM users WHERE id = :id",
            ['id' => $userId]
        );
        
        // Get transaction history for trust calculation
        $transactions = $this->db->fetchAll(
            "SELECT COUNT(*) as total, 
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
                    SUM(CASE WHEN created_at > NOW() - INTERVAL '30 days' THEN 1 ELSE 0 END) as recent
             FROM transactions WHERE user_id = :user_id",
            ['user_id' => $userId]
        );
        
        return [
            'success' => true,
            'data' => [
                'trust_score' => $user['trust_score'],
                'total_swaps' => $user['total_swaps'],
                'success_rate' => $user['success_rate'],
                'transaction_count' => $transactions[0]['total'] ?? 0,
                'successful_transactions' => $transactions[0]['successful'] ?? 0,
                'recent_transactions' => $transactions[0]['recent'] ?? 0,
            ]
        ];
    }
    
    private function getStats(int $userId): array {
        // Get various stats
        $stats = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN type = 'swap' THEN 1 ELSE 0 END) as total_swaps,
                SUM(CASE WHEN type = 'swap' AND status = 'success' THEN amount ELSE 0 END) as swap_volume,
                SUM(CASE WHEN created_at > NOW() - INTERVAL '30 days' THEN 1 ELSE 0 END) as this_month
             FROM transactions WHERE user_id = :user_id",
            ['user_id' => $userId]
        );
        
        $memberSince = $this->db->fetchOne(
            "SELECT member_since FROM users WHERE id = :id",
            ['id' => $userId]
        );
        
        return [
            'success' => true,
            'data' => [
                'total_transactions' => (int)$stats['total_transactions'],
                'successful_transactions' => (int)$stats['successful'],
                'total_swaps' => (int)$stats['total_swaps'],
                'swap_volume' => (float)$stats['swap_volume'],
                'this_month' => (int)$stats['this_month'],
                'member_since' => $memberSince['member_since'],
            ]
        ];
    }
    
    private function getRecentContacts(int $userId): array {
        // Get recent transaction recipients
        $contacts = $this->db->fetchAll(
            "SELECT DISTINCT receiver_identifier as phone, 
                    MAX(created_at) as last_used,
                    COUNT(*) as frequency
             FROM transactions 
             WHERE user_id = :user_id AND receiver_identifier IS NOT NULL
             GROUP BY receiver_identifier
             ORDER BY last_used DESC
             LIMIT 10",
            ['user_id' => $userId]
        );
        
        // Format contacts
        $formattedContacts = array_map(function($contact) {
            return [
                'phone' => $contact['phone'],
                'last_used' => $contact['last_used'],
                'frequency' => (int)$contact['frequency'],
                'name' => $this->guessContactName($contact['phone'])
            ];
        }, $contacts);
        
        return ['success' => true, 'data' => $formattedContacts];
    }
    
    private function guessContactName(string $phone): string {
        // In a real app, this would lookup from user's contacts
        // For now, return generic names
        $names = ['Maman', 'Papa', 'Jean', 'Marie', 'Junior', 'Alice', 'Paul', 'Sophie'];
        $hash = array_sum(str_split(preg_replace('/[^0-9]/', '', $phone)));
        return $names[$hash % count($names)];
    }
}
