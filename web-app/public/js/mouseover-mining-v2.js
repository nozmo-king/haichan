/**
 * Haichan Mouseover Mining System v2.1
 * Fixed detection for threads, posts, and images with smooth transitions
 */

class MouseoverMiningSystemV2 {
    constructor() {
        this.isEnabled = true;
        this.activeMining = new Map();
        this.currentTarget = null;
        this.miningStats = {
            totalHashes: 0,
            totalProofs: 0,
            sessionStart: Date.now()
        };
        this.targetPattern = '21e8';
        this.hoverTimeout = null;
        
        this.init();
    }
    
    init() {
        console.log('🔥 Mouseover Mining System v2.1 initializing...');
        this.setupMouseoverDetection();
        this.setupMiningStyles();
        this.connectToDashboard();
        console.log('✅ Mouseover Mining System v2.1 ready');
    }
    
    setupMouseoverDetection() {
        // Remove existing listeners
        document.removeEventListener('mouseover', this.handleMouseover);
        document.removeEventListener('mouseout', this.handleMouseout);
        
        // Add new listeners with smooth transitions
        document.addEventListener('mouseover', (e) => this.handleMouseover(e));
        document.addEventListener('mouseout', (e) => this.handleMouseout(e));
    }
    
    handleMouseover(event) {
        if (!this.isEnabled) return;
        
        const target = event.target;
        const mineableData = this.getMineableData(target);
        
        if (mineableData) {
            // Clear any pending stop timeout
            if (this.hoverTimeout) {
                clearTimeout(this.hoverTimeout);
                this.hoverTimeout = null;
            }
            
            // Don't restart if already mining the same target
            if (this.currentTarget && this.currentTarget.id === mineableData.id && this.currentTarget.type === mineableData.type) {
                return;
            }
            
            this.startMining(target, mineableData);
        }
    }
    
    handleMouseout(event) {
        const target = event.target;
        const mineableData = this.getMineableData(target);
        
        if (mineableData) {
            // Add small delay before stopping to prevent flickering
            this.hoverTimeout = setTimeout(() => {
                this.stopMining(target);
            }, 100);
        }
    }
    
    getMineableData(element) {
        let current = element;
        
        // Walk up the DOM tree to find mineable elements
        for (let i = 0; i < 6; i++) {
            if (!current) break;
            
            // PRIORITY 1: Images with hash data
            if (current.tagName === 'IMG' && current.dataset.hash) {
                const hash = current.dataset.hash;
                const threadId = current.dataset.threadId;
                const postId = current.dataset.postId;
                
                if (hash && hash.length === 64) {
                    const context = threadId ? `Thread #${threadId}` : postId ? `Post #${postId}` : 'Image';
                    return {
                        type: 'image',
                        id: hash,
                        data: `image_${hash}_${Date.now()}`,
                        displayName: `🖼️ ${context}`,
                        hash: hash,
                        points: 25,
                        element: current
                    };
                }
            }
            
            // PRIORITY 2: Posts with .post class
            if (current.classList && current.classList.contains('post')) {
                // Check if it's a thread listing (has data-thread-id)
                if (current.dataset.threadId) {
                    return {
                        type: 'thread',
                        id: current.dataset.threadId,
                        data: `thread_${current.dataset.threadId}_${Date.now()}`,
                        displayName: `🧵 Thread #${current.dataset.threadId}`,
                        points: 20,
                        element: current
                    };
                }
                
                // Check if it's a post in thread view (look for post number)
                const postNoElement = current.querySelector('.post-no');
                if (postNoElement) {
                    const postText = postNoElement.textContent || '';
                    const match = postText.match(/No\.(\d+)/);
                    if (match) {
                        const postId = match[1];
                        
                        // Check if this is the OP (original post) by looking for thread title
                        const hasThreadTitle = current.querySelector('.post-content strong');
                        
                        if (hasThreadTitle && !current.querySelector('.post').length) {
                            // This is the original post
                            return {
                                type: 'thread-op',
                                id: postId,
                                data: `thread_op_${postId}_${Date.now()}`,
                                displayName: `🧵 Original Post #${postId}`,
                                points: 22,
                                element: current
                            };
                        } else {
                            // This is a reply
                            return {
                                type: 'post',
                                id: postId,
                                data: `post_${postId}_${Date.now()}`,
                                displayName: `💬 Reply #${postId}`,
                                points: 18,
                                element: current
                            };
                        }
                    }
                }
            }
            
            // PRIORITY 3: Catalog threads
            if (current.classList && current.classList.contains('catalog-thread')) {
                const threadId = current.dataset.threadId;
                if (threadId) {
                    return {
                        type: 'catalog-thread',
                        id: threadId,
                        data: `catalog_${threadId}_${Date.now()}`,
                        displayName: `📋 Catalog Thread #${threadId}`,
                        points: 16,
                        element: current
                    };
                }
            }
            
            current = current.parentElement;
        }
        
        return null;
    }
    
    async startMining(element, mineableData) {
        // Stop any existing mining first
        if (this.currentTarget) {
            this.stopAllMining();
        }
        
        console.log(`⛏️ Starting mining: ${mineableData.displayName}`);
        
        // Visual feedback with smooth transition
        this.addMiningVisual(mineableData.element);
        this.updateDashboardTarget(mineableData);
        
        // Create mining session
        const miningSession = {
            element: mineableData.element,
            data: mineableData,
            startTime: Date.now(),
            hashes: 0,
            isActive: true
        };
        
        this.activeMining.set(mineableData.element, miningSession);
        this.currentTarget = mineableData;
        
        // Start mining
        this.performMining(miningSession);
    }
    
    stopMining(element) {
        const session = this.activeMining.get(element);
        if (session) {
            session.isActive = false;
            this.activeMining.delete(element);
            this.removeMiningVisual(element);
            
            console.log(`⏹️ Stopped mining: ${session.data.displayName}`);
        }
        
        // Clear current target if no active mining
        if (this.activeMining.size === 0) {
            this.currentTarget = null;
            this.updateDashboardTarget(null);
        }
    }
    
    stopAllMining() {
        this.activeMining.forEach((session, element) => {
            session.isActive = false;
            this.removeMiningVisual(element);
        });
        this.activeMining.clear();
        this.currentTarget = null;
        this.updateDashboardTarget(null);
    }
    
    async performMining(session) {
        const { data, element } = session;
        let nonce = Math.floor(Math.random() * 1000000);
        const batchSize = 1000;
        
        while (session.isActive && nonce < 10000000) {
            for (let i = 0; i < batchSize && session.isActive; i++) {
                const input = `${data.data}_${nonce}`;
                const hash = await this.calculateSHA256(input);
                
                session.hashes++;
                this.miningStats.totalHashes++;
                
                // Check for proof
                if (hash.startsWith(this.targetPattern)) {
                    console.log(`💎 PROOF FOUND! ${data.displayName}: ${hash}`);
                    
                    // Stop mining
                    session.isActive = false;
                    
                    // Success animation
                    this.showProofSuccess(element, hash, data.points, session.hashes);
                    
                    // Update stats
                    this.miningStats.totalProofs++;
                    
                    // Update dashboard if available
                    if (window.enhancedMiningDashboard) {
                        window.enhancedMiningDashboard.addProof(hash, data.points);
                    }
                    
                    // Submit proof
                    this.submitProof({
                        type: data.type,
                        targetId: data.id,
                        hash: hash,
                        nonce: nonce,
                        input: input,
                        attempts: session.hashes,
                        points: data.points
                    });
                    
                    break;
                }
                
                nonce++;
            }
            
            // Update display
            this.updateMiningDisplay(session);
            
            // Yield control
            await new Promise(resolve => setTimeout(resolve, 1));
        }
    }
    
    async calculateSHA256(input) {
        const encoder = new TextEncoder();
        const data = encoder.encode(input);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }
    
    addMiningVisual(element) {
        if (!element) return;
        
        element.classList.add('haichan-mining-active');
        
        // Smooth glow effect
        element.style.transition = 'all 0.3s ease';
        element.style.boxShadow = '0 0 20px rgba(0, 212, 255, 0.8)';
        element.style.borderColor = '#00d4ff';
        element.style.transform = 'scale(1.02)';
        
        // Add mining indicator
        if (!element.querySelector('.mining-indicator')) {
            const indicator = document.createElement('div');
            indicator.className = 'mining-indicator';
            indicator.innerHTML = '⛏️';
            indicator.style.cssText = `
                position: absolute;
                top: 8px;
                right: 8px;
                background: linear-gradient(135deg, #00d4ff, #0099cc);
                color: white;
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: bold;
                z-index: 1001;
                animation: miningPulse 1.5s infinite;
                box-shadow: 0 2px 8px rgba(0, 212, 255, 0.4);
            `;
            
            // Ensure parent positioning
            if (getComputedStyle(element).position === 'static') {
                element.style.position = 'relative';
            }
            
            element.appendChild(indicator);
        }
    }
    
    removeMiningVisual(element) {
        if (!element) return;
        
        element.classList.remove('haichan-mining-active');
        element.style.transition = 'all 0.3s ease';
        element.style.boxShadow = '';
        element.style.transform = '';
        element.style.borderColor = '';
        
        // Remove indicator
        const indicator = element.querySelector('.mining-indicator');
        if (indicator) {
            indicator.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => indicator.remove(), 300);
        }
    }
    
    showProofSuccess(element, hash, points, attempts) {
        if (!element) return;
        
        // Just a subtle flash effect - NO ugly popup
        element.style.animation = 'proofFlash 0.4s ease-in-out 2';
        
        // Clean up animation
        setTimeout(() => {
            element.style.animation = '';
        }, 800);
        
        console.log(`💎 Proof found: ${hash.substring(0, 12)}... (+${points} pts, ${attempts.toLocaleString()} attempts)`);
    }
    
    updateDashboardTarget(target) {
        if (window.enhancedMiningDashboard) {
            if (target) {
                window.enhancedMiningDashboard.setTarget(target.displayName);
            } else {
                window.enhancedMiningDashboard.setTarget('Hover over content');
            }
        }
    }
    
    updateMiningDisplay(session) {
        const elapsed = Math.max(1, (Date.now() - session.startTime) / 1000);
        const hashrate = Math.floor(session.hashes / elapsed);
        
        // Update dashboard if available
        if (window.enhancedMiningDashboard) {
            window.enhancedMiningDashboard.updateHashCount(this.miningStats.totalHashes);
            window.enhancedMiningDashboard.updateHashrate(hashrate);
        }
    }
    
    async submitProof(proof) {
        try {
            const response = await fetch('/api/mouseover-mining/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(proof)
            });
            
            if (response.ok) {
                const result = await response.json();
                console.log('✅ Proof submitted:', result);
            }
        } catch (error) {
            console.log('⚠️ Proof submission (API not available)');
        }
    }
    
    setupMiningStyles() {
        if (document.getElementById('mouseover-mining-styles-v2')) return;
        
        const style = document.createElement('style');
        style.id = 'mouseover-mining-styles-v2';
        style.textContent = `
            .haichan-mining-active {
                cursor: crosshair !important;
            }
            
            @keyframes miningPulse {
                0%, 100% { 
                    opacity: 1; 
                    transform: scale(1); 
                }
                50% { 
                    opacity: 0.8; 
                    transform: scale(1.1); 
                }
            }
            
            @keyframes proofSuccess {
                0% {
                    opacity: 0;
                    transform: translate(-50%, -50%) scale(0.5);
                }
                20% {
                    opacity: 1;
                    transform: translate(-50%, -50%) scale(1.2);
                }
                80% {
                    opacity: 1;
                    transform: translate(-50%, -55%) scale(1);
                }
                100% {
                    opacity: 0;
                    transform: translate(-50%, -70%) scale(0.8);
                }
            }
            
            @keyframes proofFlash {
                0%, 100% { 
                    background: inherit; 
                    filter: none;
                }
                50% { 
                    background: rgba(255, 215, 0, 0.3) !important;
                    filter: brightness(1.3);
                }
            }
            
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
            
            .proof-success-float {
                pointer-events: none;
            }
            
            .proof-icon {
                font-size: 20px;
                margin-bottom: 8px;
            }
            
            .proof-title {
                font-size: 14px;
                font-weight: bold;
                margin-bottom: 6px;
                color: #000;
            }
            
            .proof-hash {
                font-size: 10px;
                font-family: 'Courier New', monospace;
                margin-bottom: 6px;
                color: #333;
                letter-spacing: 1px;
            }
            
            .proof-stats {
                font-size: 9px;
                color: #666;
                font-weight: normal;
            }
        `;
        
        document.head.appendChild(style);
    }
    
    connectToDashboard() {
        setTimeout(() => {
            const prefixSelector = document.getElementById('prefix-selector');
            if (prefixSelector) {
                this.targetPattern = prefixSelector.value;
                prefixSelector.addEventListener('change', (e) => {
                    this.targetPattern = e.target.value;
                    console.log(`🎯 Mining difficulty changed to: ${this.targetPattern}`);
                });
            }
        }, 1000);
    }
    
    // Public methods
    enable() {
        this.isEnabled = true;
        console.log('✅ Mouseover mining enabled');
    }
    
    disable() {
        this.isEnabled = false;
        this.stopAllMining();
        console.log('⏹️ Mouseover mining disabled');
    }
    
    getStats() {
        const elapsed = Math.max(1, (Date.now() - this.miningStats.sessionStart) / 1000);
        return {
            totalHashes: this.miningStats.totalHashes,
            totalProofs: this.miningStats.totalProofs,
            sessionLength: elapsed,
            averageHashrate: Math.floor(this.miningStats.totalHashes / elapsed),
            activeMining: this.activeMining.size,
            currentTarget: this.currentTarget?.displayName || 'None'
        };
    }
}

// Initialize the system
document.addEventListener('DOMContentLoaded', function() {
    if (window.mouseoverMining) {
        console.log('🔄 Replacing existing mouseover mining system');
    }
    
    window.mouseoverMiningV2 = new MouseoverMiningSystemV2();
    window.mouseoverMining = window.mouseoverMiningV2; // Backward compatibility
    
    console.log('🚀 Mouseover Mining System v2.1 loaded');
});

// Also initialize if DOM already loaded
if (document.readyState !== 'loading') {
    window.mouseoverMiningV2 = new MouseoverMiningSystemV2();
    window.mouseoverMining = window.mouseoverMiningV2;
}