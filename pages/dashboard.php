<!-- Dashboard Page -->
<div class="dashboard-page min-vh-100 position-relative" style="background: #0a0f0f;">
    <!-- Header -->
    <header class="sticky-top z-50 glass border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center" style="max-width: 1400px; margin: 0 auto;">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: linear-gradient(135deg, #007f80, #f97316);">
                    <span class="material-symbols-outlined text-white" style="font-size: 1.5rem;">water_drop</span>
                </div>
                <span class="fw-bold fs-5">LiquiSwap</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-icon">
                    <span class="material-symbols-outlined">notifications</span>
                </button>
                <div class="d-flex align-items-center gap-2 glass-card rounded-pill px-3 py-2">
                    <div class="rounded-circle" style="width: 32px; height: 32px; background: linear-gradient(135deg, #007f80, #f97316);"></div>
                    <span class="fw-semibold fs-7 d-none d-sm-block">Jean Paul</span>
                    <span class="material-symbols-outlined fs-6">expand_more</span>
                </div>
            </div>
        </div>
    </header>

    <div class="main-content-area p-4" style="max-width: 1400px; margin: 0 auto;">
        <!-- Greeting -->
        <div class="mb-4">
            <h2 class="time-greeting fs-2 fw-bold mb-1">Bonjour, Jean Paul!</h2>
            <p class="text-secondary fs-7">Here's your financial overview</p>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-layout">
            <!-- Left Column -->
            <div class="d-flex flex-column gap-4">
                <!-- Balance Card -->
                <div class="card-balance position-relative overflow-hidden" style="border-radius: 24px; padding: 2rem;">
                    <div class="position-relative z-10">
                        <p class="text-white-50 fs-8 fw-semibold text-uppercase mb-2" style="letter-spacing: 0.1em;">Unified Balance</p>
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <h3 class="balance-amount display-4 fw-bold mb-0">750,000 <span class="fs-4">XAF</span></h3>
                            <button class="btn p-0" style="background: none; border: none; color: rgba(255,255,255,0.5);">
                                <span class="material-symbols-outlined">visibility_off</span>
                            </button>
                        </div>
                        
                        <!-- Wallet Breakdown -->
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <div class="glass-card px-3 py-2 d-flex align-items-center gap-2" style="background: rgba(255,255,255,0.1);">
                                <span class="material-symbols-outlined text-warning fs-6">signal_cellular_alt</span>
                                <span class="fs-7">450,000 MTN</span>
                            </div>
                            <div class="glass-card px-3 py-2 d-flex align-items-center gap-2" style="background: rgba(255,255,255,0.1);">
                                <span class="material-symbols-outlined text-orange fs-6">signal_cellular_alt</span>
                                <span class="fs-7">300,000 Orange</span>
                            </div>
                            <div class="glass-card px-3 py-2 d-flex align-items-center gap-2" style="background: rgba(255,255,255,0.1);">
                                <span class="material-symbols-outlined text-primary fs-6">account_balance</span>
                                <span class="fs-7">500,000 Bank</span>
                            </div>
                        </div>
                        
                        <!-- Deposit Button -->
                        <button class="btn btn-liquid-orange px-4 py-2 d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined">add</span>
                            <span>Deposit</span>
                        </button>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="glass-card p-4">
                    <h4 class="fs-6 fw-bold mb-4">Quick Actions</h4>
                    <div class="quick-actions-grid row g-3">
                        <div class="col-4">
                            <a href="/swap" class="quick-action-btn d-flex flex-column align-items-center gap-2 p-3 rounded-xl text-decoration-none haptic-click" style="background: rgba(0, 127, 128, 0.1); border: 1px solid rgba(0, 127, 128, 0.2);">
                                <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-lg" style="width: 48px; height: 48px; background: linear-gradient(135deg, #007f80, #005c5d);">
                                    <span class="material-symbols-outlined text-white">swap_horiz</span>
                                </div>
                                <span class="fs-8 fw-medium text-center" style="color: #94a3b8;">OM ↔ MOMO</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="/bundles" class="quick-action-btn d-flex flex-column align-items-center gap-2 p-3 rounded-xl text-decoration-none haptic-click" style="background: rgba(249, 115, 22, 0.1); border: 1px solid rgba(249, 115, 22, 0.2);">
                                <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-lg" style="width: 48px; height: 48px; background: linear-gradient(135deg, #f97316, #ea580c);">
                                    <span class="material-symbols-outlined text-white">package_2</span>
                                </div>
                                <span class="fs-8 fw-medium text-center" style="color: #94a3b8;">Bundles</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="/airtime-bundle" class="quick-action-btn d-flex flex-column align-items-center gap-2 p-3 rounded-xl text-decoration-none haptic-click" style="background: rgba(251, 191, 36, 0.1); border: 1px solid rgba(251, 191, 36, 0.2);">
                                <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-lg" style="width: 48px; height: 48px; background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                                    <span class="material-symbols-outlined text-white">phone_android</span>
                                </div>
                                <span class="fs-8 fw-medium text-center" style="color: #94a3b8;">Airtime</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="/bundles" class="quick-action-btn d-flex flex-column align-items-center gap-2 p-3 rounded-xl text-decoration-none haptic-click" style="background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.2);">
                                <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-lg" style="width: 48px; height: 48px; background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                                    <span class="material-symbols-outlined text-white">local_offer</span>
                                </div>
                                <span class="fs-8 fw-medium text-center" style="color: #94a3b8;">Deals</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="/ussd" class="quick-action-btn d-flex flex-column align-items-center gap-2 p-3 rounded-xl text-decoration-none haptic-click" style="background: rgba(0, 127, 128, 0.1); border: 1px solid rgba(0, 127, 128, 0.2);">
                                <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-lg" style="width: 48px; height: 48px; background: linear-gradient(135deg, #007f80, #005c5d);">
                                    <span class="material-symbols-outlined text-white">dialpad</span>
                                </div>
                                <span class="fs-8 fw-medium text-center" style="color: #94a3b8;">USSD</span>
                            </a>
                        </div>
                        <div class="col-4">
                            <a href="/settings" class="quick-action-btn d-flex flex-column align-items-center gap-2 p-3 rounded-xl text-decoration-none haptic-click" style="background: rgba(100, 116, 139, 0.1); border: 1px solid rgba(100, 116, 139, 0.2);">
                                <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-lg" style="width: 48px; height: 48px; background: linear-gradient(135deg, #64748b, #475569);">
                                    <span class="material-symbols-outlined text-white">support_agent</span>
                                </div>
                                <span class="fs-8 fw-medium text-center" style="color: #94a3b8;">Support</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Good Deals -->
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fs-6 fw-bold mb-0">Good Deals</h4>
                        <a href="/bundles" class="fs-8 fw-medium" style="color: #007f80;">View all</a>
                    </div>
                    <div class="d-flex gap-3 overflow-auto pb-2 no-scrollbar">
                        <div class="bundle-card flex-shrink-0 glass-card p-4" style="width: 200px; min-width: 200px;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge-hot">Hot</span>
                                <span class="material-symbols-outlined text-warning">signal_cellular_alt</span>
                            </div>
                            <h5 class="fs-6 fw-bold mb-1">Turbo 20GB</h5>
                            <p class="fs-8 text-secondary mb-3">20GB per month with nightly bonus</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-5 fw-bold text-primary">5,000</span>
                                <span class="fs-8 text-secondary">XAF</span>
                            </div>
                        </div>
                        <div class="bundle-card flex-shrink-0 glass-card p-4" style="width: 200px; min-width: 200px;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge-deal">Deal</span>
                                <span class="material-symbols-outlined text-orange">signal_cellular_alt</span>
                            </div>
                            <h5 class="fs-6 fw-bold mb-1">Home Premium</h5>
                            <p class="fs-8 text-secondary mb-3">Best for remote workers</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-5 fw-bold text-primary">25,000</span>
                                <span class="fs-8 text-secondary">XAF</span>
                            </div>
                        </div>
                        <div class="bundle-card flex-shrink-0 glass-card p-4" style="width: 200px; min-width: 200px;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge-deal">Deal</span>
                                <span class="material-symbols-outlined text-primary">signal_cellular_alt</span>
                            </div>
                            <h5 class="fs-6 fw-bold mb-1">5GB Combo</h5>
                            <p class="fs-8 text-secondary mb-3">5GB + 100 minutes</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-5 fw-bold text-primary">3,000</span>
                                <span class="fs-8 text-secondary">XAF</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="d-flex flex-column gap-4">
                <!-- Live Feed -->
                <div class="glass-card p-4">
                    <h4 class="fs-6 fw-bold mb-4">Live Feed</h4>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-lg" style="background: rgba(0, 127, 128, 0.05);">
                            <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(34, 197, 94, 0.1);">
                                <span class="material-symbols-outlined text-success">check_circle</span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="fs-7 fw-medium mb-0">Swap completed</p>
                                <p class="fs-8 text-secondary mb-0">12,400 XAF • 2 mins ago</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 p-3 rounded-lg" style="background: rgba(249, 115, 22, 0.05);">
                            <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(249, 115, 22, 0.1);">
                                <span class="material-symbols-outlined text-orange">notifications</span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="fs-7 fw-medium mb-0">Price alert</p>
                                <p class="fs-8 text-secondary mb-0">OM↔MOMO rate changed</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 p-3 rounded-lg" style="background: rgba(0, 127, 128, 0.05);">
                            <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(0, 127, 128, 0.1);">
                                <span class="material-symbols-outlined text-primary">check_circle</span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="fs-7 fw-medium mb-0">Bundle purchased</p>
                                <p class="fs-8 text-secondary mb-0">MTN 5GB • 5 mins ago</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Market Ticker -->
                <div class="glass-card p-3 overflow-hidden">
                    <div class="d-flex gap-4 animate-ticker" style="white-space: nowrap;">
                        <span class="fs-8"><span class="text-secondary">OM↔MOMO:</span> <span class="fw-bold">1.00</span></span>
                        <span class="fs-8"><span class="text-secondary">XAF/USD:</span> <span class="fw-bold text-success">↗ 610</span></span>
                        <span class="fs-8"><span class="text-secondary">BTC/XAF:</span> <span class="fw-bold text-success">↗ 35M</span></span>
                        <span class="fs-8"><span class="text-secondary">OM↔MOMO:</span> <span class="fw-bold">1.00</span></span>
                        <span class="fs-8"><span class="text-secondary">XAF/USD:</span> <span class="fw-bold text-success">↗ 610</span></span>
                        <span class="fs-8"><span class="text-secondary">BTC/XAF:</span> <span class="fw-bold text-success">↗ 35M</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.text-orange {
    color: #f97316 !important;
}

.fs-7 {
    font-size: 0.875rem;
}

.fs-8 {
    font-size: 0.75rem;
}

.rounded-xl {
    border-radius: 1rem;
}

.text-white-50 {
    color: rgba(255, 255, 255, 0.5) !important;
}

@media (min-width: 992px) {
    .dashboard-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }
}
</style>
