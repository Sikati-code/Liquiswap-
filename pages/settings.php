<!-- Settings Page -->
<div class="settings-page min-vh-100 position-relative" style="background: #0a0f0f;">
    <!-- Header -->
    <header class="sticky-top z-50 glass border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center" style="max-width: 1400px; margin: 0 auto;">
            <div class="d-flex align-items-center gap-3">
                <a href="/dashboard" class="btn btn-icon">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="fs-5 fw-bold mb-0">Settings</h1>
                    <p class="fs-8 text-secondary mb-0">Manage your preferences</p>
                </div>
            </div>
        </div>
    </header>

    <div class="main-content-area p-4" style="max-width: 1000px; margin: 0 auto;">
        <!-- Account Section -->
        <div class="glass-card p-4 mb-4">
            <h3 class="fs-6 fw-bold mb-4 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary">account_circle</span>
                Account
            </h3>
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center p-3 rounded-lg cursor-pointer" style="background: rgba(0,127,128,0.05);" onclick="editPersonalInfo()">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(0,127,128,0.1);">
                            <span class="material-symbols-outlined text-primary">person</span>
                        </div>
                        <div>
                            <p class="fs-6 fw-semibold mb-0">Personal Info</p>
                            <p class="fs-8 text-secondary mb-0">Name, email, phone</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-secondary">chevron_right</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center p-3 rounded-lg cursor-pointer" style="background: rgba(0,127,128,0.05);" onclick="manageWallets()">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(0,127,128,0.1);">
                            <span class="material-symbols-outlined text-primary">account_balance_wallet</span>
                        </div>
                        <div>
                            <p class="fs-6 fw-semibold mb-0">Linked Wallets</p>
                            <p class="fs-8 text-secondary mb-0">4 wallets connected</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-secondary">chevron_right</span>
                </div>
            </div>
        </div>

        <!-- Preferences Section -->
        <div class="glass-card p-4 mb-4">
            <h3 class="fs-6 fw-bold mb-4 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-orange">tune</span>
                Preferences
            </h3>
            <div class="d-flex flex-column gap-3">
                <!-- Notifications -->
                <div class="d-flex justify-content-between align-items-center p-3 rounded-lg">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(249,115,22,0.1);">
                            <span class="material-symbols-outlined text-orange">notifications</span>
                        </div>
                        <div>
                            <p class="fs-6 fw-semibold mb-0">Push Notifications</p>
                            <p class="fs-8 text-secondary mb-0">Transaction alerts, updates</p>
                        </div>
                    </div>
                    <label class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" checked style="width: 3rem; height: 1.5rem; background-color: #334155; border-color: #475569;">
                    </label>
                </div>
                
                <!-- Dark Mode -->
                <div class="d-flex justify-content-between align-items-center p-3 rounded-lg">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(251,191,36,0.1);">
                            <span class="material-symbols-outlined text-warning">dark_mode</span>
                        </div>
                        <div>
                            <p class="fs-6 fw-semibold mb-0">Dark Mode</p>
                            <p class="fs-8 text-secondary mb-0">Easier on the eyes</p>
                        </div>
                    </div>
                    <label class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" checked style="width: 3rem; height: 1.5rem; background-color: #334155; border-color: #475569;">
                    </label>
                </div>
                
                <!-- Language -->
                <div class="d-flex justify-content-between align-items-center p-3 rounded-lg cursor-pointer" style="background: rgba(0,127,128,0.05);" onclick="changeLanguage()">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(0,127,128,0.1);">
                            <span class="material-symbols-outlined text-primary">language</span>
                        </div>
                        <div>
                            <p class="fs-6 fw-semibold mb-0">Language</p>
                            <p class="fs-8 text-secondary mb-0">English (US)</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-secondary">chevron_right</span>
                </div>
            </div>
        </div>

        <!-- Security Section -->
        <div class="glass-card p-4 mb-4">
            <h3 class="fs-6 fw-bold mb-4 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-success">security</span>
                Security
            </h3>
            <div class="d-flex flex-column gap-3">
                <!-- Biometric Login -->
                <div class="d-flex justify-content-between align-items-center p-3 rounded-lg">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(34,197,94,0.1);">
                            <span class="material-symbols-outlined text-success">face</span>
                        </div>
                        <div>
                            <p class="fs-6 fw-semibold mb-0">Biometric Login</p>
                            <p class="fs-8 text-secondary mb-0">Face ID, fingerprint</p>
                        </div>
                    </div>
                    <label class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" checked style="width: 3rem; height: 1.5rem; background-color: #334155; border-color: #475569;">
                    </label>
                </div>
                
                <!-- Change Password -->
                <div class="d-flex justify-content-between align-items-center p-3 rounded-lg cursor-pointer" style="background: rgba(0,127,128,0.05);" onclick="changePassword()">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(0,127,128,0.1);">
                            <span class="material-symbols-outlined text-primary">password</span>
                        </div>
                        <div>
                            <p class="fs-6 fw-semibold mb-0">Change Password</p>
                            <p class="fs-8 text-secondary mb-0">Last changed 30 days ago</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-secondary">chevron_right</span>
                </div>
                
                <!-- 2FA -->
                <div class="d-flex justify-content-between align-items-center p-3 rounded-lg cursor-pointer" style="background: rgba(0,127,128,0.05);" onclick="setup2FA()">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(139,92,246,0.1);">
                            <span class="material-symbols-outlined text-purple">phonelink_lock</span>
                        </div>
                        <div>
                            <p class="fs-6 fw-semibold mb-0">Two-Factor Auth</p>
                            <p class="fs-8 text-secondary mb-0">Add extra security layer</p>
                        </div>
                    </div>
                    <span class="badge badge-deal">Setup</span>
                </div>
            </div>
        </div>

        <!-- Support Section -->
        <div class="glass-card p-4 mb-4">
            <h3 class="fs-6 fw-bold mb-4 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-secondary">support_agent</span>
                Support
            </h3>
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center p-3 rounded-lg cursor-pointer" style="background: rgba(0,127,128,0.05);" onclick="openHelpCenter()">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(100,116,139,0.1);">
                            <span class="material-symbols-outlined text-secondary">help_center</span>
                        </div>
                        <div>
                            <p class="fs-6 fw-semibold mb-0">Help Center</p>
                            <p class="fs-8 text-secondary mb-0">FAQs, guides, tutorials</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-secondary">chevron_right</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center p-3 rounded-lg cursor-pointer" style="background: rgba(0,127,128,0.05);" onclick="contactSupport()">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(100,116,139,0.1);">
                            <span class="material-symbols-outlined text-secondary">contact_support</span>
                        </div>
                        <div>
                            <p class="fs-6 fw-semibold mb-0">Contact Us</p>
                            <p class="fs-8 text-secondary mb-0">Get help from our team</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-secondary">chevron_right</span>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="glass-card p-4 mb-4" style="border: 1px solid rgba(239,68,68,0.2);">
            <h3 class="fs-6 fw-bold mb-4 d-flex align-items-center gap-2" style="color: #ef4444;">
                <span class="material-symbols-outlined">warning</span>
                Danger Zone
            </h3>
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center p-3 rounded-lg" style="background: rgba(239,68,68,0.05);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(239,68,68,0.1);">
                            <span class="material-symbols-outlined text-danger">logout</span>
                        </div>
                        <div>
                            <p class="fs-6 fw-semibold mb-0">Log Out</p>
                            <p class="fs-8 text-secondary mb-0">Sign out from all devices</p>
                        </div>
                    </div>
                    <button class="btn btn-glass px-3 py-2" onclick="logout()">Log Out</button>
                </div>
            </div>
        </div>

        <!-- App Info -->
        <div class="text-center py-4">
            <p class="fs-8 text-secondary mb-1">LiquiSwap v1.0.0</p>
            <p class="fs-9 text-secondary">© 2024 LiquiSwap. All rights reserved.</p>
        </div>
    </div>
</div>

<style>
.fs-7 {
    font-size: 0.875rem;
}

.fs-8 {
    font-size: 0.75rem;
}

.fs-9 {
    font-size: 0.625rem;
}

.badge-deal {
    background: rgba(0, 127, 128, 0.2);
    color: #007f80;
    border: 1px solid rgba(0, 127, 128, 0.3);
    font-size: 0.625rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.25rem 0.5rem;
    border-radius: 999px;
}

.text-purple {
    color: #8b5cf6 !important;
}

.form-check-input:checked {
    background-color: #007f80;
    border-color: #007f80;
}

.form-check-input {
    cursor: pointer;
}
</style>

<script>
(function() {
    // Settings handlers
    window.editPersonalInfo = function() {
        Utils.toast.info('Personal info editor coming soon');
    };
    
    window.manageWallets = function() {
        Utils.toast.info('Wallet management coming soon');
    };
    
    window.changeLanguage = function() {
        Utils.toast.info('Language selection coming soon');
    };
    
    window.changePassword = function() {
        Utils.toast.info('Password change coming soon');
    };
    
    window.setup2FA = function() {
        Utils.toast.info('2FA setup coming soon');
    };
    
    window.openHelpCenter = function() {
        Utils.toast.info('Help center coming soon');
    };
    
    window.contactSupport = function() {
        Utils.toast.info('Support contact coming soon');
    };
    
    window.logout = function() {
        if (confirm('Are you sure you want to log out?')) {
            Utils.toast.info('Logging out...');
            setTimeout(() => window.location.href = '/login', 1000);
        }
    };
})();
</script>
