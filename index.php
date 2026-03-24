<?php
require_once 'includes/config.php';

// Redirect based on authentication status
if ($auth->isLoggedIn()) {
    redirect('dashboard.php');
} else {
    redirect('splash.php');
}
?>
