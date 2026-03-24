<?php
/**
 * Helper Functions
 * Utility functions used throughout the application
 */

/**
 * Send JSON response
 */
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Send success response
 */
function successResponse(array $data = [], string $message = ''): void {
    $response = ['success' => true];
    if ($message) $response['message'] = $message;
    if (!empty($data)) $response['data'] = $data;
    jsonResponse($response);
}

/**
 * Send error response
 */
function errorResponse(string $error, int $statusCode = 400, array $details = []): void {
    $response = ['success' => false, 'error' => $error];
    if (!empty($details)) $response['details'] = $details;
    jsonResponse($response, $statusCode);
}

/**
 * Validate required fields
 */
function validateRequired(array $data, array $required): array {
    $missing = [];
    foreach ($required as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $missing[] = $field;
        }
    }
    return $missing;
}

/**
 * Sanitize input string
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate UUID
 */
function generateUUID(): string {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * Format currency (XAF/FCFA)
 */
function formatCurrency(float $amount, string $currency = 'XAF'): string {
    $formatted = number_format($amount, 0, ',', ' ');
    return $formatted . ' ' . $currency;
}

/**
 * Format phone number for display
 */
function formatPhone(string $phone): string {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) === 9) {
        return substr($phone, 0, 2) . ' ' . substr($phone, 2, 2) . ' ' . substr($phone, 4, 2) . ' ' . substr($phone, 6, 3);
    }
    return $phone;
}

/**
 * Generate transaction reference
 */
function generateReference(string $prefix = 'LS'): string {
    return $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Calculate swap fee
 */
function calculateFee(float $amount, bool $includeCashout = false): array {
    $feePercentage = $includeCashout ? CASHOUT_FEE_PERCENTAGE : DEFAULT_FEE_PERCENTAGE;
    $fee = round($amount * ($feePercentage / 100), 2);
    $receiverGets = $amount - $fee;
    
    return [
        'amount' => $amount,
        'fee' => $fee,
        'fee_percentage' => $feePercentage,
        'receiver_gets' => $receiverGets,
    ];
}

/**
 * Log security event
 */
function logSecurity(string $event, array $data = []): void {
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'data' => $data
    ];
    error_log('[SECURITY] ' . json_encode($log));
}

/**
 * Get greeting based on time
 */
function getGreeting(string $name = ''): string {
    $hour = (int)date('G');
    $greeting = '';
    
    if ($hour >= 5 && $hour < 12) {
        $greeting = 'Bonjour';
    } elseif ($hour >= 12 && $hour < 18) {
        $greeting = 'Bon après-midi';
    } else {
        $greeting = 'Bonsoir';
    }
    
    return $name ? "{$greeting}, {$name}!" : $greeting;
}

/**
 * Format relative time
 */
function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return date('M j, Y', $time);
}

/**
 * Validate Cameroon phone number
 */
function isValidCameroonPhone(string $phone): bool {
    $pattern = '/^(\+237\s?)?[6|2][0-9]{8}$/';
    return preg_match($pattern, $phone);
}

/**
 * Normalize phone to +237 format
 */
function normalizePhone(string $phone): string {
    $phone = preg_replace('/\s+/', '', $phone);
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    if (strpos($phone, '+237') === 0) {
        return $phone;
    }
    
    if (strpos($phone, '237') === 0) {
        return '+' . $phone;
    }
    
    return '+237' . $phone;
}

/**
 * Mask sensitive data
 */
function maskString(string $str, int $visibleStart = 2, int $visibleEnd = 2): string {
    $len = strlen($str);
    if ($len <= $visibleStart + $visibleEnd) {
        return str_repeat('*', $len);
    }
    return substr($str, 0, $visibleStart) . str_repeat('*', $len - $visibleStart - $visibleEnd) . substr($str, -$visibleEnd);
}

/**
 * Get client IP address
 */
function getClientIp(): string {
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
 * Check if request is AJAX
 */
function isAjax(): bool {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Require user to be logged in, redirect to login if not
 */
function requireLogin(): void {
    global $auth;
    if (!$auth->isLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * Set flash message
 */
function setFlashMessage(string $message, string $type = 'info'): void {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get flash message
 */
function getFlashMessage(): ?array {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Redirect to URL with optional flash message
 */
function redirect(string $url, string $message = '', string $type = 'info'): void {
    if ($message) {
        setFlashMessage($message, $type);
    }
    header("Location: {$url}", true, 302);
    exit;
}

/**
 * Rate limit check helper
 */
function checkRateLimit(string $identifier, int $maxAttempts = 5, int $windowSeconds = 900): bool {
    $key = 'rate_limit_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 1, 'first_attempt' => time()];
        return true;
    }
    
    $data = &$_SESSION[$key];
    
    // Reset if window expired
    if (time() - $data['first_attempt'] > $windowSeconds) {
        $data['attempts'] = 1;
        $data['first_attempt'] = time();
        return true;
    }
    
    // Check limit
    if ($data['attempts'] >= $maxAttempts) {
        return false;
    }
    
    $data['attempts']++;
    return true;
}

/**
 * Paginate results
 */
function paginate(array $items, int $page, int $perPage): array {
    $total = count($items);
    $totalPages = (int)ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    
    return [
        'items' => array_slice($items, $offset, $perPage),
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1,
        ]
    ];
}
