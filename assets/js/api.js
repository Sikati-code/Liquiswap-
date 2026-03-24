/**
 * LiquiSwap API Client
 * Handles all API requests with error handling and authentication
 */

const API = {
    baseUrl: window.APP_CONFIG?.url || '',
    
    /**
     * Get CSRF token
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || 
               window.APP_CONFIG?.csrfToken || '';
    },
    
    /**
     * Default request headers
     */
    getHeaders() {
        return {
            'Content-Type': 'application/json',
            'X-CSRF-Token': this.getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest'
        };
    },
    
    /**
     * Make API request
     */
    async request(endpoint, method = 'GET', data = null, options = {}) {
        const url = `${this.baseUrl}/api/${endpoint}`;
        
        const config = {
            method,
            headers: this.getHeaders(),
            credentials: 'same-origin',
            ...options
        };
        
        if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
            config.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(url, config);
            
            // Check for authentication errors
            if (response.status === 401) {
                // Redirect to login
                window.location.href = '/login';
                return { success: false, error: 'Session expired' };
            }
            
            // Check for rate limiting
            if (response.status === 429) {
                Utils.toast.error('Too many requests. Please try again later.');
                return { success: false, error: 'Rate limited' };
            }
            
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.error || `HTTP ${response.status}`);
            }
            
            return result;
            
        } catch (error) {
            console.error('API Error:', error);
            return { 
                success: false, 
                error: error.message || 'Network error. Please check your connection.' 
            };
        }
    },
    
    /**
     * GET request
     */
    get(endpoint, params = {}) {
        const queryString = Object.keys(params)
            .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`)
            .join('&');
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url, 'GET');
    },
    
    /**
     * POST request
     */
    post(endpoint, data) {
        return this.request(endpoint, 'POST', data);
    },
    
    /**
     * PUT request
     */
    put(endpoint, data) {
        return this.request(endpoint, 'PUT', data);
    },
    
    /**
     * DELETE request
     */
    delete(endpoint) {
        return this.request(endpoint, 'DELETE');
    }
};

/**
 * Auth API
 */
API.auth = {
    register(data) {
        return API.post('auth/register', data);
    },
    
    login(phone, password, remember = false) {
        return API.post('auth/login', { phone_number: phone, password, remember });
    },
    
    logout() {
        return API.post('auth/logout');
    },
    
    biometricLogin(userId) {
        return API.post('auth/biometric', { user_id: userId });
    },
    
    forgotPassword(phone) {
        return API.post('auth/forgot-password', { phone_number: phone });
    },
    
    resetPassword(token, newPassword) {
        return API.post('auth/reset-password', { token, new_password: newPassword });
    },
    
    check() {
        return API.get('auth/check');
    }
};

/**
 * User API
 */
API.user = {
    profile() {
        return API.get('user/profile');
    },
    
    updateProfile(data) {
        return API.put('user/profile', data);
    },
    
    wallets() {
        return API.get('user/wallets');
    },
    
    addWallet(data) {
        return API.post('user/wallets', data);
    },
    
    updateWallet(id, data) {
        return API.put(`user/wallets/${id}`, data);
    },
    
    deleteWallet(id) {
        return API.delete(`user/wallets/${id}`);
    },
    
    trustScore() {
        return API.get('user/trust-score');
    },
    
    stats() {
        return API.get('user/stats');
    },
    
    recentContacts() {
        return API.get('user/recent-contacts');
    }
};

/**
 * Swap API
 */
API.swap = {
    rate() {
        return API.get('swap/rate');
    },
    
    calculate(amount, cashout = false) {
        return API.post('swap/calculate', { amount, cashout });
    },
    
    create(data) {
        return API.post('swap/create', data);
    },
    
    confirm(reference) {
        return API.post('swap/confirm', { reference });
    },
    
    history(params = {}) {
        return API.get('swap/history', params);
    },
    
    status(reference) {
        return API.get('swap/status', { reference });
    }
};

/**
 * Bundles API
 */
API.bundles = {
    list(params = {}) {
        return API.get('bundles', params);
    },
    
    search(query) {
        return API.get('bundles/search', { q: query });
    },
    
    get(id) {
        return API.get(`bundles/${id}`);
    },
    
    purchase(bundleId, phoneNumber, paymentMethod = 'MTN') {
        return API.post('bundles/purchase', { 
            bundle_id: bundleId, 
            phone_number: phoneNumber,
            payment_method: paymentMethod 
        });
    },
    
    convertAirtime(bundleId, phoneNumber, fromOperator) {
        return API.post('bundles/airtime-convert', {
            bundle_id: bundleId,
            phone_number: phoneNumber,
            from_operator: fromOperator
        });
    },
    
    categories() {
        return API.get('bundles/categories');
    }
};

/**
 * USSD API
 */
API.ussd = {
    list(params = {}) {
        return API.get('ussd', params);
    },
    
    search(query) {
        return API.get('ussd/search', { q: query });
    },
    
    categories() {
        return API.get('ussd/categories');
    },
    
    operators() {
        return API.get('ussd/operators');
    }
};

/**
 * Transactions API
 */
API.transactions = {
    list(params = {}) {
        return API.get('transactions', params);
    },
    
    get(uuid) {
        return API.get(`transactions/${uuid}`);
    },
    
    filter(filters) {
        return API.post('transactions/filter', filters);
    }
};

/**
 * Settings API
 */
API.settings = {
    get() {
        return API.get('settings');
    },
    
    update(data) {
        return API.put('settings', data);
    },
    
    changePassword(currentPassword, newPassword) {
        return API.post('settings/change-password', { 
            current_password: currentPassword, 
            new_password: newPassword 
        });
    },
    
    toggleBiometric(enabled) {
        return API.post('settings/biometric', { enabled });
    },
    
    toggle2FA(enabled) {
        return API.post('settings/2fa', { enabled });
    },
    
    updateTheme(darkMode) {
        return API.post('settings/theme', { dark_mode: darkMode });
    },
    
    updateLanguage(language) {
        return API.post('settings/language', { language });
    },
    
    updateNotifications(settings) {
        return API.post('settings/notifications', settings);
    },
    
    sessions() {
        return API.get('settings/sessions');
    },
    
    revokeSession(sessionId) {
        return API.post('settings/revoke-session', { session_id: sessionId });
    }
};

// Make API available globally
window.API = API;
