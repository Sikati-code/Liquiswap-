<?php
/**
 * Swap API
 * Handles OM ↔ MOMO swap operations
 */

class SwapAPI {
    private Database $db;
    private Auth $auth;
    private Session $session;
    
    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth();
        $this->session = new Session();
    }
    
    public function handle(string $action, string $method, array $data, array $queryParams): array {
        // Require authentication for all swap endpoints
        $user = $this->auth->getCurrentUser();
        if (!$user) {
            http_response_code(401);
            return ['success' => false, 'error' => 'Authentication required'];
        }
        
        switch ($action) {
            case 'rate':
                return $this->getCurrentRate();
            case 'calculate':
                return $this->calculateFees($data);
            case 'create':
                return $this->createSwap($user['id'], $data);
            case 'confirm':
                return $this->confirmSwap($user['id'], $data);
            case 'history':
                return $this->getSwapHistory($user['id'], $queryParams);
            case 'status':
                return isset($data['reference']) ? 
                    $this->getSwapStatus($user['id'], $data['reference']) : 
                    ['success' => false, 'error' => 'Reference required'];
            default:
                return ['success' => false, 'error' => 'Invalid swap action'];
        }
    }
    
    /**
     * Get current exchange rate
     */
    private function getCurrentRate(): array {
        // In production, this would fetch from external API
        // For demo, return fixed rate
        return [
            'success' => true,
            'data' => [
                'rate' => 1.00,
                'from' => 'OM',
                'to' => 'MOMO',
                'updated_at' => date('Y-m-d H:i:s'),
                'provider' => 'LiquiSwap',
                'expires_in' => 300 // 5 minutes
            ]
        ];
    }
    
    /**
     * Calculate fees and receive amount
     */
    private function calculateFees(array $data): array {
        if (empty($data['amount']) || !is_numeric($data['amount'])) {
            return ['success' => false, 'error' => 'Valid amount required'];
        }
        
        $amount = (float)$data['amount'];
        $includeCashout = $data['cashout'] ?? false;
        
        if ($amount < MIN_SWAP_AMOUNT || $amount > MAX_SWAP_AMOUNT) {
            return [
                'success' => false, 
                'error' => "Amount must be between " . formatCurrency(MIN_SWAP_AMOUNT) . " and " . formatCurrency(MAX_SWAP_AMOUNT)
            ];
        }
        
        $calculation = calculateFee($amount, $includeCashout);
        
        return [
            'success' => true,
            'data' => [
                'amount' => $amount,
                'fee' => $calculation['fee'],
                'fee_percentage' => $calculation['fee_percentage'],
                'receiver_gets' => $calculation['receiver_gets'],
                'exchange_rate' => 1.00,
                'you_pay' => $amount,
                'estimated_time' => '< 2 minutes',
                'cashout_included' => $includeCashout
            ]
        ];
    }
    
    /**
     * Create a new swap transaction
     */
    private function createSwap(int $userId, array $data): array {
        $required = ['amount', 'source_provider', 'recipient_number'];
        $missing = validateRequired($data, $required);
        
        if (!empty($missing)) {
            return ['success' => false, 'error' => 'Missing required fields', 'fields' => $missing];
        }
        
        $amount = (float)$data['amount'];
        
        // Validate amount
        if ($amount < MIN_SWAP_AMOUNT || $amount > MAX_SWAP_AMOUNT) {
            return ['success' => false, 'error' => 'Amount out of range'];
        }
        
        // Calculate fees
        $includeCashout = $data['cashout'] ?? false;
        $calculation = calculateFee($amount, $includeCashout);
        
        // Generate reference
        $reference = generateReference('SWAP');
        
        // Determine operators
        $sourceProvider = strtoupper($data['source_provider']);
        $targetProvider = $sourceProvider === 'ORANGE' ? 'MTN' : 'ORANGE';
        
        // Create transaction
        try {
            $transactionId = $this->db->insert('transactions', [
                'user_id' => $userId,
                'type' => 'swap',
                'subtype' => strtolower("{$sourceProvider}_to_{$targetProvider}"),
                'amount' => $amount,
                'fee' => $calculation['fee'],
                'receiver_identifier' => normalizePhone($data['recipient_number']),
                'operator' => $targetProvider,
                'status' => 'pending',
                'reference' => $reference,
                'metadata' => json_encode([
                    'source_provider' => $sourceProvider,
                    'target_provider' => $targetProvider,
                    'include_cashout' => $includeCashout,
                    'exchange_rate' => 1.00
                ])
            ]);
            
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'reference' => $reference,
                'status' => 'pending',
                'data' => [
                    'amount' => $amount,
                    'fee' => $calculation['fee'],
                    'receiver_gets' => $calculation['receiver_gets'],
                    'recipient' => maskString($data['recipient_number']),
                ],
                'message' => 'Swap initiated. Please confirm to proceed.'
            ];
            
        } catch (Exception $e) {
            error_log("Swap creation failed: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create swap'];
        }
    }
    
    /**
     * Confirm and execute swap
     */
    private function confirmSwap(int $userId, array $data): array {
        if (empty($data['reference'])) {
            return ['success' => false, 'error' => 'Reference required'];
        }
        
        $reference = $data['reference'];
        
        // Get transaction
        $transaction = $this->db->fetchOne(
            "SELECT * FROM transactions WHERE reference = :reference AND user_id = :user_id",
            ['reference' => $reference, 'user_id' => $userId]
        );
        
        if (!$transaction) {
            return ['success' => false, 'error' => 'Transaction not found'];
        }
        
        if ($transaction['status'] !== 'pending') {
            return ['success' => false, 'error' => 'Transaction already ' . $transaction['status']];
        }
        
        // Check user balance
        $metadata = json_decode($transaction['metadata'], true);
        $sourceProvider = $metadata['source_provider'] ?? 'MTN';
        
        $wallet = $this->db->fetchOne(
            "SELECT id, balance FROM wallets 
             WHERE user_id = :user_id AND provider = :provider",
            ['user_id' => $userId, 'provider' => $sourceProvider]
        );
        
        if (!$wallet) {
            return ['success' => false, 'error' => 'Source wallet not found'];
        }
        
        $totalRequired = $transaction['amount'] + $transaction['fee'];
        
        if ($wallet['balance'] < $totalRequired) {
            return ['success' => false, 'error' => 'Insufficient funds'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Deduct from source wallet
            $this->db->update(
                'wallets',
                ['balance' => $wallet['balance'] - $totalRequired],
                "id = :id",
                ['id' => $wallet['id']]
            );
            
            // Update transaction status
            $this->db->update(
                'transactions',
                ['status' => 'processing', 'updated_at' => date('Y-m-d H:i:s')],
                "id = :id",
                ['id' => $transaction['id']]
            );
            
            // In production, this would call external payment API
            // For demo, simulate processing
            if (MOCK_PAYMENTS) {
                // Simulate success
                sleep(1); // Simulate API call
                
                $this->db->update(
                    'transactions',
                    ['status' => 'success', 'updated_at' => date('Y-m-d H:i:s')],
                    "id = :id",
                    ['id' => $transaction['id']]
                );
                
                // Update user stats
                $this->updateUserStats($userId);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'reference' => $reference,
                'status' => 'success',
                'message' => 'Swap completed successfully!',
                'data' => [
                    'amount_sent' => $transaction['amount'],
                    'fee' => $transaction['fee'],
                    'receiver_gets' => $transaction['amount'] - $transaction['fee'],
                ]
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Swap confirmation failed: " . $e->getMessage());
            return ['success' => false, 'error' => 'Swap failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get swap history
     */
    private function getSwapHistory(int $userId, array $params): array {
        $page = (int)($params['page'] ?? 1);
        $perPage = min((int)($params['per_page'] ?? 20), 100);
        $offset = ($page - 1) * $perPage;
        
        $status = $params['status'] ?? null;
        $days = (int)($params['days'] ?? 30);
        
        $whereClause = "user_id = :user_id AND type = 'swap' AND created_at > NOW() - INTERVAL '{$days} days'";
        $queryParams = ['user_id' => $userId];
        
        if ($status) {
            $whereClause .= " AND status = :status";
            $queryParams['status'] = $status;
        }
        
        $swaps = $this->db->fetchAll(
            "SELECT transaction_uuid, type, subtype, amount, fee, receiver_identifier, 
                    operator, status, reference, metadata, created_at
             FROM transactions 
             WHERE {$whereClause}
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($queryParams, ['limit' => $perPage, 'offset' => $offset])
        );
        
        // Decode metadata
        $swaps = array_map(function($swap) {
            $swap['metadata'] = json_decode($swap['metadata'] ?? '{}', true);
            return $swap;
        }, $swaps);
        
        // Get total count
        $countResult = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM transactions WHERE {$whereClause}",
            $queryParams
        );
        
        return [
            'success' => true,
            'data' => [
                'swaps' => $swaps,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => (int)$countResult['total'],
                    'total_pages' => (int)ceil($countResult['total'] / $perPage)
                ]
            ]
        ];
    }
    
    /**
     * Get swap status
     */
    private function getSwapStatus(int $userId, string $reference): array {
        $transaction = $this->db->fetchOne(
            "SELECT transaction_uuid, status, reference, amount, fee, created_at, updated_at
             FROM transactions 
             WHERE reference = :reference AND user_id = :user_id",
            ['reference' => $reference, 'user_id' => $userId]
        );
        
        if (!$transaction) {
            return ['success' => false, 'error' => 'Transaction not found'];
        }
        
        return [
            'success' => true,
            'data' => $transaction
        ];
    }
    
    /**
     * Update user statistics after successful swap
     */
    private function updateUserStats(int $userId): void {
        $stats = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful
             FROM transactions 
             WHERE user_id = :user_id AND type = 'swap'",
            ['user_id' => $userId]
        );
        
        $total = (int)$stats['total'];
        $successful = (int)$stats['successful'];
        $successRate = $total > 0 ? round(($successful / $total) * 100, 2) : 0;
        
        // Calculate trust score (base 50 + success rate bonus - max 100)
        $trustScore = min(50 + ($successRate / 2), 100);
        
        $this->db->update('users', [
            'total_swaps' => $total,
            'success_rate' => $successRate,
            'trust_score' => $trustScore,
            'updated_at' => date('Y-m-d H:i:s')
        ], "id = :id", ['id' => $userId]);
    }
}
