<!-- Splash Screen Page -->
<div class="splash-container min-vh-100 d-flex flex-column align-items-center justify-content-center position-relative overflow-hidden">
    <!-- Background Effects -->
    <div class="splash-bg position-absolute inset-0">
        <div class="blob blob-1 position-absolute rounded-circle" style="top: -10%; left: -10%; width: 40%; height: 40%;"></div>
        <div class="blob blob-2 position-absolute rounded-circle" style="bottom: -5%; right: -5%; width: 35%; height: 35%;"></div>
        <div class="refraction-overlay position-absolute inset-0"></div>
    </div>
    
    <!-- Main Content -->
    <div class="splash-content position-relative z-10 text-center px-4" style="max-width: 400px;">
        <!-- Logo -->
        <div class="logo-section mb-5 animate-fade-in">
            <div class="logo-wrapper position-relative d-inline-flex align-items-center justify-content-center mb-4" style="width: 120px; height: 120px;">
                <div class="logo-glow position-absolute inset-0 rounded-circle" style="background: linear-gradient(135deg, #007f80, #f97316); opacity: 0.2; filter: blur(30px);"></div>
                <span class="material-symbols-outlined liquid-gradient-text" style="font-size: 5rem; font-variation-settings: 'FILL' 1;">water_drop</span>
            </div>
            <h1 class="display-4 fw-bold mb-2">
                Liqui<span class="liquid-gradient-text">Swap</span>
            </h1>
            <p class="text-secondary fs-5">Serving Cameroon & Central Africa</p>
        </div>
        
        <!-- Progress Indicator -->
        <div class="progress-section w-100 animate-fade-in delay-200">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-uppercase fs-7 fw-semibold tracking-widest" style="color: #007f80;">Initialising</span>
                <span class="text-uppercase fs-7 fw-semibold" style="color: #64748b;" id="progress-text">0%</span>
            </div>
            <div class="progress" style="height: 3px; background: rgba(0, 127, 128, 0.2); border-radius: 3px;">
                <div id="splash-progress" class="progress-bar" role="progressbar" 
                     style="width: 0%; background: linear-gradient(90deg, #007f80, #f97316); border-radius: 3px; transition: width 0.3s ease;">
                </div>
            </div>
            <p class="mt-3 text-muted fst-italic fs-7">Premium Exchange Experience</p>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="splash-footer position-absolute bottom-0 start-0 end-0 d-flex justify-content-center align-items-center gap-2 pb-4" style="opacity: 0.6;">
        <span class="material-symbols-outlined text-primary" style="font-size: 1rem;">verified_user</span>
        <span class="text-uppercase fs-8 fw-medium tracking-widest" style="color: #64748b; font-size: 0.625rem;">Secure Central African Liquidity</span>
    </div>
</div>

<style>
.splash-container {
    background: linear-gradient(180deg, #0a0f0f 0%, #050808 100%);
}

.blob {
    background: rgba(0, 127, 128, 0.15);
    filter: blur(80px);
}

.blob-2 {
    background: rgba(249, 115, 22, 0.1);
}

.refraction-overlay {
    background: radial-gradient(circle at 50% 50%, rgba(0, 127, 128, 0.05) 0%, transparent 70%);
}

.liquid-gradient-text {
    background: linear-gradient(45deg, #007f80, #f97316, #007f80);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.tracking-widest {
    letter-spacing: 0.2em;
}
</style>

<script>
(function() {
    let progress = 0;
    const progressBar = document.getElementById('splash-progress');
    const progressText = document.getElementById('progress-text');
    
    // Simulate loading progress
    const interval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress >= 100) {
            progress = 100;
            clearInterval(interval);
            
            // Redirect after completion
            setTimeout(() => {
                window.location.href = '/preloader';
            }, 500);
        }
        
        if (progressBar) progressBar.style.width = progress + '%';
        if (progressText) progressText.textContent = Math.round(progress) + '%';
    }, 200);
})();
</script>
