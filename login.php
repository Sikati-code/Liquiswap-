<?php
require_once 'includes/config.php';

// Check if user is already logged in
if ($auth->isLoggedIn()) {
    redirect('dashboard.php');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($phone, $password)) {
        redirect('dashboard.php', 'Welcome back!', 'success');
    } else {
        $error = 'Invalid phone number or password';
    }
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $data = [
        'full_name' => sanitize($_POST['full_name'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'phone_number' => sanitize($_POST['phone_number'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'mtn_phone' => sanitize($_POST['mtn_phone'] ?? ''),
        'orange_phone' => sanitize($_POST['orange_phone'] ?? ''),
        'bank_account' => sanitize($_POST['bank_account'] ?? ''),
        'bank_balance' => floatval($_POST['bank_balance'] ?? 0)
    ];
    
    if ($auth->register($data)) {
        redirect('login.php', 'Account created successfully! Please login.', 'success');
    } else {
        $register_error = 'Registration failed. Please try again.';
    }
}

$flash = getFlashMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LiquiSwap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0A0F0F 0%, #1a2a2a 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #ffffff;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo {
            font-size: 32px;
            font-weight: 700;
            color: #008080;
            margin-bottom: 10px;
        }
        
        .tagline {
            font-size: 14px;
            opacity: 0.7;
        }
        
        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.05);
            padding: 5px;
            border-radius: 12px;
        }
        
        .tab-btn {
            flex: 1;
            padding: 12px;
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
        
        .form-floating {
            margin-bottom: 20px;
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
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .form-floating label {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: #008080;
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
        
        .btn-outline-primary {
            border: 1px solid #008080;
            color: #008080;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-outline-primary:hover {
            background: #008080;
            border-color: #008080;
        }
        
        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .divider span {
            background: #0A0F0F;
            padding: 0 20px;
            position: relative;
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }
        
        .biometric-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 14px;
            color: white;
            width: 100%;
            transition: all 0.3s;
        }
        
        .biometric-btn:hover {
            background: rgba(255, 255, 255, 0.08);
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
        
        .input-group-text {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
        }
        
        .form-check-input:checked {
            background-color: #008080;
            border-color: #008080;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo">LiquiSwap</div>
                <div class="tagline">Instant P2P Financial Exchange</div>
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
            
            <?php if (isset($register_error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $register_error ?>
                </div>
            <?php endif; ?>
            
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="switchTab('signin')">Sign In</button>
                <button class="tab-btn" onclick="switchTab('signup')">Create Account</button>
            </div>
            
            <!-- Sign In Tab -->
            <div id="signin-tab" class="tab-content active">
                <form method="POST">
                    <div class="form-floating">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-phone"></i>
                            </span>
                            <input type="tel" class="form-control" name="phone" placeholder="Phone Number" required>
                        </div>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary mb-3">
                        Sign In
                    </button>
                </form>
                
                <div class="divider">
                    <span>OR</span>
                </div>
                
                <button class="btn biometric-btn mb-3" onclick="biometricLogin()">
                    <i class="fas fa-fingerprint me-2"></i>
                    Sign in with Face ID
                </button>
                
                <div class="text-center">
                    <a href="#" class="text-decoration-none" style="color: #008080; font-size: 14px;">
                        Forgot Password?
                    </a>
                </div>
            </div>
            
            <!-- Sign Up Tab -->
            <div id="signup-tab" class="tab-content">
                <form method="POST">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-floating">
                        <input type="text" class="form-control" name="full_name" placeholder="Full Name" required>
                        <label for="full_name">Full Name</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="email" class="form-control" name="email" placeholder="Email Address">
                        <label for="email">Email Address (Optional)</label>
                    </div>
                    
                    <div class="form-floating">
                        <div class="input-group">
                            <span class="input-group-text">+237</span>
                            <input type="tel" class="form-control" name="phone_number" placeholder="6XXXXXXXX" required>
                        </div>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" name="password_confirm" placeholder="Confirm Password" required>
                        <label for="password_confirm">Confirm Password</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="tel" class="form-control" name="mtn_phone" placeholder="MTN Number">
                        <label for="mtn_phone">MTN Number (Optional)</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="tel" class="form-control" name="orange_phone" placeholder="Orange Number">
                        <label for="orange_phone">Orange Number (Optional)</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="text" class="form-control" name="bank_account" placeholder="Bank Account">
                        <label for="bank_account">Bank Account (Optional)</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="number" class="form-control" name="bank_balance" placeholder="0" step="0.01">
                        <label for="bank_balance">Initial Bank Balance (Optional)</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/utils.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="assets/js/interactive.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching functionality
            window.switchTab = function(tab) {
                // Remove active classes
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Add active classes
                if (tab === 'signin') {
                    document.getElementById('signin-tab').classList.add('active');
                    document.querySelectorAll('.tab-btn')[0].classList.add('active');
                } else {
                    document.getElementById('signup-tab').classList.add('active');
                    document.querySelectorAll('.tab-btn')[1].classList.add('active');
                }
            };
            
            // Biometric login
            window.biometricLogin = function() {
                if ('credentials' in navigator) {
                    Interactive.showToast('Biometric authentication would be triggered here', 'info');
                } else {
                    Interactive.showToast('Biometric authentication is not supported on this device', 'warning');
                }
            };
            
            // Add floating label functionality
            document.querySelectorAll('.form-group').forEach(group => {
                const input = group.querySelector('.form-control');
                if (input) {
                    input.addEventListener('focus', function() {
                        group.classList.add('focused');
                    });
                    
                    input.addEventListener('blur', function() {
                        if (!this.value) {
                            group.classList.remove('focused');
                        }
                    });
                    
                    // Check initial value
                    if (input.value) {
                        group.classList.add('focused');
                    }
                }
            });
            
            // Form submissions handled by Interactive.js
            const loginForm = document.querySelector('#loginForm');
            if (loginForm) {
                loginForm.dataset.action = 'login';
                loginForm.dataset.ajax = 'true';
            }
            
            const registerForm = document.querySelector('#registerForm');
            if (registerForm) {
                registerForm.dataset.action = 'register';
                registerForm.dataset.ajax = 'true';
            }
        });
    </script>
</body>
</html>
