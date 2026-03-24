<!-- Bundles Marketplace Page -->
<div class="bundles-page min-vh-100 position-relative" style="background: #0a0f0f;">
    <!-- Header -->
    <header class="sticky-top z-50 glass border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center" style="max-width: 1400px; margin: 0 auto;">
            <div class="d-flex align-items-center gap-3">
                <a href="/dashboard" class="btn btn-icon">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="fs-5 fw-bold mb-0">Bundles Marketplace</h1>
                    <p class="fs-8 text-secondary mb-0">Best data plans & offers</p>
                </div>
            </div>
            <button class="btn btn-icon">
                <span class="material-symbols-outlined">search</span>
            </button>
        </div>
    </header>

    <div class="main-content-area p-4" style="max-width: 1400px; margin: 0 auto;">
        <!-- Search Bar -->
        <div class="glass-card p-3 mb-4">
            <div class="input-group input-group-liquid">
                <span class="input-group-text">
                    <span class="material-symbols-outlined">search</span>
                </span>
                <input type="text" id="bundle-search" class="form-control form-control-liquid" placeholder="Search bundles...">
            </div>
        </div>

        <!-- Operator Tabs -->
        <div class="d-flex gap-2 mb-4 overflow-auto pb-2 no-scrollbar">
            <button type="button" class="btn btn-liquid px-4 py-2" onclick="filterOperator('all')">All</button>
            <button type="button" class="btn btn-glass px-4 py-2 d-flex align-items-center gap-2" onclick="filterOperator('MTN')">
                <span class="material-symbols-outlined text-warning fs-6">signal_cellular_alt</span>
                MTN
            </button>
            <button type="button" class="btn btn-glass px-4 py-2 d-flex align-items-center gap-2" onclick="filterOperator('ORANGE')">
                <span class="material-symbols-outlined text-orange fs-6">signal_cellular_alt</span>
                Orange
            </button>
            <button type="button" class="btn btn-glass px-4 py-2 d-flex align-items-center gap-2" onclick="filterOperator('CAMTEL')">
                <span class="material-symbols-outlined text-primary fs-6">signal_cellular_alt</span>
                Camtel
            </button>
            <button type="button" class="btn btn-glass px-4 py-2 d-flex align-items-center gap-2" onclick="filterOperator('NEXTTEL')">
                <span class="material-symbols-outlined text-success fs-6">signal_cellular_alt</span>
                Nexttel
            </button>
        </div>

        <!-- Hero Banner -->
        <div class="card-balance position-relative overflow-hidden mb-4" style="border-radius: 20px; padding: 2rem;">
            <div class="position-relative z-10">
                <span class="badge-hot mb-2 d-inline-block">Hot Deal</span>
                <h2 class="display-5 fw-bold mb-2">Weekly Data Packs</h2>
                <p class="text-white-50 mb-3">Best value data bundles for heavy users</p>
                <button class="btn btn-light px-4 py-2">Explore Deals</button>
            </div>
        </div>

        <!-- Bundles Grid -->
        <h3 class="fs-6 fw-bold mb-3">Popular Bundles</h3>
        <div class="row g-3" id="bundles-grid">
            <!-- MTN Bundles -->
            <div class="col-12 col-md-6 col-lg-4 bundle-item" data-operator="MTN">
                <div class="glass-card p-4 h-100 magnetic-hover">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-warning">signal_cellular_alt</span>
                            <span class="fs-8 fw-semibold">MTN</span>
                        </div>
                        <span class="badge-hot">Hot</span>
                    </div>
                    <h4 class="fs-5 fw-bold mb-1">Monthly Maxi</h4>
                    <p class="fs-8 text-secondary mb-3">30 days high-speed data with unlimited WhatsApp</p>
                    <div class="d-flex align-items-baseline gap-1 mb-3">
                        <span class="display-6 fw-bold text-primary">12</span>
                        <span class="fs-4 fw-semibold">GB</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-7 text-secondary">30 Days</span>
                        <span class="fs-8 text-secondary text-decoration-line-through">3,000 XAF</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fs-4 fw-bold">2,500 <span class="fs-6">XAF</span></span>
                        <span class="badge-deal">Good Deal</span>
                    </div>
                    <button class="btn btn-liquid w-100 mt-3 py-2" onclick="buyBundle(1, 'Monthly Maxi')">Buy Now</button>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4 bundle-item" data-operator="MTN">
                <div class="glass-card p-4 h-100 magnetic-hover">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-warning">signal_cellular_alt</span>
                            <span class="fs-8 fw-semibold">MTN</span>
                        </div>
                    </div>
                    <h4 class="fs-5 fw-bold mb-1">Turbo 20GB</h4>
                    <p class="fs-8 text-secondary mb-3">20GB per month with nightly bonus data</p>
                    <div class="d-flex align-items-baseline gap-1 mb-3">
                        <span class="display-6 fw-bold text-primary">20</span>
                        <span class="fs-4 fw-semibold">GB</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-7 text-secondary">30 Days</span>
                        <span class="fs-8 text-secondary text-decoration-line-through">6,500 XAF</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fs-4 fw-bold">5,000 <span class="fs-6">XAF</span></span>
                    </div>
                    <button class="btn btn-liquid w-100 mt-3 py-2" onclick="buyBundle(2, 'Turbo 20GB')">Buy Now</button>
                </div>
            </div>

            <!-- Orange Bundles -->
            <div class="col-12 col-md-6 col-lg-4 bundle-item" data-operator="ORANGE">
                <div class="glass-card p-4 h-100 magnetic-hover">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-orange">signal_cellular_alt</span>
                            <span class="fs-8 fw-semibold">Orange</span>
                        </div>
                    </div>
                    <h4 class="fs-5 fw-bold mb-1">Home Premium</h4>
                    <p class="fs-8 text-secondary mb-3">Best for remote workers and streaming</p>
                    <div class="d-flex align-items-baseline gap-1 mb-3">
                        <span class="display-6 fw-bold text-primary">50</span>
                        <span class="fs-4 fw-semibold">GB</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-7 text-secondary">90 Days</span>
                        <span class="fs-8 text-secondary text-decoration-line-through">30,000 XAF</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fs-4 fw-bold">25,000 <span class="fs-6">XAF</span></span>
                        <span class="badge-deal">Best Value</span>
                    </div>
                    <button class="btn btn-liquid w-100 mt-3 py-2" onclick="buyBundle(3, 'Home Premium')">Buy Now</button>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4 bundle-item" data-operator="ORANGE">
                <div class="glass-card p-4 h-100 magnetic-hover">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-orange">signal_cellular_alt</span>
                            <span class="fs-8 fw-semibold">Orange</span>
                        </div>
                        <span class="badge-hot">Popular</span>
                    </div>
                    <h4 class="fs-5 fw-bold mb-1">Max Social Packs</h4>
                    <p class="fs-8 text-secondary mb-3">Unlimited social media access</p>
                    <div class="d-flex align-items-baseline gap-1 mb-3">
                        <span class="display-6 fw-bold text-primary">∞</span>
                        <span class="fs-4 fw-semibold">Social</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-7 text-secondary">30 Days</span>
                        <span class="fs-8 text-secondary text-decoration-line-through">3,000 XAF</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fs-4 fw-bold">2,500 <span class="fs-6">XAF</span></span>
                    </div>
                    <button class="btn btn-liquid w-100 mt-3 py-2" onclick="buyBundle(4, 'Max Social Packs')">Buy Now</button>
                </div>
            </div>

            <!-- Camtel Bundles -->
            <div class="col-12 col-md-6 col-lg-4 bundle-item" data-operator="CAMTEL">
                <div class="glass-card p-4 h-100 magnetic-hover">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-primary">signal_cellular_alt</span>
                            <span class="fs-8 fw-semibold">Camtel</span>
                        </div>
                        <span class="badge-hot">Unlimited</span>
                    </div>
                    <h4 class="fs-5 fw-bold mb-1">Blue One Daily</h4>
                    <p class="fs-8 text-secondary mb-3">Truly unlimited data with zero speed throttling</p>
                    <div class="d-flex align-items-baseline gap-1 mb-3">
                        <span class="display-6 fw-bold text-primary">∞</span>
                        <span class="fs-4 fw-semibold">Data</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-7 text-secondary">24 Hours</span>
                        <span class="fs-8 text-secondary text-decoration-line-through">1,000 XAF</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fs-4 fw-bold">500 <span class="fs-6">XAF</span></span>
                    </div>
                    <button class="btn btn-liquid w-100 mt-3 py-2" onclick="buyBundle(5, 'Blue One Daily')">Buy Now</button>
                </div>
            </div>

            <!-- Nexttel Bundles -->
            <div class="col-12 col-md-6 col-lg-4 bundle-item" data-operator="NEXTTEL">
                <div class="glass-card p-4 h-100 magnetic-hover">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-success">signal_cellular_alt</span>
                            <span class="fs-8 fw-semibold">Nexttel</span>
                        </div>
                    </div>
                    <h4 class="fs-5 fw-bold mb-1">G-Special Social</h4>
                    <p class="fs-8 text-secondary mb-3">Optimized for social media apps</p>
                    <div class="d-flex align-items-baseline gap-1 mb-3">
                        <span class="display-6 fw-bold text-primary">5</span>
                        <span class="fs-4 fw-semibold">GB</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-7 text-secondary">7 Days</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fs-4 fw-bold">2,500 <span class="fs-6">XAF</span></span>
                    </div>
                    <button class="btn btn-liquid w-100 mt-3 py-2" onclick="buyBundle(6, 'G-Special Social')">Buy Now</button>
                </div>
            </div>
        </div>

        <!-- Custom Bundle Card -->
        <div class="glass-card p-4 mt-4">
            <div class="d-flex align-items-center gap-4">
                <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 60px; height: 60px; background: linear-gradient(135deg, #007f80, #005c5d);">
                    <span class="material-symbols-outlined text-white fs-2">add_circle</span>
                </div>
                <div class="flex-grow-1">
                    <h4 class="fs-5 fw-bold mb-1">Create Custom Bundle</h4>
                    <p class="fs-8 text-secondary mb-0">Build your perfect data plan</p>
                </div>
                <button class="btn btn-glass py-2 px-4">
                    <span>Configure</span>
                    <span class="material-symbols-outlined ms-1">arrow_forward</span>
                </button>
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

.text-white-50 {
    color: rgba(255, 255, 255, 0.5) !important;
}
</style>

<script>
(function() {
    const searchInput = document.getElementById('bundle-search');
    const bundleItems = document.querySelectorAll('.bundle-item');
    
    // Search functionality
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        bundleItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(query) ? '' : 'none';
        });
    });
    
    // Filter by operator
    window.filterOperator = function(operator) {
        bundleItems.forEach(item => {
            if (operator === 'all' || item.dataset.operator === operator) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    };
    
    // Buy bundle
    window.buyBundle = function(id, name) {
        // Show confirmation or navigate to checkout
        Utils.toast.success(`Added ${name} to checkout`);
    };
})();
</script>
