class ThemeSwitcher {
    constructor() {
        this.currentTheme = localStorage.getItem('haichan-theme') || 'business';
        this.init();
    }

    init() {
        this.createSwitcher();
        this.applyTheme(this.currentTheme);
        this.attachEventListeners();
    }

    createSwitcher() {
        const switcher = document.createElement('div');
        switcher.id = 'theme-switcher';
        switcher.innerHTML = `
            <div class="theme-switcher-container">
                <div class="theme-switcher-label">AESTHETIC</div>
                <div class="theme-toggle">
                    <button class="theme-btn" data-theme="business" title="Business (ビジネス) - Windows 95 Corporate">
                        <span class="theme-icon">💼</span>
                        <span class="theme-name">ビジネス</span>
                        <span class="theme-subtitle">BUSINESS</span>
                    </button>
                    <button class="theme-btn" data-theme="pleasure" title="Pleasure (楽しみ) - Windows 2095 Cyberpunk">
                        <span class="theme-icon">🎮</span>
                        <span class="theme-name">楽しみ</span>
                        <span class="theme-subtitle">PLEASURE</span>
                    </button>
                </div>
            </div>
        `;

        // Add styles for the switcher
        const style = document.createElement('style');
        style.textContent = `
            #theme-switcher {
                position: fixed;
                top: 100px;
                right: 20px;
                z-index: 10001;
                font-family: 'Courier New', monospace;
                font-size: 10px;
                user-select: none;
            }

            .theme-switcher-container {
                background: rgba(0, 0, 0, 0.8);
                border: 1px solid #666;
                border-radius: 4px;
                padding: 8px;
                backdrop-filter: blur(5px);
            }

            .theme-switcher-label {
                text-align: center;
                color: #ccc;
                font-size: 8px;
                margin-bottom: 6px;
                letter-spacing: 1px;
            }

            .theme-toggle {
                display: flex;
                gap: 4px;
            }

            .theme-btn {
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
                color: #ccc;
                padding: 6px 8px;
                cursor: pointer;
                font-size: 8px;
                line-height: 1.1;
                transition: all 0.2s ease;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 2px;
                min-width: 50px;
                border-radius: 2px;
            }

            .theme-btn:hover {
                background: rgba(255, 255, 255, 0.2);
                border-color: rgba(255, 255, 255, 0.4);
                transform: translateY(-1px);
            }

            .theme-btn.active {
                background: rgba(255, 255, 255, 0.3);
                border-color: rgba(255, 255, 255, 0.6);
                color: white;
                box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
            }

            .theme-icon {
                font-size: 12px;
                margin-bottom: 1px;
            }

            .theme-name {
                font-weight: bold;
                font-size: 9px;
            }

            .theme-subtitle {
                font-size: 6px;
                opacity: 0.7;
                letter-spacing: 0.5px;
            }

            /* Business theme styling for switcher */
            .theme-business #theme-switcher .theme-switcher-container {
                background: linear-gradient(135deg, #f0f0f0, #d0d0d0);
                border: 2px solid;
                border-color: #ffffff #808080 #808080 #ffffff;
                color: #000;
            }

            .theme-business #theme-switcher .theme-switcher-label {
                color: #000;
            }

            .theme-business #theme-switcher .theme-btn {
                background: #e0e0e0;
                border: 1px solid;
                border-color: #ffffff #808080 #808080 #ffffff;
                color: #000;
            }

            .theme-business #theme-switcher .theme-btn:hover {
                background: #d0d0d0;
            }

            .theme-business #theme-switcher .theme-btn.active {
                background: #316ac5;
                color: white;
                border-color: #808080 #ffffff #ffffff #808080;
            }

            /* Pleasure theme styling for switcher */
            .theme-pleasure #theme-switcher .theme-switcher-container {
                background: linear-gradient(135deg, #1a0030, #2a0050);
                border: 2px solid #00ffff;
                color: #00ff41;
                box-shadow: 0 0 15px rgba(0, 255, 255, 0.5);
            }

            .theme-pleasure #theme-switcher .theme-switcher-label {
                color: #00ff41;
                text-shadow: 0 0 5px currentColor;
            }

            .theme-pleasure #theme-switcher .theme-btn {
                background: rgba(26, 0, 48, 0.8);
                border: 1px solid #ff00ff;
                color: #00ff41;
                text-shadow: 0 0 3px currentColor;
                box-shadow: 0 0 5px rgba(255, 0, 255, 0.3);
            }

            .theme-pleasure #theme-switcher .theme-btn:hover {
                background: rgba(42, 0, 80, 0.9);
                border-color: #00ffff;
                box-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
            }

            .theme-pleasure #theme-switcher .theme-btn.active {
                background: linear-gradient(135deg, #9400d3, #ff1493);
                border-color: #00ffff;
                color: #ffffff;
                text-shadow: 0 0 5px currentColor;
                box-shadow: 0 0 15px rgba(0, 255, 255, 0.8);
            }
        `;

        document.head.appendChild(style);
        document.body.appendChild(switcher);
    }

    attachEventListeners() {
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const theme = e.currentTarget.dataset.theme;
                this.switchTheme(theme);
            });
        });
    }

    switchTheme(theme) {
        this.currentTheme = theme;
        this.applyTheme(theme);
        localStorage.setItem('haichan-theme', theme);
        
        // Update button states
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.theme === theme);
        });

        // Log theme change
        console.log(`🎨 Theme switched to ${theme === 'business' ? 'Business (ビジネス)' : 'Pleasure (楽しみ)'}`);
    }

    applyTheme(theme) {
        // Remove existing theme classes
        document.body.classList.remove('theme-business', 'theme-pleasure');
        
        // Apply new theme
        document.body.classList.add(`theme-${theme}`);
        
        // Update button states
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.theme === theme);
        });
    }
}

// Initialize theme switcher when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.themeSwitcher = new ThemeSwitcher();
});