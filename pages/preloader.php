<!-- Liquid Preloader Page -->
<div class="preloader-container min-vh-100 d-flex flex-column position-relative overflow-hidden" style="background: radial-gradient(circle at center, #1a3a3a 0%, #0f2323 100%);">
    <!-- Background Elements -->
    <div class="preloader-bg position-absolute inset-0">
        <div class="bg-blob position-absolute rounded-circle" style="top: 25%; left: 25%; width: 400px; height: 400px; background: rgba(0, 127, 128, 0.05); filter: blur(100px);"></div>
        <div class="bg-blob position-absolute rounded-circle" style="bottom: 25%; right: 25%; width: 400px; height: 400px; background: rgba(249, 115, 22, 0.05); filter: blur(100px);"></div>
    </div>
    
    <!-- Header -->
    <header class="preloader-header d-flex align-items-center justify-content-between px-4 py-3 position-relative z-10" style="border-bottom: 1px solid rgba(0, 127, 128, 0.1);">
        <div class="d-flex align-items-center gap-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">water_drop</span>
            <span class="fw-bold fs-5">LiquiSwap</span>
        </div>
        <button class="btn btn-icon" onclick="toggleSettings()">
            <span class="material-symbols-outlined">settings</span>
        </button>
    </header>
    
    <!-- Main Content -->
    <main class="preloader-main flex-grow-1 d-flex flex-column align-items-center justify-content-center px-4 position-relative z-10">
        <!-- Morphing Blob Container -->
        <div class="blob-container position-relative mb-5" style="width: 280px; height: 280px;">
            <!-- The Liquid Blob -->
            <div class="morph-blob position-absolute inset-0 d-flex align-items-center justify-content-center animate-liquid-morph" 
                 style="background: linear-gradient(135deg, #007f80 0%, #f97316 50%, #fbbf24 100%); border-radius: 42% 58% 70% 30% / 45% 45% 55% 55%; box-shadow: 0 0 50px -12px rgba(0, 127, 128, 0.5);">
                <!-- Inner liquid effect -->
                <div class="position-absolute inset-0" style="background: rgba(0, 0, 0, 0.2); backdrop-filter: blur(2px); border-radius: inherit;"></div>
                
                <!-- Progress Percentage -->
                <div class="position-relative z-10 text-center text-white">
                    <span id="loading-percentage" class="display-3 fw-bold tracking-tighter">0</span>
                    <span class="fs-4 opacity-75">%</span>
                    <p class="fs-8 fw-bold text-uppercase tracking-widest opacity-70 mt-2 mb-0" style="letter-spacing: 0.2em;">Flowing</p>
                </div>
                
                <!-- Wave effect -->
                <div class="position-absolute bottom-0 start-0 end-0" style="height: 50%; background: linear-gradient(to top, rgba(255, 255, 255, 0.1), transparent);"></div>
            </div>
        </div>
        
        <!-- Loading Info -->
        <div class="loading-info text-center" style="max-width: 320px;">
            <h2 class="fs-4 fw-bold mb-2">Initializing Protocol</h2>
            <p class="text-secondary fs-7 mb-4">Connecting to the deep abyss of decentralized liquidity pools...</p>
            
            <!-- Status Bar -->
            <div class="status-section mb-4">
                <div class="d-flex justify-content-between align-items-end mb-2" style="font-size: 0.625rem;">
                    <span class="fw-bold text-uppercase tracking-wider" style="color: #007f80; letter-spacing: 0.15em;">Liquidity Depth Check</span>
                    <span id="liquidity-status" class="fw-bold" style="color: #f97316;">Optimal</span>
                </div>
                <div class="progress" style="height: 6px; background: #0f2323; border-radius: 3px;">
                    <div id="liquidity-progress" class="progress-bar" 
                         style="width: 0%; background: linear-gradient(90deg, #007f80, #f97316, #fbbf24); border-radius: 3px; transition: width 0.3s ease;">
                    </div>
                </div>
            </div>
            
            <p class="text-muted fst-italic fs-8">Syncing teal to orange transition states...</p>
        </div>
    </main>
    
    <!-- Footer Stats -->
    <footer class="preloader-footer px-4 py-3 d-flex justify-content-between align-items-center position-relative z-10">
        <div class="d-flex gap-4">
            <div class="d-flex flex-column">
                <span class="text-uppercase fw-bold fs-9" style="color: #64748b; letter-spacing: 0.1em;">Gas Price</span>
                <span class="fs-7 fw-medium">12 Gwei</span>
            </div>
            <div class="d-flex flex-column">
                <span class="text-uppercase fw-bold fs-9" style="color: #64748b; letter-spacing: 0.1em;">Latency</span>
                <span class="fs-7 fw-medium">24ms</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="position-relative d-inline-flex" style="width: 8px; height: 8px;">
                <span class="position-absolute d-inline-flex w-100 h-100 rounded-circle animate-pulse" style="background: #007f80; opacity: 0.75;"></span>
                <span class="position-relative d-inline-flex rounded-circle w-100 h-100" style="background: #007f80;"></span>
            </span>
            <span class="fs-8 text-secondary">Mainnet Connected</span>
        </div>
    </footer>
</div>

<style>
.inset-0 {
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}

.tracking-tighter {
    letter-spacing: -0.05em;
}

.tracking-widest {
    letter-spacing: 0.2em;
}

.animate-liquid-morph {
    animation: liquidMorph 8s ease-in-out infinite;
}

@keyframes liquidMorph {
    0%, 100% {
        border-radius: 42% 58% 70% 30% / 45% 45% 55% 55%;
    }
    25% {
        border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
    }
    50% {
        border-radius: 50% 60% 30% 60% / 30% 60% 70% 40%;
    }
    75% {
        border-radius: 60% 40% 60% 40% / 70% 30% 50% 60%;
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
    let progress = 0;
    const percentageEl = document.getElementById('loading-percentage');
    const liquidityProgress = document.getElementById('liquidity-progress');
    
    function updateProgress() {
        progress += Math.random() * 8 + 2;
        if (progress >= 100) {
            progress = 100;
            
            // Redirect to login
            setTimeout(() => {
                window.location.href = '/login';
            }, 800);
            return;
        }
        
        if (percentageEl) percentageEl.textContent = Math.round(progress);
        if (liquidityProgress) liquidityProgress.style.width = progress + '%';
        
        // Update liquidity status text
        const statusEl = document.getElementById('liquidity-status');
        if (statusEl) {
            if (progress < 30) {
                statusEl.textContent = 'Connecting...';
                statusEl.style.color = '#007f80';
            } else if (progress < 60) {
                statusEl.textContent = 'Verifying...';
                statusEl.style.color = '#fbbf24';
            } else if (progress < 90) {
                statusEl.textContent = 'Optimal';
                statusEl.style.color = '#f97316';
            } else {
                statusEl.textContent = 'Ready';
                statusEl.style.color = '#22c55e';
            }
        }
        
        setTimeout(updateProgress, 150);
    }
    
    // Start animation
    setTimeout(updateProgress, 500);
    
    // Toggle settings function
    window.toggleSettings = function() {
        // Placeholder for settings toggle
        console.log('Settings clicked');
    };
})();
</script>
