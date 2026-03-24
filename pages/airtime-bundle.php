<!-- Airtime to Bundle Conversion Page -->
<div class="conversion-page min-vh-100 position-relative" style="background: #0a0f0f;">
    <!-- Header -->
    <header class="sticky-top z-50 glass border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center" style="max-width: 1400px; margin: 0 auto;">
            <div class="d-flex align-items-center gap-3">
                <a href="/dashboard" class="btn btn-icon">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="fs-5 fw-bold mb-0">Airtime → Bundle</h1>
                    <p class="fs-8 text-secondary mb-0">Convert airtime to data bundles</p>
                </div>
            </div>
            <button class="btn btn-icon">
                <span class="material-symbols-outlined">help</span>
            </button>
        </div>
    </header>

    <div class="main-content-area p-4" style="max-width: 800px; margin: 0 auto;">
        <!-- Step Indicator -->
        <div class="d-flex justify-content-center mb-5">
            <div class="d-flex align-items-center gap-3">
                <div class="step-indicator active" data-step="1">
                    <div class="step-circle">1</div>
                    <span class="step-label">Airtime</span>
                </div>
                <div class="step-line"></div>
                <div class="step-indicator" data-step="2">
                    <div class="step-circle">2</div>
                    <span class="step-label">Bundle</span>
                </div>
                <div class="step-line"></div>
                <div class="step-indicator" data-step="3">
                    <div class="step-circle">3</div>
                    <span class="step-label">Confirm</span>
                </div>
            </div>
        </div>

        <!-- Step 1: Airtime Source -->
        <div id="step1" class="step-content">
            <div class="glass-card p-4 mb-4">
                <h3 class="fs-6 fw-bold mb-4">Select Airtime Source</h3>
                
                <!-- Phone Number Input -->
                <div class="mb-4">
                    <label class="d-block fs-8 fw-semibold text-uppercase mb-2" style="color: #64748b; letter-spacing: 0.1em;">Payer Phone Number</label>
                    <div class="input-group input-group-liquid">
                        <span class="input-group-text">
                            <span class="me-2">🇨🇲</span>
                            <span class="text-secondary">+237</span>
                        </span>
                        <input type="tel" id="payer-phone" class="form-control form-control-liquid" placeholder="6XX XXX XXX">
                    </div>
                </div>
                
                <!-- Provider Selection -->
                <div class="mb-4">
                    <label class="d-block fs-8 fw-semibold text-uppercase mb-3" style="color: #64748b; letter-spacing: 0.1em;">Airtime Provider</label>
                    <div class="d-flex flex-wrap gap-3">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="airtime-provider" value="MTN" class="d-none" checked>
                            <div class="glass-card p-3 text-center haptic-click" style="border: 2px solid #fbbf24;">
                                <span class="material-symbols-outlined text-warning fs-4 mb-1">signal_cellular_alt</span>
                                <p class="fs-7 fw-semibold mb-0">MTN</p>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="airtime-provider" value="ORANGE" class="d-none">
                            <div class="glass-card p-3 text-center haptic-click" style="border: 2px solid transparent;">
                                <span class="material-symbols-outlined text-orange fs-4 mb-1">signal_cellular_alt</span>
                                <p class="fs-7 fw-semibold mb-0">Orange</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Recent Contacts -->
                <div>
                    <p class="fs-8 text-secondary mb-2">Recent</p>
                    <div class="d-flex gap-2 overflow-auto pb-2 no-scrollbar">
                        <button type="button" class="btn btn-glass d-flex align-items-center gap-2 py-2 px-3" onclick="setPayerPhone('677123456')">
                            <span class="material-symbols-outlined fs-6">person</span>
                            <span class="fs-8">Maman</span>
                        </button>
                        <button type="button" class="btn btn-glass d-flex align-items-center gap-2 py-2 px-3" onclick="setPayerPhone('677234567')">
                            <span class="material-symbols-outlined fs-6">person</span>
                            <span class="fs-8">Papa</span>
                        </button>
                        <button type="button" class="btn btn-glass d-flex align-items-center gap-2 py-2 px-3" onclick="setPayerPhone('677345678')">
                            <span class="material-symbols-outlined fs-6">person</span>
                            <span class="fs-8">Junior</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <button type="button" class="btn btn-liquid w-100 py-4 d-flex align-items-center justify-content-center gap-2" onclick="goToStep(2)">
                <span>Continue</span>
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </div>

        <!-- Step 2: Bundle Selection -->
        <div id="step2" class="step-content d-none">
            <div class="glass-card p-3 mb-4">
                <div class="input-group input-group-liquid">
                    <span class="input-group-text">
                        <span class="material-symbols-outlined">search</span>
                    </span>
                    <input type="text" id="bundle-search" class="form-control form-control-liquid" placeholder="Search bundles...">
                </div>
            </div>

            <div class="row g-3 mb-4" id="bundles-grid">
                <!-- Bundle Cards -->
                <div class="col-12 col-md-6 bundle-item">
                    <div class="glass-card p-4 h-100 magnetic-hover cursor-pointer" onclick="selectBundle(1, 'MTN 2GB', 2000)">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="material-symbols-outlined text-warning">signal_cellular_alt</span>
                            <span class="badge-deal">Popular</span>
                        </div>
                        <h4 class="fs-5 fw-bold mb-1">MTN 2GB</h4>
                        <p class="fs-8 text-secondary mb-3">2GB data for 7 days</p>
                        <div class="d-flex align-items-baseline gap-1 mb-3">
                            <span class="display-6 fw-bold text-primary">2</span>
                            <span class="fs-4 fw-semibold">GB</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-4 fw-bold">2,000 <span class="fs-6">XAF</span></span>
                            <span class="fs-8 text-success">+250MB bonus</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 bundle-item">
                    <div class="glass-card p-4 h-100 magnetic-hover cursor-pointer" onclick="selectBundle(2, 'Orange 5GB', 5000)">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="material-symbols-outlined text-orange">signal_cellular_alt</span>
                            <span class="badge-hot">Hot</span>
                        </div>
                        <h4 class="fs-5 fw-bold mb-1">Orange 5GB</h4>
                        <p class="fs-8 text-secondary mb-3">5GB data for 30 days</p>
                        <div class="d-flex align-items-baseline gap-1 mb-3">
                            <span class="display-6 fw-bold text-primary">5</span>
                            <span class="fs-4 fw-semibold">GB</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-4 fw-bold">5,000 <span class="fs-6">XAF</span></span>
                            <span class="fs-8 text-success">+500MB bonus</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 bundle-item">
                    <div class="glass-card p-4 h-100 magnetic-hover cursor-pointer" onclick="selectBundle(3, 'MTN 10GB', 10000)">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="material-symbols-outlined text-warning">signal_cellular_alt</span>
                        </div>
                        <h4 class="fs-5 fw-bold mb-1">MTN 10GB</h4>
                        <p class="fs-8 text-secondary mb-3">10GB data for 30 days</p>
                        <div class="d-flex align-items-baseline gap-1 mb-3">
                            <span class="display-6 fw-bold text-primary">10</span>
                            <span class="fs-4 fw-semibold">GB</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-4 fw-bold">10,000 <span class="fs-6">XAF</span></span>
                            <span class="fs-8 text-success">+1GB bonus</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 bundle-item">
                    <div class="glass-card p-4 h-100 magnetic-hover cursor-pointer" onclick="selectBundle(4, 'Orange 20GB', 20000)">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="material-symbols-outlined text-orange">signal_cellular_alt</span>
                            <span class="badge-deal">Best Value</span>
                        </div>
                        <h4 class="fs-5 fw-bold mb-1">Orange 20GB</h4>
                        <p class="fs-8 text-secondary mb-3">20GB data for 60 days</p>
                        <div class="d-flex align-items-baseline gap-1 mb-3">
                            <span class="display-6 fw-bold text-primary">20</span>
                            <span class="fs-4 fw-semibold">GB</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-4 fw-bold">20,000 <span class="fs-6">XAF</span></span>
                            <span class="fs-8 text-success">+2GB bonus</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3">
                <button type="button" class="btn btn-glass px-4 py-3" onclick="goToStep(1)">Back</button>
                <button type="button" class="btn btn-liquid flex-grow-1 py-3" onclick="goToStep(3)" id="continue-to-confirm" disabled>
                    Select Bundle to Continue
                </button>
            </div>
        </div>

        <!-- Step 3: Confirmation -->
        <div id="step3" class="step-content d-none">
            <div class="glass-card p-4 mb-4">
                <h3 class="fs-6 fw-bold mb-4">Transaction Preview</h3>
                
                <!-- Conversion Details -->
                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: rgba(255,255,255,0.1) !important;">
                        <span class="fs-7 text-secondary">Airtime Deducted</span>
                        <span id="airtime-deducted" class="fs-7 fw-semibold">0 XAF</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: rgba(255,255,255,0.1) !important;">
                        <span class="fs-7 text-secondary">Bundle Received</span>
                        <span id="bundle-received" class="fs-7 fw-semibold">-</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: rgba(255,255,255,0.1) !important;">
                        <span class="fs-7 text-secondary">LiquiSwap Bonus</span>
                        <span class="fs-7 fw-semibold text-success">+250 MB Extra</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3">
                        <span class="fs-6 fw-bold">Total Deductible</span>
                        <span id="total-deductible" class="fs-5 fw-bold text-primary">0 XAF</span>
                    </div>
                </div>
                
                <!-- Payer Info -->
                <div class="glass-card p-3" style="background: rgba(0,127,128,0.05);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-lg" style="width: 40px; height: 40px; background: rgba(0,127,128,0.1);">
                            <span class="material-symbols-outlined text-primary">person</span>
                        </div>
                        <div>
                            <p class="fs-8 text-secondary mb-0">Payer</p>
                            <p id="payer-display" class="fs-7 fw-semibold mb-0">-</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-3">
                <button type="button" class="btn btn-glass px-4 py-3" onclick="goToStep(2)">Back</button>
                <button type="button" class="btn btn-liquid flex-grow-1 py-3 d-flex align-items-center justify-content-center gap-2" onclick="confirmConversion()">
                    <span>Convert Airtime</span>
                    <span class="material-symbols-outlined">swap_horiz</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.step-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    border: 2px solid rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    transition: all 0.3s ease;
}

.step-indicator.active .step-circle {
    background: #007f80;
    border-color: #007f80;
    color: white;
}

.step-label {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 500;
}

.step-indicator.active .step-label {
    color: #007f80;
}

.step-line {
    width: 60px;
    height: 2px;
    background: rgba(255,255,255,0.2);
}

.fs-7 {
    font-size: 0.875rem;
}

.fs-8 {
    font-size: 0.75rem;
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

input[type="radio"]:checked + div {
    border-color: #007f80 !important;
    background: rgba(0, 127, 128, 0.1);
}
</style>

<script>
(function() {
    let selectedBundle = null;
    let currentStep = 1;
    
    // Navigation
    window.goToStep = function(step) {
        // Hide all steps
        document.querySelectorAll('.step-content').forEach(el => el.classList.add('d-none'));
        
        // Show selected step
        document.getElementById(`step${step}`).classList.remove('d-none');
        
        // Update indicators
        document.querySelectorAll('.step-indicator').forEach((el, index) => {
            if (index < step) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });
        
        currentStep = step;
    };
    
    // Set payer phone
    window.setPayerPhone = function(phone) {
        document.getElementById('payer-phone').value = phone;
    };
    
    // Select bundle
    window.selectBundle = function(id, name, price) {
        selectedBundle = { id, name, price };
        
        // Update UI
        document.querySelectorAll('.bundle-item .glass-card').forEach(card => {
            card.style.border = '2px solid transparent';
        });
        event.currentTarget.style.border = '2px solid #007f80';
        
        // Enable continue button
        const btn = document.getElementById('continue-to-confirm');
        btn.disabled = false;
        btn.textContent = `Continue with ${name}`;
    };
    
    // Confirm conversion
    window.confirmConversion = function() {
        if (!selectedBundle) {
            Utils.toast.error('Please select a bundle');
            return;
        }
        
        const phone = document.getElementById('payer-phone').value;
        if (!phone || phone.length < 9) {
            Utils.toast.error('Please enter a valid phone number');
            return;
        }
        
        Utils.showLoading('Converting airtime...');
        
        // Simulate conversion
        setTimeout(() => {
            Utils.hideLoading();
            Utils.toast.success('Airtime converted successfully!');
            
            // Reset form
            selectedBundle = null;
            document.getElementById('payer-phone').value = '';
            goToStep(1);
        }, 2000);
    };
    
    // Update confirmation details when going to step 3
    const originalGoToStep = window.goToStep;
    window.goToStep = function(step) {
        if (step === 3 && selectedBundle) {
            document.getElementById('airtime-deducted').textContent = Utils.formatCurrency(selectedBundle.price);
            document.getElementById('bundle-received').textContent = selectedBundle.name;
            document.getElementById('total-deductible').textContent = Utils.formatCurrency(selectedBundle.price);
            document.getElementById('payer-display').textContent = Utils.formatPhone(document.getElementById('payer-phone').value);
        }
        originalGoToStep(step);
    };
    
    // Search bundles
    document.getElementById('bundle-search')?.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll('.bundle-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(query) ? '' : 'none';
        });
    });
})();
</script>
