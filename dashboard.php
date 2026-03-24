<?php
require_once 'includes/config.php';
requireLogin();

$user = $auth->getUser();
$wallets = $auth->getUserWallets();

// Get recent transactions
$transactions_query = "SELECT t.*, b.name as bundle_name, b.operator as bundle_operator 
                       FROM transactions t 
                       LEFT JOIN bundles b ON t.bundle_id = b.id 
                       WHERE t.user_id = :user_id 
                       ORDER BY t.created_at DESC 
                       LIMIT 5";
$stmt = $db->prepare($transactions_query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$recent_transactions = $stmt->fetchAll();

// Get featured bundles
$bundles_query = "SELECT * FROM bundles WHERE is_good_deal = 1 OR is_hot = 1 ORDER BY is_hot DESC, price ASC LIMIT 2";
$stmt = $db->prepare($bundles_query);
$stmt->execute();
$featured_bundles = $stmt->fetchAll();

// Calculate total balance
$total_balance = array_sum(array_column(array_map(function($wallet) {
    $wallet['balance'] = $wallet['balance'] ?? 0;
    return $wallet;
}, $wallets), 'balance'));

$flash = getFlashMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LiquiSwap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .dashboard-header {
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .greeting {
            font-size: 18px;
            font-weight: 500;
        }
        
        .notification-icon {
            width: 24px;
            height: 24px;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        
        .notification-icon:hover {
            opacity: 1;
        }
        
        .balance-section {
            padding: 30px 0;
            text-align: center;
        }
        
        .unified-balance {
            font-size: 36px;
            font-weight: 700;
            color: #008080;
            margin-bottom: 10px;
        }
        
        .balance-change {
            font-size: 14px;
            color: #51cf66;
            margin-bottom: 30px;
        }
        
        .wallets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 40px;
        }
        
        .wallet-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }
        
        .wallet-card:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-2px);
        }
        
        .wallet-provider {
            font-size: 12px;
            opacity: 0.7;
            margin-bottom: 5px;
        }
        
        .wallet-balance {
            font-size: 20px;
            font-weight: 600;
        }
        
        .quick-actions {
            margin-bottom: 40px;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .action-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .action-btn:hover {
            background: rgba(0, 128, 128, 0.2);
            border-color: #008080;
            color: white;
            transform: translateY(-2px);
        }
        
        .action-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #008080, #00a8a8);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .action-label {
            font-size: 12px;
            font-weight: 500;
        }
        
        .center-action {
            background: linear-gradient(135deg, #008080, #00a8a8);
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: -30px auto 20px;
            box-shadow: 0 10px 30px rgba(0, 128, 128, 0.3);
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #008080;
        }
        
        .deals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .deal-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .deal-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .deal-price {
            font-size: 18px;
            font-weight: 700;
            color: #008080;
            margin-bottom: 15px;
        }
        
        .btn-activate {
            background: #008080;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            color: white;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-activate:hover {
            background: #00a8a8;
        }
        
        .live-feed {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            padding: 20px;
        }
        
        .feed-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .feed-item:last-child {
            border-bottom: none;
        }
        
        .feed-details {
            flex: 1;
        }
        
        .feed-title {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .feed-time {
            font-size: 12px;
            opacity: 0.6;
        }
        
        .feed-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .status-success {
            background: rgba(25, 135, 84, 0.2);
            color: #51cf66;
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
        
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: rgba(25, 135, 84, 0.1);
            color: #51cf66;
        }
        
        @media (max-width: 768px) {
            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .wallets-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <div class="header-content">
                <div class="greeting">
                    <?= getGreeting($user['full_name']) ?>
                </div>
                <svg class="notification-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                </svg>
            </div>
        </div>
        
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>" role="alert">
                <?= $flash['message'] ?>
            </div>
        <?php endif; ?>
        
        <div class="balance-section">
            <div class="unified-balance">
                FCFA <?= number_format($total_balance, 0, '.', ',') ?>
            </div>
            <div class="balance-change">
                <i class="fas fa-arrow-up"></i> +2.4% this month
            </div>
            
            <div class="wallets-grid">
                <?php foreach ($wallets as $wallet): ?>
                    <div class="wallet-card">
                        <div class="wallet-provider"><?= $wallet['provider'] ?></div>
                        <div class="wallet-balance">
                            <?= number_format($wallet['balance'] ?? 0, 0, '.', ',') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="quick-actions">
            <div class="actions-grid">
                <a href="swap.php" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="action-label">OM <> MOMO</div>
                </a>
                
                <a href="#" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="action-label">AIRTIME</div>
                </a>
                
                <a href="bundles.php" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <div class="action-label">BUNDLES</div>
                </a>
                
                <a href="#" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div class="action-label">GOODDEALS</div>
                </a>
                
                <a href="ussd.php" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-hashtag"></i>
                    </div>
                    <div class="action-label">USSD CODES</div>
                </a>
                
                <a href="#" class="action-btn">
                    <div class="action-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="action-label">SUPPORT</div>
                </a>
            </div>
            
            <div class="center-action">
                <i class="fas fa-plus"></i>
            </div>
        </div>
        
        <div class="curated-deals">
            <div class="section-title">CURATED DEALS</div>
            <div class="deals-grid">
                <?php foreach ($featured_bundles as $bundle): ?>
                    <div class="deal-card">
                        <div class="deal-title">
                            <?= $bundle['operator'] ?> <?= $bundle['name'] ?>
                            <?php if ($bundle['is_hot']): ?>
                                <span style="color: #ff6b6b; font-size: 10px;">🔥 HOT</span>
                            <?php endif; ?>
                        </div>
                        <div class="deal-price">
                            <?= $bundle['data_amount'] ?>/Month
                        </div>
                        <div class="deal-price">
                            <?= number_format($bundle['price'], 0, '.', ',') ?> FCFA
                        </div>
                        <button class="btn-activate" onclick="activateBundle(<?= $bundle['id'] ?>)">
                            ACTIVATE
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="live-feed">
            <div class="section-title">LIVE FEED</div>
            <?php foreach ($recent_transactions as $transaction): ?>
                <div class="feed-item">
                    <div class="feed-details">
                        <div class="feed-title">
                            <?php
                            switch($transaction['type']) {
                                case 'swap':
                                    echo strtoupper($transaction['subtype']) . ' ';
                                    echo number_format($transaction['amount'], 0, '.', ',');
                                    break;
                                case 'airtime':
                                    echo $transaction['operator'] . ' Airtime ';
                                    echo number_format($transaction['amount'], 0, '.', ',');
                                    break;
                                case 'bundle':
                                    echo $transaction['bundle_operator'] . ' ' . $transaction['bundle_name'];
                                    break;
                                default:
                                    echo ucfirst($transaction['type']);
                            }
                            ?>
                        </div>
                        <div class="feed-time"><?= timeAgo($transaction['created_at']) ?></div>
                    </div>
                    <div class="feed-status status-<?= $transaction['status'] ?>">
                        <?= strtoupper($transaction['status']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="bottom-nav">
        <div class="nav-items">
            <a href="dashboard.php" class="nav-item active">
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
    <script src="assets/js/utils.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="assets/js/interactive.js"></script>
    <script>
        // Initialize app when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Activate bundle functionality
            window.activateBundle = function(bundleId) {
                if (confirm('Are you sure you want to activate this bundle?')) {
                    // Show loading state
                    event.target.setLoading(true);
                    
                    // Simulate API call
                    setTimeout(() => {
                        event.target.setLoading(false);
                        Interactive.showToast('Bundle activated successfully!', 'success');
                    }, 1500);
                }
            };
            
            // Quick actions functionality
            window.showQuickActions = function() {
                Interactive.showToast('Quick actions menu would appear here', 'info');
            };
        });
    </script>
</body>
</html>
