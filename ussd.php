<?php
require_once 'includes/config.php';
requireLogin();

// Get USSD codes with filters
$operator_filter = $_GET['operator'] ?? '';
$category_filter = $_GET['category'] ?? '';

$ussd_query = "SELECT * FROM ussd_codes WHERE is_active = 1";
$params = [];

if (!empty($operator_filter)) {
    $ussd_query .= " AND operator = :operator";
    $params['operator'] = $operator_filter;
}

if (!empty($category_filter)) {
    $ussd_query .= " AND category = :category";
    $params['category'] = $category_filter;
}

$ussd_query .= " ORDER BY operator, category, name";

$stmt = $db->prepare($ussd_query);
$stmt->execute($params);
$ussd_codes = $stmt->fetchAll();

// Get unique operators and categories
$operators_query = "SELECT DISTINCT operator FROM ussd_codes WHERE is_active = 1 ORDER BY operator";
$stmt = $db->prepare($operators_query);
$stmt->execute();
$operators = $stmt->fetchAll(PDO::FETCH_COLUMN);

$categories_query = "SELECT DISTINCT category FROM ussd_codes WHERE is_active = 1 ORDER BY category";
$stmt = $db->prepare($categories_query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Group codes by operator and category
$grouped_codes = [];
foreach ($ussd_codes as $code) {
    $grouped_codes[$code['operator']][$code['category']][] = $code;
}

$flash = getFlashMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USSD Codes - LiquiSwap</title>
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
        
        .ussd-container {
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
            min-width: 150px;
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
        
        .operator-section {
            margin-bottom: 40px;
        }
        
        .operator-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(0, 128, 128, 0.3);
        }
        
        .operator-logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #008080, #00a8a8);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            margin-right: 15px;
        }
        
        .operator-name {
            font-size: 20px;
            font-weight: 600;
            color: #008080;
        }
        
        .category-section {
            margin-bottom: 30px;
        }
        
        .category-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .category-icon {
            width: 30px;
            height: 30px;
            background: rgba(0, 128, 128, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        
        .ussd-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .ussd-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px;
            transition: all 0.3s;
        }
        
        .ussd-card:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-2px);
        }
        
        .ussd-name {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .ussd-code {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: 700;
            color: #008080;
            margin-bottom: 8px;
            background: rgba(0, 128, 128, 0.1);
            padding: 8px 12px;
            border-radius: 8px;
            display: inline-block;
        }
        
        .ussd-description {
            font-size: 12px;
            opacity: 0.7;
            line-height: 1.4;
        }
        
        .copy-btn {
            background: rgba(0, 128, 128, 0.2);
            border: 1px solid rgba(0, 128, 128, 0.3);
            border-radius: 6px;
            padding: 4px 8px;
            color: #008080;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 8px;
        }
        
        .copy-btn:hover {
            background: rgba(0, 128, 128, 0.3);
        }
        
        .copy-btn.copied {
            background: rgba(81, 207, 102, 0.2);
            border-color: rgba(81, 207, 102, 0.3);
            color: #51cf66;
        }
        
        .search-box {
            position: relative;
            margin-bottom: 15px;
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
        
        .quick-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .quick-action-btn {
            background: rgba(0, 128, 128, 0.2);
            border: 1px solid rgba(0, 128, 128, 0.3);
            border-radius: 20px;
            padding: 6px 12px;
            color: #008080;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .quick-action-btn:hover {
            background: rgba(0, 128, 128, 0.3);
            color: white;
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
            .ussd-grid {
                grid-template-columns: 1fr;
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
        <div class="ussd-container">
            <div class="page-header">
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
                <div class="page-title">USSD Codes Library</div>
                <div></div>
            </div>
            
            <div class="filter-section">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search USSD codes..." id="ussd_search">
                </div>
                
                <div class="filter-row">
                    <select class="filter-select" id="operator_filter" onchange="filterUSSD()">
                        <option value="">All Operators</option>
                        <?php foreach ($operators as $operator): ?>
                            <option value="<?= $operator ?>" <?= $operator_filter === $operator ? 'selected' : '' ?>>
                                <?= $operator ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select class="filter-select" id="category_filter" onchange="filterUSSD()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category ?>" <?= $category_filter === $category ? 'selected' : '' ?>>
                                <?= ucfirst($category) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="quick-actions">
                    <a href="#" class="quick-action-btn" onclick="dialUSSD('*556#')">
                        <i class="fas fa-phone me-1"></i> Check Balance
                    </a>
                    <a href="#" class="quick-action-btn" onclick="dialUSSD('*126#')">
                        <i class="fas fa-exchange-alt me-1"></i> Transfer Airtime
                    </a>
                    <a href="#" class="quick-action-btn" onclick="dialUSSD('*141*2#')">
                        <i class="fas fa-wifi me-1"></i> Buy Data
                    </a>
                </div>
            </div>
            
            <?php if (empty($grouped_codes)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-hashtag fa-3x mb-3" style="opacity: 0.3;"></i>
                    <h4>No USSD codes found</h4>
                    <p class="text-muted">Try adjusting your filters</p>
                </div>
            <?php else: ?>
                <?php foreach ($grouped_codes as $operator => $categories): ?>
                    <div class="operator-section">
                        <div class="operator-header">
                            <div class="operator-logo">
                                <?= substr($operator, 0, 2) ?>
                            </div>
                            <div class="operator-name"><?= $operator ?></div>
                        </div>
                        
                        <?php foreach ($categories as $category => $codes): ?>
                            <div class="category-section">
                                <div class="category-title">
                                    <div class="category-icon">
                                        <?php
                                        $icon = 'fas fa-hashtag';
                                        switch($category) {
                                            case 'balance':
                                                $icon = 'fas fa-wallet';
                                                break;
                                            case 'data':
                                                $icon = 'fas fa-wifi';
                                                break;
                                            case 'airtime':
                                                $icon = 'fas fa-mobile-alt';
                                                break;
                                            case 'services':
                                                $icon = 'fas fa-cog';
                                                break;
                                        }
                                        ?>
                                        <i class="<?= $icon ?>"></i>
                                    </div>
                                    <?= ucfirst($category) ?>
                                </div>
                                
                                <div class="ussd-grid">
                                    <?php foreach ($codes as $code): ?>
                                        <div class="ussd-card">
                                            <div class="ussd-name"><?= $code['name'] ?></div>
                                            <div class="ussd-code" id="code_<?= $code['id'] ?>"><?= $code['code'] ?></div>
                                            <div class="ussd-description"><?= $code['description'] ?></div>
                                            <button class="copy-btn" onclick="copyCode('<?= $code['code'] ?>', <?= $code['id'] ?>)">
                                                <i class="fas fa-copy me-1"></i> Copy
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
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
        function copyCode(code, elementId) {
            navigator.clipboard.writeText(code).then(() => {
                const btn = event.target.closest('.copy-btn');
                btn.classList.add('copied');
                btn.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
                
                setTimeout(() => {
                    btn.classList.remove('copied');
                    btn.innerHTML = '<i class="fas fa-copy me-1"></i> Copy';
                }, 2000);
            });
        }
        
        function dialUSSD(code) {
            // In a mobile app, this would open the phone dialer
            if (confirm('Dial ' + code + '?')) {
                window.location.href = 'tel:' + code;
            }
        }
        
        function filterUSSD() {
            const operator = document.getElementById('operator_filter').value;
            const category = document.getElementById('category_filter').value;
            
            let url = 'ussd.php';
            const params = new URLSearchParams();
            
            if (operator) params.append('operator', operator);
            if (category) params.append('category', category);
            
            if (params.toString()) {
                url += '?' + params.toString();
            }
            
            window.location.href = url;
        }
        
        function showQuickActions() {
            alert('Quick actions menu would appear here');
        }
        
        // Search functionality
        document.getElementById('ussd_search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const ussdCards = document.querySelectorAll('.ussd-card');
            
            ussdCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
