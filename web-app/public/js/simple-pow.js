/**
 * Simple Proof-of-Work System
 * Real mining with mouseover functionality and bottom toolbar
 */

class SimpleProofOfWork {
    constructor() {
        console.log('🔨 Simple PoW: Initialized');
    }

    async sha256(text) {
        const encoder = new TextEncoder();
        const data = encoder.encode(text);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    async acquireProofFor(payload) {
        console.log('🔨 Simple PoW: Getting challenge for', payload);
        
        // Validate payload
        if (!payload.target_type || !payload.action || !payload.difficulty) {
            throw new Error('Invalid payload: missing required fields (target_type, action, difficulty)');
        }
        
        // 1. Get challenge from server
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                         document.querySelector('input[name="_token"]')?.value || '';
        
        if (!csrfToken) {
            console.warn('No CSRF token found, request may fail');
        }
        
        const challengeResponse = await fetch('/api/mining/challenges', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        if (!challengeResponse.ok) {
            const errorText = await challengeResponse.text();
            console.error('🔨 Simple PoW: Challenge request failed:', challengeResponse.status, errorText);
            throw new Error('Failed to get challenge: ' + challengeResponse.statusText);
        }

        const challenge = await challengeResponse.json();
        
        if (!challenge.success) {
            console.error('🔨 Simple PoW: Challenge response failed:', challenge);
            throw new Error('Challenge failed: ' + (challenge.message || 'Unknown error'));
        }

        console.log('🔨 Simple PoW: Challenge received', challenge);

        // 2. Mine proof
        const challengeData = JSON.stringify(challenge.canonical_payload).replace(/\\\//g, "/");
        console.log('🔨 Simple PoW: Starting mining with data:', challengeData);
        const proof = await this.mine(challengeData, payload.difficulty);
        
        console.log('🔨 Simple PoW: Proof found', proof);

        // 3. Return proof with challenge token
        return {
            nonce: proof.nonce,
            hash: proof.hash,
            challenge_id: challenge.token
        };
    }

    async mine(data, difficulty) {
        console.log('🔨 Simple PoW: Mining with difficulty', difficulty);
        
        let nonce = 0;
        const maxAttempts = 1000000; // Prevent infinite loops
        
        while (nonce < maxAttempts) {
            const testData = data + ':' + nonce;
            const hash = await this.sha256(testData);
            
            if (hash.toLowerCase().startsWith(difficulty.toLowerCase())) {
                console.log('🔨 Simple PoW: Found valid hash after', nonce, 'attempts');
                return { nonce, hash };
            }
            
            nonce++;
            
            // Update progress every 10000 hashes
            if (nonce % 10000 === 0) {
                console.log('🔨 Simple PoW: Progress -', nonce, 'hashes attempted');
                // Allow UI to update
                await new Promise(resolve => setTimeout(resolve, 1));
            }
        }
        
        throw new Error('Mining failed: Max attempts reached');
    }
}

// SimpleMouseoverMiner class for seamless background mining
class SimpleMouseoverMiner {
    constructor(pow) {
        this.pow = pow;
        this.currentTarget = null;
        this.enabled = true;
        this.currentDifficulty = '21e8';
        this.stats = { proofs: 0, points: 0, hashes: 0 };
        this.setupMouseoverEvents();
        console.log('🖱️ Mouseover mining: Initialized');
        console.log('🔍 Looking for elements with data-mine-type attribute...');
        
        // Debug: Log mineable elements on page
        setTimeout(() => {
            const mineableElements = document.querySelectorAll('[data-mine-type]');
            console.log(`📊 Found ${mineableElements.length} mineable elements on page`);
            mineableElements.forEach(el => {
                console.log(`  - ${el.dataset.mineType}: ${el.dataset.threadId || el.dataset.postId || 'no-id'} (${el.tagName})`);
            });
            
            // Also check for specific element types
            const threadElements = document.querySelectorAll('[data-mine-type="thread"]');
            const postElements = document.querySelectorAll('[data-mine-type="post"]');
            const imageElements = document.querySelectorAll('[data-mine-type="image"]');
            
            console.log(`  💡 Breakdown: ${threadElements.length} threads, ${postElements.length} posts, ${imageElements.length} images`);
        }, 2000);
    }


    setupMouseoverEvents() {
        document.addEventListener('mouseover', (e) => {
            if (!this.enabled) return;
            
            const target = e.target.closest('[data-mine-type]');
            if (target && target !== this.currentTarget) {
                this.startMiningWithFeedback(target);
            }
        });

        document.addEventListener('mouseout', (e) => {
            const target = e.target.closest('[data-mine-type]');
            if (target === this.currentTarget) {
                this.stopMiningWithFeedback(target);
            }
        });
    }

    startMiningWithFeedback(target) {
        // Add immediate visual feedback
        target.classList.add('mouseover-mining');
        
        // Add mining cursor effect
        target.style.cursor = 'url(\'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="36" viewBox="0 0 32 36" fill="none"><path d="M4 4l24 12-12 6-6 12-6-30z" fill="%2300A9A5" stroke="%2390C2E7" stroke-width="2"/><circle cx="16" cy="18" r="3" fill="%2390C2E7"/></svg>\'), crosshair';
        
        // Create mining status indicator
        this.showMiningStatusIndicator(target, 'active');
        
        // Start actual mining
        this.startMining(target);
    }

    stopMiningWithFeedback(target) {
        // Remove visual feedback
        target.classList.remove('mouseover-mining');
        target.style.cursor = '';
        
        // Update status indicator
        this.showMiningStatusIndicator(target, 'idle');
        
        // Stop actual mining
        this.stopMining();
        
        // Clean up status indicator after delay
        setTimeout(() => {
            const indicator = target.querySelector('.mining-status-indicator');
            if (indicator) {
                indicator.remove();
            }
        }, 2000);
    }

    showMiningStatusIndicator(target, status) {
        // Remove existing indicator
        const existingIndicator = target.querySelector('.mining-status-indicator');
        if (existingIndicator) {
            existingIndicator.remove();
        }
        
        const indicator = document.createElement('div');
        indicator.className = `mining-status-indicator mining-status-${status}`;
        
        let content = '';
        switch (status) {
            case 'active':
                content = '<div class="mining-loader"></div><span>MINING</span>';
                break;
            case 'success':
                content = '<span>💎</span><span>HASH FOUND</span>';
                break;
            case 'idle':
            default:
                content = '<span>⚡</span><span>READY</span>';
                break;
        }
        
        indicator.innerHTML = content;
        indicator.style.cssText = `
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            white-space: nowrap;
            pointer-events: none;
        `;
        
        target.style.position = target.style.position || 'relative';
        target.appendChild(indicator);
        
        return indicator;
    }

    async startMining(element) {
        if (!this.enabled) return;
        
        this.currentTarget = element;
        
        const mineType = element.dataset.mineType;
        const threadId = element.dataset.threadId;
        const postId = element.dataset.postId;
        const boardCode = element.dataset.boardCode || 'd';

        try {
            let targetType, targetId;
            
            if (mineType === 'thread' || mineType === 'thread-op') {
                targetType = 'thread';
                targetId = threadId;
            } else if (mineType === 'post') {
                targetType = 'post';  
                targetId = postId;
            } else {
                targetType = 'general';
                targetId = null;
            }

            // Use current difficulty from toolbar
            const proof = await this.pow.acquireProofFor({
                board_code: boardCode,
                target_type: targetType,
                target_id: targetId,
                action: 'mine',
                difficulty: this.currentDifficulty
            });

            if (proof) {
                // Show success status first
                this.showMiningStatusIndicator(element, 'success');
                
                // Then show the hash discovery effect
                this.showSubtleEffect(element);
                
                // Submit proof
                await this.submitRealProof(proof, targetType, targetId, boardCode, this.currentDifficulty);
                
                // Update stats with animations
                const oldStats = { ...this.stats };
                this.stats.proofs++;
                this.stats.points += this.calculatePoints(this.currentDifficulty);
                this.stats.hashes += parseInt(proof.nonce) || 1;
                
                // Animate stat changes
                this.animateStatChanges(oldStats, this.stats);
                
                if (window.miningToolbar) {
                    window.miningToolbar.updateStats(this.stats.proofs, this.stats.points, this.stats.hashes);
                }
                
                // Update mining dashboard activity
                if (window.MiningDashboard) {
                    const isLegendary = this.currentDifficulty === '21e8';
                    const icon = isLegendary ? '💎' : '⚡';
                    const description = `${isLegendary ? 'Legendary' : 'Regular'} hash discovered (${this.currentDifficulty})`;
                    window.MiningDashboard.addActivity(icon, description);
                }
                
                // Show achievement notification for legendary hashes
                if (this.currentDifficulty === '21e8') {
                    this.showAchievementNotification('Legendary Hash Discovered!', proof.hash);
                }
            }
        } catch (error) {
            console.log('Mining failed silently:', error);
        }
    }

    calculatePoints(difficulty) {
        const points = {
            '5': 0.01, '4': 0.02, '3': 0.05, '2': 0.1,
            '21': 0.1, '21e': 0.5, '21e8': 100
        };
        return points[difficulty] || 0.1;
    }

    async submitRealProof(proof, targetType, targetId, boardCode, difficulty) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                             document.querySelector('input[name="_token"]')?.value || '';
            
            const response = await fetch('/api/proof-submissions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    challenge_token: proof.challenge_id,
                    client_nonce: proof.nonce,
                    hash: proof.hash
                })
            });
            
            if (response.ok) {
                console.log('✅ Proof submitted successfully');
            }
        } catch (error) {
            console.log('Failed to submit proof:', error);
        }
    }

    showSubtleEffect(element) {
        // Determine if this is a 21e8 proof (legendary) or regular proof
        const isLegendary = this.currentDifficulty === '21e8';
        
        if (isLegendary) {
            this.showLegendaryHashEffect(element);
        } else {
            this.showRegularHashEffect(element);
        }
        
        // Create particle effect
        this.createParticleEffect(element);
        
        // Show floating indicator with appropriate styling
        this.showFloatingIndicator(element, isLegendary);
    }

    showLegendaryHashEffect(element) {
        // Store original styles
        const originalClass = element.className;
        
        // Apply legendary hash discovery animation
        element.classList.add('hash-discovery-legendary');
        
        // Create ripple effect container
        const rippleContainer = document.createElement('div');
        rippleContainer.className = 'hash-ripple-container';
        rippleContainer.style.cssText = `
            position: absolute;
            inset: -20px;
            pointer-events: none;
            z-index: 1000;
            border-radius: inherit;
        `;
        
        // Add multiple ripple rings
        for (let i = 0; i < 3; i++) {
            const ripple = document.createElement('div');
            ripple.style.cssText = `
                position: absolute;
                inset: ${i * 5}px;
                border: 2px solid rgba(0, 169, 165, ${0.6 - i * 0.15});
                border-radius: inherit;
                animation: hashSuccessRipple ${1.5 + i * 0.3}s ease-out forwards;
                animation-delay: ${i * 0.1}s;
            `;
            rippleContainer.appendChild(ripple);
        }
        
        element.style.position = element.style.position || 'relative';
        element.appendChild(rippleContainer);
        
        // Clean up after animation
        setTimeout(() => {
            element.classList.remove('hash-discovery-legendary');
            if (rippleContainer.parentNode) {
                rippleContainer.remove();
            }
        }, 3000);
    }

    showRegularHashEffect(element) {
        const originalClass = element.className;
        
        // Apply regular hash discovery animation
        element.classList.add('hash-discovery');
        
        // Add subtle glow effect
        const originalBoxShadow = element.style.boxShadow;
        element.style.boxShadow = `
            ${originalBoxShadow}, 
            0 0 20px rgba(0, 169, 165, 0.4),
            inset 0 0 10px rgba(144, 194, 231, 0.2)
        `;
        
        // Clean up after animation
        setTimeout(() => {
            element.classList.remove('hash-discovery');
            element.style.boxShadow = originalBoxShadow;
        }, 1500);
    }

    createParticleEffect(element) {
        const particleContainer = document.createElement('div');
        particleContainer.className = 'mining-particles';
        
        // Create 6 particles for a rich effect
        for (let i = 0; i < 6; i++) {
            const particle = document.createElement('div');
            particle.className = 'mining-particle';
            
            // Random positioning within the element
            const rect = element.getBoundingClientRect();
            const randomX = Math.random() * 100;
            const randomY = Math.random() * 100;
            
            particle.style.left = `${randomX}%`;
            particle.style.top = `${randomY}%`;
            particle.style.animationDelay = `${i * 0.1}s`;
            
            particleContainer.appendChild(particle);
        }
        
        element.style.position = element.style.position || 'relative';
        element.appendChild(particleContainer);
        
        // Remove particles after animation
        setTimeout(() => {
            if (particleContainer.parentNode) {
                particleContainer.remove();
            }
        }, 3000);
    }

    showFloatingIndicator(element, isLegendary) {
        const indicator = document.createElement('div');
        
        if (isLegendary) {
            indicator.innerHTML = '💎⚡';
            indicator.style.cssText = `
                position: absolute;
                top: -15px;
                right: -5px;
                font-size: 16px;
                pointer-events: none;
                animation: legendaryIndicatorFloat 2s ease-out forwards;
                z-index: 1001;
                text-shadow: 0 0 10px rgba(0, 169, 165, 0.8);
                filter: drop-shadow(0 0 5px rgba(144, 194, 231, 0.6));
            `;
        } else {
            indicator.innerHTML = '⚡';
            indicator.style.cssText = `
                position: absolute;
                top: -10px;
                right: 5px;
                font-size: 12px;
                color: var(--accent-primary);
                pointer-events: none;
                animation: regularIndicatorFloat 1.5s ease-out forwards;
                z-index: 1001;
                text-shadow: 0 0 5px rgba(0, 169, 165, 0.5);
            `;
        }
        
        // Add indicator animations if they don't exist
        this.ensureIndicatorAnimations();
        
        element.style.position = element.style.position || 'relative';
        element.appendChild(indicator);
        
        // Remove indicator after animation
        setTimeout(() => {
            if (indicator.parentNode) {
                indicator.remove();
            }
        }, isLegendary ? 2500 : 2000);
    }

    ensureIndicatorAnimations() {
        if (!document.getElementById('indicator-animations')) {
            const style = document.createElement('style');
            style.id = 'indicator-animations';
            style.textContent = `
                @keyframes legendaryIndicatorFloat {
                    0% { 
                        opacity: 0; 
                        transform: translateY(10px) scale(0.5) rotate(0deg); 
                    }
                    20% { 
                        opacity: 1; 
                        transform: translateY(-5px) scale(1.2) rotate(45deg); 
                    }
                    60% { 
                        transform: translateY(-20px) scale(1) rotate(180deg); 
                    }
                    100% { 
                        opacity: 0; 
                        transform: translateY(-40px) scale(0.8) rotate(360deg); 
                    }
                }
                
                @keyframes regularIndicatorFloat {
                    0% { 
                        opacity: 0; 
                        transform: translateY(5px) scale(0.8); 
                    }
                    30% { 
                        opacity: 1; 
                        transform: translateY(-5px) scale(1.1); 
                    }
                    100% { 
                        opacity: 0; 
                        transform: translateY(-25px) scale(0.9); 
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    animateStatChanges(oldStats, newStats) {
        // Find stat display elements and animate them
        const statElements = document.querySelectorAll('.mining-stat-value, .mining-metric-value');
        
        statElements.forEach(element => {
            const statType = element.dataset.stat || element.className;
            let oldValue, newValue;
            
            if (statType.includes('proofs') || statType.includes('proof')) {
                oldValue = oldStats.proofs;
                newValue = newStats.proofs;
            } else if (statType.includes('points') || statType.includes('point')) {
                oldValue = oldStats.points;
                newValue = newStats.points;
            } else if (statType.includes('hashes') || statType.includes('hash')) {
                oldValue = oldStats.hashes;
                newValue = newStats.hashes;
            }
            
            if (oldValue !== undefined && newValue !== oldValue) {
                this.animateNumberChange(element, oldValue, newValue);
            }
        });
    }

    animateNumberChange(element, fromValue, toValue) {
        element.classList.add('updating');
        
        const duration = 800;
        const startTime = performance.now();
        const isInteger = Number.isInteger(fromValue) && Number.isInteger(toValue);
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Use easing function for smooth animation
            const easeProgress = 1 - Math.pow(1 - progress, 3);
            
            const currentValue = fromValue + (toValue - fromValue) * easeProgress;
            
            if (isInteger) {
                element.textContent = Math.round(currentValue).toLocaleString();
            } else {
                element.textContent = currentValue.toFixed(1);
            }
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                element.classList.remove('updating');
                // Final value to ensure accuracy
                element.textContent = isInteger ? toValue.toLocaleString() : toValue.toFixed(1);
            }
        };
        
        requestAnimationFrame(animate);
    }

    showAchievementNotification(title, hash) {
        const notification = document.createElement('div');
        notification.className = 'achievement-notification';
        notification.innerHTML = `
            <div class="achievement-icon">💎</div>
            <div class="achievement-content">
                <div class="achievement-title">${title}</div>
                <div class="achievement-hash">${hash.substring(0, 16)}...</div>
            </div>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: var(--bg-primary);
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-floating);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: var(--space-md);
            max-width: 350px;
            opacity: 0;
            transform: translateX(100%);
            transition: all var(--transition-smooth);
        `;
        
        // Add notification styles if they don't exist
        this.ensureNotificationStyles();
        
        document.body.appendChild(notification);
        
        // Animate in
        requestAnimationFrame(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        });
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }, 5000);
    }

    ensureNotificationStyles() {
        if (!document.getElementById('achievement-notification-styles')) {
            const style = document.createElement('style');
            style.id = 'achievement-notification-styles';
            style.textContent = `
                .achievement-notification {
                    font-family: var(--font-secondary);
                }
                
                .achievement-icon {
                    font-size: var(--font-size-xl);
                    animation: achievementPulse 2s ease-in-out infinite;
                }
                
                .achievement-title {
                    font-size: var(--font-size-md);
                    font-weight: 600;
                    margin-bottom: var(--space-xs);
                }
                
                .achievement-hash {
                    font-size: var(--font-size-xs);
                    font-family: var(--font-primary);
                    opacity: 0.9;
                }
                
                @keyframes achievementPulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                }
            `;
            document.head.appendChild(style);
        }
    }

    stopMining() {
        this.currentTarget = null;
    }
}

// Mining toolbar class
class MiningToolbar {
    constructor(miner) {
        this.miner = miner;
        this.power = 5; // 1-10 scale
        this.stats = { proofs: 0, points: 0, hashes: 0 };
        // DISABLED: Toolbar removed as vestigial component
        // this.createToolbar();
        this.updateMiningDifficulty();
    }

    createToolbar() {
        // DEPRECATED: Toolbar functionality removed
        console.log('ℹ️ Mining toolbar disabled (vestigial component)');
        return;
        
        /* DEPRECATED CODE BELOW - DO NOT RE-ENABLE
        // Remove any existing toolbar first
        const existingToolbar = document.getElementById('mining-toolbar');
        if (existingToolbar) {
            existingToolbar.remove();
        }
        
        const toolbar = document.createElement('div');
        toolbar.id = 'mining-toolbar';
        toolbar.style.cssText = `
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            height: 60px !important;
            background: linear-gradient(135deg, #708B75, #5a7860) !important;
            color: #F5F5DC !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 0 20px !important;
            font-family: monospace !important;
            font-size: 12px !important;
            z-index: 99999 !important;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.3) !important;
            border-top: 2px solid #9AB87A !important;
        `;

        toolbar.innerHTML = `
            <div style="display: flex; align-items: center; gap: 15px;">
                <span>⚡ Mining Difficulty: <strong>21e8</strong></span>
                <span id="power-value">Power: ${this.power}/10</span>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <span id="mining-stats">Proofs: ${this.stats.proofs} | Points: ${this.stats.points} | Hashes: ${this.stats.hashes}</span>
                <button id="toggle-mining" style="padding: 5px 10px; background: #4CAF50; border: none; border-radius: 3px; color: white; cursor: pointer;">ON</button>
            </div>
        `;

        document.body.appendChild(toolbar);
        
        // Add bottom padding to body to prevent toolbar from covering content
        document.body.style.paddingBottom = '80px';
        
        this.setupToolbarEvents();
        console.log('🎯 Mining toolbar created and added to DOM');
    }

    setupToolbarEvents() {
        const powerValue = document.getElementById('power-value');
        const toggleBtn = document.getElementById('toggle-mining');

        toggleBtn.addEventListener('click', () => {
            if (this.miner.enabled) {
                this.miner.enabled = false;
                toggleBtn.textContent = 'OFF';
                toggleBtn.style.background = '#f44336';
            } else {
                this.miner.enabled = true;
                toggleBtn.textContent = 'ON';
                toggleBtn.style.background = '#4CAF50';
            }
        });
    }

    updateMiningDifficulty() {
        // All mining uses 21e8 for now
        this.miner.currentDifficulty = '21e8';
    }

    updateStats(proofs, points, hashes) {
        this.stats = { proofs, points, hashes };
        const statsEl = document.getElementById('mining-stats');
        if (statsEl) {
            statsEl.textContent = `Proofs: ${proofs} | Points: ${points.toFixed(1)} | Hashes: ${hashes}`;
        }
    }
}

// Reply Form PoW Handler - auto-initialize
class ReplyFormMiner {
    constructor(pow) {
        this.pow = pow;
        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        const replyForm = document.querySelector('.unified-post-form');
        if (!replyForm) {
            console.log('No reply form found');
            return;
        }

        const contentInput = document.getElementById('post-content');
        const submitBtn = document.getElementById('reply-submit-btn');
        const miningStatus = document.getElementById('reply-mining-status');

        if (!contentInput || !submitBtn || !miningStatus) {
            console.log('Reply form elements missing');
            return;
        }

        console.log('🔨 Reply form mining: Setting up');

        let miningTimeout;
        let hasProof = false;

        // Start mining when content is filled
        contentInput.addEventListener('input', () => {
            clearTimeout(miningTimeout);
            const content = contentInput.value.trim();
            
            if (content.length >= 5 && !hasProof) {
                miningTimeout = setTimeout(() => this.startMining(replyForm, miningStatus), 1500);
            }
        });

        // Form submission handler
        replyForm.addEventListener('submit', async (e) => {
            const currentHash = replyForm.querySelector('input[name="pow_hash"]').value.trim();
            const content = contentInput.value.trim();
            
            if (content.length >= 5 && !currentHash) {
                e.preventDefault();
                miningStatus.innerHTML = '<span style="color: #ffc107;">⛏️ Mining required before submission...</span>';
                
                try {
                    await this.startMining(replyForm, miningStatus);
                    const newHash = replyForm.querySelector('input[name="pow_hash"]').value.trim();
                    
                    if (newHash) {
                        submitBtn.textContent = '⏳ Posting...';
                        submitBtn.disabled = true;
                        miningStatus.innerHTML = '<span style="color: #28a745;">✅ Submitting...</span>';
                        replyForm.submit();
                    } else {
                        throw new Error('Mining failed to produce hash');
                    }
                } catch (error) {
                    console.error('Mining error:', error);
                    miningStatus.innerHTML = '<span style="color: #dc3545;">❌ Mining failed: ' + error.message + '</span>';
                }
            }
        });
    }

    async startMining(form, statusElement) {
        const contentInput = document.getElementById('post-content');
        const content = contentInput.value.trim();
        
        if (content.length < 5) {
            statusElement.innerHTML = 'Content too short for mining';
            return;
        }

        // Create sophisticated loading animation
        statusElement.innerHTML = `
            <span style="color: var(--text-mining); display: flex; align-items: center; gap: var(--space-sm);">
                <div class="mining-loader"></div>
                <span>Initializing quantum mining...</span>
            </span>
        `;

        // Add progress animation
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress < 90) {
                statusElement.innerHTML = `
                    <span style="color: var(--text-mining); display: flex; align-items: center; gap: var(--space-sm);">
                        <div class="mining-loader"></div>
                        <span>Mining... ${Math.round(progress)}%</span>
                    </span>
                `;
            }
        }, 200);

        try {
            // Get board and thread from URL
            const boardMatch = window.location.pathname.match(/\/(\w+)\/(\d+)$/);
            if (!boardMatch) {
                throw new Error('Cannot determine board and thread');
            }
            
            const [, boardCode, threadId] = boardMatch;
            
            const proof = await this.pow.acquireProofFor({
                board_code: boardCode,
                target_type: 'reply',
                target_id: threadId,
                action: 'create',
                difficulty: '21e8'
            });

            // Clear progress interval
            clearInterval(progressInterval);

            // Fill form fields
            form.querySelector('input[name="pow_nonce"]').value = proof.nonce.toString();
            form.querySelector('input[name="pow_hash"]').value = proof.hash;
            form.querySelector('input[name="pow_challenge_id"]').value = proof.challenge_id;
            
            // Show success with animation
            statusElement.innerHTML = `
                <span style="color: var(--text-accent); display: flex; align-items: center; gap: var(--space-sm);">
                    <span class="hash-discovery">💎</span>
                    <span>Quantum hash discovered! Ready to submit.</span>
                </span>
            `;
            
            // Enable submit button with enhanced styling
            const submitBtn = document.getElementById('reply-submit-btn');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.textContent = '⚡ Post Reply';
                submitBtn.classList.add('hash-discovery');
                
                // Remove animation class after effect
                setTimeout(() => {
                    submitBtn.classList.remove('hash-discovery');
                }, 1000);
            }
            
            return proof;

        } catch (error) {
            // Clear progress interval on error
            clearInterval(progressInterval);
            
            console.error('Mining error:', error);
            console.log('Mining context:', {
                boardCode,
                threadId,
                url: window.location.pathname,
                content: content.substring(0, 100) + '...'
            });
            
            let errorMsg = error.message;
            if (error.message.includes('Challenge failed')) {
                errorMsg = 'Server challenge failed - please try again';
            } else if (error.message.includes('fetch')) {
                errorMsg = 'Network error - check connection';
            } else if (error.message.includes('Cannot determine')) {
                errorMsg = 'Page URL format error - refresh page';
            }
            
            statusElement.innerHTML = `
                <span style="color: var(--text-warning); display: flex; align-items: center; gap: var(--space-sm);">
                    <span>⚠️</span>
                    <span>Mining error: ${errorMsg}</span>
                </span>
            `;
            
            // Auto-retry once after 2 seconds for certain errors
            if (error.message.includes('Challenge failed') || error.message.includes('Network')) {
                statusElement.innerHTML += `
                    <br><span style="color: var(--text-muted); font-size: var(--font-size-xs);">
                        Auto-retrying in 2 seconds...
                    </span>
                `;
                
                setTimeout(() => {
                    console.log('Auto-retrying mining after error...');
                    this.startMining(form, statusElement);
                }, 2000);
            }
            
            throw error;
        }
    }
}

// Initialize immediately
console.log('🔨 Simple PoW: Creating instance...');
window.simplePoW = new SimpleProofOfWork();

// Initialize reply form mining
window.replyFormMiner = new ReplyFormMiner(window.simplePoW);

// Initialize everything when DOM is ready for mouseover mining
// Only create toolbar if we're not on the mining page
if (!window.location.pathname.includes('/mining')) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.mouseoverMiner = new SimpleMouseoverMiner(window.simplePoW);
            window.miningToolbar = new MiningToolbar(window.mouseoverMiner);
            
            // Dispatch ready event for toolbar
            if (typeof window.CustomEvent === 'function') {
                window.dispatchEvent(new CustomEvent('mouseoverMinerReady', { detail: window.mouseoverMiner }));
            }
        });
    } else {
        window.mouseoverMiner = new SimpleMouseoverMiner(window.simplePoW);
        window.miningToolbar = new MiningToolbar(window.mouseoverMiner);
        
        // Dispatch ready event for toolbar immediately
        setTimeout(() => {
            if (typeof window.CustomEvent === 'function') {
                window.dispatchEvent(new CustomEvent('mouseoverMinerReady', { detail: window.mouseoverMiner }));
            }
        }, 100);
    }
}

console.log('🔨 Simple PoW: Ready with reply form mining, mouseover mining and toolbar');