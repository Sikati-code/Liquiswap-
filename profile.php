<?php
require_once 'includes/config.php';
requireLogin();

$user = $auth->getUser();
$wallets = $auth->getUserWallets();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        
        if (!empty($full_name)) {
            $update_query = "UPDATE users SET full_name = :full_name, email = :email WHERE id = :id";
            $stmt = $db->prepare($update_query);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                redirect('profile.php', 'Profile updated successfully!', 'success');
            } else {
                $error = 'Failed to update profile';
            }
        } else {
            $error = 'Full name is required';
        }
    } elseif ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'All password fields are required';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 8) {
            $error = 'Password must be at least 8 characters';
        } else {
            // Verify current password
            $user_query = "SELECT password_hash FROM users WHERE id = :id";
            $stmt = $db->prepare($user_query);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();
            $user_data = $stmt->fetch();
            
            if ($user_data && password_verify($current_password, $user_data['password_hash'])) {
                $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
                $update_query = "UPDATE users SET password_hash = :password WHERE id = :id";
                $stmt = $db->prepare($update_query);
                $stmt->bindParam(':password', $new_hash);
                $stmt->bindParam(':id', $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    redirect('profile.php', 'Password changed successfully!', 'success');
                } else {
                    $error = 'Failed to change password';
                }
            } else {
                $error = 'Current password is incorrect';
            }
        }
    } elseif ($_POST['action'] === 'add_wallet') {
        $provider = sanitize($_POST['provider']);
        $account_identifier = sanitize($_POST['account_identifier']);
        
        if (!empty($provider) && !empty($account_identifier)) {
            // Check if wallet already exists
            $check_query = "SELECT id FROM wallets WHERE user_id = :user_id AND provider = :provider";
            $stmt = $db->prepare($check_query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':provider', $provider);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                $error = 'Wallet for this provider already exists';
            } else {
                $insert_query = "INSERT INTO wallets (user_id, provider, account_identifier, balance) 
                                 VALUES (:user_id, :provider, :identifier, 0)";
                $stmt = $db->prepare($insert_query);
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->bindParam(':provider', $provider);
                $stmt->bindParam(':identifier', $account_identifier);
                
                if ($stmt->execute()) {
                    redirect('profile.php', 'Wallet added successfully!', 'success');
                } else {
                    $error = 'Failed to add wallet';
                }
            }
        } else {
            $error = 'All fields are required';
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
    <title>Profile - LiquiSwap</title>
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
        
        .profile-container {
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
        
        .profile-header {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #008080, #00a8a8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            margin: 0 auto 20px;
        }
        
        .profile-name {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .profile-phone {
            font-size: 16px;
            opacity: 0.7;
            margin-bottom: 20px;
        }
        
        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #008080;
        }
        
        .stat-label {
            font-size: 12px;
            opacity: 0.7;
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
        
        .btn-primary {
            background: linear-gradient(135deg, #008080, #00a8a8);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 128, 128, 0.3);
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
        
        .wallets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .wallet-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .wallet-card:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        
        .wallet-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .wallet-provider {
            font-size: 16px;
            font-weight: 600;
        }
        
        .wallet-balance {
            font-size: 20px;
            font-weight: 700;
            color: #008080;
        }
        
        .wallet-account {
            font-size: 14px;
            opacity: 0.7;
            margin-bottom: 10px;
        }
        
        .wallet-status {
            display: flex;
            gap: 10px;
            font-size: 12px;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 6px;
        }
        
        .status-primary {
            background: rgba(0, 128, 128, 0.2);
            color: #008080;
        }
        
        .status-success {
            background: rgba(25, 135, 84, 0.2);
            color: #51cf66;
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
        
        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.05);
            padding: 5px;
            border-radius: 12px;
        }
        
        .tab-btn {
            flex: 1;
            padding: 10px;
            border: none;
            background: transparent;
            color: rgba(255, 255, 255, 0.7);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .tab-btn.active {
            background: #008080;
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
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
        
        @media (max-width: 768px) {
            .wallets-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-stats {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <div class="page-header">
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
                <div class="page-title">Profile</div>
                <a href="settings.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-cog me-1"></i> Settings
                </a>
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
            
            <div class="profile-header">
                <div class="profile-avatar">
                    <?= substr($user['full_name'], 0, 2) ?>
                </div>
                <div class="profile-name"><?= $user['full_name'] ?></div>
                <div class="profile-phone"><?= $user['phone_number'] ?></div>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?= $user['trust_score'] ?></div>
                        <div class="stat-label">Trust Score</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $user['total_swaps'] ?></div>
                        <div class="stat-label">Total Swaps</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $user['success_rate'] ?>%</div>
                        <div class="stat-label">Success Rate</div>
                    </div>
                </div>
            </div>
            
            <div class="section-card">
                <div class="section-title">My Wallets</div>
                <div class="wallets-grid">
                    <?php foreach ($wallets as $wallet): ?>
                        <div class="wallet-card">
                            <div class="wallet-header">
                                <div class="wallet-provider"><?= $wallet['provider'] ?></div>
                                <div class="wallet-balance"><?= number_format($wallet['balance'] ?? 0, 0, '.', ',') ?></div>
                            </div>
                            <div class="wallet-account"><?= $wallet['account_identifier'] ?></div>
                            <div class="wallet-status">
                                <?php if ($wallet['is_primary']): ?>
                                    <span class="status-badge status-primary">Primary</span>
                                <?php endif; ?>
                                <?php if ($wallet['is_verified']): ?>
                                    <span class="status-badge status-success">Verified</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="section-card">
                <div class="tab-buttons">
                    <button class="tab-btn active" onclick="switchTab('profile')">Profile Info</button>
                    <button class="tab-btn" onclick="switchTab('password')">Change Password</button>
                    <button class="tab-btn" onclick="switchTab('wallet')">Add Wallet</button>
                </div>
                
                <div id="profile-tab" class="tab-content active">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" value="<?= htmlspecialchars($user['phone_number']) ?>" readonly>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Profile
                        </button>
                    </form>
                </div>
                
                <div id="password-tab" class="tab-content">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required minlength="8">
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" required minlength="8">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-lock me-2"></i> Change Password
                        </button>
                    </form>
                </div>
                
                <div id="wallet-tab" class="tab-content">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_wallet">
                        
                        <div class="mb-3">
                            <label for="provider" class="form-label">Provider</label>
                            <select class="form-control" name="provider" required>
                                <option value="">Select Provider</option>
                                <option value="MTN">MTN</option>
                                <option value="ORANGE">Orange</option>
                                <option value="CAMTEL">Camtel</option>
                                <option value="NEXTTEL">Nexttel</option>
                                <option value="BANK">Bank</option>
                                <option value="EXPRESS_UNION">Express Union</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="account_identifier" class="form-label">Account Number/Phone</label>
                            <input type="text" class="form-control" name="account_identifier" placeholder="Enter account number or phone" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Add Wallet
                        </button>
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
            
            <a href="profile.php" class="nav-item active">
                <i class="fas fa-user nav-icon"></i>
                <span class="nav-label">PROFILE</span>
            </a>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tab + '-tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
        
        function showQuickActions() {
            alert('Quick actions menu would appear here');
        }
    </script>
</body>
</html>
