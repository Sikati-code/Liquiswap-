<!-- 404 Error Page -->
<div class="error-page min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden" style="background: #0a0f0f;">
    <!-- Background Effects -->
    <div class="error-bg position-absolute inset-0">
        <div class="blob blob-1 position-absolute rounded-circle" style="top: -10%; left: -10%; width: 40%; height: 40%; background: linear-gradient(135deg, #007f80 0%, #004d4d 100%); filter: blur(80px); opacity: 0.3;"></div>
        <div class="blob blob-2 position-absolute rounded-circle" style="bottom: -10%; right: -10%; width: 35%; height: 35%; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); filter: blur(80px); opacity: 0.3;"></div>
    </div>
    
    <!-- Main Content -->
    <div class="error-content position-relative z-10 text-center px-4" style="max-width: 600px;">
        <!-- 404 Display -->
        <div class="error-number mb-5 position-relative">
            <h1 class="display-1 fw-bold mb-0 liquid-gradient-text">404</h1>
            <div class="error-ripple position-absolute inset-0 d-flex align-items-center justify-content-center">
                <span class="material-symbols-outlined" style="font-size: 6rem; opacity: 0.1;">water_drop</span>
            </div>
        </div>
        
        <!-- Error Message -->
        <div class="error-message mb-5">
            <h2 class="fs-2 fw-bold mb-3">Page Not Found</h2>
            <p class="text-secondary fs-6 mb-4">Oops! The page you're looking for seems to have dissolved into the digital ether.</p>
            <p class="text-secondary fs-7">The requested URL was not found on this server. Please check the address and try again.</p>
        </div>
        
        <!-- Action Buttons -->
        <div class="error-actions d-flex flex-column flex-sm-row gap-3 justify-content-center mb-5">
            <a href="/dashboard" class="btn btn-liquid px-4 py-3 d-flex align-items-center justify-content-center gap-2">
                <span class="material-symbols-outlined">home</span>
                <span>Go Home</span>
            </a>
            <button type="button" class="btn btn-glass px-4 py-3 d-flex align-items-center justify-content-center gap-2" onclick="goBack()">
                <span class="material-symbols-outlined">arrow_back</span>
                <span>Go Back</span>
            </button>
            <button type="button" class="btn btn-glass px-4 py-3 d-flex align-items-center justify-content-center gap-2" onclick="reloadPage()">
                <span class="material-symbols-outlined">refresh</span>
                <span>Reload</span>
            </button>
        </div>
        
        <!-- Help Section -->
        <div class="glass-card p-4">
            <h3 class="fs-6 fw-bold mb-3">Looking for something?</h3>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <a href="/dashboard" class="d-flex flex-column align-items-center gap-2 text-decoration-none">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(0, 127, 128, 0.1);">
                            <span class="material-symbols-outlined text-primary fs-4">grid_view</span>
                        </div>
                        <span class="fs-8 fw-medium text-center">Dashboard</span>
                    </a>
                </div>
                <div class="col-12 col-md-4">
                    <a href="/swap" class="d-flex flex-column align-items-center gap-2 text-decoration-none">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(249, 115, 22, 0.1);">
                            <span class="material-symbols-outlined text-orange fs-4">swap_horiz</span>
                        </div>
                        <span class="fs-8 fw-medium text-center">Swap</span>
                    </a>
                </div>
                <div class="col-12 col-md-4">
                    <a href="/bundles" class="d-flex flex-column align-items-center gap-2 text-decoration-none">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width: 48px; height: 48px; background: rgba(251, 191, 36, 0.1);">
                            <span class="material-symbols-outlined text-warning fs-4">package_2</span>
                        </div>
                        <span class="fs-8 fw-medium text-center">Bundles</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Contact Support -->
        <div class="mt-5">
            <p class="text-secondary fs-8 mb-3">Still can't find what you're looking for?</p>
            <a href="/settings" class="btn btn-glass px-4 py-2 d-inline-flex align-items-center gap-2">
                <span class="material-symbols-outlined">support_agent</span>
                <span>Contact Support</span>
            </a>
        </div>
    </div>
    
    <!-- Animated Water Drop -->
    <div class="floating-drop position-absolute" style="bottom: 10%; left: 5%; animation: float 6s ease-in-out infinite;">
        <span class="material-symbols-outlined text-primary" style="font-size: 2rem; opacity: 0.3;">water_drop</span>
    </div>
    
    <div class="floating-drop position-absolute" style="top: 20%; right: 8%; animation: float 8s ease-in-out infinite; animation-delay: 2s;">
        <span class="material-symbols-outlined text-orange" style="font-size: 1.5rem; opacity: 0.3;">water_drop</span>
    </div>
</div>

<style>
.error-page {
    min-height: 100vh;
}

.liquid-gradient-text {
    background: linear-gradient(45deg, #007f80, #f97316, #007f80);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: liquid-gradient-shift 15s ease infinite;
}

@keyframes liquid-gradient-shift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.error-number {
    position: relative;
    display: inline-block;
}

.error-ripple {
    pointer-events: none;
}

.error-ripple::before {
    content: '';
    position: absolute;
    inset: -20px;
    border: 2px solid rgba(0, 127, 128, 0.2);
    border-radius: 50%;
    animation: ripple 3s ease-out infinite;
}

@keyframes ripple {
    0% {
        transform: scale(0.8);
        opacity: 1;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

.floating-drop {
    pointer-events: none;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(5deg);
    }
}

.fs-7 {
    font-size: 0.875rem;
}

.fs-8 {
    font-size: 0.75rem;
}

.fs-9 {
    font-size: 0.625rem;
}
</style>

<script>
(function() {
    // Go back functionality
    window.goBack = function() {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = '/dashboard';
        }
    };
    
    // Reload page
    window.reloadPage = function() {
        window.location.reload();
    };
    
    // Log the 404 error for debugging
    console.error('404 Error: Page not found at', window.location.pathname);
    
    // Add some interactive effects
    const errorNumber = document.querySelector('.error-number');
    if (errorNumber) {
        errorNumber.addEventListener('mouseenter', () => {
            errorNumber.style.transform = 'scale(1.05)';
            errorNumber.style.transition = 'transform 0.3s ease';
        });
        
        errorNumber.addEventListener('mouseleave', () => {
            errorNumber.style.transform = 'scale(1)';
        });
    }
})();
</script>
