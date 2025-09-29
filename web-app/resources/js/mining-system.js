/**
 * HAICHAN MINING SYSTEM
 * Clean, modular mining implementation
 */

class HaichanMiner {
    constructor() {
        this.isActive = true;
        this.currentTarget = null;
        this.miningInterval = null;
        this.sessionStats = {
            hashes: 0,
            proofs: 0,
            points: 0
        };
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.initializeUI();
        this.startNeuralCanvas();
        this.setupGlobalMining();
    }
    
    setupEventListeners() {
        // Mining toggle
        const miningToggle = document.getElementById('mining-toggle');
        if (miningToggle) {
            miningToggle.addEventListener('click', () => this.toggleMining());
        }
        
        // Dashboard toggle
        const dashboardToggle = document.getElementById('dashboard-toggle');
        if (dashboardToggle) {
            dashboardToggle.addEventListener('click', () => this.toggleDashboard());
        }
    }
    
    setupGlobalMining() {
        // Make images mineable
        this.setupImageMining();
        
        // Make threads mineable
        this.setupThreadMining();
        
        // Setup hash clicking functionality
        this.setupHashClicking();
        
        // Setup thread creation mining
        this.setupThreadCreationMining();
        
        // Setup reply form mining
        this.setupReplyFormMining();
    }
    
    setupImageMining() {
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            img.classList.add('mineable');
            img.addEventListener('mouseenter', () => this.startMining(img, 'image'));
            img.addEventListener('mouseleave', () => this.stopMining());
        });
    }
    
    setupThreadMining() {
        const threads = document.querySelectorAll('.catalog-thread, .post[data-thread-id]');
        threads.forEach(thread => {
            thread.classList.add('mineable');
            thread.addEventListener('mouseenter', () => this.startMining(thread, 'thread'));
            thread.addEventListener('mouseleave', () => this.stopMining());
        });
    }
    
    startMining(element, type) {
        if (!this.isActive) return;
        
        this.currentTarget = { element, type };
        this.updateTargetDisplay(element, type);
        this.addMiningVisuals(element);
        
        // Clear any existing mining
        clearInterval(this.miningInterval);
        
        // Start continuous mining
        this.mine(element, type);
        this.miningInterval = setInterval(() => {
            if (this.currentTarget && this.currentTarget.element === element) {
                this.mine(element, type);
            }
        }, 200);
    }
    
    stopMining() {
        clearInterval(this.miningInterval);
        this.currentTarget = null;
        this.updateTargetDisplay(null);
        this.removeMiningVisuals();
    }
    
    async mine(element, type) {
        try {
            const target = this.getMiningTarget(element, type);
            if (!target) return;
            
            const proof = await this.generateProof(target, type);
            if (proof) {
                await this.submitProof(proof, target, type);
                this.showProofAnimation(element, proof.points || 1);
                this.updateSessionStats('proofs', 1);
                this.updateSessionStats('points', proof.points || 1);
            }
            
            this.updateSessionStats('hashes', 1);
            this.triggerVisualization(proof?.hash, proof ? 'success' : 'normal');
            
        } catch (error) {
            console.warn('Mining error:', error);
        }
    }
    
    getMiningTarget(element, type) {
        if (type === 'image') {
            const libraryImage = element.closest('[data-image-id]');
            return libraryImage ? libraryImage.dataset.imageId : element.src.split('/').pop();
        } else if (type === 'thread') {
            return element.dataset.threadId;
        }
        return null;
    }
    
    async generateProof(target, type) {
        const data = `${type}-${target}-${Date.now()}`;
        const pattern = '21e8'; // Basic pattern
        let nonce = crypto.getRandomValues(new Uint32Array(1))[0];
        const maxAttempts = 1000;
        
        for (let attempts = 0; attempts < maxAttempts; attempts++) {
            const hashInput = `${data}:${nonce}`;
            const hash = await this.sha256(hashInput);
            
            // Check for rare patterns
            const rareMatch = this.checkRarePattern(hash);
            if (rareMatch) {
                return {
                    hash,
                    nonce,
                    data: hashInput,
                    pattern: rareMatch.pattern,
                    points: rareMatch.points
                };
            }
            
            // Check for basic pattern
            if (hash.startsWith(pattern)) {
                return {
                    hash,
                    nonce,
                    data: hashInput,
                    pattern,
                    points: 1
                };
            }
            
            nonce++;
            
            // Yield control periodically
            if (attempts % 50 === 0) {
                await new Promise(resolve => setTimeout(resolve, 1));
            }
        }
        
        return null;
    }
    
    async submitProof(proof, target, type) {
        const endpoint = type === 'image' ? '/api/image-library/mine' : '/api/submit-proof';
        const payload = type === 'image' 
            ? {
                image_id: target,
                proof_hash: proof.hash,
                proof_nonce: proof.nonce,
                proof_data: proof.data,
                proof_pattern: proof.pattern
            }
            : {
                hash: proof.hash,
                nonce: proof.nonce,
                data: proof.data,
                pattern: proof.pattern,
                target_type: 'thread',
                target_id: target
            };
        
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        });
        
        return await response.json();
    }
    
    checkRarePattern(hash) {
        const rarePatterns = {
            'deadbeef': { points: 3133, rarity: '🏆 LEGENDARY' },
            '1337': { points: 1337, rarity: '👑 ELITE' },
            '777': { points: 777, rarity: '🍀 LUCKY' },
            '666': { points: 666, rarity: '😈 CURSED' },
            '000': { points: 500, rarity: '⚡ RARE' },
            '111': { points: 400, rarity: '⚡ RARE' },
            'fff': { points: 300, rarity: '✨ UNCOMMON' }
        };
        
        for (const [pattern, data] of Object.entries(rarePatterns)) {
            if (hash.toLowerCase().startsWith(pattern)) {
                this.showRareHashNotification(pattern, hash, data);
                return { pattern, ...data };
            }
        }
        
        return null;
    }
    
    async sha256(message) {
        const msgBuffer = new TextEncoder().encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }
    
    // UI Methods
    updateTargetDisplay(element, type) {
        const targetDisplay = document.getElementById('toolbar-target');
        if (!targetDisplay) return;
        
        if (element && type) {
            const target = this.getMiningTarget(element, type);
            targetDisplay.textContent = `${type}: ${target}`;
        } else {
            targetDisplay.textContent = 'None';
        }
    }
    
    addMiningVisuals(element) {
        element.classList.add('mining-active');
        element.style.boxShadow = '0 0 10px rgba(255, 215, 0, 0.8)';
        element.style.border = '2px solid #FFD700';
    }
    
    removeMiningVisuals() {
        document.querySelectorAll('.mining-active').forEach(el => {
            el.classList.remove('mining-active');
            el.style.boxShadow = '';
            el.style.border = '';
        });
    }
    
    showProofAnimation(element, points) {
        const floater = document.createElement('div');
        floater.textContent = `⚡ +${points} PoW!`;
        floater.className = 'proof-animation';
        
        // Position relative to element
        const rect = element.getBoundingClientRect();
        floater.style.cssText = `
            position: fixed;
            left: ${rect.left + rect.width/2}px;
            top: ${rect.top + rect.height/2}px;
            transform: translate(-50%, -50%);
            background: rgba(154, 184, 122, 0.95);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            z-index: 10001;
            pointer-events: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        `;
        
        document.body.appendChild(floater);
        
        // Animate
        let start = Date.now();
        const animate = () => {
            const elapsed = Date.now() - start;
            const progress = elapsed / 2000;
            
            if (progress <= 1) {
                floater.style.transform = `translate(-50%, calc(-50% - ${50 * progress}px))`;
                floater.style.opacity = Math.max(0, 1 - progress);
                requestAnimationFrame(animate);
            } else {
                floater.remove();
            }
        };
        
        requestAnimationFrame(animate);
    }
    
    showRareHashNotification(pattern, hash, data) {
        const notificationArea = document.getElementById('rare-hash-notifications');
        const hashList = document.getElementById('rare-hash-list');
        
        if (notificationArea && hashList) {
            notificationArea.style.display = 'block';
            
            const notification = document.createElement('div');
            notification.className = 'rare-hash-notification';
            notification.innerHTML = `
                <div style="color: #FFD700; font-weight: bold;">
                    ${data.rarity} ${pattern.toUpperCase()}
                </div>
                <div style="color: #666; font-size: 10px;">
                    ${hash.substring(0, 16)}... (+${data.points} pts)
                </div>
            `;
            
            hashList.insertBefore(notification, hashList.firstChild);
            
            // Keep only 5 recent notifications
            while (hashList.children.length > 5) {
                hashList.removeChild(hashList.lastChild);
            }
        }
    }
    
    updateSessionStats(stat, value) {
        this.sessionStats[stat] += value;
        const element = document.getElementById(`session-${stat}`);
        if (element) {
            element.textContent = this.sessionStats[stat];
        }
    }
    
    toggleMining() {
        this.isActive = !this.isActive;
        const toggle = document.getElementById('mining-toggle');
        if (toggle) {
            toggle.textContent = `Auto-Mine: ${this.isActive ? 'ON' : 'OFF'}`;
        }
        
        if (!this.isActive) {
            this.stopMining();
        }
    }
    
    toggleDashboard() {
        const dashboard = document.getElementById('mining-dashboard');
        const content = dashboard.querySelector('.mining-dashboard__content');
        const toggle = document.getElementById('dashboard-toggle');
        
        if (content.style.display === 'none') {
            content.style.display = 'block';
            toggle.textContent = '_';
        } else {
            content.style.display = 'none';
            toggle.textContent = '+';
        }
    }
    
    initializeUI() {
        // Initialize dashboard as collapsed
        const content = document.querySelector('.mining-dashboard__content');
        if (content) {
            content.style.display = 'none';
        }
    }
    
    setupHashClicking() {
        // Setup copy-to-clipboard functionality for hashes
        document.addEventListener('click', (e) => {
            // Hash copy functionality (for image library)
            if (e.target.classList.contains('hash-copy')) {
                const hash = e.target.dataset.hash;
                if (hash) {
                    this.copyToClipboard(hash);
                    this.showCopyFeedback(e.target);
                }
            }
            
            // Clickable hashes in posts/threads for quoting
            if (e.target.classList.contains('post-hash') || e.target.classList.contains('thread-hash')) {
                const hash = e.target.textContent.trim();
                this.insertHashQuote(hash);
            }
        });
    }
    
    setupThreadCreationMining() {
        // Check if we're on thread creation page
        const threadMiner = document.getElementById('start-mining');
        if (!threadMiner) return;
        
        // Initialize thread creation mining system
        if (typeof ThreadCreationMiner === 'undefined') {
            this.initializeThreadCreationMiner();
        }
    }
    
    setupReplyFormMining() {
        // Setup mining for reply forms
        const replyForms = document.querySelectorAll('.reply-form, .post-form');
        replyForms.forEach(form => {
            const miningContainer = form.querySelector('.pow-mining-container');
            if (miningContainer) {
                this.initializeFormMining(form);
            }
        });
    }
    
    copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).catch(err => {
                console.warn('Failed to copy to clipboard:', err);
                this.fallbackCopyTextToClipboard(text);
            });
        } else {
            this.fallbackCopyTextToClipboard(text);
        }
    }
    
    fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
        } catch (err) {
            console.warn('Fallback: Could not copy text:', err);
        }
        
        document.body.removeChild(textArea);
    }
    
    showCopyFeedback(element) {
        const originalText = element.textContent;
        const originalColor = element.style.color;
        
        element.textContent = 'Copied!';
        element.style.color = '#28a745';
        element.style.fontWeight = 'bold';
        
        setTimeout(() => {
            element.textContent = originalText;
            element.style.color = originalColor;
            element.style.fontWeight = '';
        }, 1500);
    }
    
    insertHashQuote(hash) {
        // Find active textarea/input for inserting quote
        const activeElement = document.activeElement;
        let target = null;
        
        if (activeElement && (activeElement.tagName === 'TEXTAREA' || activeElement.tagName === 'INPUT')) {
            target = activeElement;
        } else {
            // Look for content textarea in forms
            target = document.querySelector('textarea[name="content"]') || 
                    document.querySelector('textarea[name="reply_content"]') ||
                    document.querySelector('#content') ||
                    document.querySelector('#reply_content');
        }
        
        if (target) {
            const quote = `>>${hash}\n`;
            const cursorPos = target.selectionStart;
            const textBefore = target.value.substring(0, cursorPos);
            const textAfter = target.value.substring(target.selectionEnd);
            
            target.value = textBefore + quote + textAfter;
            target.selectionStart = target.selectionEnd = cursorPos + quote.length;
            target.focus();
            
            // Show feedback
            this.showQuoteFeedback(target);
        }
    }
    
    showQuoteFeedback(element) {
        const feedback = document.createElement('div');
        feedback.textContent = 'Hash quoted!';
        feedback.style.cssText = `
            position: absolute;
            background: rgba(40, 167, 69, 0.9);
            color: white;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            z-index: 10000;
            pointer-events: none;
        `;
        
        const rect = element.getBoundingClientRect();
        feedback.style.left = rect.left + 'px';
        feedback.style.top = (rect.top - 30) + 'px';
        
        document.body.appendChild(feedback);
        
        setTimeout(() => {
            feedback.remove();
        }, 2000);
    }
    
    initializeThreadCreationMiner() {
        // Enhanced thread creation mining integration
        const startBtn = document.getElementById('start-mining');
        const stopBtn = document.getElementById('stop-mining');
        const submitBtn = document.getElementById('submit-thread');
        const statusEl = document.getElementById('mining-status');
        const hashRateEl = document.getElementById('hash-rate');
        const currentHashEl = document.getElementById('current-hash');
        const progressBar = document.getElementById('mining-progress-bar');
        
        if (!startBtn) return;
        
        let threadMiner = {
            isRunning: false,
            startTime: 0,
            hashCount: 0,
            
            async start() {
                this.isRunning = true;
                this.startTime = Date.now();
                this.hashCount = 0;
                
                startBtn.disabled = true;
                stopBtn.disabled = false;
                statusEl.textContent = '🔄 Mining...';
                statusEl.style.color = '#ffc107';
                
                const challengeId = document.getElementById('pow_challenge_id').value;
                const proof = await this.mineProof(challengeId);
                
                if (proof && this.isRunning) {
                    document.getElementById('pow_nonce').value = proof.nonce;
                    document.getElementById('pow_hash').value = proof.hash;
                    
                    statusEl.textContent = '✅ Proof Found!';
                    statusEl.style.color = '#28a745';
                    submitBtn.disabled = false;
                    submitBtn.style.background = '#28a745';
                    submitBtn.style.color = 'white';
                    submitBtn.textContent = 'Create Thread';
                    
                    progressBar.style.width = '100%';
                    progressBar.style.background = '#28a745';
                }
                
                startBtn.disabled = false;
                stopBtn.disabled = true;
            },
            
            stop() {
                this.isRunning = false;
                statusEl.textContent = '⛔ Stopped';
                statusEl.style.color = '#dc3545';
                startBtn.disabled = false;
                stopBtn.disabled = true;
            },
            
            async mineProof(challengeId) {
                const pattern = '21e8';
                let nonce = Math.floor(Math.random() * 1000000);
                const maxAttempts = 100000;
                
                for (let attempts = 0; attempts < maxAttempts && this.isRunning; attempts++) {
                    const data = `${challengeId}:${nonce}`;
                    const hash = await this.sha256(data);
                    
                    this.hashCount++;
                    currentHashEl.textContent = hash.substring(0, 16) + '...';
                    
                    // Update progress and hash rate
                    if (attempts % 50 === 0) {
                        const elapsed = (Date.now() - this.startTime) / 1000;
                        const hashRate = Math.floor(this.hashCount / elapsed);
                        hashRateEl.textContent = hashRate + ' H/s';
                        
                        const progress = (attempts / maxAttempts) * 100;
                        progressBar.style.width = progress + '%';
                        
                        // Yield control
                        await new Promise(resolve => setTimeout(resolve, 1));
                    }
                    
                    if (hash.startsWith(pattern)) {
                        return { hash, nonce, data };
                    }
                    
                    nonce++;
                }
                
                return null;
            },
            
            async sha256(message) {
                const msgBuffer = new TextEncoder().encode(message);
                const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            }
        };
        
        startBtn.addEventListener('click', () => threadMiner.start());
        stopBtn.addEventListener('click', () => threadMiner.stop());
    }
    
    initializeFormMining(form) {
        // Initialize mining for reply forms
        const miningBtn = form.querySelector('.start-form-mining');
        const submitBtn = form.querySelector('button[type="submit"]');
        
        if (!miningBtn || !submitBtn) return;
        
        miningBtn.addEventListener('click', async () => {
            miningBtn.disabled = true;
            miningBtn.textContent = '⛏️ Mining...';
            
            const proof = await this.generateFormProof();
            
            if (proof) {
                const nonceField = form.querySelector('input[name="pow_nonce"]') || this.createHiddenField(form, 'pow_nonce');
                const hashField = form.querySelector('input[name="pow_hash"]') || this.createHiddenField(form, 'pow_hash');
                
                nonceField.value = proof.nonce;
                hashField.value = proof.hash;
                
                submitBtn.disabled = false;
                submitBtn.style.background = '#28a745';
                submitBtn.style.color = 'white';
                
                miningBtn.textContent = '✅ Proof Complete';
                miningBtn.style.background = '#28a745';
            }
        });
    }
    
    createHiddenField(form, name) {
        const field = document.createElement('input');
        field.type = 'hidden';
        field.name = name;
        form.appendChild(field);
        return field;
    }
    
    async generateFormProof() {
        const challengeId = 'form-' + Date.now();
        const pattern = '21e8';
        let nonce = Math.floor(Math.random() * 1000000);
        
        for (let attempts = 0; attempts < 10000; attempts++) {
            const data = `${challengeId}:${nonce}`;
            const hash = await this.sha256(data);
            
            if (hash.startsWith(pattern)) {
                return { hash, nonce, data };
            }
            
            nonce++;
            
            if (attempts % 100 === 0) {
                await new Promise(resolve => setTimeout(resolve, 1));
            }
        }
        
        return null;
    }
    
    startNeuralCanvas() {
        const canvas = document.getElementById('neural-mining-canvas');
        if (!canvas) return;
        
        // Simple neural network visualization
        const ctx = canvas.getContext('2d');
        let nodes = [];
        
        // Create nodes
        for (let i = 0; i < 20; i++) {
            nodes.push({
                x: Math.random() * window.innerWidth,
                y: Math.random() * window.innerHeight,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                activity: 0
            });
        }
        
        const animate = () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // Update and draw nodes
            nodes.forEach(node => {
                node.x += node.vx;
                node.y += node.vy;
                node.activity = Math.max(0, node.activity - 0.01);
                
                // Bounce off edges
                if (node.x <= 0 || node.x >= canvas.width) node.vx *= -1;
                if (node.y <= 0 || node.y >= canvas.height) node.vy *= -1;
                
                // Draw node
                ctx.fillStyle = `rgba(154, 184, 122, ${0.1 + node.activity * 0.5})`;
                ctx.beginPath();
                ctx.arc(node.x, node.y, 3 + node.activity * 3, 0, Math.PI * 2);
                ctx.fill();
            });
            
            requestAnimationFrame(animate);
        };
        
        animate();
        
        // Expose trigger function
        this.triggerVisualization = (hash, type = 'normal') => {
            const node = nodes[Math.floor(Math.random() * nodes.length)];
            node.activity = type === 'success' ? 1.0 : 0.3;
        };
    }
}

// Initialize global mining system
function initializeGlobalMining() {
    if (!window.haichanMiner) {
        window.haichanMiner = new HaichanMiner();
    }
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeGlobalMining);
} else {
    initializeGlobalMining();
}