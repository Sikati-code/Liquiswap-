<?php
require_once 'includes/config.php';
requireLogin();

$user = $auth->getUser();

// Get transactions with filters
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$search_query = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$transactions_query = "SELECT t.*, b.name as bundle_name, b.operator as bundle_operator 
                       FROM transactions t 
                       LEFT JOIN bundles b ON t.bundle_id = b.id 
                       WHERE t.user_id = :user_id";
$params = ['user_id' => $_SESSION['user_id']];

if (!empty($type_filter)) {
    $transactions_query .= " AND t.type = :type";
    $params['type'] = $type_filter;
}

if (!empty($status_filter)) {
    $transactions_query .= " AND t.status = :status";
    $params['status'] = $status_filter;
}

if (!empty($date_filter)) {
    switch($date_filter) {
        case 'today':
            $transactions_query .= " AND DATE(t.created_at) = CURDATE()";
            break;
        case 'week':
            $transactions_query .= " AND t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $transactions_query .= " AND t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }
}

if (!empty($search_query)) {
    $transactions_query .= " AND (t.reference LIKE :search OR t.receiver_identifier LIKE :search)";
    $search_param = '%' . $search_query . '%';
    $params['search'] = $search_param;
}

// Count total records
$count_query = str_replace("SELECT t.*, b.name as bundle_name, b.operator as bundle_operator", "SELECT COUNT(*)", $transactions_query);
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_transactions = $stmt->fetchColumn();

// Get paginated results
$transactions_query .= " ORDER BY t.created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($transactions_query);
foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll();

$total_pages = ceil($total_transactions / $per_page);

// Get transaction statistics
$stats_query = "SELECT 
                   COUNT(*) as total_transactions,
                   COALESCE(SUM(amount), 0) as total_amount,
                   COALESCE(SUM(fee), 0) as total_fees,
                   COUNT(CASE WHEN status = 'success' THEN 1 END) as successful_transactions
                FROM transactions 
                WHERE user_id = :user_id";
$stmt = $db->prepare($stats_query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$stats = $stmt->fetch();

$flash = getFlashMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - LiquiSwap</title>
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
        
        .history-container {
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #008080;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            opacity: 0.7;
        }
        
        .filter-section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .filter-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .filter-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 10px 15px;
            color: white;
            min-width: 120px;
        }
        
        .filter-select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #008080;
            box-shadow: 0 0 0 0.2rem rgba(0, 128, 128, 0.25);
            color: white;
            outline: none;
        }
        
        .filter-select option {
            background: #1a2a2a;
            color: white;
        }
        
        .search-box {
            position: relative;
            flex: 1;
            min-width: 200px;
        }
        
        .search-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 10px 15px 10px 45px;
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
        
        .transactions-list {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }
        
        .transaction-item {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .transaction-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .transaction-item:last-child {
            border-bottom: none;
        }
        
        .transaction-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .transaction-type {
            font-size: 16px;
            font-weight: 600;
        }
        
        .transaction-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .status-success {
            background: rgba(25, 135, 84, 0.2);
            color: #51cf66;
        }
        
        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }
        
        .status-failed {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
        }
        
        .transaction-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .transaction-info {
            flex: 1;
        }
        
        .transaction-amount {
            font-size: 18px;
            font-weight: 700;
            color: #008080;
            margin-bottom: 5px;
        }
        
        .transaction-meta {
            font-size: 12px;
            opacity: 0.7;
        }
        
        .transaction-time {
            font-size: 12px;
            opacity: 0.6;
            text-align: right;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 30px;
        }
        
        .page-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 8px 12px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .page-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }
        
        .page-btn.active {
            background: #008080;
            border-color: #008080;
        }
        
        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .no-transactions {
            text-align: center;
            padding: 60px 20px;
            opacity: 0.7;
        }
        
        .no-transactions i {
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
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .filter-row {
                flex-direction: column;
            }
            
            .filter-select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="history-container">
            <div class="page-header">
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
                <div class="page-title">Transaction History</div>
                <div></div>
            </div>
            
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>" role="alert">
                    <?= $flash['message'] ?>
                </div>
            <?php endif; ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($stats['total_transactions'], 0) ?></div>
                    <div class="stat-label">Total Transactions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($stats['total_amount'], 0, '.', ',') ?> XAF</div>
                    <div class="stat-label">Total Amount</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($stats['total_fees'], 0, '.', ',') ?> XAF</div>
                    <div class="stat-label">Total Fees</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['successful_transactions'] ?></div>
                    <div class="stat-label">Successful</div>
                </div>
            </div>
            
            <div class="filter-section">
                <form method="GET">
                    <div class="filter-row">
                        <select name="type" class="filter-select">
                            <option value="">All Types</option>
                            <option value="swap" <?= $type_filter === 'swap' ? 'selected' : '' ?>>Swap</option>
                            <option value="airtime" <?= $type_filter === 'airtime' ? 'selected' : '' ?>>Airtime</option>
                            <option value="bundle" <?= $type_filter === 'bundle' ? 'selected' : '' ?>>Bundle</option>
                            <option value="conversion" <?= $type_filter === 'conversion' ? 'selected' : '' ?>>Conversion</option>
                        </select>
                        
                        <select name="status" class="filter-select">
                            <option value="">All Status</option>
                            <option value="success" <?= $status_filter === 'success' ? 'selected' : '' ?>>Success</option>
                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="failed" <?= $status_filter === 'failed' ? 'selected' : '' ?>>Failed</option>
                        </select>
                        
                        <select name="date" class="filter-select">
                            <option value="">All Time</option>
                            <option value="today" <?= $date_filter === 'today' ? 'selected' : '' ?>>Today</option>
                            <option value="week" <?= $date_filter === 'week' ? 'selected' : '' ?>>This Week</option>
                            <option value="month" <?= $date_filter === 'month' ? 'selected' : '' ?>>This Month</option>
                        </select>
                        
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="search" class="search-input" placeholder="Search by reference..." value="<?= htmlspecialchars($search_query) ?>">
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if (empty($transactions)): ?>
                <div class="transactions-list">
                    <div class="no-transactions">
                        <i class="fas fa-history"></i>
                        <h4>No transactions found</h4>
                        <p>Try adjusting your filters or make a new transaction</p>
                        <a href="dashboard.php" class="btn btn-primary mt-3">
                            Make a Transaction
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="transactions-list">
                    <?php foreach ($transactions as $transaction): ?>
                        <div class="transaction-item" onclick="showTransactionDetails('<?= $transaction['transaction_uuid'] ?>')">
                            <div class="transaction-header">
                                <div class="transaction-type">
                                    <?php
                                    $type_display = ucfirst($transaction['type']);
                                    if ($transaction['subtype']) {
                                        $type_display .= ' - ' . str_replace('_', ' ', $transaction['subtype']);
                                    }
                                    echo $type_display;
                                    ?>
                                </div>
                                <div class="transaction-status status-<?= $transaction['status'] ?>">
                                    <?= strtoupper($transaction['status']) ?>
                                </div>
                            </div>
                            
                            <div class="transaction-details">
                                <div class="transaction-info">
                                    <div class="transaction-amount">
                                        <?= number_format($transaction['amount'] ?? 0, 0, '.', ',') ?> XAF
                                    </div>
                                    <div class="transaction-meta">
                                        Ref: <?= $transaction['reference'] ?>
                                        <?php if ($transaction['bundle_name']): ?>
                                            | <?= $transaction['bundle_operator'] ?> <?= $transaction['bundle_name'] ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="transaction-time">
                                    <?= date('M j, Y H:i', strtotime($transaction['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?><?= !empty($type_filter) ? '&type=' . $type_filter : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?><?= !empty($date_filter) ? '&date=' . $date_filter : '' ?><?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>" class="page-btn">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <a href="?page=<?= $i ?><?= !empty($type_filter) ? '&type=' . $type_filter : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?><?= !empty($date_filter) ? '&date=' . $date_filter : '' ?><?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>" 
                               class="page-btn <?= $i === $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?><?= !empty($type_filter) ? '&type=' . $type_filter : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?><?= !empty($date_filter) ? '&date=' . $date_filter : '' ?><?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>" class="page-btn">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
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
            
            <a href="history.php" class="nav-item active">
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
        function showTransactionDetails(transactionId) {
            // In a real app, this would show a modal with full transaction details
            alert('Transaction details for ' + transactionId + ' would be shown here in a modal');
        }
        
        function showQuickActions() {
            alert('Quick actions menu would appear here');
        }
        
        // Auto-submit form when filters change
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
        
        // Search on Enter key
        document.querySelector('.search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    </script>
</body>
</html>
