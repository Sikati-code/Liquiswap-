/**
 * LiquiSwap Utilities
 * Helper functions, toast notifications, and UI utilities
 */

const Utils = {
    /**
     * Format currency
     */
    formatCurrency(amount, currency = 'XAF') {
        const formatter = new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
        return formatter.format(amount);
    },
    
    /**
     * Format number with commas
     */
    formatNumber(num) {
        return new Intl.NumberFormat('fr-FR').format(num);
    },
    
    /**
     * Format phone number (Cameroon format)
     */
    formatPhone(phone) {
        const cleaned = phone.replace(/\D/g, '');
        const match = cleaned.match(/^(237)?(\d{2})(\d{2})(\d{2})(\d{3})$/);
        if (match) {
            return `${match[2]} ${match[3]} ${match[4]} ${match[5]}`;
        }
        return phone;
    },
    
    /**
     * Normalize phone to +237 format
     */
    normalizePhone(phone) {
        const cleaned = phone.replace(/\D/g, '');
        if (cleaned.length === 9) {
            return '+237' + cleaned;
        }
        if (cleaned.startsWith('237') && cleaned.length === 12) {
            return '+' + cleaned;
        }
        if (cleaned.startsWith('0') && cleaned.length === 10) {
            return '+237' + cleaned.substring(1);
        }
        return phone;
    },
    
    /**
     * Validate Cameroon phone number
     */
    isValidPhone(phone) {
        const pattern = /^(\+237\s?)?[6|2][0-9]{8}$/;
        return pattern.test(this.normalizePhone(phone));
    },
    
    /**
     * Get greeting based on time
     */
    getGreeting(name = '') {
        const hour = new Date().getHours();
        let greeting = '';
        
        if (hour >= 5 && hour < 12) {
            greeting = 'Bonjour';
        } else if (hour >= 12 && hour < 18) {
            greeting = "Bon après-midi";
        } else {
            greeting = 'Bonsoir';
        }
        
        return name ? `${greeting}, ${name}!` : greeting;
    },
    
    /**
     * Format relative time
     */
    timeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000);
        
        if (diff < 60) return 'Just now';
        if (diff < 3600) return `${Math.floor(diff / 60)} min ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)} hours ago`;
        if (diff < 604800) return `${Math.floor(diff / 86400)} days ago`;
        
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },
    
    /**
     * Debounce function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    /**
     * Throttle function
     */
    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },
    
    /**
     * Generate UUID
     */
    generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    },
    
    /**
     * Copy to clipboard
     */
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (err) {
            // Fallback
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                return true;
            } catch (e) {
                return false;
            } finally {
                document.body.removeChild(textarea);
            }
        }
    },
    
    /**
     * Show loading overlay
     */
    showLoading(message = 'Processing...') {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            const msgEl = overlay.querySelector('p');
            if (msgEl) msgEl.textContent = message;
            overlay.classList.remove('d-none');
        }
    },
    
    /**
     * Hide loading overlay
     */
    hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.add('d-none');
        }
    },
    
    /**
     * Animate element
     */
    animate(element, animation, duration = 300) {
        return new Promise((resolve) => {
            element.style.animation = `${animation} ${duration}ms ease forwards`;
            setTimeout(() => {
                element.style.animation = '';
                resolve();
            }, duration);
        });
    },
    
    /**
     * Scroll to element
     */
    scrollTo(element, offset = 80) {
        const top = element.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top, behavior: 'smooth' });
    },
    
    /**
     * Check if element is in viewport
     */
    isInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    },
    
    /**
     * Store data in localStorage
     */
    storage: {
        set(key, value) {
            try {
                localStorage.setItem(key, JSON.stringify(value));
                return true;
            } catch (e) {
                return false;
            }
        },
        
        get(key, defaultValue = null) {
            try {
                const item = localStorage.getItem(key);
                return item ? JSON.parse(item) : defaultValue;
            } catch (e) {
                return defaultValue;
            }
        },
        
        remove(key) {
            localStorage.removeItem(key);
        },
        
        clear() {
            localStorage.clear();
        }
    }
};

/**
 * Toast Notifications
 */
Utils.toast = {
    container: null,
    
    init() {
        this.container = document.getElementById('toast-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            this.container.style.zIndex = '9999';
            this.container.style.paddingBottom = 'calc(70px + 1rem)';
            document.body.appendChild(this.container);
        }
    },
    
    show(message, type = 'info', duration = 3000) {
        this.init();
        
        const toast = document.createElement('div');
        toast.className = `toast toast-liquid ${type} align-items-center mb-2`;
        toast.setAttribute('role', 'alert');
        
        const iconMap = {
            success: 'check_circle',
            error: 'error',
            warning: 'warning',
            info: 'info'
        };
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined">${iconMap[type]}</span>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        this.container.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast, { delay: duration });
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
        
        return bsToast;
    },
    
    success(message, duration) {
        return this.show(message, 'success', duration);
    },
    
    error(message, duration) {
        return this.show(message, 'error', duration);
    },
    
    warning(message, duration) {
        return this.show(message, 'warning', duration);
    },
    
    info(message, duration) {
        return this.show(message, 'info', duration);
    }
};

/**
 * Form validation
 */
Utils.validate = {
    required(value) {
        return value && value.toString().trim().length > 0;
    },
    
    email(value) {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(value);
    },
    
    phone(value) {
        return Utils.isValidPhone(value);
    },
    
    minLength(value, min) {
        return value && value.length >= min;
    },
    
    maxLength(value, max) {
        return !value || value.length <= max;
    },
    
    password(value) {
        // At least 8 chars, 1 uppercase, 1 lowercase, 1 number
        const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        return pattern.test(value);
    },
    
    match(value1, value2) {
        return value1 === value2;
    },
    
    amount(value, min = 100, max = 5000000) {
        const num = parseFloat(value);
        return !isNaN(num) && num >= min && num <= max;
    }
};

/**
 * Magnetic button effect
 */
Utils.magneticButton = {
    init(selector = '.magnetic-effect') {
        const buttons = document.querySelectorAll(selector);
        
        buttons.forEach(btn => {
            btn.addEventListener('mousemove', (e) => {
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                
                btn.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px)`;
            });
            
            btn.addEventListener('mouseleave', () => {
                btn.style.transform = 'translate(0, 0)';
            });
        });
    }
};

/**
 * Intersection Observer for animations
 */
Utils.observeAnimations = {
    init(selector = '.animate-on-scroll') {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll(selector).forEach(el => {
            el.classList.add('initial-hidden-up');
            observer.observe(el);
        });
    }
};

// Initialize utilities when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    Utils.toast.init();
    Utils.observeAnimations.init();
});

// Make Utils available globally
window.Utils = Utils;
