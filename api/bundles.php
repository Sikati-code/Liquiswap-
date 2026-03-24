<?php
/**
 * Bundles API
 * Handles bundle marketplace operations
 */

class BundlesAPI {
    private Database $db;
    private Auth $auth;
    
    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth();
    }
    
    public function handle(string $action, ?string $id, string $method, array $data, array $queryParams): array {
        switch ($action) {
            case '':
            case 'list':
                return $this->getBundles($queryParams);
            case 'search':
                return $this->searchBundles($queryParams);
            case 'purchase':
                return $this->purchaseBundle($data);
            case 'airtime-convert':
                return $this->convertAirtime($data);
            case 'categories':
                return $this->getCategories();
            default:
                // Check if action is a bundle ID
                if (is_numeric($action)) {
                    return $this->getBundleById((int)$action);
                }
                return ['success' => false, 'error' => 'Invalid bundles action'];
        }
    }
    
    /**
     * Get all bundles with filtering
     */
    private function getBundles(array $params): array {
        $operator = $params['operator'] ?? null;
        $category = $params['category'] ?? null;
        $isHot = isset($params['hot']) ? true : null;
        $isGoodDeal = isset($params['deal']) ? true : null;
        $page = (int)($params['page'] ?? 1);
        $perPage = min((int)($params['per_page'] ?? 20), 100);
        
        $whereClause = "is_active = true";
        $queryParams = [];
        
        if ($operator) {
            $whereClause .= " AND operator = :operator";
            $queryParams['operator'] = strtoupper($operator);
        }
        
        if ($isHot) {
            $whereClause .= " AND is_hot = true";
        }
        
        if ($isGoodDeal) {
            $whereClause .= " AND is_good_deal = true";
        }
        
        $offset = ($page - 1) * $perPage;
        
        $bundles = $this->db->fetchAll(
            "SELECT id, operator, name, description, data_amount, voice_minutes, 
                    sms_count, validity, price, original_price, is_hot, is_good_deal
             FROM bundles 
             WHERE {$whereClause}
             ORDER BY is_hot DESC, is_good_deal DESC, price ASC
             LIMIT :limit OFFSET :offset",
            array_merge($queryParams, ['limit' => $perPage, 'offset' => $offset])
        );
        
        // Get total count
        $countResult = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM bundles WHERE {$whereClause}",
            $queryParams
        );
        
        // Group by operator for easier frontend consumption
        $grouped = [];
        foreach ($bundles as $bundle) {
            $grouped[$bundle['operator']][] = $bundle;
        }
        
        return [
            'success' => true,
            'data' => [
                'bundles' => $bundles,
                'grouped' => $grouped,
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
     * Search bundles
     */
    private function searchBundles(array $params): array {
        $query = $params['q'] ?? '';
        
        if (empty($query)) {
            return ['success' => false, 'error' => 'Search query required'];
        }
        
        $bundles = $this->db->fetchAll(
            "SELECT id, operator, name, description, data_amount, validity, price, is_hot, is_good_deal
             FROM bundles 
             WHERE is_active = true 
             AND (name ILIKE :query OR description ILIKE :query OR data_amount ILIKE :query)
             ORDER BY price ASC
             LIMIT 20",
            ['query' => '%' . $query . '%']
        );
        
        return [
            'success' => true,
            'data' => [
                'bundles' => $bundles,
                'query' => $query,
                'count' => count($bundles)
            ]
        ];
    }
    
    /**
     * Get bundle by ID
     */
    private function getBundleById(int $id): array {
        $bundle = $this->db->fetchOne(
            "SELECT * FROM bundles WHERE id = :id AND is_active = true",
            ['id' => $id]
        );
        
        if (!$bundle) {
            return ['success' => false, 'error' => 'Bundle not found'];
        }
        
        return ['success' => true, 'data' => $bundle];
    }
    
    /**
     * Purchase a bundle
     */
    private function purchaseBundle(array $data): array {
        // Require authentication
        $user = $this->auth->getCurrentUser();
        if (!$user) {
            http_response_code(401);
            return ['success' => false, 'error' => 'Authentication required'];
        }
        
        $required = ['bundle_id', 'phone_number'];
        $missing = validateRequired($data, $required);
        
        if (!empty($missing)) {
            return ['success' => false, 'error' => 'Missing required fields', 'fields' => $missing];
        }
        
        $bundleId = (int)$data['bundle_id'];
        $phoneNumber = normalizePhone($data['phone_number']);
        $paymentMethod = $data['payment_method'] ?? 'MTN';
        
        // Get bundle details
        $bundle = $this->db->fetchOne(
            "SELECT * FROM bundles WHERE id = :id AND is_active = true",
            ['id' => $bundleId]
        );
        
        if (!$bundle) {
            return ['success' => false, 'error' => 'Bundle not found or inactive'];
        }
        
        // Check user balance
        $wallet = $this->db->fetchOne(
            "SELECT id, balance FROM wallets 
             WHERE user_id = :user_id AND provider = :provider",
            ['user_id' => $user['id'], 'provider' => $paymentMethod]
        );
        
        if (!$wallet) {
            return ['success' => false, 'error' => 'Payment wallet not found'];
        }
        
        if ($wallet['balance'] < $bundle['price']) {
            return ['success' => false, 'error' => 'Insufficient funds'];
        }
        
        // Generate reference
        $reference = generateReference('BUNDLE');
        
        try {
            $this->db->beginTransaction();
            
            // Deduct balance
            $this->db->update(
                'wallets',
                ['balance' => $wallet['balance'] - $bundle['price']],
                "id = :id",
                ['id' => $wallet['id']]
            );
            
            // Create transaction
            $transactionId = $this->db->insert('transactions', [
                'user_id' => $user['id'],
                'type' => 'bundle',
                'amount' => $bundle['price'],
                'fee' => 0,
                'receiver_identifier' => $phoneNumber,
                'operator' => $bundle['operator'],
                'bundle_id' => $bundleId,
                'status' => MOCK_PAYMENTS ? 'success' : 'pending',
                'reference' => $reference,
                'metadata' => json_encode([
                    'bundle_name' => $bundle['name'],
                    'data_amount' => $bundle['data_amount'],
                    'validity' => $bundle['validity']
                ])
            ]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'reference' => $reference,
                'status' => MOCK_PAYMENTS ? 'success' : 'pending',
                'message' => 'Bundle purchased successfully!',
                'data' => [
                    'bundle_name' => $bundle['name'],
                    'operator' => $bundle['operator'],
                    'price' => $bundle['price'],
                    'phone_number' => maskString($phoneNumber),
                ]
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Bundle purchase failed: " . $e->getMessage());
            return ['success' => false, 'error' => 'Purchase failed'];
        }
    }
    
    /**
     * Convert airtime to bundle
     */
    private function convertAirtime(array $data): array {
        // Require authentication
        $user = $this->auth->getCurrentUser();
        if (!$user) {
            http_response_code(401);
            return ['success' => false, 'error' => 'Authentication required'];
        }
        
        $required = ['bundle_id', 'phone_number', 'from_operator'];
        $missing = validateRequired($data, $required);
        
        if (!empty($missing)) {
            return ['success' => false, 'error' => 'Missing required fields', 'fields' => $missing];
        }
        
        $bundleId = (int)$data['bundle_id'];
        $phoneNumber = normalizePhone($data['phone_number']);
        $fromOperator = strtoupper($data['from_operator']);
        
        // Get bundle details
        $bundle = $this->db->fetchOne(
            "SELECT * FROM bundles WHERE id = :id AND is_active = true",
            ['id' => $bundleId]
        );
        
        if (!$bundle) {
            return ['success' => false, 'error' => 'Bundle not found'];
        }
        
        // Generate reference
        $reference = generateReference('CONV');
        
        try {
            $transactionId = $this->db->insert('transactions', [
                'user_id' => $user['id'],
                'type' => 'conversion',
                'subtype' => 'airtime_to_bundle',
                'amount' => $bundle['price'],
                'fee' => 0,
                'receiver_identifier' => $phoneNumber,
                'operator' => $fromOperator,
                'bundle_id' => $bundleId,
                'status' => MOCK_PAYMENTS ? 'success' : 'pending',
                'reference' => $reference,
                'metadata' => json_encode([
                    'from_operator' => $fromOperator,
                    'bundle_operator' => $bundle['operator'],
                    'bundle_name' => $bundle['name'],
                    'airtime_deducted' => $bundle['price'],
                    'bonus_mb' => 250 // LiquiSwap bonus
                ])
            ]);
            
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'reference' => $reference,
                'message' => 'Airtime conversion initiated',
                'data' => [
                    'from_airtime' => $bundle['price'],
                    'to_bundle' => $bundle['name'],
                    'bonus' => '250 MB Extra',
                    'total_deductible' => $bundle['price'],
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Airtime conversion failed: " . $e->getMessage());
            return ['success' => false, 'error' => 'Conversion failed'];
        }
    }
    
    /**
     * Get bundle categories/operators
     */
    private function getCategories(): array {
        $operators = $this->db->fetchAll(
            "SELECT DISTINCT operator, COUNT(*) as bundle_count 
             FROM bundles 
             WHERE is_active = true 
             GROUP BY operator 
             ORDER BY operator"
        );
        
        $categories = [
            ['id' => 'balance', 'name' => 'Balance Check', 'icon' => 'account_balance'],
            ['id' => 'data', 'name' => 'Data Bundles', 'icon' => 'data_usage'],
            ['id' => 'airtime', 'name' => 'Airtime', 'icon' => 'phone_android'],
            ['id' => 'services', 'name' => 'Services', 'icon' => 'miscellaneous_services'],
            ['id' => 'gaming', 'name' => 'Gaming', 'icon' => 'sports_esports'],
        ];
        
        return [
            'success' => true,
            'data' => [
                'operators' => $operators,
                'categories' => $categories
            ]
        ];
    }
}
