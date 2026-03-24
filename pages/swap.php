<!-- OM ↔ MOMO Swap Page -->
<div class="swap-page min-vh-100 position-relative" style="background: #0a0f0f;">
    <!-- Header -->
    <header class="sticky-top z-50 glass border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center" style="max-width: 1400px; margin: 0 auto;">
            <div class="d-flex align-items-center gap-3">
                <a href="/dashboard" class="btn btn-icon">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="fs-5 fw-bold mb-0">OM ↔ MOMO Swap</h1>
                    <p class="fs-8 text-secondary mb-0">Transfer between networks instantly</p>
                </div>
            </div>
            <button class="btn btn-icon">
                <span class="material-symbols-outlined">help</span>
            </button>
        </div>
    </header>

    <div class="main-content-area p-4" style="max-width: 800px; margin: 0 auto;">
        <!-- Hero Section -->
        <div class="glass-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h2 class="fs-4 fw-bold mb-2">Transfer Instantly</h2>
                    <p class="text-secondary fs-7 mb-0">No hidden charges. Competitive rates.</p>
                </div>
                <div class="text-end">
                    <span class="fs-8 text-secondary d-block">Current Rate</span>
                    <span class="fs-3 fw-bold text-primary">1.00</span>
                </div>
            </div>
            
            <!-- Exchange Rate Display -->
            <div class="d-flex align-items-center justify-content-center gap-4 mb-4">
                <div class="text-center">
                    <div class="d-flex align-items-center justify-content-center rounded-xl mb-2" style="width: 60px; height: 60px; background: linear-gradient(135deg, #f97316, #ea580c);">
                        <span class="material-symbols-outlined text-white fs-2">signal_cellular_alt</span>
                    </div>
                    <span class="fs-7 fw-semibold">Orange</span>
                </div>
                <div class="d-flex flex-column align-items-center">
                    <span class="material-symbols-outlined text-secondary fs-3">sync_alt</span>
                    <span class="fs-8 text-secondary">1:1</span>
                </div>
                <div class="text-center">
                    <div class="d-flex align-items-center justify-content-center rounded-xl mb-2" style="width: 60px; height: 60px; background: linear-gradient(135deg, #FFD700, #FFA500);">
                        <span class="material-symbols-outlined text-dark fs-2">signal_cellular_alt</span>
                    </div>
                    <span class="fs-7 fw-semibold">MTN</span>
                </div>
            </div>
        </div>

        <!-- Fee Calculator -->
        <div class="glass-card p-4 mb-4">
            <h3 class="fs-6 fw-bold mb-4">Fee Calculator</h3>
            
            <!-- Amount Input -->
            <div class="mb-4">
                <label class="d-block fs-8 fw-semibold text-uppercase mb-2" style="color: #64748b; letter-spacing: 0.1em;">Amount to Send (XAF)</label>
                <div class="position-relative">
                    <input type="number" id="swap-amount" class="form-control form-control-liquid fs-4 fw-bold" 
                           placeholder="0" min="100" max="5000000" style="padding-left: 1rem;">
                    <span class="position-absolute end-0 top-50 translate-middle-y me-3 fs-7 text-secondary">XAF</span>
                </div>
                <div class="d-flex gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-glass flex-1 py-2" onclick="setAmount(1000)">1,000</button>
                    <button type="button" class="btn btn-sm btn-glass flex-1 py-2" onclick="setAmount(5000)">5,000</button>
                    <button type="button" class="btn btn-sm btn-glass flex-1 py-2" onclick="setAmount(10000)">10,000</button>
                    <button type="button" class="btn btn-sm btn-glass flex-1 py-2" onclick="setAmount(50000)">50,000</button>
                </div>
            </div>

            <!-- Calculation Results -->
            <div id="calculation-results" class="d-none">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: rgba(255,255,255,0.1) !important;">
                    <span class="fs-7 text-secondary">Amount to send</span>
                    <span id="calc-amount" class="fs-7 fw-semibold">0 XAF</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: rgba(255,255,255,0.1) !important;">
                    <span class="fs-7 text-secondary">Fee (1.5%)</span>
                    <span id="calc-fee" class="fs-7 fw-semibold text-orange">0 XAF</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-3">
                    <span class="fs-6 fw-bold">Receiver gets</span>
                    <span id="calc-receiver" class="fs-5 fw-bold text-primary">0 XAF</span>
                </div>
            </div>

            <!-- Cashout Fee Option -->
            <div class="d-flex align-items-center gap-3 p-3 rounded-lg mt-3" style="background: rgba(0, 127, 128, 0.05);">
                <input type="checkbox" id="cashout-fee" class="form-check-input" style="width: 1.25rem; height: 1.25rem;">
                <label for="cashout-fee" class="fs-7 mb-0 flex-grow-1">
                    <span class="fw-semibold">I pay the cashout fee</span>
                    <span class="d-block text-secondary">Add 2% to cover receiver's withdrawal</span>
                </label>
            </div>
        </div>

        <!-- Source Account -->
        <div class="glass-card p-4 mb-4">
            <h3 class="fs-6 fw-bold mb-3">Source Account</h3>
            <div class="d-flex flex-wrap gap-2">
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="source" value="ORANGE" class="d-none" checked>
                    <div class="glass-card p-3 text-center haptic-click" style="border: 2px solid #f97316;">
                        <span class="material-symbols-outlined text-orange fs-4 mb-1">signal_cellular_alt</span>
                        <p class="fs-7 fw-semibold mb-0">Orange Money</p>
                        <p class="fs-8 text-secondary mb-0">300,000 XAF</p>
                    </div>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="source" value="MTN" class="d-none">
                    <div class="glass-card p-3 text-center haptic-click" style="border: 2px solid transparent;">
                        <span class="material-symbols-outlined text-warning fs-4 mb-1">signal_cellular_alt</span>
                        <p class="fs-7 fw-semibold mb-0">MTN MoMo</p>
                        <p class="fs-8 text-secondary mb-0">450,000 XAF</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Recipient -->
        <div class="glass-card p-4 mb-4">
            <h3 class="fs-6 fw-bold mb-3">Recipient</h3>
            <div class="mb-3">
                <label class="d-block fs-8 fw-semibold text-uppercase mb-2" style="color: #64748b; letter-spacing: 0.1em;">Phone Number</label>
                <div class="input-group input-group-liquid">
                    <span class="input-group-text">
                        <span class="me-2">🇨🇲</span>
                        <span class="text-secondary">+237</span>
                    </span>
                    <input type="tel" id="recipient-phone" class="form-control form-control-liquid" placeholder="6XX XXX XXX">
                </div>
            </div>
            
            <!-- Recent Contacts -->
            <div class="mb-3">
                <p class="fs-8 text-secondary mb-2">Recent</p>
                <div class="d-flex gap-2 overflow-auto pb-2 no-scrollbar">
                    <button type="button" class="btn btn-glass d-flex align-items-center gap-2 py-2 px-3" onclick="setRecipient('677123456')">
                        <span class="material-symbols-outlined fs-6">person</span>
                        <span class="fs-8">Maman</span>
                    </button>
                    <button type="button" class="btn btn-glass d-flex align-items-center gap-2 py-2 px-3" onclick="setRecipient('677234567')">
                        <span class="material-symbols-outlined fs-6">person</span>
                        <span class="fs-8">Papa</span>
                    </button>
                    <button type="button" class="btn btn-glass d-flex align-items-center gap-2 py-2 px-3" onclick="setRecipient('677345678')">
                        <span class="material-symbols-outlined fs-6">person</span>
                        <span class="fs-8">Junior</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Review Button -->
        <button type="button" class="btn btn-liquid w-100 py-4 mb-4 d-flex align-items-center justify-content-center gap-2" onclick="reviewSwap()">
            <span>Review Swap</span>
            <span class="material-symbols-outlined">arrow_forward</span>
        </button>
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

input[type="radio"]:checked + div {
    border-color: #007f80 !important;
    background: rgba(0, 127, 128, 0.1);
}
</style>

<script>
(function() {
    const amountInput = document.getElementById('swap-amount');
    const resultsDiv = document.getElementById('calculation-results');
    const calcAmount = document.getElementById('calc-amount');
    const calcFee = document.getElementById('calc-fee');
    const calcReceiver = document.getElementById('calc-receiver');
    const cashoutCheckbox = document.getElementById('cashout-fee');
    
    function calculateFees() {
        const amount = parseFloat(amountInput.value) || 0;
        if (amount <= 0) {
            resultsDiv.classList.add('d-none');
            return;
        }
        
        const feePercentage = cashoutCheckbox.checked ? 2.0 : 1.5;
        const fee = Math.round(amount * feePercentage / 100);
        const receiverGets = amount - fee;
        
        calcAmount.textContent = Utils.formatCurrency(amount);
        calcFee.textContent = Utils.formatCurrency(fee) + ` (${feePercentage}%)`;
        calcReceiver.textContent = Utils.formatCurrency(receiverGets);
        
        resultsDiv.classList.remove('d-none');
    }
    
    amountInput.addEventListener('input', calculateFees);
    cashoutCheckbox.addEventListener('change', calculateFees);
    
    window.setAmount = function(value) {
        amountInput.value = value;
        calculateFees();
    };
    
    window.setRecipient = function(phone) {
        document.getElementById('recipient-phone').value = phone;
    };
    
    window.reviewSwap = function() {
        const amount = parseFloat(amountInput.value);
        if (!amount || amount < 100) {
            Utils.toast.error('Please enter a valid amount (minimum 100 XAF)');
            return;
        }
        
        const phone = document.getElementById('recipient-phone').value;
        if (!phone || phone.length < 9) {
            Utils.toast.error('Please enter a valid phone number');
            return;
        }
        
        // Show confirmation modal
        Utils.toast.success('Swap ready for confirmation');
    };
})();
</script>
