<!-- USSD Codes Library Page -->
<div class="ussd-page min-vh-100 position-relative" style="background: #0a0f0f;">
    <!-- Header -->
    <header class="sticky-top z-50 glass border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center" style="max-width: 1400px; margin: 0 auto;">
            <div class="d-flex align-items-center gap-3">
                <a href="/dashboard" class="btn btn-icon">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="fs-5 fw-bold mb-0">USSD Library</h1>
                    <p class="fs-8 text-secondary mb-0">Direct access to mobile services</p>
                </div>
            </div>
        </div>
    </header>

    <div class="main-content-area p-4" style="max-width: 1200px; margin: 0 auto;">
        <!-- Hero Section -->
        <div class="mb-4">
            <span class="text-primary fw-bold fs-8 text-uppercase tracking-wider" style="letter-spacing: 0.2em;">Central African Fintech Hub</span>
            <h2 class="display-4 fw-bold mt-2 mb-2">USSD <span class="text-primary">Direct</span> Library</h2>
            <p class="text-secondary fs-6">Instant access to critical mobile services across the region. No internet? No problem.</p>
        </div>

        <!-- Search & Filter -->
        <div class="glass-card p-3 mb-4 sticky-top" style="top: 70px; z-index: 40;">
            <div class="d-flex flex-column flex-md-row gap-3">
                <div class="position-relative flex-grow-1">
                    <span class="material-symbols-outlined position-absolute start-0 top-50 translate-middle-y ms-3 text-primary">search</span>
                    <input type="text" id="ussd-search" class="form-control form-control-liquid ps-5" placeholder="Find codes (e.g. Orange Money, *155#)" style="background: rgba(0,0,0,0.3);">
                </div>
                <div class="d-flex gap-2 overflow-auto pb-2 pb-md-0 no-scrollbar">
                    <button class="btn btn-liquid px-4 py-2 whitespace-nowrap" onclick="filterCategory('all')">All</button>
                    <button class="btn btn-glass px-4 py-2 whitespace-nowrap" onclick="filterCategory('balance')">Balance</button>
                    <button class="btn btn-glass px-4 py-2 whitespace-nowrap" onclick="filterCategory('data')">Data</button>
                    <button class="btn btn-glass px-4 py-2 whitespace-nowrap" onclick="filterCategory('airtime')">Airtime</button>
                    <button class="btn btn-glass px-4 py-2 whitespace-nowrap" onclick="filterCategory('services')">Services</button>
                </div>
            </div>
        </div>

        <!-- USSD Codes Grid -->
        <div class="row g-4" id="ussd-grid">
            <!-- Balance Codes -->
            <div class="col-12 col-md-6 col-lg-4 ussd-item" data-category="balance">
                <div class="glass-card p-4 h-100 neo-liquid-glow">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(0, 127, 128, 0.1);">
                            <span class="material-symbols-outlined text-primary fs-5">account_balance</span>
                        </div>
                        <span class="badge-deal">Balance</span>
                    </div>
                    <h4 class="fs-5 fw-bold mb-1">Check Main Balance</h4>
                    <p class="fs-8 text-secondary mb-4">Orange & MTN Central Africa common query</p>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-lg mb-4" style="background: rgba(0,0,0,0.3);">
                        <code class="fs-4 fw-bold text-orange font-monospace">*155#</code>
                        <button class="btn btn-icon" onclick="copyCode('*155#')">
                            <span class="material-symbols-outlined text-primary">content_copy</span>
                        </button>
                    </div>
                    <a href="tel:*155#" class="btn btn-liquid w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined">call</span>
                        <span>Dial Now</span>
                    </a>
                </div>
            </div>

            <!-- Data Codes -->
            <div class="col-12 col-md-6 col-lg-4 ussd-item" data-category="data">
                <div class="glass-card p-4 h-100 neo-liquid-glow">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(249, 115, 22, 0.1);">
                            <span class="material-symbols-outlined text-orange fs-5">data_usage</span>
                        </div>
                        <span class="badge-hot">Data</span>
                    </div>
                    <h4 class="fs-5 fw-bold mb-1">Buy Data Bundles</h4>
                    <p class="fs-8 text-secondary mb-4">Daily, Weekly and Monthly 4G plans</p>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-lg mb-4" style="background: rgba(0,0,0,0.3);">
                        <code class="fs-4 fw-bold text-warning font-monospace">*141*2#</code>
                        <button class="btn btn-icon" onclick="copyCode('*141*2#')">
                            <span class="material-symbols-outlined text-orange">content_copy</span>
                        </button>
                    </div>
                    <a href="tel:*141*2#" class="btn btn-liquid-orange w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined">call</span>
                        <span>Dial Now</span>
                    </a>
                </div>
            </div>

            <!-- Transfer Codes -->
            <div class="col-12 col-md-6 col-lg-4 ussd-item" data-category="services">
                <div class="glass-card p-4 h-100 neo-liquid-glow">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(251, 191, 36, 0.1);">
                            <span class="material-symbols-outlined text-warning fs-5">payments</span>
                        </div>
                        <span class="badge-deal">Services</span>
                    </div>
                    <h4 class="fs-5 fw-bold mb-1">MoMo Transfer</h4>
                    <p class="fs-8 text-secondary mb-4">Send money to any mobile number</p>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-lg mb-4" style="background: rgba(0,0,0,0.3);">
                        <code class="fs-4 fw-bold text-primary font-monospace">*126#</code>
                        <button class="btn btn-icon" onclick="copyCode('*126#')">
                            <span class="material-symbols-outlined text-warning">content_copy</span>
                        </button>
                    </div>
                    <a href="tel:*126#" class="btn w-100 py-3 d-flex align-items-center justify-content-center gap-2" style="background: #fbbf24; color: #0a0f0f; font-weight: 700;">
                        <span class="material-symbols-outlined">call</span>
                        <span>Dial Now</span>
                    </a>
                </div>
            </div>

            <!-- Recharge Codes -->
            <div class="col-12 col-md-6 col-lg-4 ussd-item" data-category="airtime">
                <div class="glass-card p-4 h-100 neo-liquid-glow">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(0, 127, 128, 0.1);">
                            <span class="material-symbols-outlined text-primary fs-5">phone_iphone</span>
                        </div>
                        <span class="badge-deal">Airtime</span>
                    </div>
                    <h4 class="fs-5 fw-bold mb-1">Recharge Card</h4>
                    <p class="fs-8 text-secondary mb-4">Direct scratch card top-up portal</p>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-lg mb-4" style="background: rgba(0,0,0,0.3);">
                        <code class="fs-4 fw-bold text-orange font-monospace">*135*PIN#</code>
                        <button class="btn btn-icon" onclick="copyCode('*135*PIN#')">
                            <span class="material-symbols-outlined text-primary">content_copy</span>
                        </button>
                    </div>
                    <a href="tel:*135*" class="btn btn-liquid w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined">call</span>
                        <span>Dial Now</span>
                    </a>
                </div>
            </div>

            <!-- Customer Service -->
            <div class="col-12 col-md-6 col-lg-4 ussd-item" data-category="support">
                <div class="glass-card p-4 h-100 neo-liquid-glow">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(100, 116, 139, 0.1);">
                            <span class="material-symbols-outlined text-secondary fs-5">help_center</span>
                        </div>
                        <span class="badge-deal">Support</span>
                    </div>
                    <h4 class="fs-5 fw-bold mb-1">Customer Service</h4>
                    <p class="fs-8 text-secondary mb-4">Speak with a local agent directly</p>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-lg mb-4" style="background: rgba(0,0,0,0.3);">
                        <code class="fs-4 fw-bold font-monospace">955</code>
                        <button class="btn btn-icon" onclick="copyCode('955')">
                            <span class="material-symbols-outlined text-secondary">content_copy</span>
                        </button>
                    </div>
                    <a href="tel:955" class="btn btn-glass w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined">call</span>
                        <span>Dial Now</span>
                    </a>
                </div>
            </div>

            <!-- Night Data -->
            <div class="col-12 col-md-6 col-lg-4 ussd-item" data-category="data">
                <div class="glass-card p-4 h-100 neo-liquid-glow">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(249, 115, 22, 0.1);">
                            <span class="material-symbols-outlined text-orange fs-5">wifi_tethering</span>
                        </div>
                        <span class="badge-hot">Night</span>
                    </div>
                    <h4 class="fs-5 fw-bold mb-1">Night Data Plan</h4>
                    <p class="fs-8 text-secondary mb-4">Low-cost midnight surfing bundles</p>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-lg mb-4" style="background: rgba(0,0,0,0.3);">
                        <code class="fs-4 fw-bold text-warning font-monospace">*150*47#</code>
                        <button class="btn btn-icon" onclick="copyCode('*150*47#')">
                            <span class="material-symbols-outlined text-orange">content_copy</span>
                        </button>
                    </div>
                    <a href="tel:*150*47#" class="btn btn-liquid-orange w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined">call</span>
                        <span>Dial Now</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Pro Tip Section -->
        <div class="glass-card p-4 mt-5 d-flex flex-column flex-md-row align-items-center justify-content-between gap-4" style="background: linear-gradient(135deg, rgba(0, 127, 128, 0.1), rgba(0,0,0,0.3));">
            <div class="d-flex align-items-center gap-4">
                <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 64px; height: 64px; background: rgba(0, 127, 128, 0.2);">
                    <span class="material-symbols-outlined text-primary fs-2">lightbulb</span>
                </div>
                <div>
                    <h4 class="fs-5 fw-bold mb-1">Pro Tip: Offline Access</h4>
                    <p class="text-secondary fs-7 mb-0">Add these codes to your phone's shortcuts for instant access during outages.</p>
                </div>
            </div>
            <button class="btn btn-glass px-4 py-3">
                <span class="material-symbols-outlined me-2">download</span>
                Download PDF
            </button>
        </div>
    </div>
</div>

<style>
.text-orange {
    color: #f97316 !important;
}

.whitespace-nowrap {
    white-space: nowrap;
}

.neo-liquid-glow {
    transition: box-shadow 0.3s ease;
}

.neo-liquid-glow:hover {
    box-shadow: 0 0 30px rgba(0, 127, 128, 0.3);
}

.font-monospace {
    font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
}
</style>

<script>
(function() {
    const searchInput = document.getElementById('ussd-search');
    const ussdItems = document.querySelectorAll('.ussd-item');
    
    // Search functionality
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        ussdItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(query) ? '' : 'none';
        });
    });
    
    // Filter by category
    window.filterCategory = function(category) {
        ussdItems.forEach(item => {
            if (category === 'all' || item.dataset.category === category) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    };
    
    // Copy code to clipboard
    window.copyCode = async function(code) {
        const success = await Utils.copyToClipboard(code);
        if (success) {
            Utils.toast.success('Code copied: ' + code);
        }
    };
})();
</script>
