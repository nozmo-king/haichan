/**
 * Simple Proof-of-Work System
 * Real mining with mouseover functionality and bottom toolbar
 */

class SimpleProofOfWork {
    constructor() {
        console.log('🔨 Simple PoW: Initialized');
        
        // Mining state tracking
        this.isMining = false;
        this.currentHashrate = 0;
        this.totalHashes = 0;
        this.startTime = null;
        this.miningStats = {
            totalProofs: 0,
            sessionProofs: 0,
            sessionStartTime: Date.now()
        };
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
                    // For 'pow_params' target_type, action and difficulty are not required in the initial payload
                    if (!payload.target_type || 
                        (payload.target_type !== 'pow_params' && (!payload.action || !payload.difficulty))) {
                        throw new Error('Invalid payload: missing required fields (target_type, action, difficulty)');
                    }        
        // 1. Get challenge from server
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                         document.querySelector('input[name="_token"]')?.value || '';
        
        if (!csrfToken) {
            console.warn('No CSRF token found, request may fail');
        }
        
        // Use the actual working endpoint
        let endpoint = '/api/mining/challenges';
        let method = 'POST';
        let body = {
            board_code: payload.board_code || null,
            target_type: payload.target_type,
            target_id: payload.target_id ? String(payload.target_id) : null,
            action: payload.action || 'create',
            difficulty: payload.difficulty
        };

        console.log('🔨 Simple PoW: Requesting challenge from', endpoint, 'with payload', payload);
        
        if (!csrfToken && method === 'POST') {
            console.warn('No CSRF token found, POST request may fail');
        }
        
        const fetchOptions = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        };

        if (method === 'POST') {
            fetchOptions.body = JSON.stringify(body);
        }

        const challengeResponse = await fetch(endpoint, fetchOptions);

        if (!challengeResponse.ok) {
            let errorText;
            try {
                errorText = await challengeResponse.text();
            } catch (e) {
                errorText = 'Network error';
            }
            
            console.error('🔨 Simple PoW: Challenge request failed:', challengeResponse.status, errorText);
            
            // Provide more specific error messages
            let userFriendlyError = 'Failed to get challenge';
            if (challengeResponse.status === 401) {
                userFriendlyError = 'Authentication required - please log in';
            } else if (challengeResponse.status === 403) {
                userFriendlyError = 'Access forbidden - check permissions';
            } else if (challengeResponse.status === 422) {
                userFriendlyError = 'Invalid request data';
            } else if (challengeResponse.status >= 500) {
                userFriendlyError = 'Server error - please try again';
            }
            
            throw new Error(userFriendlyError + ': ' + challengeResponse.statusText);
        }

        const challenge = await challengeResponse.json();
        
        // If requesting PoW parameters, return the challenge directly without 'success' validation
        if (payload.target_type === 'pow_params') {
            console.log('🔨 Simple PoW: PoW parameters received', challenge);
            return challenge;
        }

        if (!challenge.success) {
            console.error('🔨 Simple PoW: Challenge response failed:', challenge);
            throw new Error('Failed to get challenge: ' + (challenge.message || 'Unknown error'));
        }

        console.log('🔨 Simple PoW: Challenge received', challenge);

        // 2. Mine proof
        const challengeData = JSON.stringify(challenge.canonical_payload).replace(/\\\//g, "/");
        console.log('🔨 Simple PoW: Starting mining with data:', challengeData);
        const proof = await this.mine(challengeData, payload.difficulty);
        
        console.log('🔨 Simple PoW: Proof found', proof);

        // 3. Return proof with challenge token (called 'token' in response, not 'challenge_id')
        return {
            nonce: proof.nonce,
            hash: proof.hash,
            challenge_id: challenge.token,  // Backend returns 'token' field
            op_id: challenge.op_id || null   // May not be present
        };
    }

    async mine(data, difficulty) {
        console.log('🔨 Simple PoW: Mining with difficulty', difficulty);
        
        // Start mining state tracking
        this.isMining = true;
        this.startTime = Date.now();
        this.totalHashes = 0;
        
        let nonce = 0;
        const maxAttempts = 100000000; // Increased for higher difficulty proofs like 21e8
        
        while (nonce < maxAttempts) {
            const testData = data + ':' + nonce;
            const hash = await this.sha256(testData);
            
            if (hash.toLowerCase().startsWith(difficulty.toLowerCase())) {
                console.log('🔨 Simple PoW: Found valid hash after', nonce, 'attempts');
                
                // End mining state tracking
                this.isMining = false;
                this.miningStats.totalProofs++;
                this.miningStats.sessionProofs++;
                
                // Notify toolbar of completed mining
                this.notifyMiningComplete();
                
                return { nonce, hash };
            }
            
            nonce++;
            this.totalHashes = nonce;
            
            // Update progress every 10000 hashes
            if (nonce % 10000 === 0) {
                // Calculate current hashrate
                const elapsed = (Date.now() - this.startTime) / 1000;
                this.currentHashrate = Math.round(nonce / elapsed);
                
                console.log('🔨 Simple PoW: Progress -', nonce, 'hashes attempted,', this.currentHashrate, 'H/s');
                
                // Notify toolbar of mining progress
                this.notifyMiningProgress();
                
                // Allow UI to update
                await new Promise(resolve => setTimeout(resolve, 1));
            }
        }
        
        // Mining failed - reset state
        this.isMining = false;
        this.currentHashrate = 0;
        this.notifyMiningComplete();
        
        throw new Error('Mining failed: Max attempts reached');
    }
    
    // Notify toolbar of mining progress
    notifyMiningProgress() {
        if (window.HaichanState) {
            window.HaichanState.setState('mining.isActive', this.isMining);
            window.HaichanState.setState('mining.hashrate', this.currentHashrate);
            window.HaichanState.setState('mining.totalHashes', this.totalHashes);
        }
        
        // Also dispatch custom event for toolbar
        window.dispatchEvent(new CustomEvent('mining:progress', {
            detail: {
                isActive: this.isMining,
                hashrate: this.currentHashrate,
                totalHashes: this.totalHashes,
                sessionProofs: this.miningStats.sessionProofs
            }
        }));
    }
    
    // Notify toolbar when mining completes
    notifyMiningComplete() {
        if (window.HaichanState) {
            window.HaichanState.setState('mining.isActive', false);
            window.HaichanState.setState('mining.hashrate', 0);
            window.HaichanState.setState('mining.stats.totalProofs', this.miningStats.totalProofs);
            window.HaichanState.setState('mining.stats.sessionProofs', this.miningStats.sessionProofs);
        }
        
        // Dispatch completion event
        window.dispatchEvent(new CustomEvent('mining:complete', {
            detail: {
                totalProofs: this.miningStats.totalProofs,
                sessionProofs: this.miningStats.sessionProofs,
                lastHashrate: this.currentHashrate
            }
        }));
        
        // Reset current hashrate after completion
        this.currentHashrate = 0;
    }
}

// SimpleMouseoverMiner class for seamless background mining
class SimpleMouseoverMiner {
    constructor(pow) {
        this.pow = pow;
        this.currentTarget = null;
        this.enabled = true;
        this.currentDifficulty = '21';
        this.stats = { proofs: 0, points: 0, hashes: 0 };
        this.setupInteractionEvents(); // Updated to handle both mouse and touch
        console.log('🖱️ Interaction mining: Initialized');
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


    setupInteractionEvents() {
        // Mouse events
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

        // Touch events for mobile compatibility
        document.addEventListener('touchstart', (e) => {
            if (!this.enabled) return;
            
            const target = e.target.closest('[data-mine-type]');
            if (target && target !== this.currentTarget) {
                this.startMiningWithFeedback(target);
            }
        }, { passive: true }); // Use passive to improve scrolling performance

        document.addEventListener('touchend', (e) => {
            const target = e.target.closest('[data-mine-type]');
            // Check if the touch ended on the current target
            if (target === this.currentTarget) {
                this.stopMiningWithFeedback(target);
            }
        });

        // Optional: Track touch movement for more continuous activity
        let lastTouchX = 0;
        let lastTouchY = 0;
        document.addEventListener('touchmove', (e) => {
            if (!this.enabled || !this.currentTarget) return;
            
            const touch = e.touches[0];
            if (touch) {
                const deltaX = Math.abs(touch.clientX - lastTouchX);
                const deltaY = Math.abs(touch.clientY - lastTouchY);
                
                // Consider a touchmove significant if it moves more than a few pixels
                if (deltaX > 5 || deltaY > 5) {
                    // Increment mouse_movement_count or similar metric
                    // For now, just log to indicate activity
                    // console.log('Touch movement detected on current target');
                }
                lastTouchX = touch.clientX;
                lastTouchY = touch.clientY;
            }
        }, { passive: true });
    }

    startMiningWithFeedback(target) {
        // Add immediate visual feedback
        if (target && target.classList) {
            target.classList.add('mouseover-mining');
            
            // Add mining cursor effect
            target.style.cursor = 'url(\'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="36" viewBox="0 0 32 36" fill="none"><path d="M4 4l24 12-12 6-6 12-6-30z" fill="%2300A9A5" stroke="%2390C2E7" stroke-width="2"/><circle cx="16" cy="18" r="3" fill="%2390C2E7"/></svg>\'), crosshair';
        }
        
        // Create mining status indicator
        this.showMiningStatusIndicator(target, 'active');
        
        // Start actual mining
        this.startMining(target);
    }

    stopMiningWithFeedback(target) {
        // Remove visual feedback
        if (target && target.classList) {
            target.classList.remove('mouseover-mining');
            target.style.cursor = '';
        }
        
        // Update status indicator
        if (target) {
            this.showMiningStatusIndicator(target, 'idle');
        }
        
        // Stop actual mining
        this.stopMining();
        
        // Clean up status indicator after delay
        if (target) {
            setTimeout(() => {
                const indicator = target.querySelector('.mining-status-indicator');
                if (indicator) {
                    indicator.remove();
                }
            }, 2000);
        }
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
            case 'error':
                content = '<span>❌</span><span>LOGIN REQUIRED</span>';
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

        // DOODLE BOARD SPECIAL HANDLING - NO TEXT MINING
        if (boardCode === 'ddl') {
            console.log('🎨 Doodle board detected - text-based mining disabled');
            this.showMiningStatusIndicator(element, 'error');
            this.showMiningActivity('idle', 'Doodle board requires visual canvas mining only');
            return;
        }

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

            // Use fixed difficulty for mouseover mining (simpler and works)
            const difficulty = '21e8';  // Server minimum difficulty for mine actions

            const proof = await this.pow.acquireProofFor({
                board_code: boardCode,
                target_type: targetType,
                target_id: targetId,
                action: 'mine',
                difficulty: difficulty
            });
            
            this.currentDifficulty = difficulty;

            if (proof) {
                // Show success status first
                this.showMiningStatusIndicator(element, 'success');
                
                // Then show the hash discovery effect
                this.showSubtleEffect(element);
                
                // Submit proof
                await this.submitRealProof(proof, targetType, targetId, boardCode, this.currentDifficulty, {}, localStorage.getItem('user_pubkey'));
                
                // Update stats with animations
                const oldStats = { ...this.stats };
                this.stats.proofs++;
                this.stats.points += this.calculatePoints(this.currentDifficulty);
                this.stats.hashes += parseInt(proof.nonce) || 1;
                
                // Animate stat changes
                this.animateStatChanges(oldStats, this.stats);
                

                
                // Update mining dashboard activity
                if (window.MiningDashboard) {
                    const isLegendary = this.currentDifficulty === '21e8'; // Keep for future legendary status
                    const icon = isLegendary ? '💎' : '⚡';
                    const description = `${isLegendary ? 'Legendary' : 'Regular'} hash discovered (${this.currentDifficulty})`;
                    window.MiningDashboard.addActivity(icon, description);
                }
                
                // Show achievement notification for legendary hashes
                if (this.currentDifficulty === '21e8') { // Keep for future legendary bonus
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

    async submitRealProof(proof, targetType, targetId, boardCode, difficulty, postDraft, userPubkeyHex) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                             document.querySelector('input[name="_token"]')?.value || '';
            
            const endpoint = '/api/mining/submit-proof';
            const body = {
                challenge_token: proof.challenge_id,
                client_nonce: proof.nonce,
                hash: proof.hash,
            };

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(body)
            });
            
            if (response.ok) {
                const data = await response.json();
                console.log('✅ Proof submitted successfully', data);
                
                // Dispatch event with full response data
                if (data.success) {
                    document.dispatchEvent(new CustomEvent('proofSubmitted', {
                        detail: {
                            points: data.points,
                            total_points: data.total_points,
                            hash: data.hash,
                            pattern: data.pattern,
                            user_level: data.user_level
                        }
                    }));
                }
            } else {
                const errorData = await response.json();
                document.dispatchEvent(new CustomEvent('miningError', {
                    detail: { message: errorData.message || 'Failed to submit proof' }
                }));
            }
        } catch (error) {
            console.log('Failed to submit proof:', error);
            document.dispatchEvent(new CustomEvent('miningError', {
                detail: { message: error.message || 'Network error' }
            }));
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

    generateFallbackPubkey() {
        // NO DUMMY KEYS ALLOWED - Generate real cryptographic key
        const privateKey = new Uint8Array(32);
        crypto.getRandomValues(privateKey);
        
        // Convert to hex for secp256k1 key generation
        const privateKeyHex = Array.from(privateKey, byte => byte.toString(16).padStart(2, '0')).join('');
        
        // Generate real public key using secp256k1
        if (window.secp256k1 && window.secp256k1.derivePublicKey) {
            const realPubkey = window.secp256k1.derivePublicKey(privateKeyHex);
            localStorage.setItem('user_pubkey', realPubkey);
            localStorage.setItem('user_privkey', privateKeyHex);
            console.log('🔑 Generated REAL cryptographic public key for anonymous mining');
            
            this.showAchievementNotification(
                'Real Mining Key Generated!', 
                'Cryptographically secure key created'
            );
        } else {
            console.error('❌ Cannot generate real key - secp256k1 library not loaded');
            throw new Error('Real key generation required - no dummy keys allowed');
        }
    }

    stopMining() {
        this.currentTarget = null;
    }
}

// Mining toolbar class


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
        console.log('🔍 ReplyFormMiner: Starting setup...');
        console.log('🔍 Current URL:', window.location.pathname);
        console.log('🔍 Document ready state:', document.readyState);
        
        // Check if we're on a page that should have a form
        const url = window.location.pathname;
        const isThreadPage = /^\/[a-z]+\/\d+$/.test(url); // matches /gen/1, /tech/5, etc.
        const isBoardPage = /^\/[a-z]+\/?$/.test(url); // matches /gen, /tech/, etc.
        
        // Try to find any post form - reply form or thread creation form
        let replyForm = document.querySelector('.unified-post-form');
        
        if (!replyForm && isBoardPage) {
            // On board page, look for thread creation form
            replyForm = document.querySelector('#new-thread-form');
            console.log('📋 ReplyFormMiner: On board page, checking for thread form');
        }
        
        // Wait for form to exist in DOM
        console.log('🔍 ReplyFormMiner: Form found?', !!replyForm);
        if (!replyForm) {
            console.log('❌ No reply form found with class .unified-post-form');
            
            // Only retry if we're on a thread page where we expect a form
            if (isThreadPage) {
                console.log('🔄 Retrying on thread page...');
                setTimeout(() => this.setup(), 2000);
            } else {
                console.log('ℹ️ Not on thread page, form not expected');
            }
            return;
        }
        
        // Elements may exist but form container might be hidden
        const contentInput = document.getElementById('post-content');
        const submitBtn = document.getElementById('reply-submit-btn');
        const miningStatus = document.getElementById('reply-mining-status');

        console.log('🔍 ReplyFormMiner: Elements found?', {
            contentInput: !!contentInput,
            submitBtn: !!submitBtn,
            miningStatus: !!miningStatus
        });

        // Even if form is hidden initially, elements should exist
        // Only fail if they truly don't exist in the DOM at all
        if (!contentInput || !submitBtn) {
            console.log('❌ Reply form critical elements missing:', {
                contentInput: contentInput ? 'found' : 'MISSING',
                submitBtn: submitBtn ? 'found' : 'MISSING', 
                miningStatus: miningStatus ? 'found' : 'MISSING'
            });
            
            // Set up observer to retry when form becomes visible
            console.log('🔄 Setting up form visibility observer...');
            this.observeFormVisibility(replyForm);
            return;
        }

        // Add hidden fields for post_draft and user_pubkey_hex
        const postDraftInput = document.createElement('input');
        postDraftInput.type = 'hidden';
        postDraftInput.name = 'post_draft';
        replyForm.appendChild(postDraftInput);

        const userPubkeyHexInput = document.createElement('input');
        userPubkeyHexInput.type = 'hidden';
        userPubkeyHexInput.name = 'user_pubkey_hex';
        userPubkeyHexInput.value = localStorage.getItem('user_pubkey') || ''; // Dynamically get from localStorage
        replyForm.appendChild(userPubkeyHexInput);

        const opIdInput = document.createElement('input');
        opIdInput.type = 'hidden';
        opIdInput.name = 'op_id';
        replyForm.appendChild(opIdInput);

        console.log('🔨 Reply form mining: Setting up');

        let miningTimeout;
        let hasProof = false;

        // Start mining when content is filled
        contentInput.addEventListener('input', () => {
            console.log('📝 Input detected, content length:', contentInput.value.trim().length);
            clearTimeout(miningTimeout);
            const content = contentInput.value.trim();
            
            // Reset proof status when content changes significantly
            hasProof = false;
            
            if (content.length >= 5) {
                console.log('🔄 Content sufficient, starting mining in 1.5 seconds...');
                if (miningStatus) {
                    miningStatus.innerHTML = '<span style="color: #9AB87A;">🔄 Preparing to mine...</span>';
                }
                miningTimeout = setTimeout(async () => {
                    try {
                        await this.startMining(replyForm, miningStatus);
                        hasProof = true;
                        console.log('✅ Mining completed successfully');
                    } catch (error) {
                        console.error('❌ Mining failed:', error);
                        hasProof = false;
                    }
                }, 1500);
            } else {
                if (miningStatus) {
                    miningStatus.innerHTML = '<span style="color: #6c757d;">💡 Start typing to begin mining...</span>';
                }
            }
        });

        // Form submission handler
        replyForm.addEventListener('submit', async (e) => {
            e.preventDefault(); // Always prevent default form submission

            const currentHash = replyForm.querySelector('input[name="pow_hash"]')?.value?.trim() || '';
            const content = contentInput.value.trim();
            const submitBtn = document.getElementById('reply-submit-btn');
            const miningStatus = document.getElementById('reply-mining-status');

            if (content.length < 5) {
                if (miningStatus) {
                    miningStatus.innerHTML = '<span style="color: #dc3545;">❌ Content too short</span>';
                }
                return;
            }

            if (!currentHash) {
                if (miningStatus) {
                    miningStatus.innerHTML = '<span style="color: #ffc107;">⛏️ Mining required before submission...</span>';
                }
                
                try {
                    await this.startMining(replyForm, miningStatus);
                    const newHash = replyForm.querySelector('input[name="pow_hash"]')?.value?.trim() || '';
                    
                    if (!newHash) {
                        throw new Error('Mining failed to produce hash');
                    }
                } catch (error) {
                    console.error('Mining error:', error);
                    if (miningStatus) {
                        miningStatus.innerHTML = '<span style="color: #dc3545;">❌ Mining failed: ' + error.message + '</span>';
                    }
                    return;
                }
            }

            // Proceed with submission via fetch
            submitBtn.textContent = '⏳ Posting...';
            submitBtn.disabled = true;
            if (miningStatus) {
                miningStatus.innerHTML = '<span style="color: #28a745;">✅ Submitting...</span>';
            }

            try {
                const formData = new FormData(replyForm);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

                const response = await fetch(replyForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    // Success - redirect to the new post or reload
                    if (response.redirected) {
                        window.location.href = response.url;
                    } else {
                        // If not redirected, assume it's a successful API response and reload
                        window.location.reload();
                    }
                } else {
                    const errorData = await response.json();
                    console.error('Form submission failed:', errorData);
                    alert('Reply submission failed: ' + (errorData.message || 'Unknown error'));

                    submitBtn.disabled = false;
                    submitBtn.textContent = '⚡ Post Reply';
                    if (miningStatus) {
                        miningStatus.innerHTML = '<span style="color: #dc3545;">❌ Submission failed</span>';
                    }
                }
            } catch (error) {
                console.error('Network error during submission:', error);
                alert('Network error occurred. Please try again.');

                submitBtn.disabled = false;
                submitBtn.textContent = '⚡ Post Reply';
                if (miningStatus) {
                    miningStatus.innerHTML = '<span style="color: #dc3545;">❌ Network error</span>';
                }
            }
        }); // Added missing closing brace here
        
        console.log('✅ Reply form mining setup complete');
    }

    observeFormVisibility(formElement) {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const parentForm = document.getElementById('reply-form');
                    const isVisible = parentForm && parentForm.style.display !== 'none';
                    
                    if (isVisible) {
                        console.log('🔄 Form became visible, retrying setup...');
                        observer.disconnect();
                        setTimeout(() => this.setup(), 200);
                    }
                }
            });
        });

        // Observe the parent container that controls visibility
        const parentForm = document.getElementById('reply-form');
        if (parentForm) {
            observer.observe(parentForm, { attributes: true, attributeFilter: ['style'] });
            console.log('👁️ Form visibility observer set up on #reply-form');
        } else {
            console.warn('⚠️ Could not find #reply-form for visibility observation');
        }
    }

    async startMining(form, statusElement) {
        const contentInput = document.getElementById('post-content');
        const content = contentInput.value.trim();
        
        if (content.length < 5) {
            if (statusElement) {
                statusElement.innerHTML = 'Content too short for mining';
            }
            return;
        }

        // Get board and thread from URL - support both /board/thread/id and /board/id formats
        let boardMatch = window.location.pathname.match(/\/(\w+)\/thread\/(\d+)/);
        if (!boardMatch) {
            boardMatch = window.location.pathname.match(/\/(\w+)\/(\d+)$/);
        }
        if (!boardMatch) {
            if (statusElement) {
                statusElement.innerHTML = '<span style="color: red;">❌ Page URL format error - refresh page</span>';
            }
            console.error('URL pattern not recognized:', window.location.pathname);
            return;
        }
        
        const [, boardCode, threadId] = boardMatch;
        console.log('🔍 Mining context:', { boardCode, threadId, url: window.location.pathname });

        // Create sophisticated loading animation
        if (statusElement) {
            statusElement.innerHTML = `
                <span style="color: #9AB87A; display: flex; align-items: center; gap: 8px;">
                    <div class="mining-loader"></div>
                    <span>Initializing quantum mining...</span>
                </span>
            `;
        }

        // Add progress animation
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress < 90 && statusElement) {
                statusElement.innerHTML = `
                    <span style="color: #9AB87A; display: flex; align-items: center; gap: 8px;">
                        <div class="mining-loader"></div>
                        <span>Mining... ${Math.round(progress)}%</span>
                    </span>
                `;
            }
        }, 200);

        try {
            // Use fixed difficulty for now - simpler and works
            const difficulty = '21e8';

            const proof = await this.pow.acquireProofFor({
                board_code: boardCode,
                target_type: 'reply',
                target_id: threadId,
                action: 'create',
                difficulty: difficulty
            });

            // Clear progress interval
            clearInterval(progressInterval);

            // Fill form fields
            form.querySelector('input[name="pow_nonce"]').value = proof.nonce.toString();
            form.querySelector('input[name="pow_hash"]').value = proof.hash;
            form.querySelector('input[name="pow_challenge_id"]').value = proof.challenge_id;
            
            // Store draft and user info for recovery
            if (form.querySelector('input[name="post_draft"]')) {
                form.querySelector('input[name="post_draft"]').value = JSON.stringify({ body: content, attachments: [], refs: [] });
            }
            
            if (form.querySelector('input[name="op_id"]') && proof.op_id) {
                form.querySelector('input[name="op_id"]').value = proof.op_id;
            }
            
            // Show success with animation
            if (statusElement) {
                statusElement.innerHTML = `
                    <span style="color: #00A9A5; display: flex; align-items: center; gap: 8px;">
                        <span class="hash-discovery">💎</span>
                        <span>Quantum hash discovered! Ready to submit.</span>
                    </span>
                `;
            }
            
            // Enable submit button with enhanced styling
            const submitBtn = document.getElementById('reply-submit-btn');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('tui-btn-disabled');
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
            
            if (statusElement) {
                statusElement.innerHTML = `
                    <span style="color: #dc3545; display: flex; align-items: center; gap: 8px;">
                        <span>⚠️</span>
                        <span>Mining error: ${errorMsg}</span>
                    </span>
                `;
            
                // Auto-retry once after 2 seconds for certain errors
                if (error.message.includes('Challenge failed') || error.message.includes('Network')) {
                    statusElement.innerHTML += `
                        <br><span style="color: #6c757d; font-size: 11px;">
                            Auto-retrying in 2 seconds...
                        </span>
                    `;
                    
                    setTimeout(() => {
                        console.log('Auto-retrying mining after error...');
                        this.startMining(form, statusElement);
                    }, 2000);
                }
            }
            
            throw error;
        }
    }
}

// Ensure mining CSS animations are available
if (!document.getElementById('mining-animations')) {
    const style = document.createElement('style');
    style.id = 'mining-animations';
    style.textContent = `
        .mining-loader {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid #9AB87A;
            border-radius: 50%;
            border-top-color: transparent;
            animation: mining-loader-spin 1s linear infinite;
        }
        
        @keyframes mining-loader-spin {
            to { transform: rotate(360deg); }
        }
        
        .hash-discovery {
            animation: hash-discovery-pulse 0.6s ease-out;
        }
        
        @keyframes hash-discovery-pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .mining-status-indicator {
            background: rgba(112, 139, 117, 0.9);
            color: #F5F5DC;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 4px;
        }
    `;
    document.head.appendChild(style);
}

// Initialize immediately
console.log('🔨 Simple PoW: Creating instance...');
window.simplePoW = new SimpleProofOfWork();

// Initialize reply form mining when DOM is ready
function initializeReplyFormMiner() {
    console.log('🔨 Initializing ReplyFormMiner...');
    
    // Ensure simplePoW is available
    if (!window.simplePoW) {
        console.log('⏳ Waiting for simplePoW to be available...');
        setTimeout(initializeReplyFormMiner, 500);
        return;
    }
    
    try {
        window.replyFormMiner = new ReplyFormMiner(window.simplePoW);
        console.log('✅ ReplyFormMiner initialized successfully');
    } catch (error) {
        console.error('❌ Failed to initialize ReplyFormMiner:', error);
        // Retry once after delay
        setTimeout(() => {
            try {
                window.replyFormMiner = new ReplyFormMiner(window.simplePoW);
                console.log('✅ ReplyFormMiner initialized on retry');
            } catch (retryError) {
                console.error('❌ ReplyFormMiner retry failed:', retryError);
            }
        }, 2000);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeReplyFormMiner);
} else {
    initializeReplyFormMiner();
}

// Initialize everything when DOM is ready for mouseover mining
// Only create toolbar if we're not on the mining page
if (!window.location.pathname.includes('/mining')) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.mouseoverMiner = new SimpleMouseoverMiner(window.simplePoW);
            
            // Dispatch ready event for toolbar
            if (typeof window.CustomEvent === 'function') {
                window.dispatchEvent(new CustomEvent('mouseoverMinerReady', { detail: window.mouseoverMiner }));
            }
        });
    } else {
        window.mouseoverMiner = new SimpleMouseoverMiner(window.simplePoW);
        
        // Dispatch ready event for toolbar immediately
        setTimeout(() => {
            if (typeof window.CustomEvent === 'function') {
                window.dispatchEvent(new CustomEvent('mouseoverMinerReady', { detail: window.mouseoverMiner }));
            }
        }, 100);
    }
}

// Mining system diagnostics
window.MiningDiagnostics = {
    checkSystem: async function() {
        console.log('🔍 Running mining system diagnostics...');
        
        const results = {
            userPubkey: !!localStorage.getItem('user_pubkey'),
            csrfToken: !!document.querySelector('meta[name="csrf-token"]')?.content,
            simplePoW: !!window.simplePoW,
            wasmPowMiner: !!window.wasmPowMiner,
            mouseoverMiner: !!window.mouseoverMiner,
            replyFormMiner: !!window.replyFormMiner,
            apiEndpoints: {
                powParams: false,
                threadBegin: false,
                replyBegin: false
            }
        };
        
        // Test API endpoints
        try {
            const powParamsResponse = await fetch('/api/pow.params');
            results.apiEndpoints.powParams = powParamsResponse.ok;
        } catch (e) {
            console.warn('pow.params endpoint test failed:', e);
        }
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const testResponse = await fetch('/api/thread.begin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    post_draft: { title: 'test', body: 'test', attachments: [], refs: [] },
                    client_op_id: 'test-00000000-0000-0000-0000-000000000000',
                    user_pubkey_hex: localStorage.getItem('user_pubkey') // NO DUMMY FALLBACK - Real key required
                })
            });
            results.apiEndpoints.threadBegin = testResponse.ok || testResponse.status === 422;
        } catch (e) {
            console.warn('thread.begin endpoint test failed:', e);
        }
        
        console.table(results);
        
        // Generate fix suggestions
        const fixes = [];
        if (!results.userPubkey) {
            fixes.push('Missing user public key - generate real key: window.simplePoW.generateFallbackPubkey()');
        }
        if (!results.csrfToken) {
            fixes.push('Missing CSRF token - ensure meta tag is present in HTML head');
        }
        if (!results.simplePoW) {
            fixes.push('SimpleProofOfWork not initialized - check script loading');
        }
        
        if (fixes.length > 0) {
            console.log('🔧 Suggested fixes:');
            fixes.forEach(fix => console.log('  -', fix));
        } else {
            console.log('✅ All mining system components appear healthy');
        }
        
        return results;
    },
    
    fixCommonIssues: function() {
        // Automatically fix common issues
        if (!localStorage.getItem('user_pubkey')) {
            // NO DUMMY KEYS - Generate real cryptographic key
            if (window.simplePoW && window.simplePoW.generateFallbackPubkey) {
                window.simplePoW.generateFallbackPubkey();
                console.log('✅ Generated REAL cryptographic public key');
            } else {
                console.error('❌ Cannot generate real key - mining system not ready');
            }
        }
        
        if (!window.simplePoW) {
            window.simplePoW = new SimpleProofOfWork();
            console.log('✅ Reinitializing SimpleProofOfWork');
        }
        
        console.log('🔧 Common mining issues fixed - try mining again');
    }
};

console.log('🔨 Simple PoW: Ready with reply form mining, mouseover mining and toolbar');
console.log('🔍 Run MiningDiagnostics.checkSystem() to debug mining issues');
console.log('🔧 Run MiningDiagnostics.fixCommonIssues() to auto-fix common problems');