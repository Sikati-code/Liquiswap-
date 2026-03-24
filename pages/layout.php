<?php
/**
 * Base Layout Template
 * All pages are wrapped in this layout unless they output full HTML
 */
?>
<!DOCTYPE html>
<html lang="en" class="dark" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($currentMeta['title']); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/animations.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
    
    <!-- PWA Meta -->
    <meta name="theme-color" content="#007f80">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
    
    <!-- App Data -->
    <script>
        window.APP_CONFIG = {
            name: '<?php echo APP_NAME; ?>',
            url: '<?php echo APP_URL; ?>',
            currency: '<?php echo CURRENCY_CODE; ?>',
            currencySymbol: '<?php echo CURRENCY_SYMBOL; ?>',
            phonePrefix: '<?php echo PHONE_PREFIX; ?>',
            isLoggedIn: <?php echo $isLoggedIn ? 'true' : 'false'; ?>,
            csrfToken: '<?php echo htmlspecialchars($csrfToken); ?>'
        };
        
        <?php if ($currentUser): ?>
        window.CURRENT_USER = {
            id: <?php echo $currentUser['id']; ?>,
            name: '<?php echo htmlspecialchars($currentUser['full_name']); ?>',
            phone: '<?php echo htmlspecialchars($currentUser['phone_number']); ?>',
            trustScore: <?php echo $currentUser['trust_score']; ?>,
            role: '<?php echo $currentUser['role']; ?>'
        };
        <?php endif; ?>
    </script>
</head>
<body class="<?php echo $currentMeta['bodyClass']; ?>">
    <!-- Toast Container -->
    <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay d-none">
        <div class="spinner-container">
            <div class="liquid-spinner"></div>
            <p class="mt-3 text-primary">Processing...</p>
        </div>
    </div>
    
    <!-- Page Content -->
    <main id="main-content" class="main-content">
        <?php echo $content; ?>
    </main>
    
    <!-- Bottom Navigation (mobile) - shown on protected pages -->
    <?php if ($isLoggedIn && in_array($page, ['dashboard', 'swap', 'bundles', 'history', 'profile'])): ?>
    <nav class="bottom-nav d-md-none">
        <a href="/dashboard" class="nav-item <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">grid_view</span>
            <span class="nav-label">Dash</span>
        </a>
        <a href="/swap" class="nav-item <?php echo $page === 'swap' ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">swap_calls</span>
            <span class="nav-label">Swap</span>
        </a>
        <div class="nav-item nav-center">
            <button class="btn-add" onclick="window.location.href='/swap'">
                <span class="material-symbols-outlined">add</span>
            </button>
        </div>
        <a href="/history" class="nav-item <?php echo $page === 'history' ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">history</span>
            <span class="nav-label">History</span>
        </a>
        <a href="/profile" class="nav-item <?php echo $page === 'profile' ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">person</span>
            <span class="nav-label">Profile</span>
        </a>
    </nav>
    <?php endif; ?>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/utils.js"></script>
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/app.js"></script>
    
    <?php if (file_exists(__DIR__ . '/../assets/js/' . $page . '.js')): ?>
    <script src="/assets/js/<?php echo $page; ?>.js"></script>
    <?php endif; ?>
</body>
</html>
