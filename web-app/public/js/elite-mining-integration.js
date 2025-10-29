/**
 * HAICHAN ELITE MINING INTEGRATION
 * Integrates all premium mining components into a seamless elite experience
 * Replaces simple-pow.js with sophisticated mining for 256 elite users
 */

class EliteMiningIntegration {
    constructor() {
        this.components = {
            pow: null,
            mouseoverMiner: null,
            miniDashboard: null,
            visualEffects: null,
            achievements: null
        };
        
        this.isInitialized = false;
        this.stats = {
            proofs: 0,
            points: 0,
            hashes: 0,
            hashrate: 0,
            efficiency: 0,
            sessions: 0,
            legendary_proofs: 0
        };
        
        this.settings = {
            intensity: 'ELITE',
            visualEffects: true,
            soundEffects: false,
            autoShowDashboard: true
        };
        
        this.initialize();
        console.log('👑 Elite Mining Integration initialized');
    }
    
    async initialize() {
        try {
            // Wait for dependencies to load
            await this.waitForDependencies();
            
            // Initialize core PoW engine
            this.components.pow = window.simplePoW || new SimpleProofOfWork();
            
            // Initialize enhanced mouseover miner
            if (window.EnhancedMouseoverMiner) {
                this.components.mouseoverMiner = new EnhancedMouseoverMiner(this.components.pow);
                
                // Override stats updating to sync with integration
                const originalUpdateStats = this.components.mouseoverMiner.updateGlobalDashboard;
                this.components.mouseoverMiner.updateGlobalDashboard = () => {
                    this.syncStats();
                    originalUpdateStats.call(this.components.mouseoverMiner);
                };
            }
            
            // Initialize premium mini dashboard
            if (window.PremiumMiniDashboard && !window.premiumMiniDashboard) {
                this.components.miniDashboard = new PremiumMiniDashboard();
            } else {
                this.components.miniDashboard = window.premiumMiniDashboard;
            }
            
            // Initialize visual effects
            this.components.visualEffects = window.visualMiningEffects;
            
            // Load saved settings and stats
            this.loadSettings();
            this.loadStats();
            
            // Setup integrations
            this.setupIntegrations();
            this.setupKeyboardShortcuts();
            this.setupReputationSystem();
            
            // Connect to global state if available
            this.connectToGlobalState();
            
            this.isInitialized = true;
            
            // Show elite welcome message
            this.showEliteWelcome();
            
            console.log('✅ Elite Mining System fully initialized');
            
        } catch (error) {
            console.error('❌ Elite Mining initialization failed:', error);
            this.fallbackToSimpleMining();
        }
    }
    
    async waitForDependencies() {
        const maxWait = 10000; // 10 seconds
        const startTime = Date.now();
        
        const checkDependencies = () => {
            return window.SimpleProofOfWork && 
                   (window.EnhancedMouseoverMiner || window.SimpleMouseoverMiner) &&
                   window.VisualMiningEffects;
        };
        
        return new Promise((resolve, reject) => {
            const check = () => {
                if (checkDependencies()) {
                    resolve();
                } else if (Date.now() - startTime > maxWait) {
                    reject(new Error('Dependencies not loaded within timeout'));
                } else {
                    setTimeout(check, 100);
                }
            };
            check();
        });
    }
    
    setupIntegrations() {
        // Integrate mouseover miner with visual effects
        if (this.components.mouseoverMiner && this.components.visualEffects) {
            const originalStartMining = this.components.mouseoverMiner.startPremiumMining;
            this.components.mouseoverMiner.startPremiumMining = async (element, intensity) => {
                // Start visual effects
                this.components.visualEffects.startMiningVisualization(element, this.settings.intensity);
                
                // Call original method
                return await originalStartMining.call(this.components.mouseoverMiner, element, intensity);
            };
            
            const originalStopMining = this.components.mouseoverMiner.stopPremiumMining;
            this.components.mouseoverMiner.stopPremiumMining = (element) => {
                // Stop visual effects
                this.components.visualEffects.stopMiningVisualization(element);
                
                // Call original method
                originalStopMining.call(this.components.mouseoverMiner, element);
            };
            
            // Integrate hash discovery with visual effects
            const originalHandleSuccess = this.components.mouseoverMiner.handleMiningSuccess;
            this.components.mouseoverMiner.handleMiningSuccess = async (element, proof, statusIndicator) => {
                // Show visual hash discovery effect
                const difficulty = proof.difficulty || this.getDifficultyFromHash(proof.hash);
                this.components.visualEffects.showHashDiscovery(element, difficulty, proof.hash);
                
                // Update reputation system
                this.updateReputation(difficulty, proof);
                
                // Call original method
                return await originalHandleSuccess.call(this.components.mouseoverMiner, element, proof, statusIndicator);
            };
        }
        
        // Integrate mini dashboard with mining stats
        if (this.components.miniDashboard) {
            setInterval(() => {
                if (this.components.mouseoverMiner) {
                    const minerStats = this.components.mouseoverMiner.getStats ? this.components.mouseoverMiner.getStats() : this.components.mouseoverMiner.stats;
                    this.components.miniDashboard.setStats(minerStats);
                }
            }, 1000);
        }
        
        // Setup form mining integration
        this.setupFormMining();
    }
    
    setupFormMining() {
        // Enhanced form mining with visual feedback
        if (window.ReplyFormMiner) {
            const originalStartMining = window.ReplyFormMiner.prototype.startMining;
            window.ReplyFormMiner.prototype.startMining = async function(form, statusElement) {
                // Add visual effects to form
                if (window.eliteMiningIntegration?.components.visualEffects) {
                    window.eliteMiningIntegration.components.visualEffects.startMiningVisualization(form, 'ELITE');
                }
                
                // Call original with enhanced feedback
                const result = await originalStartMining.call(this, form, statusElement);
                
                // Stop visual effects
                if (window.eliteMiningIntegration?.components.visualEffects) {
                    window.eliteMiningIntegration.components.visualEffects.stopMiningVisualization(form);
                }
                
                return result;
            };
        }
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Elite mining shortcuts (Ctrl + Shift + key for exclusivity)
            if (e.ctrlKey && e.shiftKey) {
                switch(e.key.toLowerCase()) {
                    case 'm': // Toggle mining dashboard
                        e.preventDefault();
                        this.toggleMiniDashboard();
                        break;
                    case 'i': // Cycle intensity
                        e.preventDefault();
                        this.cycleIntensity();
                        break;
                    case 'v': // Toggle visual effects
                        e.preventDefault();
                        this.toggleVisualEffects();
                        break;
                    case 's': // Show stats overlay
                        e.preventDefault();
                        this.showStatsOverlay();
                        break;
                    case 'r': // Show reputation
                        e.preventDefault();
                        this.showReputationPanel();
                        break;
                }
            }
        });
    }
    
    setupReputationSystem() {
        this.reputation = {
            level: 1,
            experience: 0,
            title: 'Novice Miner',
            badges: new Set(),
            legendary_count: 0,
            efficiency_rating: 0
        };
        
        // Load saved reputation
        const saved = localStorage.getItem('elite_mining_reputation');
        if (saved) {
            this.reputation = { ...this.reputation, ...JSON.parse(saved) };
        }
        
        this.updateReputationTitle();
    }
    
    connectToGlobalState() {
        if (window.HaichanState) {
            // Subscribe to relevant state changes
            window.HaichanState.on('mining.settings', (settings) => {
                this.settings = { ...this.settings, ...settings };
                this.saveSettings();
            });
            
            // Publish mining stats to global state
            setInterval(() => {
                window.HaichanState.setState('mining.stats', this.stats);
                window.HaichanState.setState('mining.reputation', this.reputation);
            }, 5000);
        }
    }
    
    syncStats() {
        if (!this.components.mouseoverMiner) return;
        
        const minerStats = this.components.mouseoverMiner.getStats ? 
              this.components.mouseoverMiner.getStats() : 
              this.components.mouseoverMiner.stats;
              
        this.stats = {
            ...this.stats,
            ...minerStats,
            efficiency: this.calculateEfficiency(),
            legendary_proofs: this.countLegendaryProofs()
        };
        
        this.saveStats();
        
        // Update mini dashboard
        if (this.components.miniDashboard) {
            this.components.miniDashboard.setStats(this.stats);
        }
        
        // Update persistent toolbar
        this.updatePersistentToolbar();
    }
    
    updateReputation(difficulty, proof) {
        const points = this.getReputationPoints(difficulty);
        this.reputation.experience += points;
        
        if (difficulty === '21e8') {
            this.reputation.legendary_count++;
            this.reputation.badges.add('legendary_miner');
        }
        
        // Level up calculation
        const newLevel = Math.floor(this.reputation.experience / 1000) + 1;
        if (newLevel > this.reputation.level) {
            this.reputation.level = newLevel;
            this.showLevelUpNotification(newLevel);
        }
        
        this.updateReputationTitle();
        this.saveReputation();
    }
    
    updateReputationTitle() {
        const titles = [
            'Novice Miner',
            'Apprentice Hasher', 
            'Skilled Prospector',
            'Expert Cryptominer',
            'Elite Hash Master',
            'Legendary Proof Seeker',
            'Quantum Mining Virtuoso',
            'Transcendent Hash Sage'
        ];
        
        let titleIndex = Math.min(Math.floor(this.reputation.level / 3), titles.length - 1);
        
        // Special titles for legendary miners
        if (this.reputation.legendary_count >= 100) {
            this.reputation.title = 'Legendary Hash Sage';
        } else if (this.reputation.legendary_count >= 50) {
            this.reputation.title = 'Diamond Hand Miner';
        } else if (this.reputation.legendary_count >= 10) {
            this.reputation.title = 'Legendary Hunter';
        } else {
            this.reputation.title = titles[titleIndex];
        }
    }
    
    // Control methods
    toggleMiniDashboard() {
        if (this.components.miniDashboard) {
            if (this.components.miniDashboard.isShowing()) {
                this.components.miniDashboard.forceHide();
            } else {
                this.components.miniDashboard.forceShow();
            }
        }
    }
    
    cycleIntensity() {
        const intensities = ['CASUAL', 'ACTIVE', 'ELITE', 'LEGENDARY'];
        const currentIndex = intensities.indexOf(this.settings.intensity);
        this.settings.intensity = intensities[(currentIndex + 1) % intensities.length];
        
        if (this.components.mouseoverMiner) {
            this.components.mouseoverMiner.intensity = this.settings.intensity;
        }
        
        this.saveSettings();
        this.showNotification('Intensity Changed', `Mining intensity: ${this.settings.intensity}`);
    }
    
    toggleVisualEffects() {
        this.settings.visualEffects = !this.settings.visualEffects;
        
        if (this.components.visualEffects) {
            if (this.settings.visualEffects) {
                // Re-enable visual effects
                this.components.visualEffects.setQuality('high');
            } else {
                // Disable visual effects
                this.components.visualEffects.setQuality('low');
            }
        }
        
        if (this.components.mouseoverMiner) {
            this.components.mouseoverMiner.visualEffects = this.settings.visualEffects;
        }
        
        this.saveSettings();
        this.showNotification('Visual Effects', 
            this.settings.visualEffects ? 'Enhanced visuals enabled' : 'Minimal mode activated');
    }
    
    showStatsOverlay() {
        this.showOverlay('Elite Mining Statistics', this.generateStatsHTML());
    }
    
    showReputationPanel() {
        this.showOverlay('Mining Reputation', this.generateReputationHTML());
    }
    
    // UI Methods
    showEliteWelcome() {
        if (localStorage.getItem('elite_mining_welcomed')) return;
        
        this.showOverlay('Welcome to Elite Mining', `
            <div style="text-align: center; padding: 20px;">
                <h2 style="color: #00ffa5; margin-bottom: 20px;">👑 Elite Mining System Activated</h2>
                <p style="margin-bottom: 15px;">You are one of the exclusive 256 elite users with access to our premium mining experience.</p>
                <div style="background: rgba(0, 169, 165, 0.1); padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <h4 style="color: #00A9A5; margin-bottom: 10px;">Elite Features:</h4>
                    <ul style="text-align: left; margin: 10px 0;">
                        <li>🎯 Enhanced mouseover mining with visual feedback</li>
                        <li>📊 Premium floating dashboard with real-time stats</li>
                        <li>✨ Sophisticated particle effects and animations</li>
                        <li>💎 Legendary hash discovery celebrations</li>
                        <li>🏆 Advanced achievement and reputation system</li>
                    </ul>
                </div>
                <div style="background: rgba(255, 215, 0, 0.1); padding: 10px; border-radius: 6px; margin: 15px 0;">
                    <strong>🎮 Keyboard Shortcuts:</strong><br>
                    <code>Ctrl+Shift+M</code> Dashboard • <code>Ctrl+Shift+I</code> Intensity • <code>Ctrl+Shift+V</code> Effects
                </div>
                <p style="font-size: 12px; opacity: 0.8;">This welcome message will not show again.</p>
            </div>
        `, 8000);
        
        localStorage.setItem('elite_mining_welcomed', 'true');
    }
    
    showLevelUpNotification(newLevel) {
        const notification = document.createElement('div');
        notification.innerHTML = `
            <div class="level-up-notification">
                <div class="level-up-icon">🎊</div>
                <div class="level-up-content">
                    <div class="level-up-title">LEVEL UP!</div>
                    <div class="level-up-level">Level ${newLevel}</div>
                    <div class="level-up-title-name">${this.reputation.title}</div>
                </div>
            </div>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.95), rgba(255, 165, 0, 0.95));
            color: #333;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(255, 215, 0, 0.5);
            z-index: 10002;
            text-align: center;
            backdrop-filter: blur(20px);
            border: 3px solid rgba(255, 255, 255, 0.4);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Berkeley Mono', monospace;
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        requestAnimationFrame(() => {
            notification.style.transform = 'translate(-50%, -50%) scale(1)';
        });
        
        // Auto-remove
        setTimeout(() => {
            notification.style.transform = 'translate(-50%, -50%) scale(0)';
            setTimeout(() => notification.remove(), 600);
        }, 4000);
    }
    
    showOverlay(title, content, duration = 0) {
        const overlay = document.createElement('div');
        overlay.className = 'elite-mining-overlay';
        overlay.innerHTML = `
            <div class="overlay-content">
                <div class="overlay-header">
                    <h3>${title}</h3>
                    <button class="overlay-close">✕</button>
                </div>
                <div class="overlay-body">
                    ${content}
                </div>
            </div>
        `;
        
        overlay.style.cssText = `
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 10003;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        // Add overlay styles
        this.addOverlayStyles();
        
        document.body.appendChild(overlay);
        
        // Animate in
        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
        });
        
        // Close button
        overlay.querySelector('.overlay-close').addEventListener('click', () => {
            overlay.style.opacity = '0';
            setTimeout(() => overlay.remove(), 300);
        });
        
        // Auto-close if duration specified
        if (duration > 0) {
            setTimeout(() => {
                if (overlay.parentNode) {
                    overlay.style.opacity = '0';
                    setTimeout(() => overlay.remove(), 300);
                }
            }, duration);
        }
    }
    
    addOverlayStyles() {
        if (document.getElementById('elite-overlay-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'elite-overlay-styles';
        styles.textContent = `
            .overlay-content {
                background: linear-gradient(135deg, rgba(0, 40, 80, 0.95), rgba(0, 20, 40, 0.95));
                border: 2px solid rgba(0, 169, 165, 0.3);
                border-radius: 16px;
                max-width: 600px;
                max-height: 80vh;
                overflow-y: auto;
                color: #e8f4f8;
                font-family: 'Berkeley Mono', monospace;
            }
            
            .overlay-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px;
                border-bottom: 1px solid rgba(0, 169, 165, 0.2);
                background: rgba(0, 169, 165, 0.1);
            }
            
            .overlay-header h3 {
                margin: 0;
                color: #00ffa5;
                font-size: 18px;
            }
            
            .overlay-close {
                background: none;
                border: none;
                color: #e8f4f8;
                font-size: 18px;
                cursor: pointer;
                padding: 5px;
                border-radius: 4px;
                transition: background 0.2s ease;
            }
            
            .overlay-close:hover {
                background: rgba(255, 255, 255, 0.1);
            }
            
            .overlay-body {
                padding: 20px;
                line-height: 1.6;
            }
            
            .level-up-notification {
                display: flex;
                align-items: center;
                gap: 20px;
            }
            
            .level-up-icon {
                font-size: 48px;
                animation: celebration-spin 2s linear infinite;
            }
            
            .level-up-title {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 5px;
            }
            
            .level-up-level {
                font-size: 36px;
                font-weight: bold;
                color: #FF6B35;
            }
            
            .level-up-title-name {
                font-size: 16px;
                opacity: 0.9;
            }
            
            @keyframes celebration-spin {
                0% { transform: rotate(0deg) scale(1); }
                25% { transform: rotate(90deg) scale(1.1); }
                50% { transform: rotate(180deg) scale(1); }
                75% { transform: rotate(270deg) scale(1.1); }
                100% { transform: rotate(360deg) scale(1); }
            }
        `;
        document.head.appendChild(styles);
    }
    
    generateStatsHTML() {
        const efficiency = this.calculateEfficiency();
        const legendary = this.countLegendaryProofs();
        
        return `
            <div class="stats-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                <div class="stat-card" style="background: rgba(0, 169, 165, 0.1); padding: 15px; border-radius: 8px;">
                    <h4 style="color: #00A9A5; margin-bottom: 10px;">Mining Performance</h4>
                    <p><strong>Total Proofs:</strong> ${this.stats.proofs.toLocaleString()}</p>
                    <p><strong>Points Earned:</strong> ${this.stats.points.toFixed(2)}</p>
                    <p><strong>Hash Rate:</strong> ${this.stats.hashrate.toLocaleString()} H/s</p>
                    <p><strong>Efficiency:</strong> ${efficiency}%</p>
                </div>
                <div class="stat-card" style="background: rgba(255, 215, 0, 0.1); padding: 15px; border-radius: 8px;">
                    <h4 style="color: #FFD700; margin-bottom: 10px;">Elite Achievements</h4>
                    <p><strong>Mining Level:</strong> ${this.reputation.level}</p>
                    <p><strong>Title:</strong> ${this.reputation.title}</p>
                    <p><strong>Legendary Proofs:</strong> ${legendary}</p>
                    <p><strong>Total Hashes:</strong> ${this.stats.hashes.toLocaleString()}</p>
                </div>
            </div>
            <div style="background: rgba(0, 255, 165, 0.1); padding: 15px; border-radius: 8px; margin-top: 20px;">
                <h4 style="color: #00ffa5; margin-bottom: 10px;">Current Settings</h4>
                <p><strong>Intensity:</strong> ${this.settings.intensity}</p>
                <p><strong>Visual Effects:</strong> ${this.settings.visualEffects ? 'Enabled' : 'Disabled'}</p>
                <p><strong>Auto Dashboard:</strong> ${this.settings.autoShowDashboard ? 'Enabled' : 'Disabled'}</p>
            </div>
        `;
    }
    
    generateReputationHTML() {
        const badges = Array.from(this.reputation.badges).map(badge => 
            `<span style="background: rgba(255, 215, 0, 0.2); padding: 4px 8px; border-radius: 4px; margin: 2px;">${badge.replace('_', ' ')}</span>`
        ).join(' ');
        
        return `
            <div style="text-align: center; margin-bottom: 20px;">
                <h2 style="color: #FFD700; margin-bottom: 10px;">${this.reputation.title}</h2>
                <div style="font-size: 24px; margin-bottom: 10px;">Level ${this.reputation.level}</div>
                <div style="background: rgba(0, 0, 0, 0.3); border-radius: 10px; height: 20px; overflow: hidden; margin: 10px 0;">
                    <div style="background: linear-gradient(90deg, #00A9A5, #00ffa5); height: 100%; width: ${(this.reputation.experience % 1000) / 10}%; transition: width 0.5s ease;"></div>
                </div>
                <div style="font-size: 12px; opacity: 0.8;">${this.reputation.experience % 1000}/1000 XP to next level</div>
            </div>
            <div style="margin: 20px 0;">
                <h4 style="color: #00A9A5; margin-bottom: 10px;">Achievements</h4>
                <div>${badges || '<em>No badges earned yet</em>'}</div>
            </div>
            <div style="background: rgba(0, 169, 165, 0.1); padding: 15px; border-radius: 8px;">
                <h4 style="color: #00A9A5; margin-bottom: 10px;">Mining Records</h4>
                <p><strong>Legendary Hashes:</strong> ${this.reputation.legendary_count}</p>
                <p><strong>Total Experience:</strong> ${this.reputation.experience.toLocaleString()}</p>
                <p><strong>Efficiency Rating:</strong> ${this.reputation.efficiency_rating}%</p>
            </div>
        `;
    }
    
    // Utility methods
    calculateEfficiency() {
        if (this.stats.hashes === 0) return 0;
        return Math.round((this.stats.proofs / this.stats.hashes) * 100 * 1000) / 10; // Efficiency as percentage
    }
    
    countLegendaryProofs() {
        return this.reputation.legendary_count || 0;
    }
    
    getDifficultyFromHash(hash) {
        if (hash.startsWith('21e8')) return '21e8';
        if (hash.startsWith('21e')) return '21e';
        if (hash.startsWith('21')) return '21';
        if (hash.startsWith('2')) return '2';
        return '2';
    }
    
    getReputationPoints(difficulty) {
        const points = {
            '2': 10,
            '21': 50,
            '21e': 200,
            '21e8': 1000
        };
        return points[difficulty] || 10;
    }
    
    updatePersistentToolbar() {
        const toolbar = document.querySelector('#haichan-persistent-toolbar');
        if (!toolbar) return;
        
        const hashrateEl = toolbar.querySelector('.mining-hashrate');
        const totalEl = toolbar.querySelector('.mining-total');
        
        if (hashrateEl) {
            hashrateEl.textContent = `${this.stats.hashrate.toLocaleString()} H/s`;
        }
        
        if (totalEl) {
            totalEl.textContent = `${this.stats.proofs} total PoW`;
        }
    }
    
    showNotification(title, message) {
        if (this.components.mouseoverMiner && this.components.mouseoverMiner.showPremiumNotification) {
            this.components.mouseoverMiner.showPremiumNotification(title, message);
        }
    }
    
    // Data persistence
    saveSettings() {
        localStorage.setItem('elite_mining_settings', JSON.stringify(this.settings));
    }
    
    loadSettings() {
        const saved = localStorage.getItem('elite_mining_settings');
        if (saved) {
            this.settings = { ...this.settings, ...JSON.parse(saved) };
        }
    }
    
    saveStats() {
        localStorage.setItem('elite_mining_stats', JSON.stringify(this.stats));
    }
    
    loadStats() {
        const saved = localStorage.getItem('elite_mining_stats');
        if (saved) {
            this.stats = { ...this.stats, ...JSON.parse(saved) };
        }
    }
    
    saveReputation() {
        // Convert Set to Array for JSON serialization
        const reputationToSave = {
            ...this.reputation,
            badges: Array.from(this.reputation.badges)
        };
        localStorage.setItem('elite_mining_reputation', JSON.stringify(reputationToSave));
    }
    
    fallbackToSimpleMining() {
        console.warn('⚠️ Falling back to simple mining due to initialization failure');
        
        // Try to initialize basic mining if available
        if (window.simplePoW && window.SimpleMouseoverMiner) {
            window.mouseoverMiner = new SimpleMouseoverMiner(window.simplePoW);
            window.miningToolbar = new MiningToolbar(window.mouseoverMiner);
        }
    }
    
    // Public API
    getStats() {
        return { ...this.stats, reputation: this.reputation };
    }
    
    isEliteMiner() {
        return this.isInitialized && this.reputation.level >= 5;
    }
    
    destroy() {
        if (this.components.visualEffects) {
            this.components.visualEffects.destroy();
        }
        
        this.saveStats();
        this.saveSettings();
        this.saveReputation();
    }
}

// Initialize Elite Mining System
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.eliteMiningIntegration = new EliteMiningIntegration();
    });
} else {
    window.eliteMiningIntegration = new EliteMiningIntegration();
}

// Override global mining objects with elite versions
window.addEventListener('load', () => {
    if (window.eliteMiningIntegration?.isInitialized) {
        // Make elite components available globally for backward compatibility
        if (window.eliteMiningIntegration.components.mouseoverMiner) {
            window.mouseoverMiner = window.eliteMiningIntegration.components.mouseoverMiner;
        }
        
        console.log('👑 Elite Mining System has taken control');
        console.log('🎮 Use Ctrl+Shift+S to view your elite statistics');
    }
});

console.log('🚀 Elite Mining Integration loaded - Premium experience for 256 elite users');