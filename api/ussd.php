<?php
/**
 * USSD Codes API
 * Handles USSD library operations
 */

class USSDAPI {
    private Database $db;
    private Auth $auth;
    
    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth();
    }
    
    public function handle(string $action, string $method, array $data, array $queryParams): array {
        switch ($action) {
            case '':
            case 'list':
                return $this->getUSSDCodes($queryParams);
            case 'search':
                return $this->searchUSSD($queryParams);
            case 'categories':
                return $this->getCategories();
            case 'operators':
                return $this->getOperators();
            default:
                return ['success' => false, 'error' => 'Invalid USSD action'];
        }
    }
    
    /**
     * Get USSD codes with filtering
     */
    private function getUSSDCodes(array $params): array {
        $operator = $params['operator'] ?? null;
        $category = $params['category'] ?? null;
        
        $whereClause = "is_active = true";
        $queryParams = [];
        
        if ($operator && $operator !== 'ALL') {
            $whereClause .= " AND (operator = :operator OR operator = 'ALL')";
            $queryParams['operator'] = strtoupper($operator);
        }
        
        if ($category) {
            $whereClause .= " AND category = :category";
            $queryParams['category'] = strtolower($category);
        }
        
        $codes = $this->db->fetchAll(
            "SELECT id, operator, category, name, code, description
             FROM ussd_codes 
             WHERE {$whereClause}
             ORDER BY 
                CASE category 
                    WHEN 'balance' THEN 1 
                    WHEN 'data' THEN 2 
                    WHEN 'airtime' THEN 3 
                    WHEN 'services' THEN 4 
                    WHEN 'banking' THEN 5 
                    WHEN 'support' THEN 6 
                    ELSE 7 
                END,
                operator, name",
            $queryParams
        );
        
        // Group by category
        $grouped = [];
        foreach ($codes as $code) {
            $grouped[$code['category']][] = $code;
        }
        
        return [
            'success' => true,
            'data' => [
                'codes' => $codes,
                'grouped' => $grouped,
                'count' => count($codes)
            ]
        ];
    }
    
    /**
     * Search USSD codes
     */
    private function searchUSSD(array $params): array {
        $query = $params['q'] ?? '';
        
        if (empty($query)) {
            return ['success' => false, 'error' => 'Search query required'];
        }
        
        $codes = $this->db->fetchAll(
            "SELECT id, operator, category, name, code, description
             FROM ussd_codes 
             WHERE is_active = true 
             AND (name ILIKE :query OR code ILIKE :query OR description ILIKE :query)
             ORDER BY name
             LIMIT 20",
            ['query' => '%' . $query . '%']
        );
        
        return [
            'success' => true,
            'data' => [
                'codes' => $codes,
                'query' => $query,
                'count' => count($codes)
            ]
        ];
    }
    
    /**
     * Get USSD categories
     */
    private function getCategories(): array {
        $categories = [
            ['id' => 'all', 'name' => 'All Services', 'icon' => 'apps', 'color' => 'primary'],
            ['id' => 'balance', 'name' => 'Balance', 'icon' => 'account_balance', 'color' => 'primary'],
            ['id' => 'data', 'name' => 'Data Bundles', 'icon' => 'data_usage', 'color' => 'orange'],
            ['id' => 'airtime', 'name' => 'Airtime', 'icon' => 'phone_android', 'color' => 'gold'],
            ['id' => 'services', 'name' => 'Services', 'icon' => 'miscellaneous_services', 'color' => 'teal'],
            ['id' => 'banking', 'name' => 'Banking', 'icon' => 'account_balance_wallet', 'color' => 'blue'],
            ['id' => 'support', 'name' => 'Support', 'icon' => 'help_center', 'color' => 'slate'],
        ];
        
        return [
            'success' => true,
            'data' => $categories
        ];
    }
    
    /**
     * Get operators
     */
    private function getOperators(): array {
        $operators = [
            ['id' => 'ALL', 'name' => 'All Operators', 'icon' => 'public'],
            ['id' => 'MTN', 'name' => 'MTN', 'color' => '#FFD700'],
            ['id' => 'ORANGE', 'name' => 'Orange', 'color' => '#FF6600'],
            ['id' => 'CAMTEL', 'name' => 'Camtel', 'color' => '#0066CC'],
            ['id' => 'NEXTTEL', 'name' => 'Nexttel', 'color' => '#00CC66'],
        ];
        
        return [
            'success' => true,
            'data' => $operators
        ];
    }
}
