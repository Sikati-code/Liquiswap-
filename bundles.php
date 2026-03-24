<?php
require_once 'includes/config.php';
requireLogin();

$user = $auth->getUser();
$wallets = $auth->getUserWallets();

// Handle bundle activation
if (isset($_GET['action']) && $_GET['action'] === 'activate' && isset($_GET['id'])) {
    $bundle_id = intval($_GET['id']);
    
    // Get bundle details
    $bundle_query = "SELECT * FROM bundles WHERE id = :id AND is_active = 1";
    $stmt = $db->prepare($bundle_query);
    $stmt->bindParam(':id', $bundle_id);
    $stmt->execute();
    $bundle = $stmt->fetch();
    
    if ($bundle) {
        // Check if user has sufficient balance
        $mtn_wallet = null;
        foreach ($wallets as $wallet) {
            if ($wallet['provider'] === 'MTN') {
                $mtn_wallet = $wallet;
                break;
            }
        }
        
        if ($mtn_wallet && $mtn_wallet['balance'] >= $bundle['price']) {
            try {
                $db->beginTransaction();
                
                // Create transaction
                $transaction_uuid = generateUUID();
                $transaction_query = "INSERT INTO transactions 
                    (transaction_uuid, user_id, type, amount, fee, receiver_identifier, operator, bundle_id, status, reference) 
                    VALUES (:uuid, :user_id, 'bundle', :amount, 0, :phone, :operator, :bundle_id, 'success', :reference)";
                
                $stmt = $db->prepare($transaction_query);
                $stmt->bindParam(':uuid', $transaction_uuid);
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->bindParam(':amount', $bundle['price']);
                $stmt->bindParam(':phone', $mtn_wallet['account_identifier']);
                $stmt->bindParam(':operator', $bundle['operator']);
                $stmt->bindParam(':bundle_id', $bundle_id);
                $stmt->bindParam(':reference', $transaction_uuid);
                $stmt->execute();
                
                // Update wallet balance
                $new_balance = $mtn_wallet['balance'] - $bundle['price'];
                $update_wallet = "UPDATE wallets SET balance = :balance WHERE id = :id";
                $stmt = $db->prepare($update_wallet);
                $stmt->bindParam(':balance', $new_balance);
                $stmt->bindParam(':id', $mtn_wallet['id']);
                $stmt->execute();
                
                $db->commit();
                
                redirect('bundles.php', 'Bundle activated successfully!', 'success');
                
            } catch (Exception $e) {
                $db->rollback();
                error_log("Bundle activation error: " . $e->getMessage());
                $error = 'Failed to activate bundle. Please try again.';
            }
        } else {
            $error = 'Insufficient balance to activate this bundle.';
        }
    } else {
        $error = 'Bundle not found.';
    }
}

// Get bundles with filters
$operator_filter = $_GET['operator'] ?? '';
$search_query = $_GET['search'] ?? '';

$bundles_query = "SELECT * FROM bundles WHERE is_active = 1";
$params = [];

if (!empty($operator_filter)) {
    $bundles_query .= " AND operator = :operator";
    $params['operator'] = $operator_filter;
}

if (!empty($search_query)) {
    $bundles_query .= " AND (name LIKE :search OR description LIKE :search)";
    $search_param = '%' . $search_query . '%';
    $params['search'] = $search_param;
}

$bundles_query .= " ORDER BY is_hot DESC, is_good_deal DESC, price ASC";

$stmt = $db->prepare($bundles_query);
$stmt->execute($params);
$bundles = $stmt->fetchAll();

// Get unique operators for filter
$operators_query = "SELECT DISTINCT operator FROM bundles WHERE is_active = 1 ORDER BY operator";
$stmt = $db->prepare($operators_query);
$stmt->execute();
$operators = $stmt->fetchAll(PDO::FETCH_COLUMN);

$flash = getFlashMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bundles - LiquiSwap</title>
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
        
        .bundles-container {
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
        
        .filter-section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .search-box {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px 15px 12px 45px;
            color: white;
            width: 100%;
        }
        
        .search-input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #008080;
            box-shadow: 0 0 0 0.2rem rgba(0, 128, 128, 0.25);
            color: white;
            outline: none;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
        }
        
        .operator-filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .operator-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 8px 16px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .operator-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }
        
        .operator-btn.active {
            background: #008080;
            border-color: #008080;
            color: white;
        }
        
        .bundles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .bundle-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .bundle-card:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-2px);
        }
        
        .bundle-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .operator-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #008080, #00a8a8);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 12px;
            margin-right: 12px;
        }
        
        .bundle-info {
            flex: 1;
        }
        
        .bundle-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .bundle-validity {
            font-size: 12px;
            opacity: 0.7;
        }
        
        .bundle-features {
            margin-bottom: 15px;
        }
        
        .feature-tag {
            display: inline-block;
            background: rgba(0, 128, 128, 0.2);
            border: 1px solid rgba(0, 128, 128, 0.3);
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 11px;
            margin-right: 6px;
            margin-bottom: 6px;
        }
        
        .bundle-price {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .price-current {
            font-size: 20px;
            font-weight: 700;
            color: #008080;
        }
        
        .price-original {
            font-size: 14px;
            opacity: 0.5;
            text-decoration: line-through;
        }
        
        .bundle-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-activate {
            background: linear-gradient(135deg, #008080, #00a8a8);
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            color: white;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            flex: 1;
        }
        
        .btn-activate:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 128, 128, 0.3);
        }
        
        .btn-details {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 8px 12px;
            color: white;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-details:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        
        .hot-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #ff6b6b, #ff8e53);
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 12px;
            text-transform: uppercase;
        }
        
        .deal-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #51cf66, #37b24d);
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 12px;
            text-transform: uppercase;
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
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            opacity: 0.7;
        }
        
        .no-results i {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.5;
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
            .bundles-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="bundles-container">
            <div class="page-header">
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
                <div class="page-title">Bundle Marketplace</div>
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
            
            <div class="filter-section">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <form method="GET" class="d-flex">
                        <input type="text" name="search" class="search-input" placeholder="Search MTN, Orange or Nexttel bundles..." value="<?= htmlspecialchars($search_query) ?>">
                        <button type="submit" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-white me-3">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                
                <div class="operator-filters">
                    <a href="bundles.php" class="operator-btn <?= empty($operator_filter) ? 'active' : '' ?>">
                        All
                    </a>
                    <?php foreach ($operators as $operator): ?>
                        <a href="bundles.php?operator=<?= $operator ?>&search=<?= htmlspecialchars($search_query) ?>" 
                           class="operator-btn <?= $operator_filter === $operator ? 'active' : '' ?>">
                            <?= $operator ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php if (empty($bundles)): ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h4>No bundles found</h4>
                    <p>Try adjusting your search or filters</p>
                </div>
            <?php else: ?>
                <div class="bundles-grid">
                    <?php foreach ($bundles as $bundle): ?>
                        <div class="bundle-card">
                            <?php if ($bundle['is_hot']): ?>
                                <span class="hot-badge">🔥 Hot</span>
                            <?php elseif ($bundle['is_good_deal']): ?>
                                <span class="deal-badge">Good Deal</span>
                            <?php endif; ?>
                            
                            <div class="bundle-header">
                                <div class="operator-logo">
                                    <?= substr($bundle['operator'], 0, 2) ?>
                                </div>
                                <div class="bundle-info">
                                    <div class="bundle-name"><?= $bundle['name'] ?></div>
                                    <div class="bundle-validity"><?= $bundle['validity'] ?> Validity</div>
                                </div>
                            </div>
                            
                            <div class="bundle-features">
                                <?php if (!empty($bundle['data_amount'])): ?>
                                    <span class="feature-tag"><?= $bundle['data_amount'] ?> Data</span>
                                <?php endif; ?>
                                <?php if (!empty($bundle['voice_minutes'])): ?>
                                    <span class="feature-tag"><?= $bundle['voice_minutes'] ?> Voice</span>
                                <?php endif; ?>
                                <?php if (!empty($bundle['sms_count'])): ?>
                                    <span class="feature-tag"><?= $bundle['sms_count'] ?> SMS</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="bundle-price">
                                <div>
                                    <div class="price-current"><?= number_format($bundle['price'], 0, '.', ',') ?> XAF</div>
                                    <?php if ($bundle['original_price'] && $bundle['original_price'] > $bundle['price']): ?>
                                        <div class="price-original"><?= number_format($bundle['original_price'], 0, '.', ',') ?> XAF</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="bundle-actions">
                                <button class="btn-activate" onclick="activateBundle(<?= $bundle['id'] ?>)">
                                    Activate
                                </button>
                                <button class="btn-details" onclick="showBundleDetails(<?= $bundle['id'] ?>)">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
    
    <script>
        function activateBundle(bundleId) {
            if (confirm('Are you sure you want to activate this bundle? The amount will be deducted from your MTN wallet.')) {
                window.location.href = 'bundles.php?action=activate&id=' + bundleId;
            }
        }
        
        function showBundleDetails(bundleId) {
            // In a real app, this would show a modal with bundle details
            alert('Bundle details would be shown here in a modal');
        }
        
        function showQuickActions() {
            alert('Quick actions menu would appear here');
        }
        
        // Clear search on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const searchInput = document.querySelector('.search-input');
                if (searchInput.value) {
                    searchInput.value = '';
                    searchInput.form.submit();
                }
            }
        });
    </script>
</body>
</html>
