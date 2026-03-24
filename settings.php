<?php
require_once 'includes/config.php';
requireLogin();

$user = $auth->getUser();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'toggle_biometric') {
        $enabled = isset($_POST['biometric_enabled']) ? 1 : 0;
        $update_query = "UPDATE users SET biometric_enabled = :enabled WHERE id = :id";
        $stmt = $db->prepare($update_query);
        $stmt->bindParam(':enabled', $enabled);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            redirect('settings.php', 'Biometric settings updated!', 'success');
        } else {
            $error = 'Failed to update biometric settings';
        }
    } elseif ($_POST['action'] === 'toggle_2fa') {
        $enabled = isset($_POST['two_factor_enabled']) ? 1 : 0;
        $update_query = "UPDATE users SET two_factor_enabled = :enabled WHERE id = :id";
        $stmt = $db->prepare($update_query);
        $stmt->bindParam(':enabled', $enabled);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            redirect('settings.php', '2FA settings updated!', 'success');
        } else {
            $error = 'Failed to update 2FA settings';
        }
    } elseif ($_POST['action'] === 'delete_account') {
        $password = $_POST['password'];
        
        if (empty($password)) {
            $error = 'Password is required to delete account';
        } else {
            // Verify password
            $user_query = "SELECT password_hash FROM users WHERE id = :id";
            $stmt = $db->prepare($user_query);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();
            $user_data = $stmt->fetch();
            
            if ($user_data && password_verify($password, $user_data['password_hash'])) {
                try {
                    $db->beginTransaction();
                    
                    // Delete user's wallets
                    $delete_wallets = "DELETE FROM wallets WHERE user_id = :user_id";
                    $stmt = $db->prepare($delete_wallets);
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->execute();
                    
                    // Delete user's transactions
                    $delete_transactions = "DELETE FROM transactions WHERE user_id = :user_id";
                    $stmt = $db->prepare($delete_transactions);
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->execute();
                    
                    // Delete user's sessions
                    $delete_sessions = "DELETE FROM sessions WHERE user_id = :user_id";
                    $stmt = $db->prepare($delete_sessions);
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->execute();
                    
                    // Delete user
                    $delete_user = "DELETE FROM users WHERE id = :user_id";
                    $stmt = $db->prepare($delete_user);
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->execute();
                    
                    $db->commit();
                    
                    // Logout and redirect
                    $auth->logout();
                    redirect('login.php', 'Account deleted successfully', 'success');
                    
                } catch (Exception $e) {
                    $db->rollback();
                    error_log("Account deletion error: " . $e->getMessage());
                    $error = 'Failed to delete account';
                }
            } else {
                $error = 'Incorrect password';
            }
        }
    }
}

$flash = getFlashMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - LiquiSwap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0A0F0F 0%, #1a2a2a 100%);
            color: #ffffff;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            padding-bottom: 80px;
        }
        
        .settings-container {
            padding: 20px 0;
        }
        
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 10px 15px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #008080;
        }
        
        .section-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #008080;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-icon {
            width: 35px;
            height: 35px;
            background: rgba(0, 128, 128, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
        
        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .setting-item:last-child {
            border-bottom: none;
        }
        
        .setting-info {
            flex: 1;
        }
        
        .setting-title {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .setting-description {
            font-size: 14px;
            opacity: 0.7;
            line-height: 1.4;
        }
        
        .form-switch {
            transform: scale(1.2);
        }
        
        .form-check-input:checked {
            background-color: #008080;
            border-color: #008080;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(220, 53, 69, 0.3);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #ff6b6b;
        }
        
        .alert-success {
            background: rgba(25, 135, 84, 0.1);
            color: #51cf66;
        }
        
        .modal-content {
            background: #1a2a2a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 12px;
            padding: 12px 15px;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #008080;
            box-shadow: 0 0 0 0.2rem rgba(0, 128, 128, 0.25);
            color: white;
        }
        
        .info-box {
            background: rgba(0, 128, 128, 0.1);
            border: 1px solid rgba(0, 128, 128, 0.3);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-box h6 {
            color: #008080;
            margin-bottom: 10px;
        }
        
        .info-box p {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(10, 15, 15, 0.95);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 0;
            z-index: 1000;
        }
        
        .nav-items {
            display: flex;
            justify-content: space-around;
            align-items: center;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .nav-item:hover, .nav-item.active {
            color: #008080;
        }
        
        .nav-icon {
            font-size: 20px;
        }
        
        .nav-label {
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="settings-container">
            <div class="page-header">
                <a href="profile.php" class="back-btn">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
                <div class="page-title">Settings</div>
                <div></div>
            </div>
            
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>" role="alert">
                    <?= $flash['message'] ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <div class="section-card">
                <div class="section-title">
                    <div class="section-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    Security Settings
                </div>
                
                <form method="POST">
                    <input type="hidden" name="action" value="toggle_biometric">
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">Biometric Login</div>
                            <div class="setting-description">Use fingerprint or Face ID to login quickly</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="biometric_enabled" 
                                   <?= $user['biometric_enabled'] ? 'checked' : '' ?>>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-secondary btn-sm">Save Biometric Settings</button>
                </form>
            </div>
            
            <div class="section-card">
                <div class="section-title">
                    <div class="section-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    Two-Factor Authentication
                </div>
                
                <form method="POST">
                    <input type="hidden" name="action" value="toggle_2fa">
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-title">Two-Factor Authentication</div>
                            <div class="setting-description">Add an extra layer of security to your account</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="two_factor_enabled" 
                                   <?= $user['two_factor_enabled'] ? 'checked' : '' ?>>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-secondary btn-sm">Save 2FA Settings</button>
                </form>
            </div>
            
            <div class="section-card">
                <div class="section-title">
                    <div class="section-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    Notifications
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-title">Transaction Notifications</div>
                        <div class="setting-description">Get notified about all your transactions</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" checked>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-title">Promotional Notifications</div>
                        <div class="setting-description">Receive updates about new features and offers</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox">
                    </div>
                </div>
            </div>
            
            <div class="section-card">
                <div class="section-title">
                    <div class="section-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    About
                </div>
                
                <div class="info-box">
                    <h6>LiquiSwap Version</h6>
                    <p>Version 1.0.0</p>
                    <p>Last Updated: March 22, 2026</p>
                </div>
                
                <div class="info-box">
                    <h6>Account Information</h6>
                    <p>Member Since: <?= date('M j, Y', strtotime($user['member_since'])) ?></p>
                    <p>Last Login: <?= $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : 'Never' ?></p>
                    <p>Account ID: <?= $user['uuid'] ?></p>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-title">Terms of Service</div>
                        <div class="setting-description">Read our terms and conditions</div>
                    </div>
                    <a href="#" class="btn btn-secondary btn-sm">View</a>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-title">Privacy Policy</div>
                        <div class="setting-description">Learn how we protect your data</div>
                    </div>
                    <a href="#" class="btn btn-secondary btn-sm">View</a>
                </div>
            </div>
            
            <div class="section-card">
                <div class="section-title">
                    <div class="section-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    Danger Zone
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <div class="setting-title">Delete Account</div>
                        <div class="setting-description">Permanently delete your account and all data</div>
                    </div>
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Warning:</strong> This action cannot be undone. Deleting your account will:
                    </div>
                    <ul>
                        <li>Permanently delete all your wallets and balances</li>
                        <li>Delete all transaction history</li>
                        <li>Remove all personal data</li>
                        <li>Log you out from all devices</li>
                    </ul>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="delete_account">
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Enter your password to confirm:</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i> Delete Account
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bottom-nav">
        <div class="nav-items">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-home nav-icon"></i>
                <span class="nav-label">DASH</span>
            </a>
            
            <a href="swap.php" class="nav-item">
                <i class="fas fa-exchange-alt nav-icon"></i>
                <span class="nav-label">SWAP</span>
            </a>
            
            <a href="#" class="nav-item" onclick="showQuickActions()">
                <i class="fas fa-plus-circle nav-icon"></i>
            </a>
            
            <a href="history.php" class="nav-item">
                <i class="fas fa-history nav-icon"></i>
                <span class="nav-label">HISTORY</span>
            </a>
            
            <a href="profile.php" class="nav-item">
                <i class="fas fa-user nav-icon"></i>
                <span class="nav-label">PROFILE</span>
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showQuickActions() {
            alert('Quick actions menu would appear here');
        }
    </script>
</body>
</html>
