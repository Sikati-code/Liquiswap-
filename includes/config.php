<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'liquiswap_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'LiquiSwap');
define('SITE_URL', 'http://localhost/liquiswap/');

// Fee configuration
define('DEFAULT_FEE_PERCENTAGE', 2.5);
define('CASHOUT_FEE_PERCENTAGE', 3.0);

// Timezone
date_default_timezone_set('Africa/Douala');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/functions.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize authentication
$auth = new Auth($db);
