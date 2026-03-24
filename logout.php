<?php
require_once 'includes/config.php';

$auth->logout();
redirect('login.php', 'You have been logged out successfully.', 'success');
?>
