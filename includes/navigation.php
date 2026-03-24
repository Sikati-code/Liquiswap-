<?php
/**
 * Navigation Component
 * Responsive navigation with mobile menu support
 */

// Check if user is logged in for navigation items
$isLoggedIn = isset($auth) && $auth->isLoggedIn();
$user = $isLoggedIn ? $auth->getUser() : null;
?>

<!-- Navigation Header -->
<nav class="main-nav">
    <div class="container">
        <div class="nav-content">
            <!-- Logo -->
            <div class="nav-logo">
                <a href="<?= $isLoggedIn ? 'dashboard.php' : 'index.php' ?>" class="logo">
                    <i class="fas fa-exchange-alt"></i> LiquiSwap
                </a>
            </div>
            
            <!-- Desktop Menu -->
            <div class="nav-menu desktop-menu">
                <?php if ($isLoggedIn): ?>
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="swap.php" class="nav-link">Swap</a>
                    <a href="bundles.php" class="nav-link">Bundles</a>
                    <a href="history.php" class="nav-link">History</a>
                    <a href="profile.php" class="nav-link">Profile</a>
                    <a href="settings.php" class="nav-link">Settings</a>
                <?php else: ?>
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="login.php#register" class="nav-link">Register</a>
                <?php endif; ?>
            </div>
            
            <!-- User Actions -->
            <div class="nav-actions">
                <?php if ($isLoggedIn): ?>
                    <!-- Notifications -->
                    <div class="notification-wrapper">
                        <button class="notification-icon" data-tooltip="Notifications">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="user-menu">
                        <div class="user-avatar" data-tooltip="<?= htmlspecialchars($user['full_name']) ?>">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['full_name']) ?>&background=008080&color=fff&size=32" alt="Avatar">
                        </div>
                        <div class="user-dropdown">
                            <div class="user-info">
                                <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
                                <div class="user-phone"><?= htmlspecialchars($user['phone_number']) ?></div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i> Profile
                            </a>
                            <a href="settings.php" class="dropdown-item">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <a href="logout.php" class="dropdown-item" data-action="logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-sm">Get Started</a>
                <?php endif; ?>
                
                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="mobile-menu">
            <?php if ($isLoggedIn): ?>
                <div class="mobile-user-info">
                    <div class="user-avatar">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['full_name']) ?>&background=008080&color=fff&size=40" alt="Avatar">
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
                        <div class="user-phone"><?= htmlspecialchars($user['phone_number']) ?></div>
                    </div>
                </div>
                <div class="mobile-nav-divider"></div>
            <?php endif; ?>
            
            <div class="mobile-nav-links">
                <?php if ($isLoggedIn): ?>
                    <a href="dashboard.php" class="mobile-nav-link">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="swap.php" class="mobile-nav-link">
                        <i class="fas fa-exchange-alt"></i> Swap
                    </a>
                    <a href="bundles.php" class="mobile-nav-link">
                        <i class="fas fa-box"></i> Bundles
                    </a>
                    <a href="history.php" class="mobile-nav-link">
                        <i class="fas fa-history"></i> History
                    </a>
                    <a href="profile.php" class="mobile-nav-link">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="settings.php" class="mobile-nav-link">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <a href="logout.php" class="mobile-nav-link" data-action="logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="index.php" class="mobile-nav-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="login.php" class="mobile-nav-link">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="login.php#register" class="mobile-nav-link">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Navigation Styles -->
<style>
.main-nav {
    background: rgba(10, 15, 15, 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

.nav-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 0;
}

.nav-logo .logo {
    font-size: 24px;
    font-weight: 700;
    color: var(--teal);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
}

.nav-menu {
    display: flex;
    align-items: center;
    gap: 30px;
}

.nav-link {
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
    position: relative;
}

.nav-link:hover,
.nav-link.active {
    color: var(--teal);
}

.nav-link::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--teal);
    transition: width 0.3s ease;
}

.nav-link:hover::after,
.nav-link.active::after {
    width: 100%;
}

.nav-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.notification-icon {
    position: relative;
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: 18px;
    transition: color 0.3s ease;
}

.notification-icon:hover {
    color: var(--teal);
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--danger);
    color: white;
    font-size: 10px;
    padding: 2px 5px;
    border-radius: 10px;
    min-width: 16px;
    text-align: center;
}

.user-menu {
    position: relative;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid var(--teal);
    transition: transform 0.3s ease;
}

.user-avatar:hover {
    transform: scale(1.05);
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 15px;
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    margin-top: 10px;
}

.user-menu:hover .user-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.user-info {
    margin-bottom: 10px;
}

.user-name {
    font-weight: 600;
    color: var(--text-primary);
}

.user-phone {
    font-size: 12px;
    color: var(--text-secondary);
}

.dropdown-divider {
    height: 1px;
    background: var(--border-color);
    margin: 10px 0;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-secondary);
    text-decoration: none;
    padding: 8px 0;
    transition: color 0.3s ease;
}

.dropdown-item:hover {
    color: var(--teal);
}

.mobile-menu-toggle {
    display: none;
    flex-direction: column;
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
}

.mobile-menu-toggle span {
    width: 25px;
    height: 2px;
    background: var(--text-primary);
    margin: 3px 0;
    transition: 0.3s;
}

.mobile-menu-toggle.active span:nth-child(1) {
    transform: rotate(-45deg) translate(-5px, 6px);
}

.mobile-menu-toggle.active span:nth-child(2) {
    opacity: 0;
}

.mobile-menu-toggle.active span:nth-child(3) {
    transform: rotate(45deg) translate(-5px, -6px);
}

.mobile-menu {
    display: none;
    padding-top: 20px;
    border-top: 1px solid var(--border-color);
    margin-top: 20px;
}

.mobile-menu.active {
    display: block;
}

.mobile-user-info {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: rgba(0, 128, 128, 0.1);
    border-radius: 12px;
    margin-bottom: 20px;
}

.mobile-user-info .user-avatar {
    width: 50px;
    height: 50px;
}

.mobile-nav-divider {
    height: 1px;
    background: var(--border-color);
    margin: 15px 0;
}

.mobile-nav-links {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.mobile-nav-link {
    display: flex;
    align-items: center;
    gap: 15px;
    color: var(--text-secondary);
    text-decoration: none;
    padding: 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.mobile-nav-link:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--teal);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .desktop-menu {
        display: none;
    }
    
    .mobile-menu-toggle {
        display: flex;
    }
    
    .nav-content {
        padding: 10px 0;
    }
    
    .nav-logo .logo {
        font-size: 20px;
    }
    
    .notification-icon {
        font-size: 16px;
    }
    
    .user-avatar {
        width: 35px;
        height: 35px;
    }
}

@media (max-width: 576px) {
    .nav-actions {
        gap: 10px;
    }
    
    .nav-logo .logo {
        font-size: 18px;
    }
    
    .mobile-nav-link {
        padding: 12px;
        font-size: 14px;
    }
}
</style>
