<!-- Transaction History Page -->
<div class="history-page min-vh-100 position-relative" style="background: #0a0f0f;">
    <!-- Header -->
    <header class="sticky-top z-50 glass border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center" style="max-width: 1400px; margin: 0 auto;">
            <div class="d-flex align-items-center gap-3">
                <a href="/dashboard" class="btn btn-icon">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="fs-5 fw-bold mb-0">Transaction History</h1>
                    <p class="fs-8 text-secondary mb-0">Monitor your liquid assets</p>
                </div>
            </div>
            <button class="btn btn-icon" onclick="exportHistory()">
                <span class="material-symbols-outlined">download</span>
            </button>
        </div>
    </header>

    <div class="main-content-area p-4" style="max-width: 1000px; margin: 0 auto;">
        <!-- Search & Filters -->
        <div class="d-flex flex-column gap-3 mb-4">
            <div class="position-relative">
                <span class="material-symbols-outlined position-absolute start-0 top-50 translate-middle-y ms-3 text-secondary">search</span>
                <input type="text" id="history-search" class="form-control form-control-liquid ps-5" placeholder="Search by network, type or amount...">
            </div>
            <div class="d-flex gap-2 overflow-auto pb-2 no-scrollbar">
                <button class="btn btn-glass d-flex align-items-center gap-2 py-2 px-3">
                    <span class="material-symbols-outlined fs-6">calendar_today</span>
                    <span>Date Range</span>
                </button>
                <button class="btn btn-liquid py-2 px-3">Swap</button>
                <button class="btn btn-glass py-2 px-3">Bundle</button>
                <button class="btn btn-glass py-2 px-3">Airtime</button>
                <button class="btn btn-glass py-2 px-3">Success</button>
                <button class="btn btn-glass py-2 px-3">Pending</button>
            </div>
        </div>

        <!-- Transaction Groups -->
        <div class="d-flex flex-column gap-4">
            <!-- Today -->
            <section>
                <h3 class="text-secondary fs-8 fw-bold text-uppercase mb-3 d-flex align-items-center gap-3" style="letter-spacing: 0.2em;">
                    Today
                    <span class="flex-grow-1 border-bottom" style="border-color: rgba(0,127,128,0.1) !important;"></span>
                </h3>
                <div class="d-flex flex-column gap-2">
                    <!-- Transaction 1 -->
                    <div class="glass-card p-3 d-flex align-items-center justify-content-between magnetic-hover cursor-pointer" onclick="showTransaction('tx1')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(249, 115, 22, 0.1);">
                                <span class="material-symbols-outlined text-orange">swap_horiz</span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0">Swap OM to MOMO</p>
                                <p class="fs-8 text-secondary mb-0">Orange Money → MTN MoMo</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-end d-none d-sm-block">
                                <p class="fs-8 text-secondary mb-0">14:24 PM</p>
                                <span class="badge badge-success">Success</span>
                            </div>
                            <div class="text-end">
                                <p class="fw-bold text-danger mb-0">- 25,500 XAF</p>
                                <p class="fs-9 text-secondary mb-0">Fee: 150 XAF</p>
                            </div>
                            <span class="material-symbols-outlined text-secondary">chevron_right</span>
                        </div>
                    </div>

                    <!-- Transaction 2 -->
                    <div class="glass-card p-3 d-flex align-items-center justify-content-between magnetic-hover cursor-pointer" onclick="showTransaction('tx2')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(251, 191, 36, 0.1);">
                                <span class="material-symbols-outlined text-warning">data_usage</span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0">MTN Data Bundle 2GB</p>
                                <p class="fs-8 text-secondary mb-0">Mobile Data • 677 xxx xxx</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-end d-none d-sm-block">
                                <p class="fs-8 text-secondary mb-0">09:12 AM</p>
                                <span class="badge badge-pending">Pending</span>
                            </div>
                            <div class="text-end">
                                <p class="fw-bold text-danger mb-0">- 1,000 XAF</p>
                                <p class="fs-9 text-secondary mb-0">Instant activation</p>
                            </div>
                            <span class="material-symbols-outlined text-secondary">chevron_right</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Yesterday -->
            <section>
                <h3 class="text-secondary fs-8 fw-bold text-uppercase mb-3 d-flex align-items-center gap-3" style="letter-spacing: 0.2em;">
                    Yesterday
                    <span class="flex-grow-1 border-bottom" style="border-color: rgba(0,127,128,0.1) !important;"></span>
                </h3>
                <div class="d-flex flex-column gap-2">
                    <!-- Transaction 3 -->
                    <div class="glass-card p-3 d-flex align-items-center justify-content-between magnetic-hover cursor-pointer" onclick="showTransaction('tx3')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(0, 127, 128, 0.1);">
                                <span class="material-symbols-outlined text-primary">account_balance_wallet</span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0">Wallet Top-up</p>
                                <p class="fs-8 text-secondary mb-0">Card Payment • **** 4421</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-end d-none d-sm-block">
                                <p class="fs-8 text-secondary mb-0">18:45 PM</p>
                                <span class="badge badge-success">Success</span>
                            </div>
                            <div class="text-end">
                                <p class="fw-bold text-success mb-0">+ 150,000 XAF</p>
                                <p class="fs-9 text-secondary mb-0">Verified payment</p>
                            </div>
                            <span class="material-symbols-outlined text-secondary">chevron_right</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- This Week -->
            <section>
                <h3 class="text-secondary fs-8 fw-bold text-uppercase mb-3 d-flex align-items-center gap-3" style="letter-spacing: 0.2em;">
                    This Week
                    <span class="flex-grow-1 border-bottom" style="border-color: rgba(0,127,128,0.1) !important;"></span>
                </h3>
                <div class="d-flex flex-column gap-2">
                    <!-- Transaction 4 -->
                    <div class="glass-card p-3 d-flex align-items-center justify-content-between magnetic-hover cursor-pointer" style="opacity: 0.7;" onclick="showTransaction('tx4')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(239, 68, 68, 0.1);">
                                <span class="material-symbols-outlined text-danger">error</span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0">Airtime Purchase</p>
                                <p class="fs-8 text-secondary mb-0">Orange • 699 xxx xxx</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-end d-none d-sm-block">
                                <p class="fs-8 text-secondary mb-0">Oct 24, 11:05 AM</p>
                                <span class="badge badge-failed">Failed</span>
                            </div>
                            <div class="text-end">
                                <p class="fw-bold text-secondary mb-0">500 XAF</p>
                                <p class="fs-9 text-danger mb-0">Insuff. funds</p>
                            </div>
                            <span class="material-symbols-outlined text-secondary">chevron_right</span>
                        </div>
                    </div>

                    <!-- Transaction 5 -->
                    <div class="glass-card p-3 d-flex align-items-center justify-content-between magnetic-hover cursor-pointer" onclick="showTransaction('tx5')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1);">
                                <span class="material-symbols-outlined text-primary">payments</span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0">Cash-out to Bank</p>
                                <p class="fs-8 text-secondary mb-0">UBA Cameroon • FR23..901</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="text-end d-none d-sm-block">
                                <p class="fs-8 text-secondary mb-0">Oct 22, 16:30 PM</p>
                                <span class="badge badge-success">Success</span>
                            </div>
                            <div class="text-end">
                                <p class="fw-bold text-danger mb-0">- 45,000 XAF</p>
                                <p class="fs-9 text-secondary mb-0">Ref: LS-9932</p>
                            </div>
                            <span class="material-symbols-outlined text-secondary">chevron_right</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Empty State (hidden by default) -->
        <div id="empty-state" class="d-none flex-column align-items-center justify-content-center py-5 text-center">
            <div class="d-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 96px; height: 96px; background: rgba(0,127,128,0.1);">
                <span class="material-symbols-outlined fs-1" style="color: rgba(0,127,128,0.4);">history_toggle_off</span>
            </div>
            <h4 class="fs-5 fw-bold mb-1">No transactions found</h4>
            <p class="text-secondary fs-7 mb-3">Try adjusting your filters or date range.</p>
            <button class="btn btn-liquid px-4 py-2" onclick="clearFilters()">Clear All Filters</button>
        </div>
    </div>
</div>

<style>
.fs-9 {
    font-size: 0.625rem;
}

.badge {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.25rem 0.5rem;
    border-radius: 999px;
}

.badge-success {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.badge-pending {
    background: rgba(251, 191, 36, 0.2);
    color: #fbbf24;
    border: 1px solid rgba(251, 191, 36, 0.3);
}

.badge-failed {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
}
</style>

<script>
(function() {
    const searchInput = document.getElementById('history-search');
    const transactions = document.querySelectorAll('.glass-card.magnetic-hover');
    const emptyState = document.getElementById('empty-state');
    
    // Search functionality
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        let visibleCount = 0;
        
        transactions.forEach(tx => {
            const text = tx.textContent.toLowerCase();
            const isVisible = text.includes(query);
            tx.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });
        
        // Show/hide empty state
        if (visibleCount === 0 && query.length > 0) {
            emptyState.classList.remove('d-none');
            emptyState.classList.add('d-flex');
        } else {
            emptyState.classList.add('d-none');
            emptyState.classList.remove('d-flex');
        }
    });
    
    // Show transaction details
    window.showTransaction = function(id) {
        Utils.toast.info('Transaction details: ' + id);
    };
    
    // Export history
    window.exportHistory = function() {
        Utils.toast.success('Exporting transaction history...');
    };
    
    // Clear filters
    window.clearFilters = function() {
        searchInput.value = '';
        transactions.forEach(tx => tx.style.display = '');
        emptyState.classList.add('d-none');
        emptyState.classList.remove('d-flex');
    };
})();
</script>
