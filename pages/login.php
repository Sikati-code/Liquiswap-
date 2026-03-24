<!-- Login Page -->
<div class="auth-container min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden py-4">
    <!-- Background Effects -->
    <div class="auth-bg position-absolute inset-0">
        <div class="liquid-blob position-absolute rounded-circle blob-1" style="top: -10%; left: -10%; width: 500px; height: 500px; background: linear-gradient(135deg, #007f80 0%, #004d4d 100%); filter: blur(80px); opacity: 0.4;"></div>
        <div class="liquid-blob position-absolute rounded-circle blob-2" style="bottom: -10%; right: -10%; width: 500px; height: 500px; background: linear-gradient(135deg, #007f80 0%, #004d4d 100%); filter: blur(80px); opacity: 0.4;"></div>
    </div>
    
    <!-- Main Content -->
    <div class="auth-content position-relative z-10 w-100" style="max-width: 440px; padding: 0 1.5rem;">
        <!-- Logo -->
        <div class="text-center mb-5">
            <div class="d-inline-flex align-items-center justify-content-center rounded-xl mb-3" style="background: rgba(0, 127, 128, 0.2); padding: 0.75rem; border: 1px solid rgba(0, 127, 128, 0.3);">
                <span class="material-symbols-outlined text-primary" style="font-size: 2rem; font-variation-settings: 'FILL' 1;">water_drop</span>
            </div>
            <h1 class="fw-bold tracking-tight mb-0">LiquiSwap</h1>
        </div>
        
        <!-- Glass Card -->
        <div class="glass-card overflow-hidden">
            <!-- Tabs -->
            <div class="d-flex border-bottom" style="border-color: rgba(0, 127, 128, 0.2) !important;">
                <button type="button" class="auth-tab flex-1 py-3 text-center fw-semibold position-relative" 
                        data-tab="signin" onclick="switchTab('signin')"
                        style="background: transparent; border: none; color: #007f80;">
                    Sign In
                    <span class="tab-indicator position-absolute bottom-0 start-0 end-0" style="height: 2px; background: #007f80;"></span>
                </button>
                <button type="button" class="auth-tab flex-1 py-3 text-center fw-semibold position-relative" 
                        data-tab="signup" onclick="switchTab('signup')"
                        style="background: transparent; border: none; color: #64748b;">
                    Create Account
                    <span class="tab-indicator position-absolute bottom-0 start-0 end-0 d-none" style="height: 2px; background: #007f80;"></span>
                </button>
            </div>
            
            <!-- Sign In Form -->
            <div id="signin-panel" class="p-4">
                <form data-ajax data-action="login" class="d-flex flex-column gap-4">
                    <!-- Phone Input -->
                    <div>
                        <label class="d-block text-uppercase fs-8 fw-semibold mb-2 ms-1" style="color: #64748b; letter-spacing: 0.1em;">Phone Number</label>
                        <div class="input-group input-group-liquid">
                            <span class="input-group-text">
                                <span class="me-2">🇨🇲</span>
                                <span class="text-secondary">+237</span>
                            </span>
                            <input type="tel" name="phone" class="form-control form-control-liquid" 
                                   placeholder="6XX XXX XXX" required data-phone
                                   style="padding-left: 1rem;">
                        </div>
                    </div>
                    
                    <!-- Password Input -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2 ms-1">
                            <label class="text-uppercase fs-8 fw-semibold" style="color: #64748b; letter-spacing: 0.1em;">Password</label>
                        </div>
                        <div class="position-relative">
                            <input type="password" name="password" id="login-password" 
                                   class="form-control form-control-liquid" placeholder="••••••••" required>
                            <button type="button" class="btn position-absolute end-0 top-50 translate-middle-y me-3 p-0" 
                                    data-toggle-password="#login-password" style="background: none; border: none; color: #64748b;">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Remember & Forgot -->
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="d-flex align-items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="form-check-input" style="width: 2.5rem; height: 1.25rem; background-color: #334155; border-color: #475569;">
                            <span class="fs-7 text-secondary">Remember me</span>
                        </label>
                        <a href="#" class="fs-7 fw-medium" style="color: #007f80; text-decoration: none;">Forgot password?</a>
                    </div>
                    
                    <!-- Login Button -->
                    <button type="submit" class="btn btn-liquid w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                        <span>Secure Sign In</span>
                        <span class="material-symbols-outlined">login</span>
                    </button>
                    
                    <!-- Divider -->
                    <div class="position-relative text-center my-2">
                        <hr class="border-secondary opacity-25">
                        <span class="position-absolute top-50 start-50 translate-middle px-2 fs-8 text-uppercase" style="background: #121a1a; color: #64748b;">Or continue with</span>
                    </div>
                    
                    <!-- Biometric Button -->
                    <button type="button" class="btn btn-glass w-100 py-3 d-flex align-items-center justify-content-center gap-2" onclick="handleBiometricLogin()">
                        <span class="material-symbols-outlined text-primary">face</span>
                        <span>Sign in with Face ID</span>
                    </button>
                </form>
            </div>
            
            <!-- Sign Up Form -->
            <div id="signup-panel" class="p-4 d-none">
                <form data-ajax data-action="register" class="d-flex flex-column gap-3">
                    <!-- Full Name -->
                    <div>
                        <label class="d-block text-uppercase fs-8 fw-semibold mb-2 ms-1" style="color: #64748b; letter-spacing: 0.1em;">Full Name</label>
                        <input type="text" name="full_name" class="form-control form-control-liquid" placeholder="Jean Paul" required>
                    </div>
                    
                    <!-- Phone -->
                    <div>
                        <label class="d-block text-uppercase fs-8 fw-semibold mb-2 ms-1" style="color: #64748b; letter-spacing: 0.1em;">Phone Number</label>
                        <div class="input-group input-group-liquid">
                            <span class="input-group-text">
                                <span class="me-2">🇨🇲</span>
                                <span class="text-secondary">+237</span>
                            </span>
                            <input type="tel" name="phone_number" class="form-control form-control-liquid" 
                                   placeholder="6XX XXX XXX" required data-phone>
                        </div>
                    </div>
                    
                    <!-- Email (Optional) -->
                    <div>
                        <label class="d-block text-uppercase fs-8 fw-semibold mb-2 ms-1" style="color: #64748b; letter-spacing: 0.1em;">Email <span class="fw-normal">(Optional)</span></label>
                        <input type="email" name="email" class="form-control form-control-liquid" placeholder="your@email.com">
                    </div>
                    
                    <!-- Password -->
                    <div>
                        <label class="d-block text-uppercase fs-8 fw-semibold mb-2 ms-1" style="color: #64748b; letter-spacing: 0.1em;">Password</label>
                        <input type="password" name="password" class="form-control form-control-liquid" placeholder="Min 8 characters" required minlength="8">
                    </div>
                    
                    <!-- Confirm Password -->
                    <div>
                        <label class="d-block text-uppercase fs-8 fw-semibold mb-2 ms-1" style="color: #64748b; letter-spacing: 0.1em;">Confirm Password</label>
                        <input type="password" name="password_confirm" class="form-control form-control-liquid" placeholder="Confirm your password" required>
                    </div>
                    
                    <!-- Terms -->
                    <label class="d-flex align-items-start gap-2 cursor-pointer">
                        <input type="checkbox" required class="form-check-input mt-1" style="background-color: #334155; border-color: #475569;">
                        <span class="fs-8 text-secondary">I agree to the <a href="#" style="color: #007f80;">Terms of Service</a> and <a href="#" style="color: #007f80;">Privacy Policy</a></span>
                    </label>
                    
                    <!-- Register Button -->
                    <button type="submit" class="btn btn-liquid w-100 py-3 mt-2 d-flex align-items-center justify-content-center gap-2">
                        <span>Create Account</span>
                        <span class="material-symbols-outlined">person_add</span>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Footer -->
        <p class="text-center mt-4 fs-8 text-secondary">
            By signing in, you agree to our <a href="#" style="color: #007f80;">Terms</a> and <a href="#" style="color: #007f80;">Privacy Policy</a>
        </p>
    </div>
    
    <!-- Bottom Gradient Line -->
    <div class="position-fixed bottom-0 start-0 end-0" style="height: 2px; background: linear-gradient(90deg, transparent, #007f80, transparent); opacity: 0.5;"></div>
</div>

<style>
.auth-container {
    background: #0f2323;
}

.inset-0 {
    top: 0; right: 0; bottom: 0; left: 0;
}

.tracking-tight {
    letter-spacing: -0.025em;
}

.fs-8 {
    font-size: 0.75rem;
}

.form-check-input {
    border-radius: 0.25rem;
}

.form-check-input:checked {
    background-color: #007f80;
    border-color: #007f80;
}
</style>

<script>
(function() {
    // Tab switching
    window.switchTab = function(tabName) {
        const signinPanel = document.getElementById('signin-panel');
        const signupPanel = document.getElementById('signup-panel');
        const tabs = document.querySelectorAll('.auth-tab');
        
        if (tabName === 'signin') {
            signinPanel.classList.remove('d-none');
            signupPanel.classList.add('d-none');
            tabs[0].style.color = '#007f80';
            tabs[0].querySelector('.tab-indicator').classList.remove('d-none');
            tabs[1].style.color = '#64748b';
            tabs[1].querySelector('.tab-indicator').classList.add('d-none');
        } else {
            signinPanel.classList.add('d-none');
            signupPanel.classList.remove('d-none');
            tabs[0].style.color = '#64748b';
            tabs[0].querySelector('.tab-indicator').classList.add('d-none');
            tabs[1].style.color = '#007f80';
            tabs[1].querySelector('.tab-indicator').classList.remove('d-none');
        }
    };
    
    // Biometric login handler
    window.handleBiometricLogin = function() {
        // Check if biometric is available
        if (window.PublicKeyCredential) {
            Utils.toast.info('Biometric authentication not set up yet. Please use password login.');
        } else {
            Utils.toast.error('Biometric authentication not supported on this device.');
        }
    };
})();
</script>
