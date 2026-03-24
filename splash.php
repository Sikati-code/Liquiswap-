<?php
require_once 'includes/config.php';

// Check if user is already logged in
if ($auth->isLoggedIn()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LiquiSwap - Loading</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/animations.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0A0F0F 0%, #1a2a2a 100%);
            color: #ffffff;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }
        
        .splash-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        
        .header {
            position: absolute;
            top: 30px;
            left: 30px;
            right: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #008080;
            text-decoration: none;
        }
        
        .settings-icon {
            width: 30px;
            height: 30px;
            opacity: 0.7;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        
        .settings-icon:hover {
            opacity: 1;
        }
        
        .blob-container {
            position: relative;
            width: 300px;
            height: 300px;
            margin-bottom: 50px;
        }
        
        .liquid-blob {
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #008080, #00a8a8, #008080);
            border-radius: 50%;
            position: relative;
            animation: blobMove 4s ease-in-out infinite, pulse 2s ease-in-out infinite;
            box-shadow: 0 20px 60px rgba(0, 128, 128, 0.3);
        }
        
        .blob-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 2;
        }
        
        .flowing-percentage {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 5px;
        }
        
        .flowing-text {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .status-section {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .status-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #008080;
        }
        
        .status-description {
            font-size: 14px;
            opacity: 0.7;
            max-width: 300px;
            margin: 0 auto 30px;
            line-height: 1.5;
        }
        
        .progress-container {
            max-width: 300px;
            margin: 0 auto;
        }
        
        .progress-label {
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 10px;
            text-align: left;
        }
        
        .progress {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #008080, #00a8a8);
            height: 100%;
            border-radius: 10px;
            animation: progressGrow 2s ease-out forwards;
            position: relative;
        }
        
        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        
        .status-indicator {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(0, 255, 0, 0.1);
            border: 1px solid rgba(0, 255, 0, 0.3);
            border-radius: 20px;
            font-size: 11px;
            color: #00ff00;
            margin-top: 10px;
        }
        
        .status-info {
            position: absolute;
            bottom: 40px;
            left: 30px;
            right: 30px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            opacity: 0.6;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .info-dot {
            width: 6px;
            height: 6px;
            background: #00ff00;
            border-radius: 50%;
            animation: blink 2s infinite;
        }
        
        @keyframes blobMove {
            0%, 100% { transform: scale(1) rotate(0deg); }
            25% { transform: scale(1.05) rotate(90deg); }
            50% { transform: scale(1.1) rotate(180deg); }
            75% { transform: scale(1.05) rotate(270deg); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 1; }
        }
        
        @keyframes progressGrow {
            0% { width: 0%; }
            100% { width: 85%; }
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        .fade-in {
            animation: fadeIn 1s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="splash-container">
        <div class="header">
            <a href="index.php" class="logo">LiquiSwap</a>
            <svg class="settings-icon" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
            </svg>
        </div>
        
        <div class="blob-container fade-in">
            <div class="liquid-blob"></div>
            <div class="blob-text">
                <div class="flowing-percentage">65% FLOWING</div>
                <div class="flowing-text">LIQUIDITY ACTIVE</div>
            </div>
        </div>
        
        <div class="status-section fade-in">
            <div class="status-title">Initializing Protocol</div>
            <div class="status-description">
                Connecting to the deep abyss of decentralized liquidity pools...
            </div>
            
            <div class="progress-container">
                <div class="progress-label">LIQUIDITY DEPTH CHECK</div>
                <div class="progress">
                    <div class="progress-bar" style="width: 85%;"></div>
                </div>
                <div class="status-indicator">OPTIMAL</div>
            </div>
        </div>
        
        <div class="status-info">
            <div class="info-item">
                <div class="info-dot"></div>
                <span>GAS PRICE 12 Gwei</span>
            </div>
            <div class="info-item">
                <div class="info-dot"></div>
                <span>LATENCY 24ms</span>
            </div>
            <div class="info-item">
                <div class="info-dot"></div>
                <span>Mainnet Connected</span>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <button onclick="skipToLogin()" class="btn btn-outline-light">
                <i class="fas fa-arrow-right me-2"></i>Continue to Login
            </button>
        </div>
    </div>
    
    <script>
        // Auto-redirect after 5 seconds
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 5000);
        
        // Simulate loading progress
        let progress = 0;
        const progressBar = document.querySelector('.progress-bar');
        const percentageText = document.querySelector('.flowing-percentage');
        
        const loadingInterval = setInterval(() => {
            progress += Math.random() * 10;
            if (progress >= 100) {
                progress = 100;
                clearInterval(loadingInterval);
            }
            
            progressBar.style.width = progress + '%';
            percentageText.textContent = Math.round(progress) + '% FLOWING';
        }, 500);
        
        // Manual redirect option
        function skipToLogin() {
            window.location.href = 'login.php';
        }
    </script>
</body>
</html>
