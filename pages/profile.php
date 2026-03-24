<!-- Profile Page -->
<div class="profile-page min-vh-100 position-relative" style="background: #0a0f0f;">
    <!-- Header -->
    <header class="sticky-top z-50 glass border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center" style="max-width: 1400px; margin: 0 auto;">
            <div class="d-flex align-items-center gap-3">
                <a href="/dashboard" class="btn btn-icon">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="fs-5 fw-bold mb-0">Profile</h1>
                    <p class="fs-8 text-secondary mb-0">Your financial identity</p>
                </div>
            </div>
            <button class="btn btn-icon" onclick="editProfile()">
                <span class="material-symbols-outlined">edit</span>
            </button>
        </div>
    </header>

    <div class="main-content-area p-4" style="max-width: 1000px; margin: 0 auto;">
        <!-- User Info Section -->
        <div class="glass-card p-4 mb-4">
            <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                <!-- Avatar -->
                <div class="position-relative">
                    <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 120px; height: 120px; background: linear-gradient(135deg, #007f80, #f97316);">
                        <span class="material-symbols-outlined text-white fs-1">person</span>
                    </div>
                    <div class="position-absolute bottom-0 end-0 d-flex align-items-center justify-content-center rounded-circle" style="width: 36px; height: 36px; background: #22c55e; border: 3px solid #0a0f0f;">
                        <span class="material-symbols-outlined text-white fs-5">verified</span>
                    </div>
                </div>
                
                <!-- User Details -->
                <div class="flex-grow-1 text-center text-md-start">
                    <h2 class="display-6 fw-bold mb-1">Jean Paul</h2>
                    <p class="text-secondary fs-6 mb-2">@jeanpaul_liqui</p>
                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start mb-3">
                        <span class="badge badge-deal">Premium Member</span>
                        <span class="badge badge-success">Verified</span>
                        <span class="badge badge-hot">Power User</span>
                    </div>
                    <div class="d-flex gap-4">
                        <div class="text-center">
                            <p class="fs-8 text-secondary mb-0">Member Since</p>
                            <p class="fs-6 fw-semibold mb-0">Jan 2024</p>
                        </div>
                        <div class="text-center">
                            <p class="fs-8 text-secondary mb-0">Total Swaps</p>
                            <p class="fs-6 fw-semibold mb-0">1,247</p>
                        </div>
                        <div class="text-center">
                            <p class="fs-8 text-secondary mb-0">Success Rate</p>
                            <p class="fs-6 fw-semibold mb-0">98.5%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trust Ecosystem -->
        <div class="glass-card p-4 mb-4">
            <h3 class="fs-6 fw-bold mb-4">Trust Ecosystem</h3>
            <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                <!-- Trust Score Circle -->
                <div class="position-relative" style="width: 200px; height: 200px;">
                    <svg class="position-absolute inset-0" viewBox="0 0 200 200">
                        <!-- Background circle -->
                        <circle cx="100" cy="100" r="90" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="12"/>
                        <!-- Progress circle -->
                        <circle cx="100" cy="100" r="90" fill="none" stroke="url(#trustGradient)" stroke-width="12"
                                stroke-linecap="round" stroke-dasharray="565.48" stroke-dashoffset="113.1"
                                transform="rotate(-90 100 100)"/>
                        <defs>
                            <linearGradient id="trustGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#007f80"/>
                                <stop offset="100%" style="stop-color:#f97316"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <div class="position-absolute inset-0 d-flex flex-column align-items-center justify-content-center">
                        <span class="display-5 fw-bold">94</span>
                        <span class="fs-8 text-secondary">Trust Score</span>
                    </div>
                </div>
                
                <!-- Trust Details -->
                <div class="flex-grow-1">
                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-7 text-secondary">Transaction History</span>
                            <span class="fs-7 fw-semibold">Excellent</span>
                        </div>
                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.1);">
                            <div class="progress-bar" style="width: 95%; background: linear-gradient(90deg, #007f80, #f97316);"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-7 text-secondary">Identity Verification</span>
                            <span class="fs-7 fw-semibold">Verified</span>
                        </div>
                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.1);">
                            <div class="progress-bar" style="width: 100%; background: linear-gradient(90deg, #22c55e, #16a34a);"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-7 text-secondary">Network Activity</span>
                            <span class="fs-7 fw-semibold">Very Active</span>
                        </div>
                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.1);">
                            <div class="progress-bar" style="width: 88%; background: linear-gradient(90deg, #f97316, #ea580c);"></div>
                        </div>
                    </div>
                    
                    <button class="btn btn-liquid px-4 py-2 d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined">trending_up</span>
                        <span>Improve Score</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(0, 127, 128, 0.1);">
                            <span class="material-symbols-outlined text-primary">swap_horiz</span>
                        </div>
                        <div>
                            <p class="fs-8 text-secondary mb-0">Total Swaps</p>
                            <p class="fs-4 fw-bold mb-0">1,247</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-success fs-7">trending_up</span>
                        <span class="fs-8 text-success">+12% this month</span>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(34, 197, 94, 0.1);">
                            <span class="material-symbols-outlined text-success">check_circle</span>
                        </div>
                        <div>
                            <p class="fs-8 text-secondary mb-0">Success Rate</p>
                            <p class="fs-4 fw-bold mb-0">98.5%</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-success fs-7">trending_up</span>
                        <span class="fs-8 text-success">+0.5% this month</span>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(249, 115, 22, 0.1);">
                            <span class="material-symbols-outlined text-orange">data_usage</span>
                        </div>
                        <div>
                            <p class="fs-8 text-secondary mb-0">Volume Traded</p>
                            <p class="fs-4 fw-bold mb-0">12.5M</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-orange fs-7">trending_up</span>
                        <span class="fs-8 text-orange">+25% this month</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fs-6 fw-bold mb-0">Recent Activity</h3>
                <a href="/history" class="fs-8 fw-medium" style="color: #007f80;">View all</a>
            </div>
            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3 p-3 rounded-lg" style="background: rgba(0, 127, 128, 0.05);">
                    <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(0, 127, 128, 0.1);">
                        <span class="material-symbols-outlined text-primary">swap_horiz</span>
                    </div>
                    <div class="flex-grow-1">
                        <p class="fs-7 fw-medium mb-0">Completed Swap</p>
                        <p class="fs-8 text-secondary mb-0">OM → MTN • 25,000 XAF</p>
                    </div>
                    <span class="fs-8 text-secondary">2 hours ago</span>
                </div>
                
                <div class="d-flex align-items-center gap-3 p-3 rounded-lg" style="background: rgba(249, 115, 22, 0.05);">
                    <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(249, 115, 22, 0.1);">
                        <span class="material-symbols-outlined text-orange">data_usage</span>
                    </div>
                    <div class="flex-grow-1">
                        <p class="fs-7 fw-medium mb-0">Bundle Purchased</p>
                        <p class="fs-8 text-secondary mb-0">MTN 20GB • 5,000 XAF</p>
                    </div>
                    <span class="fs-8 text-secondary">5 hours ago</span>
                </div>
                
                <div class="d-flex align-items-center gap-3 p-3 rounded-lg" style="background: rgba(34, 197, 94, 0.05);">
                    <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(34, 197, 94, 0.1);">
                        <span class="material-symbols-outlined text-success">account_balance_wallet</span>
                    </div>
                    <div class="flex-grow-1">
                        <p class="fs-7 fw-medium mb-0">Wallet Top-up</p>
                        <p class="fs-8 text-secondary mb-0">Card Payment • 150,000 XAF</p>
                    </div>
                    <span class="fs-8 text-secondary">Yesterday</span>
                </div>
            </div>
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

.badge-success {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.3);
    font-size: 0.625rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.25rem 0.5rem;
    border-radius: 999px;
}

.badge-hot {
    background: rgba(249, 115, 22, 0.2);
    color: #f97316;
    border: 1px solid rgba(249, 115, 22, 0.3);
    font-size: 0.625rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.25rem 0.5rem;
    border-radius: 999px;
}

.progress-bar {
    border-radius: 3px;
}
</style>

<script>
(function() {
    // Edit profile
    window.editProfile = function() {
        Utils.toast.info('Profile editing coming soon');
    };
    
    // Animate trust score circle on load
    setTimeout(() => {
        const circle = document.querySelector('circle[stroke="url(#trustGradient)"]');
        if (circle) {
            circle.style.transition = 'stroke-dashoffset 2s ease-in-out';
        }
    }, 500);
})();
</script>
