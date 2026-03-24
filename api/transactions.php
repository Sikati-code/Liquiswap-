<?php
/**
 * Transactions API
 * Handles transaction history and details
 */

class TransactionsAPI {
    private Database $db;
    private Auth $auth;
    
    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth();
    }
    
    public function handle(string $action, ?string $id, string $method, array $data, array $queryParams): array {
        // Require authentication for most endpoints
        $user = $this->auth->getCurrentUser();
        
        switch ($action) {
            case '':
            case 'list':
                return $user ? $this->getTransactions($user['id'], $queryParams) : 
                       ['success' => false, 'error' => 'Authentication required'];
            case 'filter':
                return $user ? $this->filterTransactions($user['id'], $data) : 
                       ['success' => false, 'error' => 'Authentication required'];
            default:
                // Check if action is a UUID
                if ($action) {
                    return $user ? $this->getTransactionDetails($user['id'], $action) : 
                           ['success' => false, 'error' => 'Authentication required'];
                }
                return ['success' => false, 'error' => 'Invalid transactions action'];
        }
    }
    
    /**
     * Get all transactions for user
     */
    private function getTransactions(int $userId, array $params): array {
        $page = (int)($params['page'] ?? 1);
        $perPage = min((int)($params['per_page'] ?? 20), 100);
        $offset = ($page - 1) * $perPage;
        
        $type = $params['type'] ?? null;
        $status = $params['status'] ?? null;
        $days = (int)($params['days'] ?? 90);
        
        $whereClause = "user_id = :user_id AND created_at > NOW() - INTERVAL '{$days} days'";
        $queryParams = ['user_id' => $userId];
        
        if ($type) {
            $whereClause .= " AND type = :type";
            $queryParams['type'] = $type;
        }
        
        if ($status) {
            $whereClause .= " AND status = :status";
            $queryParams['status'] = $status;
        }
        
        $transactions = $this->db->fetchAll(
            "SELECT t.id, t.transaction_uuid, t.type, t.subtype, t.amount, t.fee,
                    t.receiver_identifier, t.operator, t.status, t.reference, 
                    t.metadata, t.created_at, b.name as bundle_name
             FROM transactions t
             LEFT JOIN bundles b ON t.bundle_id = b.id
             WHERE {$whereClause}
             ORDER BY t.created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($queryParams, ['limit' => $perPage, 'offset' => $offset])
        );
        
        // Process transactions
        $processed = array_map(function($t) {
            $t['metadata'] = json_decode($t['metadata'] ?? '{}', true);
            $t['time_ago'] = timeAgo($t['created_at']);
            $t['formatted_amount'] = formatCurrency($t['amount']);
            $t['is_positive'] = in_array($t['type'], ['deposit']) || 
                                ($t['type'] === 'swap' && strpos($t['subtype'], 'to') !== false);
            return $t;
        }, $transactions);
        
        // Group by date
        $grouped = $this->groupByDate($processed);
        
        // Get total count
        $countResult = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM transactions WHERE {$whereClause}",
            $queryParams
        );
        
        // Get summary stats
        $stats = $this->getTransactionStats($userId, $days);
        
        return [
            'success' => true,
            'data' => [
                'transactions' => $processed,
                'grouped' => $grouped,
                'stats' => $stats,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => (int)$countResult['total'],
                    'total_pages' => (int)ceil($countResult['total'] / $perPage),
                    'has_next' => $page * $perPage < $countResult['total'],
                    'has_prev' => $page > 1,
                ]
            ]
        ];
    }
    
    /**
     * Get transaction details
     */
    private function getTransactionDetails(int $userId, string $uuid): array {
        $transaction = $this->db->fetchOne(
            "SELECT t.*, b.name as bundle_name, b.data_amount, b.validity
             FROM transactions t
             LEFT JOIN bundles b ON t.bundle_id = b.id
             WHERE (t.transaction_uuid = :uuid OR t.reference = :uuid) AND t.user_id = :user_id",
            ['uuid' => $uuid, 'user_id' => $userId]
        );
        
        if (!$transaction) {
            return ['success' => false, 'error' => 'Transaction not found'];
        }
        
        $transaction['metadata'] = json_decode($transaction['metadata'] ?? '{}', true);
        $transaction['formatted_amount'] = formatCurrency($transaction['amount']);
        $transaction['formatted_fee'] = formatCurrency($transaction['fee']);
        
        return [
            'success' => true,
            'data' => $transaction
        ];
    }
    
    /**
     * Filter transactions
     */
    private function filterTransactions(int $userId, array $filters): array {
        $whereClause = "user_id = :user_id";
        $params = ['user_id' => $userId];
        
        // Date range
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        
        // Type filter
        if (!empty($filters['types']) && is_array($filters['types'])) {
            $placeholders = [];
            foreach ($filters['types'] as $i => $type) {
                $placeholder = ":type_{$i}";
                $placeholders[] = $placeholder;
                $params[$placeholder] = $type;
            }
            $whereClause .= " AND type IN (" . implode(', ', $placeholders) . ")";
        }
        
        // Operator filter
        if (!empty($filters['operator'])) {
            $whereClause .= " AND operator = :operator";
            $params['operator'] = $filters['operator'];
        }
        
        // Amount range
        if (!empty($filters['min_amount'])) {
            $whereClause .= " AND amount >= :min_amount";
            $params['min_amount'] = $filters['min_amount'];
        }
        if (!empty($filters['max_amount'])) {
            $whereClause .= " AND amount <= :max_amount";
            $params['max_amount'] = $filters['max_amount'];
        }
        
        // Search term
        if (!empty($filters['search'])) {
            $whereClause .= " AND (reference ILIKE :search OR receiver_identifier ILIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $page = (int)($filters['page'] ?? 1);
        $perPage = min((int)($filters['per_page'] ?? 20), 100);
        $offset = ($page - 1) * $perPage;
        
        $transactions = $this->db->fetchAll(
            "SELECT id, transaction_uuid, type, subtype, amount, fee, receiver_identifier,
                    operator, status, reference, metadata, created_at
             FROM transactions 
             WHERE {$whereClause}
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset])
        );
        
        $processed = array_map(function($t) {
            $t['metadata'] = json_decode($t['metadata'] ?? '{}', true);
            $t['time_ago'] = timeAgo($t['created_at']);
            return $t;
        }, $transactions);
        
        $countResult = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM transactions WHERE {$whereClause}",
            $params
        );
        
        return [
            'success' => true,
            'data' => [
                'transactions' => $processed,
                'filters_applied' => $filters,
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
     * Group transactions by date
     */
    private function groupByDate(array $transactions): array {
        $groups = [
            'today' => [],
            'yesterday' => [],
            'this_week' => [],
            'this_month' => [],
            'older' => []
        ];
        
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $monthStart = date('Y-m-01');
        
        foreach ($transactions as $t) {
            $date = substr($t['created_at'], 0, 10);
            
            if ($date === $today) {
                $groups['today'][] = $t;
            } elseif ($date === $yesterday) {
                $groups['yesterday'][] = $t;
            } elseif ($date >= $weekStart) {
                $groups['this_week'][] = $t;
            } elseif ($date >= $monthStart) {
                $groups['this_month'][] = $t;
            } else {
                $groups['older'][] = $t;
            }
        }
        
        // Remove empty groups
        return array_filter($groups, function($group) {
            return !empty($group);
        });
    }
    
    /**
     * Get transaction statistics
     */
    private function getTransactionStats(int $userId, int $days): array {
        $stats = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN type = 'swap' THEN 1 ELSE 0 END) as swaps,
                SUM(CASE WHEN type = 'bundle' THEN 1 ELSE 0 END) as bundles,
                SUM(CASE WHEN type = 'airtime' THEN 1 ELSE 0 END) as airtime,
                SUM(amount) as total_volume
             FROM transactions 
             WHERE user_id = :user_id AND created_at > NOW() - INTERVAL '{$days} days'",
            ['user_id' => $userId]
        );
        
        return [
            'total' => (int)$stats['total'],
            'successful' => (int)$stats['successful'],
            'failed' => (int)$stats['failed'],
            'by_type' => [
                'swaps' => (int)$stats['swaps'],
                'bundles' => (int)$stats['bundles'],
                'airtime' => (int)$stats['airtime'],
            ],
            'total_volume' => (float)$stats['total_volume'],
            'period_days' => $days
        ];
    }
}
