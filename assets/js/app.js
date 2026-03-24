/**
 * LiquiSwap Main Application
 * Core application logic and initialization
 */

const App = {
    /**
     * Initialize application
     */
    init() {
        this.checkAuth();
        this.initEventListeners();
        this.initMagneticButtons();
        this.updateGreeting();
    },
    
    /**
     * Check authentication status
     */
    async checkAuth() {
        try {
            const response = await API.auth.check();
            
            if (response.success && response.authenticated) {
                // Store current user info
                window.CURRENT_USER = response.user;
                
                // Update UI elements that depend on auth
                this.updateAuthUI(response.user);
            } else {
                // User is not authenticated
                window.CURRENT_USER = null;
            }
        } catch (error) {
            console.error('Auth check failed:', error);
        }
    },
    
    /**
     * Update UI for authenticated user
     */
    updateAuthUI(user) {
        // Update greeting
        const greetingEl = document.querySelector('.user-greeting');
        if (greetingEl) {
            greetingEl.textContent = Utils.getGreeting(user.full_name);
        }
        
        // Update user name displays
        document.querySelectorAll('.user-name').forEach(el => {
            el.textContent = user.full_name;
        });
        
        // Update trust score
        document.querySelectorAll('.trust-score').forEach(el => {
            el.textContent = user.trust_score + '%';
        });
    },
    
    /**
     * Update greeting based on time
     */
    updateGreeting() {
        const greetingEl = document.querySelector('.time-greeting');
        if (greetingEl) {
            const user = window.CURRENT_USER;
            greetingEl.textContent = Utils.getGreeting(user?.name || '');
        }
    },
    
    /**
     * Initialize global event listeners
     */
    initEventListeners() {
        // Form submissions
        document.querySelectorAll('form[data-ajax]').forEach(form => {
            form.addEventListener('submit', this.handleFormSubmit.bind(this));
        });
        
        // Logout button
        document.querySelectorAll('[data-action="logout"]').forEach(btn => {
            btn.addEventListener('click', this.handleLogout.bind(this));
        });
        
        // Toggle password visibility
        document.querySelectorAll('[data-toggle-password]').forEach(btn => {
            btn.addEventListener('click', this.togglePassword.bind(this));
        });
        
        // Number inputs - prevent non-numeric
        document.querySelectorAll('input[type="number"], input[data-number-only]').forEach(input => {
            input.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            });
        });
        
        // Auto-format phone inputs
        document.querySelectorAll('input[data-phone]').forEach(input => {
            input.addEventListener('input', (e) => {
                const formatted = Utils.formatPhone(e.target.value);
                if (formatted !== e.target.value) {
                    e.target.value = formatted;
                }
            });
        });
        
        // Amount inputs - format as currency
        document.querySelectorAll('input[data-amount]').forEach(input => {
            input.addEventListener('blur', (e) => {
                const value = parseFloat(e.target.value);
                if (!isNaN(value)) {
                    e.target.value = Utils.formatNumber(value);
                }
            });
        });
        
        // Copy to clipboard
        document.querySelectorAll('[data-copy]').forEach(el => {
            el.addEventListener('click', async (e) => {
                const text = el.dataset.copy;
                const success = await Utils.copyToClipboard(text);
                if (success) {
                    Utils.toast.success('Copied to clipboard');
                }
            });
        });
    },
    
    /**
     * Initialize magnetic button effects
     */
    initMagneticButtons() {
        Utils.magneticButton.init();
    },
    
    /**
     * Handle form submissions
     */
    async handleFormSubmit(e) {
        const form = e.target;
        const action = form.dataset.action;
        
        if (!action) return;
        
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Show loading
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn?.textContent || '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        }
        
        try {
            let result;
            
            switch (action) {
                case 'login':
                    result = await API.auth.login(data.phone, data.password, data.remember === 'on');
                    if (result.success) {
                        Utils.toast.success('Welcome back!');
                        setTimeout(() => window.location.href = '/dashboard', 500);
                    } else {
                        Utils.toast.error(result.error || 'Login failed');
                    }
                    break;
                    
                case 'register':
                    result = await API.auth.register(data);
                    if (result.success) {
                        Utils.toast.success('Account created successfully!');
                        setTimeout(() => window.location.href = '/login', 500);
                    } else {
                        const error = result.errors?.[0] || result.error || 'Registration failed';
                        Utils.toast.error(error);
                    }
                    break;
                    
                case 'swap':
                    result = await API.swap.create(data);
                    if (result.success) {
                        Utils.toast.success('Swap initiated');
                        // Show confirmation modal
                        this.showSwapConfirmation(result);
                    } else {
                        Utils.toast.error(result.error);
                    }
                    break;
                    
                case 'purchase-bundle':
                    result = await API.bundles.purchase(data.bundle_id, data.phone_number);
                    if (result.success) {
                        Utils.toast.success('Bundle purchased!');
                    } else {
                        Utils.toast.error(result.error);
                    }
                    break;
                    
                default:
                    console.warn('Unknown form action:', action);
            }
            
        } catch (error) {
            Utils.toast.error('An error occurred. Please try again.');
            console.error('Form submission error:', error);
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    },
    
    /**
     * Handle logout
     */
    async handleLogout() {
        try {
            await API.auth.logout();
            Utils.toast.info('Logged out successfully');
            setTimeout(() => window.location.href = '/login', 500);
        } catch (error) {
            console.error('Logout error:', error);
            window.location.href = '/login';
        }
    },
    
    /**
     * Toggle password visibility
     */
    togglePassword(e) {
        const btn = e.currentTarget;
        const input = document.querySelector(btn.dataset.togglePassword);
        
        if (input) {
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            
            const icon = btn.querySelector('.material-symbols-outlined');
            if (icon) {
                icon.textContent = type === 'password' ? 'visibility' : 'visibility_off';
            }
        }
    },
    
    /**
     * Show swap confirmation modal
     */
    showSwapConfirmation(result) {
        // Implementation depends on modal structure
        const modal = document.getElementById('swap-confirmation-modal');
        if (modal) {
            modal.querySelector('.swap-amount').textContent = Utils.formatCurrency(result.data.amount);
            modal.querySelector('.swap-fee').textContent = Utils.formatCurrency(result.data.fee);
            modal.querySelector('.swap-receiver').textContent = result.data.receiver;
            
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    },
    
    /**
     * Confirm and execute swap
     */
    async confirmSwap(reference) {
        Utils.showLoading('Processing swap...');
        
        try {
            const result = await API.swap.confirm(reference);
            
            if (result.success) {
                Utils.toast.success('Swap completed successfully!');
                // Refresh balances
                this.refreshWalletBalances();
                // Close modal
                const modal = document.getElementById('swap-confirmation-modal');
                if (modal) {
                    bootstrap.Modal.getInstance(modal)?.hide();
                }
                // Redirect to history
                setTimeout(() => window.location.href = '/history', 1000);
            } else {
                Utils.toast.error(result.error || 'Swap failed');
            }
        } catch (error) {
            Utils.toast.error('Swap failed. Please try again.');
        } finally {
            Utils.hideLoading();
        }
    },
    
    /**
     * Refresh wallet balances
     */
    async refreshWalletBalances() {
        try {
            const result = await API.user.wallets();
            if (result.success) {
                // Update UI with new balances
                result.data.wallets.forEach(wallet => {
                    const el = document.querySelector(`[data-wallet="${wallet.provider}"] .balance`);
                    if (el) {
                        el.textContent = Utils.formatCurrency(wallet.balance);
                    }
                });
                
                // Update total balance
                const totalEl = document.querySelector('.total-balance');
                if (totalEl) {
                    totalEl.textContent = Utils.formatCurrency(result.data.total_balance);
                }
            }
        } catch (error) {
            console.error('Failed to refresh balances:', error);
        }
    },
    
    /**
     * Load and display transactions
     */
    async loadTransactions(container, options = {}) {
        try {
            const result = await API.transactions.list(options);
            
            if (!result.success) {
                container.innerHTML = '<p class="text-center text-muted">Failed to load transactions</p>';
                return;
            }
            
            if (result.data.transactions.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <span class="material-symbols-outlined" style="font-size: 3rem; opacity: 0.3;">history</span>
                        <p class="text-muted mt-2">No transactions yet</p>
                    </div>
                `;
                return;
            }
            
            // Render transactions
            container.innerHTML = result.data.transactions.map(t => this.renderTransactionItem(t)).join('');
            
        } catch (error) {
            console.error('Failed to load transactions:', error);
            container.innerHTML = '<p class="text-center text-muted">Error loading transactions</p>';
        }
    },
    
    /**
     * Render transaction item HTML
     */
    renderTransactionItem(t) {
        const icons = {
            swap: 'swap_horiz',
            bundle: 'package_2',
            airtime: 'phone_android',
            conversion: 'sync_alt',
            deposit: 'add_circle',
            withdrawal: 'remove_circle'
        };
        
        const statusColors = {
            success: 'success',
            pending: 'pending',
            failed: 'failed',
            processing: 'pending'
        };
        
        return `
            <div class="transaction-item glass-card p-3 mb-2" data-id="${t.transaction_uuid}">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-wrapper rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 44px; height: 44px; background: rgba(0, 127, 128, 0.1);">
                        <span class="material-symbols-outlined text-primary">${icons[t.type] || 'payments'}</span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-0 fw-semibold">${t.type.charAt(0).toUpperCase() + t.type.slice(1)}${t.subtype ? ' - ' + t.subtype : ''}</p>
                                <small class="text-muted">${t.time_ago}</small>
                            </div>
                            <span class="badge badge-status badge-${statusColors[t.status] || 'pending'}">${t.status}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted">${t.receiver_identifier ? Utils.formatPhone(t.receiver_identifier) : ''}</small>
                            <span class="fw-bold ${t.is_positive ? 'text-success' : ''}">
                                ${t.is_positive ? '+' : '-'}${t.formatted_amount}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },
    
    /**
     * Calculate and display swap fees
     */
    async calculateSwapFees(amount, includeCashout = false) {
        try {
            const result = await API.swap.calculate(amount, includeCashout);
            
            if (result.success) {
                return result.data;
            }
            return null;
        } catch (error) {
            console.error('Fee calculation failed:', error);
            return null;
        }
    }
};

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    App.init();
});

// Make App available globally
window.App = App;
