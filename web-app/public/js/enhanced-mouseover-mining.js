/**
 * HAICHAN ENHANCED MOUSEOVER MINING SYSTEM
 * Revolutionary PoW mining experience for 256 elite users
 * Features: Real SHA256, responsive visual feedback, premium animations
 */

class EnhancedMouseoverMiner {
    constructor(powEngine) {
        this.pow = powEngine;
        this.isActive = true;
        this.currentTarget = null;
        this.miningSession = null;
        this.stats = {
            proofs: 0,
            points: 0,
            hashes: 0,
            hashrate: 0,
            sessions: 0
        };
        
        // Mining intensity levels for premium experience
        this.intensity = 'ELITE'; // CASUAL, ACTIVE, ELITE, LEGENDARY
        this.visualEffects = true;
        this.soundEnabled = false; // Can be enabled for premium feedback
        
        // Real-time tracking
        this.hashRateTracker = new HashRateTracker();
        this.achievementSystem = new AchievementSystem();
        this.visualEffectEngine = new VisualEffectEngine();
        
        this.setupMouseoverMining();
        this.setupKeyboardShortcuts();
        console.log('🎯 Enhanced Mouseover Mining: Elite mode activated');
    }
    
    setupMouseoverMining() {
        // Enhanced mouse tracking with precision and responsiveness
        let lastMouseX = 0, lastMouseY = 0;
        let mouseMovementAccum = 0;
        let movementThreshold = 5; // Pixels to trigger mining consideration
        
        // Mouseover event with enhanced targeting
        document.addEventListener('mouseover', (e) => {
            if (!this.isActive) return;
            
            const target = e.target.closest('[data-mine-type]');
            if (!target || target === this.currentTarget) return;
            
            // Calculate mouse movement intensity
            const deltaX = Math.abs(e.clientX - lastMouseX);
            const deltaY = Math.abs(e.clientY - lastMouseY);
            const movement = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
            
            if (movement > movementThreshold) {
                this.startPremiumMining(target, movement);
            }
            
            lastMouseX = e.clientX;
            lastMouseY = e.clientY;
        });
        
        // Enhanced mouse movement tracking for continuous mining
        document.addEventListener('mousemove', (e) => {
            if (!this.isActive || !this.currentTarget) return;
            
            const deltaX = Math.abs(e.clientX - lastMouseX);
            const deltaY = Math.abs(e.clientY - lastMouseY);
            const movement = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
            
            mouseMovementAccum += movement;
            
            // Boost mining based on movement intensity
            if (mouseMovementAccum > 50) {
                this.boostMining(movement);
                mouseMovementAccum = 0;
            }
            
            lastMouseX = e.clientX;
            lastMouseY = e.clientY;
        });
        
        // Mouseout with elegant cleanup
        document.addEventListener('mouseout', (e) => {
            const target = e.target.closest('[data-mine-type]');
            if (target === this.currentTarget) {
                this.stopPremiumMining(target);
            }
        });
        
        // Touch support for mobile elite users
        this.setupTouchMining();
    }
    
    setupTouchMining() {
        let touchStartTime = 0;
        
        document.addEventListener('touchstart', (e) => {
            if (!this.isActive) return;
            
            touchStartTime = Date.now();
            const target = e.target.closest('[data-mine-type]');
            if (target && target !== this.currentTarget) {
                this.startPremiumMining(target, 10); // Base intensity for touch
            }
        }, { passive: true });
        
        document.addEventListener('touchmove', (e) => {
            if (!this.isActive || !this.currentTarget) return;
            
            const touchDuration = Date.now() - touchStartTime;
            if (touchDuration > 100) { // Minimum touch duration
                this.boostMining(5); // Touch boost
            }
        }, { passive: true });
        
        document.addEventListener('touchend', (e) => {
            const target = e.target.closest('[data-mine-type]');
            if (target === this.currentTarget) {
                this.stopPremiumMining(target);
            }
        });
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Mining control shortcuts for power users
            switch(e.key.toLowerCase()) {
                case 'm':
                    if (e.ctrlKey) {
                        e.preventDefault();
                        this.toggleMining();
                    }
                    break;
                case 'i':
                    if (e.ctrlKey) {
                        e.preventDefault();
                        this.cycleIntensity();
                    }
                    break;
                case 'v':
                    if (e.ctrlKey) {
                        e.preventDefault();
                        this.toggleVisualEffects();
                    }
                    break;
            }
        });
    }
    
    async startPremiumMining(element, movementIntensity) {
        if (this.currentTarget) return; // Already mining
        
        this.currentTarget = element;
        this.stats.sessions++;
        
        // Apply premium visual feedback immediately
        this.visualEffectEngine.startMiningVisuals(element, this.intensity);
        
        // Show enhanced cursor
        this.applyMiningCursor(element);
        
        // Display mining status with style
        const statusIndicator = this.createStatusIndicator(element);
        this.updateStatusIndicator(statusIndicator, 'mining', 'Quantum mining initiated...');
        
        try {
            // Determine target type and ID
            const mineType = element.dataset.mineType;
            const threadId = element.dataset.threadId;
            const postId = element.dataset.postId;
            const boardCode = element.dataset.boardCode || 'd';
            
            let targetType, targetId, difficulty;
            
            switch (mineType) {
                case 'thread':
                case 'thread-op':
                    targetType = 'thread';
                    targetId = threadId;
                    break;
                case 'post':
                    targetType = 'post';
                    targetId = postId;
                    break;
                default:
                    targetType = 'general';
                    targetId = null;
            }
            
            // Elite mining uses 21e8 difficulty for maximum exclusivity
            difficulty = this.getDifficultyForIntensity(this.intensity);
            
            // Update status with technical details
            this.updateStatusIndicator(statusIndicator, 'mining', 
                `Mining ${difficulty} difficulty • ${this.intensity} mode`);
            
            // Start hash rate tracking
            this.hashRateTracker.startSession();
            
            // Execute the actual mining with enhanced feedback
            const proof = await this.mineWithProgress(
                boardCode, targetType, targetId, difficulty, statusIndicator
            );
            
            if (proof) {
                await this.handleMiningSuccess(element, proof, statusIndicator);
            }
            
        } catch (error) {
            this.handleMiningError(element, error, statusIndicator);
        }
    }
    
    async mineWithProgress(boardCode, targetType, targetId, difficulty, statusIndicator) {
        const startTime = Date.now();
        let hashCount = 0;
        
        // Create a progress-enabled version of mining
        const originalMine = this.pow.mine.bind(this.pow);
        this.pow.mine = async (data, diff) => {
            const maxAttempts = this.getMaxAttemptsForDifficulty(diff);
            let nonce = 0;
            
            while (nonce < maxAttempts) {
                const testData = data + ':' + nonce;
                const hash = await this.pow.sha256(testData);
                hashCount++;
                
                // Update progress every 1000 hashes
                if (hashCount % 1000 === 0) {
                    const elapsed = (Date.now() - startTime) / 1000;
                    const hashrate = Math.round(hashCount / elapsed);
                    
                    this.updateStatusIndicator(statusIndicator, 'mining', 
                        `Mining... ${hashCount.toLocaleString()} hashes • ${hashrate} H/s`);
                    
                    this.hashRateTracker.addSample(hashrate);
                    
                    // Allow UI updates
                    await new Promise(resolve => setTimeout(resolve, 1));
                }
                
                if (hash.toLowerCase().startsWith(diff.toLowerCase())) {
                    // Restore original mining function
                    this.pow.mine = originalMine;
                    return { nonce, hash };
                }
                
                nonce++;
            }
            
            throw new Error('Mining failed: Max attempts reached');
        };
        
        // Execute mining with progress tracking
        const proof = await this.pow.acquireProofFor({
            board_code: boardCode,
            target_type: targetType,
            target_id: targetId,
            action: 'mine',
            difficulty: difficulty
        });
        
        // Update final stats
        this.stats.hashes += hashCount;
        this.hashRateTracker.endSession();
        
        return proof;
    }
    
    async handleMiningSuccess(element, proof, statusIndicator) {
        // Update stats
        this.stats.proofs++;
        this.stats.points += this.calculatePointsForDifficulty(proof.difficulty || '21e8');
        this.stats.hashrate = this.hashRateTracker.getCurrentRate();
        
        // Show legendary success animation
        this.visualEffectEngine.showHashDiscoveryEffect(element, proof);
        
        // Update status with success
        this.updateStatusIndicator(statusIndicator, 'success', 
            `💎 Legendary hash discovered! • ${proof.hash.substring(0, 16)}...`);
        
        // Achievement check
        this.achievementSystem.checkAchievements(this.stats, proof);
        
        // Update global mining dashboard if available
        this.updateGlobalDashboard();
        
        // Submit proof (silently, don't block UX)
        this.submitProofSilently(proof);
        
        // Premium notification
        this.showPremiumNotification('Hash Discovery', 
            `Elite mining successful • +${this.calculatePointsForDifficulty(proof.difficulty || '21e8')} points`);
    }
    
    handleMiningError(element, error, statusIndicator) {
        console.log('Mining failed silently:', error);
        
        // Show subtle error indication without disrupting UX
        this.updateStatusIndicator(statusIndicator, 'idle', 
            '⚡ Ready for next mining session');
        
        // No intrusive error messages for elite users
    }
    
    stopPremiumMining(element) {
        if (!this.currentTarget) return;
        
        // Clean up visual effects
        this.visualEffectEngine.stopMiningVisuals(element);
        this.removeMiningCursor(element);
        
        // Update status to idle
        const statusIndicator = element.querySelector('.mining-status-indicator');
        if (statusIndicator) {
            this.updateStatusIndicator(statusIndicator, 'idle', '⚡ Mining ready');
            
            // Auto-remove indicator after delay
            setTimeout(() => {
                if (statusIndicator.parentNode) {
                    statusIndicator.remove();
                }
            }, 2000);
        }
        
        this.currentTarget = null;
    }
    
    boostMining(movementIntensity) {
        if (!this.currentTarget) return;
        
        // Visual feedback for movement boost
        this.visualEffectEngine.addMovementBoost(this.currentTarget, movementIntensity);
        
        // Slight hashrate boost for engaged users (cosmetic)
        this.hashRateTracker.addBoost(movementIntensity * 0.1);
    }
    
    // Status indicator system
    createStatusIndicator(element) {
        const existing = element.querySelector('.mining-status-indicator');
        if (existing) existing.remove();
        
        const indicator = document.createElement('div');
        indicator.className = 'mining-status-indicator';
        indicator.style.cssText = `
            position: absolute;
            top: -30px;
            right: -10px;
            background: linear-gradient(135deg, rgba(0, 169, 165, 0.95), rgba(144, 194, 231, 0.95));
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            font-family: 'Berkeley Mono', monospace;
            z-index: 1000;
            pointer-events: none;
            box-shadow: 0 4px 12px rgba(0, 169, 165, 0.3);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 120px;
            text-align: center;
        `;
        
        element.style.position = element.style.position || 'relative';
        element.appendChild(indicator);
        
        return indicator;
    }
    
    updateStatusIndicator(indicator, status, message) {
        if (!indicator) return;
        
        const statusConfig = {
            mining: { bg: 'linear-gradient(135deg, rgba(0, 169, 165, 0.95), rgba(144, 194, 231, 0.95))', icon: '⛏️' },
            success: { bg: 'linear-gradient(135deg, rgba(144, 238, 144, 0.95), rgba(0, 169, 165, 0.95))', icon: '💎' },
            error: { bg: 'linear-gradient(135deg, rgba(220, 53, 69, 0.95), rgba(255, 107, 107, 0.95))', icon: '⚠️' },
            idle: { bg: 'linear-gradient(135deg, rgba(108, 117, 125, 0.95), rgba(134, 142, 150, 0.95))', icon: '⚡' }
        };
        
        const config = statusConfig[status] || statusConfig.idle;
        
        indicator.style.background = config.bg;
        indicator.innerHTML = `<span style="margin-right: 4px;">${config.icon}</span>${message}`;
        
        // Add pulse animation for mining status
        if (status === 'mining') {
            indicator.style.animation = 'mining-pulse 2s infinite';
        } else {
            indicator.style.animation = '';
        }
    }
    
    applyMiningCursor(element) {
        // Premium mining cursor with custom SVG
        const cursorSvg = `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none"><defs><linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:rgb(0,169,165);stop-opacity:1" /><stop offset="100%" style="stop-color:rgb(144,194,231);stop-opacity:1" /></linearGradient></defs><path d="M4 4l24 12-12 6-6 12-6-30z" fill="url(%23grad1)" stroke="white" stroke-width="1"/><circle cx="16" cy="16" r="2" fill="white" opacity="0.8"><animate attributeName="r" values="2;4;2" dur="1s" repeatCount="indefinite"/></circle></svg>`;
        
        element.style.cursor = `url('${cursorSvg}'), crosshair`;
        element.classList.add('mining-cursor-active');
    }
    
    removeMiningCursor(element) {
        element.style.cursor = '';
        element.classList.remove('mining-cursor-active');
    }
    
    // Utility methods
    getDifficultyForIntensity(intensity) {
        const difficultyMap = {
            'CASUAL': '2',
            'ACTIVE': '21',
            'ELITE': '21e',
            'LEGENDARY': '21e8'
        };
        return difficultyMap[intensity] || '21e8';
    }
    
    getMaxAttemptsForDifficulty(difficulty) {
        const attemptsMap = {
            '2': 1000,
            '21': 10000,
            '21e': 100000,
            '21e8': 10000000
        };
        return attemptsMap[difficulty] || 10000000;
    }
    
    calculatePointsForDifficulty(difficulty) {
        const pointsMap = {
            '2': 0.1,
            '21': 1,
            '21e': 10,
            '21e8': 100
        };
        return pointsMap[difficulty] || 100;
    }
    
    // Control methods
    toggleMining() {
        this.isActive = !this.isActive;
        this.showPremiumNotification(
            'Mining Control', 
            this.isActive ? 'Elite mining activated' : 'Mining paused'
        );
    }
    
    cycleIntensity() {
        const intensities = ['CASUAL', 'ACTIVE', 'ELITE', 'LEGENDARY'];
        const currentIndex = intensities.indexOf(this.intensity);
        this.intensity = intensities[(currentIndex + 1) % intensities.length];
        
        this.showPremiumNotification(
            'Intensity Changed', 
            `Mining intensity: ${this.intensity}`
        );
    }
    
    toggleVisualEffects() {
        this.visualEffects = !this.visualEffects;
        this.visualEffectEngine.enabled = this.visualEffects;
        
        this.showPremiumNotification(
            'Visual Effects', 
            this.visualEffects ? 'Enhanced visuals enabled' : 'Minimal mode activated'
        );
    }
    
    // Minimal toolbar integration
    updateGlobalDashboard() {
        // Update minimal hashrate toolbar
        if (window.MinimalHashrateToolbar) {
            window.MinimalHashrateToolbar.updateHashrate(this.stats.hashrate);
        }
        
        // Update global state for other systems
        if (window.HaichanState) {
            window.HaichanState.setState('mining.hashrate', this.stats.hashrate);
            window.HaichanState.setState('mining.isActive', this.stats.hashrate > 0);
            window.HaichanState.setState('mining.totalHashes', this.stats.hashes);
        }
    }
    
    async submitProofSilently(proof) {
        // Submit proof without blocking UX
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            // This would submit to the actual mining endpoint
            // For now, just log the proof
            console.log('✅ Proof ready for submission:', {
                nonce: proof.nonce,
                hash: proof.hash,
                challenge_id: proof.challenge_id
            });
            
        } catch (error) {
            console.log('Silent proof submission failed:', error);
        }
    }
    
    showPremiumNotification(title, message) {
        // Create elegant notification system
        const notification = document.createElement('div');
        notification.className = 'premium-mining-notification';
        notification.innerHTML = `
            <div class="notification-icon">💎</div>
            <div class="notification-content">
                <div class="notification-title">${title}</div>
                <div class="notification-message">${message}</div>
            </div>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, rgba(0, 169, 165, 0.95), rgba(144, 194, 231, 0.95));
            color: white;
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 169, 165, 0.3);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 320px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        requestAnimationFrame(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        });
        
        // Auto-remove
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 400);
        }, 4000);
    }
    
    // Diagnostic methods for elite users
    getStats() {
        return {
            ...this.stats,
            intensity: this.intensity,
            isActive: this.isActive,
            currentHashrate: this.hashRateTracker.getCurrentRate(),
            averageHashrate: this.hashRateTracker.getAverageRate()
        };
    }
}

// Hash Rate Tracking System
class HashRateTracker {
    constructor() {
        this.samples = [];
        this.maxSamples = 60; // Keep 1 minute of samples
        this.sessionStart = null;
        this.sessionHashes = 0;
    }
    
    startSession() {
        this.sessionStart = Date.now();
        this.sessionHashes = 0;
    }
    
    endSession() {
        if (this.sessionStart) {
            const elapsed = (Date.now() - this.sessionStart) / 1000;
            const finalRate = this.sessionHashes / elapsed;
            this.addSample(finalRate);
        }
        this.sessionStart = null;
    }
    
    addSample(hashrate) {
        this.samples.push({
            rate: hashrate,
            timestamp: Date.now()
        });
        
        if (this.samples.length > this.maxSamples) {
            this.samples.shift();
        }
    }
    
    addBoost(amount) {
        // Add a temporary boost to current reading
        if (this.samples.length > 0) {
            this.samples[this.samples.length - 1].rate += amount;
        }
    }
    
    getCurrentRate() {
        if (this.samples.length === 0) return 0;
        return Math.round(this.samples[this.samples.length - 1].rate);
    }
    
    getAverageRate() {
        if (this.samples.length === 0) return 0;
        const sum = this.samples.reduce((acc, sample) => acc + sample.rate, 0);
        return Math.round(sum / this.samples.length);
    }
    
    getHistoryForChart() {
        return this.samples.map(sample => sample.rate);
    }
}

// Achievement System for Elite Users
class AchievementSystem {
    constructor() {
        this.achievements = new Map();
        this.unlockedAchievements = new Set();
        this.setupAchievements();
    }
    
    setupAchievements() {
        this.achievements.set('first-hash', {
            name: 'First Strike',
            description: 'Discover your first hash',
            condition: (stats) => stats.proofs >= 1,
            icon: '⚡',
            rarity: 'common'
        });
        
        this.achievements.set('legendary-miner', {
            name: 'Legendary Miner',
            description: 'Find a 21e8 difficulty hash',
            condition: (stats, proof) => proof && proof.hash && proof.hash.startsWith('21e8'),
            icon: '💎',
            rarity: 'legendary'
        });
        
        this.achievements.set('speed-demon', {
            name: 'Speed Demon',
            description: 'Achieve over 10,000 H/s',
            condition: (stats) => stats.hashrate > 10000,
            icon: '🚀',
            rarity: 'epic'
        });
        
        this.achievements.set('mining-elite', {
            name: 'Mining Elite',
            description: 'Find 100 proofs',
            condition: (stats) => stats.proofs >= 100,
            icon: '👑',
            rarity: 'legendary'
        });
    }
    
    checkAchievements(stats, proof = null) {
        for (const [id, achievement] of this.achievements) {
            if (!this.unlockedAchievements.has(id) && achievement.condition(stats, proof)) {
                this.unlockAchievement(id, achievement);
            }
        }
    }
    
    unlockAchievement(id, achievement) {
        this.unlockedAchievements.add(id);
        
        // Show achievement notification
        this.showAchievementUnlock(achievement);
        
        // Store in localStorage for persistence
        const stored = JSON.parse(localStorage.getItem('mining_achievements') || '[]');
        stored.push({ id, unlockedAt: Date.now() });
        localStorage.setItem('mining_achievements', JSON.stringify(stored));
    }
    
    showAchievementUnlock(achievement) {
        const notification = document.createElement('div');
        notification.className = 'achievement-unlock';
        notification.innerHTML = `
            <div class="achievement-header">🏆 ACHIEVEMENT UNLOCKED</div>
            <div class="achievement-body">
                <span class="achievement-icon">${achievement.icon}</span>
                <div class="achievement-details">
                    <div class="achievement-name">${achievement.name}</div>
                    <div class="achievement-desc">${achievement.description}</div>
                    <div class="achievement-rarity rarity-${achievement.rarity}">${achievement.rarity.toUpperCase()}</div>
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
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 12px 32px rgba(255, 215, 0, 0.4);
            z-index: 10001;
            max-width: 400px;
            text-align: center;
            backdrop-filter: blur(16px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        `;
        
        document.body.appendChild(notification);
        
        // Animate in with celebration effect
        requestAnimationFrame(() => {
            notification.style.transform = 'translate(-50%, -50%) scale(1)';
        });
        
        // Auto-remove after celebration
        setTimeout(() => {
            notification.style.transform = 'translate(-50%, -50%) scale(0)';
            setTimeout(() => notification.remove(), 500);
        }, 5000);
    }
}

// Visual Effect Engine for Premium Experience
class VisualEffectEngine {
    constructor() {
        this.enabled = true;
        this.particlePool = [];
        this.setupStyles();
    }
    
    setupStyles() {
        if (document.getElementById('enhanced-mining-effects')) return;
        
        const styles = document.createElement('style');
        styles.id = 'enhanced-mining-effects';
        styles.textContent = `
            @keyframes mining-pulse {
                0%, 100% { transform: scale(1); opacity: 1; }
                50% { transform: scale(1.05); opacity: 0.8; }
            }
            
            @keyframes hash-discovery-premium {
                0% { 
                    transform: scale(1); 
                    box-shadow: 0 0 0 rgba(0, 169, 165, 0); 
                }
                25% { 
                    transform: scale(1.02); 
                    box-shadow: 0 0 20px rgba(0, 169, 165, 0.6); 
                }
                50% { 
                    transform: scale(1.05); 
                    box-shadow: 0 0 40px rgba(144, 194, 231, 0.8); 
                }
                75% { 
                    transform: scale(1.02); 
                    box-shadow: 0 0 20px rgba(0, 169, 165, 0.6); 
                }
                100% { 
                    transform: scale(1); 
                    box-shadow: 0 0 0 rgba(0, 169, 165, 0); 
                }
            }
            
            .mining-cursor-active {
                position: relative;
                overflow: visible;
            }
            
            .mining-cursor-active::before {
                content: '';
                position: absolute;
                inset: -2px;
                background: linear-gradient(45deg, rgba(0, 169, 165, 0.3), rgba(144, 194, 231, 0.3));
                border-radius: inherit;
                z-index: -1;
                animation: mining-aura 3s ease-in-out infinite;
            }
            
            @keyframes mining-aura {
                0%, 100% { opacity: 0.3; transform: scale(1); }
                50% { opacity: 0.6; transform: scale(1.02); }
            }
            
            .mining-particle {
                position: absolute;
                width: 4px;
                height: 4px;
                background: radial-gradient(circle, rgba(0, 169, 165, 1), rgba(144, 194, 231, 0.5));
                border-radius: 50%;
                pointer-events: none;
                animation: particle-float 3s ease-out forwards;
            }
            
            @keyframes particle-float {
                0% {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
                100% {
                    opacity: 0;
                    transform: translateY(-50px) scale(0.5);
                }
            }
        `;
        document.head.appendChild(styles);
    }
    
    startMiningVisuals(element, intensity) {
        if (!this.enabled) return;
        
        element.classList.add('mining-cursor-active');
        
        // Add intensity-based visual effects
        this.addMiningAura(element, intensity);
    }
    
    stopMiningVisuals(element) {
        element.classList.remove('mining-cursor-active');
        
        // Clean up particles
        const particles = element.querySelectorAll('.mining-particle');
        particles.forEach(particle => particle.remove());
    }
    
    addMiningAura(element, intensity) {
        // Different aura intensities based on mining level
        const intensityMap = {
            'CASUAL': 'rgba(0, 169, 165, 0.2)',
            'ACTIVE': 'rgba(0, 169, 165, 0.4)',
            'ELITE': 'rgba(144, 194, 231, 0.6)',
            'LEGENDARY': 'rgba(255, 215, 0, 0.8)'
        };
        
        const auraColor = intensityMap[intensity] || intensityMap['ELITE'];
        element.style.boxShadow = `inset 0 0 20px ${auraColor}`;
    }
    
    showHashDiscoveryEffect(element, proof) {
        if (!this.enabled) return;
        
        // Premium hash discovery animation
        element.style.animation = 'hash-discovery-premium 2s ease-out';
        
        // Create celebration particles
        this.createCelebrationParticles(element);
        
        // Clean up animation
        setTimeout(() => {
            element.style.animation = '';
        }, 2000);
    }
    
    createCelebrationParticles(element) {
        const rect = element.getBoundingClientRect();
        
        for (let i = 0; i < 12; i++) {
            const particle = document.createElement('div');
            particle.className = 'mining-particle';
            
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.top = `${Math.random() * 100}%`;
            particle.style.animationDelay = `${i * 0.1}s`;
            
            element.style.position = 'relative';
            element.appendChild(particle);
            
            // Remove after animation
            setTimeout(() => {
                if (particle.parentNode) {
                    particle.remove();
                }
            }, 3200);
        }
    }
    
    addMovementBoost(element, intensity) {
        if (!this.enabled) return;
        
        // Subtle boost effect for mouse movement
        const boostIntensity = Math.min(intensity / 20, 1);
        const currentShadow = element.style.boxShadow || '';
        
        element.style.boxShadow = `${currentShadow}, 0 0 ${10 + boostIntensity * 10}px rgba(144, 194, 231, ${0.3 + boostIntensity * 0.3})`;
        
        // Reset after brief moment
        setTimeout(() => {
            element.style.boxShadow = currentShadow;
        }, 200);
    }
}

// Initialize and export
window.EnhancedMouseoverMiner = EnhancedMouseoverMiner;

console.log('🎯 Enhanced Mouseover Mining System loaded');
console.log('💡 Keyboard shortcuts: Ctrl+M (toggle), Ctrl+I (intensity), Ctrl+V (visuals)');