/**
 * Global Mouseover Mining System
 * Mining power proportional to mouse movement in rolling 60-second window
 */

class GlobalMouseoverMining {
    constructor() {
        this.isActive = true;
        this.movements = []; // Store movement data with timestamps
        this.windowSize = 60000; // 60 seconds in milliseconds
        this.minMovement = 10; // Minimum pixels for movement to count
        this.maxHashrate = 5000; // Maximum hashes per second
        this.baseHashrate = 100; // Minimum hashes per second
        
        // Mining state
        this.currentTarget = null;
        this.currentMining = null;
        this.totalHashes = 0;
        this.totalPoints = 0;
        
        // Movement tracking
        this.lastMousePos = { x: 0, y: 0 };
        this.lastMovementTime = 0;
        
        this.init();
    }

    init() {
        this.setupMouseTracking();
        this.startMiningLoop();
        this.cleanupOldMovements();
        
        console.log('🖱️ Global Mouseover Mining initialized');
    }

    setupMouseTracking() {
        // Track mouse movement globally
        document.addEventListener('mousemove', (e) => {
            this.trackMovement(e.clientX, e.clientY);
        });

        // Track elements that should trigger mining
        this.setupMiningTargets();
    }

    trackMovement(x, y) {
        const now = Date.now();
        const deltaX = x - this.lastMousePos.x;
        const deltaY = y - this.lastMousePos.y;
        const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);

        // Only record significant movements
        if (distance >= this.minMovement) {
            this.movements.push({
                timestamp: now,
                distance: distance,
                velocity: this.calculateVelocity(distance, now - this.lastMovementTime),
                x: x,
                y: y
            });

            this.lastMousePos = { x, y };
            this.lastMovementTime = now;
        }
    }

    calculateVelocity(distance, timeDelta) {
        return timeDelta > 0 ? distance / timeDelta : 0;
    }

    // Calculate current mining power based on recent movement
    getCurrentMiningPower() {
        const now = Date.now();
        const cutoff = now - this.windowSize;
        
        // Get movements in the last 60 seconds
        const recentMovements = this.movements.filter(m => m.timestamp >= cutoff);
        
        if (recentMovements.length === 0) {
            return this.baseHashrate;
        }

        // Calculate various metrics
        const totalDistance = recentMovements.reduce((sum, m) => sum + m.distance, 0);
        const avgVelocity = recentMovements.reduce((sum, m) => sum + m.velocity, 0) / recentMovements.length;
        const movementFrequency = recentMovements.length / (this.windowSize / 1000);

        // Calculate mining power (logarithmic scale to prevent extreme values)
        const distanceFactor = Math.log10(totalDistance + 1) * 200;
        const velocityFactor = Math.log10(avgVelocity * 1000 + 1) * 100;
        const frequencyFactor = movementFrequency * 50;

        const totalPower = Math.min(
            this.maxHashrate,
            this.baseHashrate + distanceFactor + velocityFactor + frequencyFactor
        );

        return Math.floor(totalPower);
    }

    // Setup mining targets across different page types
    setupMiningTargets() {
        // Auto-detect page type and setup appropriate targets
        if (document.querySelector('.post')) {
            this.setupForumMining();
        }
        
        if (document.querySelector('#chat-messages')) {
            this.setupChatMining();
        }
        
        if (document.querySelector('.image-item')) {
            this.setupImageLibraryMining();
        }

        // Generic content mining
        this.setupGenericMining();

        // Watch for dynamically added content
        this.setupDynamicContentObserver();
    }

    setupDynamicContentObserver() {
        const observer = new MutationObserver((mutations) => {
            let needsUpdate = false;
            
            mutations.forEach(mutation => {
                if (mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1 && (
                            node.classList?.contains('post') ||
                            node.classList?.contains('thread') ||
                            node.classList?.contains('image-item') ||
                            node.querySelector?.('[data-mineable]') ||
                            node.hasAttribute?.('data-mineable')
                        )) {
                            needsUpdate = true;
                        }
                    });
                }
            });

            if (needsUpdate) {
                // Debounce updates
                clearTimeout(this.updateTimeout);
                this.updateTimeout = setTimeout(() => {
                    console.log('🔄 Refreshing mining targets for new content...');
                    this.setupMiningTargets();
                }, 500);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    setupForumMining() {
        // Mine posts on mouseover
        document.querySelectorAll('.post').forEach(post => {
            post.addEventListener('mouseenter', () => {
                this.startMining({
                    type: 'post',
                    id: post.dataset.id,
                    element: post,
                    difficulty: '21e8',
                    content: post.querySelector('.post-content')?.textContent || 'post'
                });
            });

            post.addEventListener('mouseleave', () => {
                this.stopMining();
            });
        });
    }

    setupChatMining() {
        // Mine chat messages on mouseover
        document.querySelectorAll('#chat-messages > div').forEach(message => {
            message.addEventListener('mouseenter', () => {
                this.startMining({
                    type: 'message',
                    id: Date.now(),
                    element: message,
                    difficulty: '21e8',
                    content: message.textContent || 'message'
                });
            });

            message.addEventListener('mouseleave', () => {
                this.stopMining();
            });
        });
    }

    setupImageLibraryMining() {
        // Mine images on mouseover
        document.querySelectorAll('.image-item').forEach(item => {
            item.addEventListener('mouseenter', () => {
                this.startMining({
                    type: 'image',
                    id: item.dataset.id,
                    element: item,
                    difficulty: '21e8',
                    content: `image_${item.dataset.id}`
                });
            });

            item.addEventListener('mouseleave', () => {
                this.stopMining();
            });
        });
    }

    setupGenericMining() {
        // Mine any element with data-mineable attribute
        document.querySelectorAll('[data-mineable]').forEach(element => {
            element.addEventListener('mouseenter', () => {
                this.startMining({
                    type: element.dataset.mineType || 'generic',
                    id: element.dataset.mineId || Date.now(),
                    element: element,
                    difficulty: element.dataset.mineDifficulty || '21e8',
                    content: element.dataset.mineContent || element.textContent || 'content'
                });
            });

            element.addEventListener('mouseleave', () => {
                this.stopMining();
            });
        });

        // Granular mining for specific elements
        this.setupGranularMining();
    }

    setupGranularMining() {
        // Mine post numbers (clickable hashes)
        document.querySelectorAll('.post-no, .clickable-hash').forEach(hash => {
            hash.addEventListener('mouseenter', (e) => {
                e.stopPropagation(); // Don't trigger parent mining
                const postId = hash.textContent.replace(/[^0-9]/g, '');
                this.startMining({
                    type: 'hash',
                    id: `hash-${postId}`,
                    element: hash,
                    difficulty: '21e',
                    content: `hash_${postId}`,
                    power: this.calculateHashPower(hash)
                });
            });

            hash.addEventListener('mouseleave', () => {
                this.stopMining();
            });
        });

        // Mine individual images with higher precision
        document.querySelectorAll('img[data-hash]').forEach(img => {
            img.addEventListener('mouseenter', (e) => {
                e.stopPropagation();
                const hash = img.dataset.hash;
                const imageId = img.dataset.imageId || img.dataset.threadId || img.dataset.postId;
                this.startMining({
                    type: 'image-precise',
                    id: `img-${imageId}`,
                    element: img,
                    difficulty: '21e8',
                    content: `image_${hash}_${imageId}`,
                    power: this.calculateImagePower(img)
                });
            });

            img.addEventListener('mouseleave', () => {
                this.stopMining();
            });
        });

        // Mine quoted text (greentext)
        document.querySelectorAll('.quote, blockquote').forEach(quote => {
            quote.addEventListener('mouseenter', (e) => {
                e.stopPropagation();
                this.startMining({
                    type: 'quote',
                    id: `quote-${Date.now()}`,
                    element: quote,
                    difficulty: '21',
                    content: quote.textContent,
                    power: this.calculateQuotePower(quote)
                });
            });

            quote.addEventListener('mouseleave', () => {
                this.stopMining();
            });
        });

        // Mine links with special handling
        document.querySelectorAll('a[href*="/thread/"], a[href*="/catalog"]').forEach(link => {
            link.addEventListener('mouseenter', (e) => {
                e.stopPropagation();
                const threadId = link.href.match(/\/(\d+)$/)?.[1];
                this.startMining({
                    type: 'link',
                    id: `link-${threadId || Date.now()}`,
                    element: link,
                    difficulty: '21e',
                    content: `link_${link.href}`,
                    power: this.calculateLinkPower(link)
                });
            });

            link.addEventListener('mouseleave', () => {
                this.stopMining();
            });
        });

        // Mine timestamps for temporal patterns
        document.querySelectorAll('[title*="ago"], time, .timestamp').forEach(time => {
            time.addEventListener('mouseenter', (e) => {
                e.stopPropagation();
                this.startMining({
                    type: 'timestamp',
                    id: `time-${Date.now()}`,
                    element: time,
                    difficulty: '21',
                    content: `time_${time.textContent}`,
                    power: this.calculateTimePower(time)
                });
            });

            time.addEventListener('mouseleave', () => {
                this.stopMining();
            });
        });
    }

    // Power calculation methods for granular mining
    calculateHashPower(element) {
        // Hashes get more power if they have special patterns
        const text = element.textContent;
        if (text.includes('666')) return 1500;
        if (text.includes('777')) return 1200;
        if (text.includes('420')) return 1000;
        if (text.includes('69')) return 900;
        return 500;
    }

    calculateImagePower(img) {
        // Larger images get more mining power
        const size = img.naturalWidth * img.naturalHeight;
        if (size > 1000000) return 2000; // 1MP+
        if (size > 500000) return 1500;  // 500K+
        if (size > 250000) return 1000;  // 250K+
        return 750;
    }

    calculateQuotePower(quote) {
        // Longer quotes get more power
        const length = quote.textContent.length;
        if (length > 500) return 1200;
        if (length > 200) return 800;
        if (length > 100) return 600;
        return 400;
    }

    calculateLinkPower(link) {
        // External links get more power
        if (link.href.includes('http') && !link.href.includes(window.location.hostname)) {
            return 1000;
        }
        return 500;
    }

    calculateTimePower(time) {
        // Recent timestamps get more power
        const text = time.textContent.toLowerCase();
        if (text.includes('second') || text.includes('minute')) return 800;
        if (text.includes('hour')) return 600;
        if (text.includes('day')) return 400;
        return 200;
    }

    startMining(target) {
        if (!this.isActive || this.currentTarget?.id === target.id) return;

        this.stopMining(); // Stop any existing mining
        this.currentTarget = target;

        // Visual feedback
        if (target.element) {
            target.element.style.boxShadow = '0 0 10px rgba(112, 139, 117, 0.5)';
            target.element.style.transition = 'box-shadow 0.3s ease';
        }

        console.log(`⛏️ Starting mouseover mining for ${target.type} ${target.id}`);
        this.mine();
    }

    stopMining() {
        if (this.currentMining) {
            clearTimeout(this.currentMining);
            this.currentMining = null;
        }

        // Remove visual feedback
        if (this.currentTarget?.element) {
            this.currentTarget.element.style.boxShadow = '';
        }

        this.currentTarget = null;
    }

    async mine() {
        if (!this.currentTarget) return;

        const target = this.currentTarget;
        // Use custom power if provided, otherwise use movement-based power
        const basePower = this.getCurrentMiningPower();
        const hashrate = target.power || basePower;
        
        // Calculate how many hashes to attempt based on current power
        const hashesPerCycle = Math.floor(hashrate / 10); // 10 cycles per second
        let hashesAttempted = 0;

        try {
            for (let i = 0; i < hashesPerCycle; i++) {
                const nonce = this.totalHashes + i;
                const data = `${target.type}:${target.id}:${target.content}:${nonce}`;
                const hash = await this.sha256(data);

                hashesAttempted++;

                // Check if we found a valid hash
                if (hash.startsWith(target.difficulty.toLowerCase())) {
                    await this.onHashFound(target, hash, nonce, hashesAttempted);
                    break;
                }
            }

            this.totalHashes += hashesAttempted;

            // Update visual indicator
            this.updateMiningStatus(hashrate, hashesAttempted);

            // Continue mining if still active
            if (this.currentTarget) {
                this.currentMining = setTimeout(() => this.mine(), 100); // 10 FPS
            }

        } catch (error) {
            console.error('Mining error:', error);
            this.stopMining();
        }
    }

    async onHashFound(target, hash, nonce, attempts) {
        console.log(`💎 Found valid hash! Type: ${target.type}, Hash: ${hash}, Attempts: ${attempts}`);

        // Calculate points based on difficulty and movement
        const basePoints = this.calculatePoints(hash);
        const movementBonus = Math.floor(this.getCurrentMiningPower() / 100);
        const totalPoints = basePoints + movementBonus;

        this.totalPoints += totalPoints;

        // Visual celebration
        this.showMiningSuccess(target.element, totalPoints, hash);

        // Submit to server if possible
        try {
            await this.submitProof(target, hash, nonce, totalPoints);
        } catch (error) {
            console.warn('Failed to submit proof:', error);
        }
    }

    calculatePoints(hash) {
        const patterns = {
            '21e8000': 10000,
            '21e800': 2500,
            '21e80': 500,
            '21e8': 100,
            '000': 1000,
            '666': 666,
            'dead': 500,
            '21': 10
        };

        for (const [pattern, points] of Object.entries(patterns)) {
            if (hash.startsWith(pattern)) {
                return points;
            }
        }

        return 1;
    }

    showMiningSuccess(element, points, hash) {
        if (!element) return;

        // Create floating points indicator
        const indicator = document.createElement('div');
        indicator.textContent = `+${points}⚡`;
        indicator.style.cssText = `
            position: fixed;
            color: #28a745;
            font-weight: bold;
            font-size: 14px;
            z-index: 10000;
            pointer-events: none;
            animation: floatUp 2s ease-out forwards;
        `;

        const rect = element.getBoundingClientRect();
        indicator.style.left = (rect.left + rect.width / 2) + 'px';
        indicator.style.top = (rect.top + rect.height / 2) + 'px';

        document.body.appendChild(indicator);

        // Add animation styles if not exists
        if (!document.getElementById('mining-animations')) {
            const style = document.createElement('style');
            style.id = 'mining-animations';
            style.textContent = `
                @keyframes floatUp {
                    0% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                    100% { opacity: 0; transform: translate(-50%, -100px) scale(1.2); }
                }
            `;
            document.head.appendChild(style);
        }

        setTimeout(() => indicator.remove(), 2000);

        // Flash the element
        element.style.background = '#e8f5e8';
        setTimeout(() => element.style.background = '', 1000);
    }

    updateMiningStatus(hashrate, attempts) {
        // Update any mining status displays
        const statusElements = document.querySelectorAll('.mining-status, #mining-status');
        statusElements.forEach(el => {
            if (el && this.currentTarget) {
                el.textContent = `⛏️ Mining ${this.currentTarget.type} @ ${hashrate} H/s (${attempts} hashes)`;
            }
        });
    }

    async submitProof(target, hash, nonce, points) {
        // Submit proof to appropriate endpoint based on target type
        let endpoint = '/api/submit-proof';
        const payload = {
            type: target.type,
            target_id: target.id,
            hash: hash,
            nonce: nonce,
            points: points,
            source: 'mouseover'
        };

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            throw new Error('Failed to submit proof');
        }

        return response.json();
    }

    cleanupOldMovements() {
        // Clean up old movement data every 30 seconds
        setInterval(() => {
            const cutoff = Date.now() - this.windowSize;
            this.movements = this.movements.filter(m => m.timestamp >= cutoff);
        }, 30000);
    }

    async sha256(text) {
        const encoder = new TextEncoder();
        const data = encoder.encode(text);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    // Public API
    getStats() {
        const currentPower = this.getCurrentMiningPower();
        const recentMovements = this.movements.filter(m => m.timestamp >= Date.now() - this.windowSize);
        
        return {
            isActive: this.isActive,
            currentPower: currentPower,
            recentMovements: recentMovements.length,
            totalHashes: this.totalHashes,
            totalPoints: this.totalPoints,
            currentTarget: this.currentTarget?.type || null
        };
    }

    toggle() {
        this.isActive = !this.isActive;
        if (!this.isActive) {
            this.stopMining();
        }
        console.log(`🖱️ Mouseover mining ${this.isActive ? 'enabled' : 'disabled'}`);
    }
}

// Initialize global mouseover mining
window.globalMouseoverMining = new GlobalMouseoverMining();

// Add debug command to console
console.log('🖱️ Global Mouseover Mining loaded. Use globalMouseoverMining.getStats() to check status');