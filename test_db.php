<?php
require_once 'includes/config.php';

try {
    echo "Testing database connection...\n";
    $result = $db->query("SELECT 1 as test");
    $row = $result->fetch();
    echo "Database connection: SUCCESS\n";
    echo "Test query result: " . $row['test'] . "\n";
    
    // Check if users table exists
    $result = $db->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() > 0) {
        echo "Users table: EXISTS\n";
    } else {
        echo "Users table: NOT FOUND - Database needs to be set up\n";
    }
    
} catch (Exception $e) {
    echo "Database connection: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
}
?>
