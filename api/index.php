<?php
/**
 * API Router
 * Handles all API requests and routes to appropriate handlers
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/JWT.php';
require_once __DIR__ . '/../includes/CSRF.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/Auth.php';

// Set CORS headers for API
header("Access-Control-Allow-Origin: " . APP_URL);
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=utf-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Parse request
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api/', '', $requestUri);
$segments = explode('/', trim($path, '/'));
$endpoint = $segments[0] ?? '';
$action = $segments[1] ?? '';
$id = $segments[2] ?? null;

// Get request body
$input = file_get_contents('php://input');
$data = json_decode($input, true) ?? $_POST ?? [];

// Parse query parameters
$queryParams = $_GET ?? [];

// Initialize response
$response = ['success' => false, 'error' => 'Invalid endpoint'];

// Route to appropriate handler
try {
    switch ($endpoint) {
        case 'auth':
            require_once __DIR__ . '/auth.php';
            $handler = new AuthAPI();
            $response = $handler->handle($action, $requestMethod, $data, $queryParams);
            break;
            
        case 'user':
            require_once __DIR__ . '/user.php';
            $handler = new UserAPI();
            $response = $handler->handle($action, $id, $requestMethod, $data, $queryParams);
            break;
            
        case 'swap':
            require_once __DIR__ . '/swap.php';
            $handler = new SwapAPI();
            $response = $handler->handle($action, $requestMethod, $data, $queryParams);
            break;
            
        case 'bundles':
            require_once __DIR__ . '/bundles.php';
            $handler = new BundlesAPI();
            $response = $handler->handle($action, $id, $requestMethod, $data, $queryParams);
            break;
            
        case 'ussd':
            require_once __DIR__ . '/ussd.php';
            $handler = new USSDAPI();
            $response = $handler->handle($action, $requestMethod, $data, $queryParams);
            break;
            
        case 'transactions':
            require_once __DIR__ . '/transactions.php';
            $handler = new TransactionsAPI();
            $response = $handler->handle($action, $id, $requestMethod, $data, $queryParams);
            break;
            
        case 'settings':
            require_once __DIR__ . '/settings.php';
            $handler = new SettingsAPI();
            $response = $handler->handle($action, $requestMethod, $data, $queryParams);
            break;
            
        default:
            $response = ['success' => false, 'error' => 'Endpoint not found'];
            http_response_code(404);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    $response = ['success' => false, 'error' => 'Internal server error'];
    http_response_code(500);
}

// Send response
jsonResponse($response, http_response_code());
