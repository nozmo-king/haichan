/**
 * MINIMAL HASHRATE TOOLBAR
 * Clean, focused bottom toolbar showing only hashrate
 * Designed for extremist aesthetic standards
 */

class MinimalHashrateToolbar {
    constructor() {
        this.element = null;
        this.hashrate = 0;
        this.isActive = false;
        this.samples = [];
        this.maxSamples = 30; // 30 seconds of samples
        
        this.init();
    }
    
    init() {
        this.createToolbar();
        this.startHashrateTracking();
        console.log('⚡ Minimal hashrate toolbar initialized');
    }
    
    createToolbar() {
        // Remove any existing toolbar
        const existing = document.getElementById('minimal-hashrate-toolbar');
        if (existing) existing.remove();
        
        // Create minimal toolbar
        this.element = document.createElement('div');
        this.element.id = 'minimal-hashrate-toolbar';
        this.element.className = 'minimal-hashrate-toolbar';
        
        this.element.innerHTML = `
            <div class="hashrate-display">
                <span class="hashrate-value">0</span>
                <span class="hashrate-unit">H/s</span>
            </div>
        `;
        
        // Add styles
        this.addStyles();
        
        // Append to body
        document.body.appendChild(this.element);
    }
    
    addStyles() {
        if (document.getElementById('minimal-toolbar-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'minimal-toolbar-styles';
        styles.textContent = `
            /* MINIMAL HASHRATE TOOLBAR - AESTHETIC EXTREMIST */
            .minimal-hashrate-toolbar {
                position: fixed;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                background: var(--neutral-0, #ffffff);
                border: var(--border-width, 1px) solid var(--neutral-3, #d4d4d8);
                border-bottom: none;
                border-radius: var(--border-radius, 4px) var(--border-radius, 4px) 0 0;
                padding: var(--space-2, 8px) var(--space-4, 16px);
                z-index: 1000;
                font-family: var(--font-mono, 'Berkeley Mono', 'Courier New', monospace);
                backdrop-filter: blur(8px);
                box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
            }
            
            .hashrate-display {
                display: flex;
                align-items: baseline;
                gap: var(--space-1, 4px);
                font-weight: var(--font-weight-medium, 500);
                line-height: 1;
            }
            
            .hashrate-value {
                font-size: var(--font-size-lg, 18px);
                color: var(--neutral-9, #18181b);
                font-weight: var(--font-weight-semibold, 600);
                font-variant-numeric: tabular-nums;
                min-width: 3ch;
                text-align: right;
            }
            
            .hashrate-unit {
                font-size: var(--font-size-sm, 14px);
                color: var(--neutral-6, #52525b);
                font-weight: var(--font-weight-medium, 500);
            }
            
            /* Subtle animation for active mining */
            .minimal-hashrate-toolbar.mining .hashrate-value {
                color: var(--accent-6, #059669);
                animation: subtle-pulse 2s ease-in-out infinite;
            }
            
            @keyframes subtle-pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.7; }
            }
            
            /* Night theme support */
            [data-theme="night"] .minimal-hashrate-toolbar {
                background: var(--neutral-1, #18181b);
                border-color: var(--neutral-6, #52525b);
            }
            
            [data-theme="night"] .hashrate-value {
                color: var(--neutral-1, #fafafa);
            }
            
            [data-theme="night"] .hashrate-unit {
                color: var(--neutral-4, #a1a1aa);
            }
            
            [data-theme="night"] .minimal-hashrate-toolbar.mining .hashrate-value {
                color: var(--accent-4, #10b981);
            }
            
            /* Anonymous mode - invert colors */
            .anonymous-mode .minimal-hashrate-toolbar {
                filter: invert(1) hue-rotate(180deg);
            }
            
            /* Responsive - hide on very small screens */
            @media (max-height: 400px) {
                .minimal-hashrate-toolbar {
                    display: none;
                }
            }
            
            /* Add small bottom padding to body */
            body {
                padding-bottom: 48px;
            }
        `;
        
        document.head.appendChild(styles);
    }
    
    updateHashrate(hashrate) {
        this.hashrate = hashrate;
        this.isActive = hashrate > 0;
        
        // Add sample for smoothing
        this.samples.push({
            rate: hashrate,
            timestamp: Date.now()
        });
        
        // Keep only recent samples
        if (this.samples.length > this.maxSamples) {
            this.samples.shift();
        }
        
        // Update display
        this.updateDisplay();
    }
    
    updateDisplay() {
        if (!this.element) return;
        
        const valueEl = this.element.querySelector('.hashrate-value');
        if (valueEl) {
            // Format hashrate for clean display
            const displayRate = this.formatHashrate(this.hashrate);
            valueEl.textContent = displayRate;
        }
        
        // Toggle mining class for visual feedback
        this.element.classList.toggle('mining', this.isActive);
    }
    
    formatHashrate(rate) {
        if (rate === 0) return '0';
        if (rate < 1000) return Math.round(rate).toString();
        if (rate < 1000000) return (rate / 1000).toFixed(1) + 'K';
        return (rate / 1000000).toFixed(1) + 'M';
    }
    
    startHashrateTracking() {
        // Update display every second
        setInterval(() => {
            this.trackMiningActivity();
        }, 1000);
        
        // Initial tracking
        this.trackMiningActivity();
    }
    
    trackMiningActivity() {
        let totalHashrate = 0;
        let isActive = false;
        
        // Check various mining systems for activity
        if (window.EnhancedMouseoverMiner) {
            const stats = window.EnhancedMouseoverMiner.prototype.getStats?.call({
                stats: { hashrate: 0 },
                hashRateTracker: { getCurrentRate: () => 0 }
            });
            if (stats && stats.hashrate) {
                totalHashrate += stats.hashrate;
                isActive = true;
            }
        }
        
        // Check for active mining from current systems
        if (window.currentMiner && window.currentMiner.isActive) {
            if (window.currentMiner.currentHashrate) {
                totalHashrate += window.currentMiner.currentHashrate;
            }
            isActive = true;
        }
        
        // Check for PoW activity
        if (window.simplePow && window.simplePow.isRunning) {
            if (window.simplePow.currentHashrate) {
                totalHashrate += window.simplePow.currentHashrate;
            }
            isActive = true;
        }
        
        // Check for any global mining stats
        if (window.HaichanState) {
            const mining = window.HaichanState.getState?.('mining');
            if (mining && mining.isActive && mining.hashrate) {
                totalHashrate = Math.max(totalHashrate, mining.hashrate);
                isActive = true;
            }
        }
        
        // Update with current hashrate
        this.updateHashrate(Math.round(totalHashrate));
    }
    
    show() {
        if (this.element) {
            this.element.style.display = 'block';
        }
    }
    
    hide() {
        if (this.element) {
            this.element.style.display = 'none';
        }
    }
    
    destroy() {
        if (this.element && this.element.parentNode) {
            this.element.parentNode.removeChild(this.element);
        }
        
        // Remove styles
        const styles = document.getElementById('minimal-toolbar-styles');
        if (styles && styles.parentNode) {
            styles.parentNode.removeChild(styles);
        }
        
        // Remove body padding
        document.body.style.paddingBottom = '';
    }
}

// Initialize when DOM is ready
function initMinimalToolbar() {
    if (!window.MinimalHashrateToolbar) {
        window.MinimalHashrateToolbar = new MinimalHashrateToolbar();
        console.log('✅ Minimal hashrate toolbar initialized');
    }
}

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMinimalToolbar);
} else {
    initMinimalToolbar();
}

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MinimalHashrateToolbar;
}