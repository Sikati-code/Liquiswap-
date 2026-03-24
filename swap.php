<?php
require_once 'includes/config.php';
requireLogin();

$user = $auth->getUser();
$wallets = $auth->getUserWallets();

// Handle swap creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_swap') {
    $from_wallet = sanitize($_POST['from_wallet']);
    $to_wallet = sanitize($_POST['to_wallet']);
    $amount = floatval($_POST['amount']);
    $recipient_phone = sanitize($_POST['recipient_phone']);
    
    // Validate inputs
    if (empty($from_wallet) || empty($to_wallet) || $amount <= 0 || empty($recipient_phone)) {
        $error = 'Please fill in all required fields';
    } else {
        // Get wallet details
        $from_wallet_query = "SELECT * FROM wallets WHERE id = :id AND user_id = :user_id";
        $stmt = $db->prepare($from_wallet_query);
        $stmt->bindParam(':id', $from_wallet);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $from_wallet_data = $stmt->fetch();
        
        if (!$from_wallet_data || $from_wallet_data['balance'] < $amount) {
            $error = 'Insufficient balance';
        } else {
            // Calculate fee
            $fee = calculateSwapFee($amount);
            $total_deductible = $amount + $fee;
            
            if ($from_wallet_data['balance'] < $total_deductible) {
                $error = 'Insufficient balance including fees';
            } else {
                try {
                    $db->beginTransaction();
                    
                    // Create transaction record
                    $transaction_uuid = generateUUID();
                    $subtype = $from_wallet_data['provider'] . '_to_' . sanitize($_POST['to_provider']);
                    
                    $transaction_query = "INSERT INTO transactions 
                        (transaction_uuid, user_id, type, subtype, amount, fee, receiver_identifier, operator, status, reference) 
                        VALUES (:uuid, :user_id, 'swap', :subtype, :amount, :fee, :recipient, :operator, 'pending', :reference)";
                    
                    $stmt = $db->prepare($transaction_query);
                    $stmt->bindParam(':uuid', $transaction_uuid);
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->bindParam(':subtype', $subtype);
                    $stmt->bindParam(':amount', $amount);
                    $stmt->bindParam(':fee', $fee);
                    $stmt->bindParam(':recipient', $recipient_phone);
                    $stmt->bindParam(':operator', $from_wallet_data['provider']);
                    $stmt->bindParam(':reference', $transaction_uuid);
                    $stmt->execute();
                    
                    $transaction_id = $db->lastInsertId();
                    
                    // Update wallet balance
                    $new_balance = $from_wallet_data['balance'] - $total_deductible;
                    $update_wallet = "UPDATE wallets SET balance = :balance WHERE id = :id";
                    $stmt = $db->prepare($update_wallet);
                    $stmt->bindParam(':balance', $new_balance);
                    $stmt->bindParam(':id', $from_wallet);
                    $stmt->execute();
                    
                    // Create escrow record
                    $escrow_query = "INSERT INTO escrow (swap_transaction_id, buyer_id, seller_id, amount, status) 
                                     VALUES (:transaction_id, :buyer_id, :seller_id, :amount, 'pending')";
                    $stmt = $db->prepare($escrow_query);
                    $stmt->bindParam(':transaction_id', $transaction_id);
                    $stmt->bindParam(':buyer_id', $_SESSION['user_id']);
                    $stmt->bindParam(':seller_id', $_SESSION['user_id']); // In real app, this would be different
                    $stmt->bindParam(':amount', $amount);
                    $stmt->execute();
                    
                    $db->commit();
                    
                    redirect('swap.php?success=1&reference=' . $transaction_uuid, 'Swap initiated successfully!', 'success');
                    
                } catch (Exception $e) {
                    $db->rollback();
                    $error = 'Swap failed. Please try again.';
                    error_log("Swap error: " . $e->getMessage());
                }
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
    <title>Swap - LiquiSwap</title>
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
        
        .swap-container {
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
        
        .swap-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .swap-section {
            margin-bottom: 25px;
        }
        
        .section-label {
            font-size: 14px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 15px;
        }
        
        .wallet-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .wallet-option {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .wallet-option:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        
        .wallet-option.selected {
            background: rgba(0, 128, 128, 0.2);
            border-color: #008080;
        }
        
        .wallet-provider {
            font-size: 12px;
            opacity: 0.7;
            margin-bottom: 5px;
        }
        
        .wallet-balance {
            font-size: 16px;
            font-weight: 600;
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
        
        .swap-arrow {
            text-align: center;
            margin: 20px 0;
            font-size: 24px;
            color: #008080;
        }
        
        .fee-calculator {
            background: rgba(0, 128, 128, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .fee-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .fee-row.total {
            font-weight: 600;
            font-size: 16px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #008080, #00a8a8);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 128, 128, 0.3);
        }
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="swap-container">
            <div class="page-header">
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
                <div class="page-title">OM ↔ MOMO Swap</div>
                <div></div>
            </div>
            
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>" role="alert">
                    <?= $flash['message'] ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="swap-card">
                    <div class="success-animation">
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <h3>Swap Initiated!</h3>
                        <p class="text-muted">Reference: <?= $_GET['reference'] ?></p>
                        <p class="text-muted">Your swap has been initiated and is being processed.</p>
                        <a href="dashboard.php" class="btn btn-primary mt-3">
                            Return to Dashboard
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="action" value="create_swap">
                    <input type="hidden" name="to_provider" id="to_provider">
                    
                    <div class="swap-card">
                        <div class="swap-section">
                            <div class="section-label">1. Select Source Wallet</div>
                            <div class="wallet-selector">
                                <?php foreach ($wallets as $wallet): ?>
                                    <div class="wallet-option" onclick="selectWallet('from', <?= $wallet['id'] ?>, '<?= $wallet['provider'] ?>', <?= $wallet['balance'] ?>)">
                                        <div class="wallet-provider"><?= $wallet['provider'] ?></div>
                                        <div class="wallet-balance"><?= number_format($wallet['balance'] ?? 0, 0, '.', ',') ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="from_wallet" id="from_wallet" required>
                        </div>
                        
                        <div class="swap-arrow">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        
                        <div class="swap-section">
                            <div class="section-label">2. Select Destination</div>
                            <div class="wallet-selector">
                                <div class="wallet-option" onclick="selectWallet('to', 'MTN', 'MTN', 0)">
                                    <div class="wallet-provider">MTN MoMo</div>
                                    <div class="wallet-balance">Receive</div>
                                </div>
                                <div class="wallet-option" onclick="selectWallet('to', 'ORANGE', 'Orange', 0)">
                                    <div class="wallet-provider">Orange Money</div>
                                    <div class="wallet-balance">Receive</div>
                                </div>
                                <div class="wallet-option" onclick="selectWallet('to', 'BANK', 'Bank', 0)">
                                    <div class="wallet-provider">Bank Account</div>
                                    <div class="wallet-balance">Receive</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="swap-section">
                            <div class="section-label">3. Enter Amount</div>
                            <input type="number" class="form-control" name="amount" id="amount" placeholder="Enter amount" min="100" step="100" required oninput="calculateFee()">
                        </div>
                        
                        <div class="swap-section">
                            <div class="section-label">4. Recipient Phone Number</div>
                            <div class="input-group">
                                <span class="input-group-text">+237</span>
                                <input type="tel" class="form-control" name="recipient_phone" placeholder="6XXXXXXXX" required>
                            </div>
                        </div>
                        
                        <div class="fee-calculator" id="fee_calculator" style="display: none;">
                            <div class="fee-row">
                                <span>Amount:</span>
                                <span id="display_amount">0 XAF</span>
                            </div>
                            <div class="fee-row">
                                <span>Service Fee (1.5%):</span>
                                <span id="display_fee">0 XAF</span>
                            </div>
                            <div class="fee-row total">
                                <span>Total Deductible:</span>
                                <span id="display_total">0 XAF</span>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" id="submit_btn" disabled>
                            Initiate Swap
                        </button>
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
            
            <a href="swap.php" class="nav-item active">
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
        let selectedFromWallet = null;
        let selectedToWallet = null;
        
        function selectWallet(type, walletId, provider, balance) {
            // Remove previous selections
            document.querySelectorAll('.wallet-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selection to clicked wallet
            event.target.closest('.wallet-option').classList.add('selected');
            
            if (type === 'from') {
                selectedFromWallet = { id: walletId, provider: provider, balance: balance };
                document.getElementById('from_wallet').value = walletId;
            } else {
                selectedToWallet = { id: walletId, provider: provider };
                document.getElementById('to_provider').value = provider;
            }
            
            checkFormValidity();
        }
        
        function calculateFee() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            
            if (amount > 0) {
                const fee = amount * 0.015; // 1.5% fee
                const total = amount + fee;
                
                document.getElementById('display_amount').textContent = amount.toLocaleString() + ' XAF';
                document.getElementById('display_fee').textContent = fee.toLocaleString() + ' XAF';
                document.getElementById('display_total').textContent = total.toLocaleString() + ' XAF';
                document.getElementById('fee_calculator').style.display = 'block';
                
                // Check if user has sufficient balance
                if (selectedFromWallet && amount > selectedFromWallet.balance) {
                    document.getElementById('submit_btn').disabled = true;
                } else {
                    checkFormValidity();
                }
            } else {
                document.getElementById('fee_calculator').style.display = 'none';
            }
        }
        
        function checkFormValidity() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const recipientPhone = document.querySelector('input[name="recipient_phone"]').value;
            
            if (selectedFromWallet && selectedToWallet && amount > 0 && recipientPhone.length >= 9) {
                if (amount <= selectedFromWallet.balance) {
                    document.getElementById('submit_btn').disabled = false;
                } else {
                    document.getElementById('submit_btn').disabled = true;
                }
            } else {
                document.getElementById('submit_btn').disabled = true;
            }
        }
        
        // Add input listener for recipient phone
        document.querySelector('input[name="recipient_phone"]').addEventListener('input', checkFormValidity);
    </script>
</body>
</html>
