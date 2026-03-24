<?php
require_once 'includes/config.php';
requireLogin();

$user = $auth->getUser();
$wallets = $auth->getUserWallets();

// Handle conversion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'convert') {
    $phone_number = sanitize($_POST['phone_number']);
    $bundle_id = intval($_POST['bundle_id']);
    $amount = floatval($_POST['amount']);
    
    // Validate inputs
    if (empty($phone_number) || empty($bundle_id) || $amount <= 0) {
        $error = 'Please fill in all required fields';
    } else {
        // Get bundle details
        $bundle_query = "SELECT * FROM bundles WHERE id = :id AND is_active = 1";
        $stmt = $db->prepare($bundle_query);
        $stmt->bindParam(':id', $bundle_id);
        $stmt->execute();
        $bundle = $stmt->fetch();
        
        if (!$bundle) {
            $error = 'Bundle not found';
        } else {
            // Check if user has sufficient airtime balance
            $mtn_wallet = null;
            foreach ($wallets as $wallet) {
                if ($wallet['provider'] === 'MTN') {
                    $mtn_wallet = $wallet;
                    break;
                }
            }
            
            if (!$mtn_wallet || $mtn_wallet['balance'] < $amount) {
                $error = 'Insufficient airtime balance';
            } else {
                try {
                    $db->beginTransaction();
                    
                    // Create transaction
                    $transaction_uuid = generateUUID();
                    $transaction_query = "INSERT INTO transactions 
                        (transaction_uuid, user_id, type, subtype, amount, fee, receiver_identifier, operator, bundle_id, status, reference) 
                        VALUES (:uuid, :user_id, 'conversion', 'airtime_to_bundle', :amount, 0, :phone, :operator, :bundle_id, 'success', :reference)";
                    
                    $stmt = $db->prepare($transaction_query);
                    $stmt->bindParam(':uuid', $transaction_uuid);
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->bindParam(':amount', $amount);
                    $stmt->bindParam(':phone', $phone_number);
                    $stmt->bindParam(':operator', $bundle['operator']);
                    $stmt->bindParam(':bundle_id', $bundle_id);
                    $stmt->bindParam(':reference', $transaction_uuid);
                    $stmt->execute();
                    
                    // Update wallet balance
                    $new_balance = $mtn_wallet['balance'] - $amount;
                    $update_wallet = "UPDATE wallets SET balance = :balance WHERE id = :id";
                    $stmt = $db->prepare($update_wallet);
                    $stmt->bindParam(':balance', $new_balance);
                    $stmt->bindParam(':id', $mtn_wallet['id']);
                    $stmt->execute();
                    
                    $db->commit();
                    
                    redirect('airtime-bundle.php?success=1&reference=' . $transaction_uuid, 'Airtime converted successfully!', 'success');
                    
                } catch (Exception $e) {
                    $db->rollback();
                    error_log("Airtime conversion error: " . $e->getMessage());
                    $error = 'Conversion failed. Please try again.';
                }
            }
        }
    }
}

// Get available bundles
$bundles_query = "SELECT * FROM bundles WHERE is_active = 1 ORDER BY is_hot DESC, price ASC";
$stmt = $db->prepare($bundles_query);
$stmt->execute();
$bundles = $stmt->fetchAll();

// Get recent recipients (mock data for demo)
$recent_recipients = [
    ['name' => 'Mom', 'initial' => 'M', 'phone' => '+237699999991'],
    ['name' => 'Papa', 'initial' => 'P', 'phone' => '+237699999992'],
    ['name' => 'Junior', 'initial' => 'J', 'phone' => '+237699999993'],
    ['name' => 'Alice', 'initial' => 'A', 'phone' => '+237699999994']
];

$flash = getFlashMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airtime to Bundle - LiquiSwap</title>
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
        
        .conversion-container {
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
            text-align: center;
        }
        
        .title-main {
            font-size: 24px;
            font-weight: 600;
            color: #008080;
            margin-bottom: 5px;
        }
        
        .title-sub {
            font-size: 14px;
            opacity: 0.7;
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
        
        .section-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 16px;
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
        
        .input-group-text {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
        }
        
        .recent-recipients {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            overflow-x: auto;
            padding-bottom: 10px;
        }
        
        .recipient-avatar {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .recipient-circle {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #008080, #00a8a8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        
        .recipient-avatar:hover .recipient-circle {
            transform: scale(1.1);
        }
        
        .recipient-name {
            font-size: 12px;
            opacity: 0.8;
        }
        
        .bundles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .bundle-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        
        .bundle-card:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-2px);
        }
        
        .bundle-card.selected {
            background: rgba(0, 128, 128, 0.2);
            border-color: #008080;
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
            font-size: 20px;
            font-weight: 700;
            color: #008080;
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
        
        .view-all-link {
            color: #008080;
            text-decoration: none;
            font-size: 14px;
            float: right;
            transition: opacity 0.3s;
        }
        
        .view-all-link:hover {
            opacity: 0.8;
        }
        
        .transaction-preview {
            background: rgba(0, 128, 128, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .preview-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .preview-arrow {
            color: #008080;
            font-size: 20px;
        }
        
        .preview-details {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .preview-detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
            opacity: 0.8;
        }
        
        .preview-total {
            font-weight: 600;
            font-size: 16px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-convert {
            background: linear-gradient(135deg, #008080, #00a8a8);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-convert:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 128, 128, 0.3);
        }
        
        .btn-convert:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .disclaimer {
            font-size: 12px;
            opacity: 0.6;
            text-align: center;
            margin-top: 15px;
            line-height: 1.4;
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
        
        .success-animation {
            text-align: center;
            padding: 40px;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #008080, #00a8a8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
            animation: successPulse 2s infinite;
        }
        
        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
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
        <div class="conversion-container">
            <div class="page-header">
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="page-title">
                    <div class="title-main">Airtime -> Bundle</div>
                    <div class="title-sub">LiquiSwap Instant Conversion</div>
                </div>
                <svg class="notification-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                </svg>
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
            
            <?php if (isset($_GET['success'])): ?>
                <div class="section-card">
                    <div class="success-animation">
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <h3>Conversion Successful!</h3>
                        <p class="text-muted">Reference: <?= $_GET['reference'] ?></p>
                        <p class="text-muted">Your airtime has been converted to bundle successfully.</p>
                        <a href="dashboard.php" class="btn btn-convert mt-3">
                            Return to Dashboard
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="action" value="convert">
                    <input type="hidden" name="bundle_id" id="selected_bundle_id">
                    <input type="hidden" name="amount" id="selected_amount">
                    
                    <div class="section-card">
                        <div class="section-title">1 Select Airtime Source</div>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="tel" class="form-control" name="phone_number" placeholder="Enter 9-digit number" required>
                            <span class="input-group-text">
                                <i class="fas fa-address-book"></i>
                            </span>
                        </div>
                        
                        <div class="recent-recipients">
                            <?php foreach ($recent_recipients as $recipient): ?>
                                <div class="recipient-avatar" onclick="selectRecipient('<?= $recipient['phone'] ?>')">
                                    <div class="recipient-circle"><?= $recipient['initial'] ?></div>
                                    <div class="recipient-name"><?= $recipient['name'] ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="section-card">
                        <div class="section-title">
                            2 Choose Bundle
                            <a href="bundles.php" class="view-all-link">View All</a>
                        </div>
                        
                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" placeholder="Search MTN, Orange or Nexttel bundles..." id="bundle_search">
                        </div>
                        
                        <div class="bundles-grid" id="bundles_container">
                            <?php foreach ($bundles as $bundle): ?>
                                <div class="bundle-card" onclick="selectBundle(<?= $bundle['id'] ?>, <?= $bundle['price'] ?>, '<?= $bundle['name'] ?>', '<?= $bundle['data_amount'] ?>')">
                                    <?php if ($bundle['is_hot']): ?>
                                        <span class="hot-badge">🔥 Hot</span>
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
                                            <span class="feature-tag"><?= $bundle['voice_minutes'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="bundle-price">
                                        <?= number_format($bundle['price'], 0, '.', ',') ?> XAF
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="section-card">
                        <div class="section-title">3 Transaction Preview</div>
                        
                        <div class="transaction-preview" id="transaction_preview" style="display: none;">
                            <div class="preview-row">
                                <span>FROM AIRTIME</span>
                                <span id="preview_amount">0 XAF</span>
                            </div>
                            <div class="preview-row">
                                <i class="fas fa-arrow-down preview-arrow"></i>
                            </div>
                            <div class="preview-row">
                                <span>TO BUNDLE</span>
                                <span id="preview_bundle">-</span>
                            </div>
                            
                            <div class="preview-details">
                                <div class="preview-detail-row">
                                    <span>Service Fee</span>
                                    <span>0 XAF</span>
                                </div>
                                <div class="preview-detail-row">
                                    <span>LiquiSwap Bonus</span>
                                    <span style="color: #51cf66;">+250 MB Extra</span>
                                </div>
                                <div class="preview-detail-row preview-total">
                                    <span>Total Deductible</span>
                                    <span id="preview_total">0 XAF</span>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-convert" id="convert_btn" disabled>
                            <i class="fas fa-bolt"></i>
                            Convert Airtime Now
                        </button>
                        
                        <div class="disclaimer">
                            By clicking convert, you authorize LiquiSwap to debit your airtime balance for the selected bundle. No refunds for incorrect numbers.
                        </div>
                    </div>
                </form>
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
        let selectedBundle = null;
        
        function selectRecipient(phone) {
            document.querySelector('input[name="phone_number"]').value = phone;
        }
        
        function selectBundle(bundleId, price, name, dataAmount) {
            // Remove previous selection
            document.querySelectorAll('.bundle-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selection to clicked bundle
            event.target.closest('.bundle-card').classList.add('selected');
            
            selectedBundle = {
                id: bundleId,
                price: price,
                name: name,
                dataAmount: dataAmount
            };
            
            // Update hidden inputs
            document.getElementById('selected_bundle_id').value = bundleId;
            document.getElementById('selected_amount').value = price;
            
            // Update preview
            updateTransactionPreview();
            
            // Enable convert button if phone is entered
            checkFormValidity();
        }
        
        function updateTransactionPreview() {
            if (selectedBundle) {
                document.getElementById('transaction_preview').style.display = 'block';
                document.getElementById('preview_amount').textContent = selectedBundle.price.toLocaleString() + ' XAF';
                document.getElementById('preview_bundle').textContent = selectedBundle.dataAmount + ' DATA';
                document.getElementById('preview_total').textContent = selectedBundle.price.toLocaleString() + ' XAF';
            } else {
                document.getElementById('transaction_preview').style.display = 'none';
            }
        }
        
        function checkFormValidity() {
            const phone = document.querySelector('input[name="phone_number"]').value;
            const convertBtn = document.getElementById('convert_btn');
            
            if (selectedBundle && phone.length >= 9) {
                convertBtn.disabled = false;
            } else {
                convertBtn.disabled = true;
            }
        }
        
        // Search functionality
        document.getElementById('bundle_search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const bundleCards = document.querySelectorAll('.bundle-card');
            
            bundleCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Phone input validation
        document.querySelector('input[name="phone_number"]').addEventListener('input', checkFormValidity);
        
        function showQuickActions() {
            alert('Quick actions menu would appear here');
        }
    </script>
</body>
</html>
