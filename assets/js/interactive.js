/**
 * LiquiSwap Interactive Elements
 * Comprehensive button functionality and user interactions
 */

const Interactive = {
    /**
     * Initialize all interactive elements
     */
    init() {
        this.initButtonEffects();
        this.initFormInteractions();
        this.initWalletInteractions();
        this.initSwapInteractions();
        this.initNavigation();
        this.initAnimations();
        this.initTooltips();
        this.initModals();
        this.initNotifications();
        this.initLoadingStates();
    },

    /**
     * Initialize button effects and interactions
     */
    initButtonEffects() {
        // Ripple effect for buttons
        document.querySelectorAll('.btn, .action-btn, .wallet-option').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Button hover effects with magnetic pull
        document.querySelectorAll('.btn-primary, .btn-success').forEach(button => {
            button.addEventListener('mouseenter', function(e) {
                this.style.transform = 'translateY(-2px) scale(1.02)';
            });
            
            button.addEventListener('mouseleave', function(e) {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Loading state for buttons
        document.querySelectorAll('[data-loading]').forEach(button => {
            button.addEventListener('click', function(e) {
                if (this.dataset.loading === 'true') {
                    e.preventDefault();
                    this.setLoading(true);
                    
                    // Simulate async operation
                    setTimeout(() => {
                        this.setLoading(false);
                    }, 2000);
                }
            });
        });
    },

    /**
     * Initialize form interactions
     */
    initFormInteractions() {
        // Login form
        const loginForm = document.querySelector('#loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleLogin(loginForm);
            });
        }

        // Registration form
        const registerForm = document.querySelector('#registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleRegistration(registerForm);
            });
        }

        // Password toggle
        document.querySelectorAll('[data-toggle-password]').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const input = document.querySelector(toggle.dataset.target);
                const icon = toggle.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Form validation
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });
    },

    /**
     * Initialize wallet interactions
     */
    initWalletInteractions() {
        // Wallet selection for swaps
        document.querySelectorAll('.wallet-option').forEach(wallet => {
            wallet.addEventListener('click', function() {
                // Remove previous selection
                document.querySelectorAll('.wallet-option').forEach(w => {
                    w.classList.remove('selected');
                });
                
                // Add selection to clicked wallet
                this.classList.add('selected');
                
                // Update hidden input
                const walletId = this.dataset.walletId;
                const input = document.querySelector('#from_wallet');
                if (input) {
                    input.value = walletId;
                }
                
                // Update display
                this.updateWalletDisplay(this);
            });
        });

        // Add wallet button
        document.querySelectorAll('[data-action="add-wallet"]').forEach(button => {
            button.addEventListener('click', () => {
                this.showAddWalletModal();
            });
        });
    },

    /**
     * Initialize swap interactions
     */
    initSwapInteractions() {
        // Amount input with real-time calculation
        const amountInput = document.querySelector('#swap_amount');
        if (amountInput) {
            amountInput.addEventListener('input', (e) => {
                this.calculateSwapAmount(e.target.value);
            });
        }

        // Provider selection
        document.querySelectorAll('[data-provider]').forEach(provider => {
            provider.addEventListener('click', function() {
                document.querySelectorAll('[data-provider]').forEach(p => {
                    p.classList.remove('active');
                });
                this.classList.add('active');
                
                const providerName = this.dataset.provider;
                document.querySelector('#to_provider').value = providerName;
            });
        });

        // Swap button
        const swapButton = document.querySelector('#swapButton');
        if (swapButton) {
            swapButton.addEventListener('click', () => {
                this.executeSwap();
            });
        }
    },

    /**
     * Initialize navigation
     */
    initNavigation() {
        // Mobile menu toggle
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const mobileMenu = document.querySelector('.mobile-menu');
        
        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener('click', () => {
                mobileMenu.classList.toggle('active');
                menuToggle.classList.toggle('active');
            });
        }

        // Navigation links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                
                // Handle external links
                if (href.startsWith('http')) {
                    return;
                }
                
                // Handle internal links with smooth transition
                e.preventDefault();
                this.navigateTo(href);
            });
        });

        // Logout
        document.querySelectorAll('[data-action="logout"]').forEach(button => {
            button.addEventListener('click', () => {
                this.handleLogout();
            });
        });
    },

    /**
     * Initialize animations
     */
    initAnimations() {
        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.card, .stat-card, .wallet-card').forEach(el => {
            observer.observe(el);
        });

        // Number counter animations
        document.querySelectorAll('[data-counter]').forEach(counter => {
            this.animateCounter(counter);
        });
    },

    /**
     * Initialize tooltips
     */
    initTooltips() {
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = e.target.dataset.tooltip;
                document.body.appendChild(tooltip);
                
                const rect = e.target.getBoundingClientRect();
                tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
                
                e.target._tooltip = tooltip;
            });
            
            element.addEventListener('mouseleave', (e) => {
                if (e.target._tooltip) {
                    e.target._tooltip.remove();
                    delete e.target._tooltip;
                }
            });
        });
    },

    /**
     * Initialize modals
     */
    initModals() {
        // Modal triggers
        document.querySelectorAll('[data-modal]').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const modalId = trigger.dataset.modal;
                this.showModal(modalId);
            });
        });

        // Modal close buttons
        document.querySelectorAll('.modal-close, [data-dismiss="modal"]').forEach(button => {
            button.addEventListener('click', () => {
                const modal = button.closest('.modal');
                this.hideModal(modal);
            });
        });

        // Close modal on background click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.hideModal(modal);
                }
            });
        });
    },

    /**
     * Initialize notifications
     */
    initNotifications() {
        // Notification button
        const notificationBtn = document.querySelector('.notification-icon');
        if (notificationBtn) {
            notificationBtn.addEventListener('click', () => {
                this.toggleNotifications();
            });
        }

        // Auto-hide notifications
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    },

    /**
     * Initialize loading states
     */
    initLoadingStates() {
        // Add loading method to buttons
        HTMLElement.prototype.setLoading = function(loading) {
            if (loading) {
                this.disabled = true;
                this.dataset.originalText = this.textContent;
                this.innerHTML = '<span class="spinner"></span> Loading...';
            } else {
                this.disabled = false;
                this.textContent = this.dataset.originalText || 'Submit';
            }
        };
    },

    /**
     * Handle login form submission
     */
    async handleLogin(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.setLoading(true);
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        try {
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            // Show success message
            this.showToast('Login successful! Redirecting...', 'success');
            
            // Redirect to dashboard
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1500);
            
        } catch (error) {
            this.showToast('Login failed. Please try again.', 'error');
        } finally {
            submitBtn.setLoading(false);
        }
    },

    /**
     * Handle registration form submission
     */
    async handleRegistration(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.setLoading(true);
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        try {
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            this.showToast('Registration successful! Please login.', 'success');
            
            // Switch to login form
            setTimeout(() => {
                this.switchToLogin();
            }, 1500);
            
        } catch (error) {
            this.showToast('Registration failed. Please try again.', 'error');
        } finally {
            submitBtn.setLoading(false);
        }
    },

    /**
     * Calculate swap amount with fees
     */
    calculateSwapAmount(amount) {
        const fee = amount * 0.025; // 2.5% fee
        const receiverGets = amount - fee;
        
        const feeElement = document.querySelector('#swap_fee');
        const receiverElement = document.querySelector('#receiver_gets');
        
        if (feeElement) feeElement.textContent = this.formatCurrency(fee);
        if (receiverElement) receiverElement.textContent = this.formatCurrency(receiverGets);
    },

    /**
     * Execute swap transaction
     */
    async executeSwap() {
        const swapButton = document.querySelector('#swapButton');
        swapButton.setLoading(true);
        
        try {
            // Simulate swap processing
            await new Promise(resolve => setTimeout(resolve, 3000));
            
            this.showToast('Swap completed successfully!', 'success');
            
            // Redirect to history
            setTimeout(() => {
                window.location.href = 'history.php';
            }, 2000);
            
        } catch (error) {
            this.showToast('Swap failed. Please try again.', 'error');
        } finally {
            swapButton.setLoading(false);
        }
    },

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },

    /**
     * Format currency
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'XAF'
        }).format(amount);
    },

    /**
     * Navigate to page with transition
     */
    navigateTo(href) {
        document.body.style.opacity = '0';
        setTimeout(() => {
            window.location.href = href;
        }, 300);
    },

    /**
     * Validate form
     */
    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], select[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                this.showFieldError(input, 'This field is required');
                isValid = false;
            } else {
                this.clearFieldError(input);
            }
        });
        
        return isValid;
    },

    /**
     * Show field error
     */
    showFieldError(field, message) {
        field.classList.add('error');
        
        let errorElement = field.parentNode.querySelector('.field-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'field-error';
            field.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
    },

    /**
     * Clear field error
     */
    clearFieldError(field) {
        field.classList.remove('error');
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    Interactive.init();
});

// Add CSS for interactive elements
const interactiveStyles = `
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        animation: ripple-animation 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .wallet-option.selected {
        border-color: var(--teal);
        background: rgba(0, 128, 128, 0.1);
        transform: scale(1.02);
    }
    
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    }
    
    .toast.show {
        transform: translateX(0);
    }
    
    .toast-success {
        background: var(--success);
    }
    
    .toast-error {
        background: var(--danger);
    }
    
    .toast-info {
        background: var(--teal);
    }
    
    .field-error {
        color: var(--danger);
        font-size: 12px;
        margin-top: 5px;
    }
    
    .form-control.error {
        border-color: var(--danger);
    }
    
    .animate-in {
        animation: slideInUp 0.6s ease forwards;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .tooltip {
        position: absolute;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
    }
    
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    
    .modal.show {
        display: flex;
    }
    
    .modal-content {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 20px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }
`;

// Add styles to head
const styleSheet = document.createElement('style');
styleSheet.textContent = interactiveStyles;
document.head.appendChild(styleSheet);
