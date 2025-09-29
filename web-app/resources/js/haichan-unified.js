/**
 * HAICHAN UNIFIED MINING SYSTEM
 * Complete rewrite combining all mining functionality
 * Provides consistent, efficient mining across all components
 */

class HaichanUnified {
    constructor() {
        this.isInitialized = false;
        this.activeMiningTargets = new Map();
        this.sessionStats = {
            hashes: 0,
            proofs: 0,
            points: 0,
            startTime: Date.now()
        };
        this.patterns = {
            basic: { pattern: '21e8', points: 1, color: '#9fd971' },
            rare: {
                '777': { points: 777, color: '#FFD700', rarity: '🍀 LUCKY' },
                '666': { points: 666, color: '#FF4444', rarity: '😈 CURSED' },
                '000': { points: 500, color: '#00FFFF', rarity: '⚡ RARE' },
                '111': { points: 400, color: '#FF69B4', rarity: '⚡ RARE' },
                'deadbeef': { points: 5000, color: '#FFD700', rarity: '🏆 LEGENDARY' },
                '1337': { points: 2500, color: '#FF6B35', rarity: '👑 ELITE' }
            }
        };
        
        this.init();
    }

    init() {
        if (this.isInitialized) return;
        this.isInitialized = true;

        this.createMiningDashboard();
        this.setupGlobalMining();
        this.setupFormMining();
        this.setupEventListeners();
        
        console.log('🚀 Haichan Unified Mining System initialized');
    }

    createMiningDashboard() {
        // Remove existing dashboards to avoid conflicts
        const existing = document.getElementById('unified-mining-dashboard');
        if (existing) existing.remove();

        const dashboard = document.createElement('div');
        dashboard.id = 'unified-mining-dashboard';
        dashboard.className = 'unified-dashboard';
        dashboard.innerHTML = `
            <div class="dashboard-header">
                <div class="dashboard-title">⛏️ HAICHAN MINING</div>
                <div class="dashboard-controls">
                    <button id="mining-toggle" class="toggle-btn">ON</button>
                    <button id="dashboard-collapse" class="collapse-btn">_</button>
                </div>
            </div>
            <div class="dashboard-content" id="dashboard-content">
                <div class="mining-stats">
                    <div class="stat-group">
                        <div class="stat-item">
                            <span class="stat-label">Hash Rate</span>
                            <span class="stat-value" id="hash-rate">0 H/s</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Session Hashes</span>
                            <span class="stat-value" id="session-hashes">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Proofs Found</span>
                            <span class="stat-value" id="session-proofs">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Points Earned</span>
                            <span class="stat-value" id="session-points">0</span>
                        </div>
                    </div>
                </div>
                <div class="current-target">
                    <div class="target-label">Current Target:</div>
                    <div class="target-value" id="current-target">Hover over content to mine</div>
                </div>
                <div class="rare-finds" id="rare-finds" style="display: none;">
                    <div class="rare-title">🎯 Rare Finds</div>
                    <div class="rare-list" id="rare-list"></div>
                </div>
            </div>
        `;

        // Add CSS styles
        const style = document.createElement('style');
        style.textContent = `
            .unified-dashboard {
                position: fixed;
                top: 20px;
                right: 20px;
                width: 300px;
                background: var(--ib-panel);
                border: 2px solid var(--ib-border);
                border-radius: 8px;
                font-family: 'Courier New', monospace;
                font-size: 11px;
                z-index: 10000;
                box-shadow: 0 8px 32px rgba(0,0,0,0.4);
                transition: all 0.3s ease;
            }

            .dashboard-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 12px;
                background: var(--ib-accent);
                color: var(--ib-bg);
                font-weight: bold;
                border-radius: 6px 6px 0 0;
            }

            .dashboard-title {
                font-size: 12px;
                letter-spacing: 1px;
            }

            .dashboard-controls {
                display: flex;
                gap: 4px;
            }

            .toggle-btn, .collapse-btn {
                background: var(--ib-bg);
                color: var(--ib-accent);
                border: 1px solid var(--ib-accent);
                padding: 2px 6px;
                border-radius: 3px;
                cursor: pointer;
                font-size: 9px;
                font-weight: bold;
            }

            .dashboard-content {
                padding: 12px;
                background: var(--ib-panel);
                border-radius: 0 0 6px 6px;
            }

            .mining-stats {
                margin-bottom: 12px;
            }

            .stat-group {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .stat-item {
                display: flex;
                justify-content: space-between;
                padding: 4px;
                background: var(--ib-bg);
                border: 1px solid var(--ib-border);
                border-radius: 3px;
            }

            .stat-label {
                color: var(--ib-text-muted);
                font-size: 9px;
            }

            .stat-value {
                color: var(--ib-accent);
                font-weight: bold;
            }

            .current-target {
                padding: 8px;
                background: var(--ib-bg);
                border: 1px solid var(--ib-border);
                border-radius: 3px;
                margin-bottom: 8px;
            }

            .target-label {
                color: var(--ib-text-muted);
                font-size: 9px;
                margin-bottom: 2px;
            }

            .target-value {
                color: var(--ib-text);
                font-weight: bold;
            }

            .rare-finds {
                padding: 8px;
                background: var(--ib-bg);
                border: 1px solid var(--ib-border);
                border-radius: 3px;
            }

            .rare-title {
                color: var(--ib-accent);
                font-weight: bold;
                margin-bottom: 4px;
                font-size: 10px;
            }

            .rare-item {
                padding: 4px;
                margin: 2px 0;
                background: rgba(159, 217, 113, 0.1);
                border-radius: 2px;
                font-size: 9px;
            }

            .mineable {
                transition: all 0.2s ease;
                cursor: crosshair;
            }

            .mining-active {
                box-shadow: 0 0 12px var(--ib-accent) !important;
                border-color: var(--ib-accent) !important;
                transform: scale(1.02);
            }

            .proof-float {
                position: fixed;
                background: var(--ib-accent);
                color: var(--ib-bg);
                padding: 4px 8px;
                border-radius: 4px;
                font-weight: bold;
                font-size: 10px;
                pointer-events: none;
                z-index: 10001;
                animation: floatUp 2s ease-out forwards;
            }

            @keyframes floatUp {
                0% { opacity: 1; transform: translateY(0); }
                100% { opacity: 0; transform: translateY(-50px); }
            }

            /* Mobile responsive */
            @media (max-width: 768px) {
                .unified-dashboard {
                    width: 280px;
                    top: 10px;
                    right: 10px;
                }
                
                .stat-group {
                    grid-template-columns: 1fr;
                }
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(dashboard);
        
        // Initialize as collapsed on mobile
        if (window.innerWidth <= 768) {
            this.toggleDashboard();
        }
    }

    setupEventListeners() {
        document.getElementById('mining-toggle')?.addEventListener('click', () => this.toggleMining());
        document.getElementById('dashboard-collapse')?.addEventListener('click', () => this.toggleDashboard());
        
        // Update stats every second
        setInterval(() => this.updateDashboardStats(), 1000);
    }

    setupGlobalMining() {
        // Make images mineable
        this.makeMineable('img', 'image');
        
        // Make posts/threads mineable
        this.makeMineable('.post, .catalog-thread, .thread-preview', 'content');
        
        // Make text areas auto-mine after typing
        this.setupTextAreaMining();
    }

    makeMineable(selector, type) {
        document.querySelectorAll(selector).forEach(element => {
            if (element.classList.contains('mineable')) return; // Already processed
            
            element.classList.add('mineable');
            element.addEventListener('mouseenter', () => this.startMining(element, type));
            element.addEventListener('mouseleave', () => this.stopMining(element));
        });
    }

    setupTextAreaMining() {
        document.querySelectorAll('textarea').forEach(textarea => {
            let timeout;
            textarea.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    if (textarea.value.length > 10) {
                        this.startContentMining(textarea);
                    }
                }, 1000);
            });
        });
    }

    startMining(element, type) {
        if (!this.isMiningEnabled()) return;

        const miningId = this.generateMiningId();
        this.activeMiningTargets.set(miningId, {
            element,
            type,
            startTime: Date.now()
        });

        element.classList.add('mining-active');
        this.updateCurrentTarget(element, type);
        this.mineTarget(miningId, element, type);
    }

    stopMining(element) {
        element.classList.remove('mining-active');
        
        // Remove from active targets
        for (const [id, target] of this.activeMiningTargets.entries()) {
            if (target.element === element) {
                this.activeMiningTargets.delete(id);
                break;
            }
        }

        if (this.activeMiningTargets.size === 0) {
            this.updateCurrentTarget(null, null);
        }
    }

    async mineTarget(miningId, element, type) {
        if (!this.activeMiningTargets.has(miningId)) return;

        try {
            const target = this.getMiningData(element, type);
            const proof = await this.generateProof(target, type);
            
            if (proof && this.activeMiningTargets.has(miningId)) {
                await this.handleProofFound(proof, element, type);
                this.sessionStats.proofs++;
                this.sessionStats.points += proof.points;
                this.showProofAnimation(element, proof);
            }
            
            this.sessionStats.hashes++;
            
            // Continue mining if still active
            if (this.activeMiningTargets.has(miningId)) {
                setTimeout(() => this.mineTarget(miningId, element, type), 100);
            }
        } catch (error) {
            console.warn('Mining error:', error);
        }
    }

    getMiningData(element, type) {
        switch (type) {
            case 'image':
                return element.src || element.dataset.imageId || 'unknown';
            case 'content':
                return element.textContent?.substring(0, 100) || element.dataset.threadId || 'content';
            default:
                return 'generic';
        }
    }

    async generateProof(target, type) {
        const data = `${type}:${target}:${Date.now()}`;
        const nonce = Math.floor(Math.random() * 1000000);
        const input = `${data}:${nonce}`;
        const hash = await this.sha256(input);

        // Check for rare patterns first
        for (const [pattern, config] of Object.entries(this.patterns.rare)) {
            if (hash.startsWith(pattern.toLowerCase())) {
                return {
                    hash,
                    nonce,
                    data: input,
                    pattern,
                    points: config.points,
                    rarity: config.rarity,
                    color: config.color
                };
            }
        }

        // Check basic pattern
        if (hash.startsWith(this.patterns.basic.pattern)) {
            return {
                hash,
                nonce,
                data: input,
                pattern: this.patterns.basic.pattern,
                points: this.patterns.basic.points,
                color: this.patterns.basic.color
            };
        }

        return null;
    }

    async handleProofFound(proof, element, type) {
        // Submit proof to server
        try {
            const response = await fetch('/api/mining/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    hash: proof.hash,
                    nonce: proof.nonce,
                    data: proof.data,
                    pattern: proof.pattern,
                    type: type,
                    points: proof.points
                })
            });

            if (response.ok) {
                const result = await response.json();
                if (proof.rarity) {
                    this.addRareFind(proof);
                }
            }
        } catch (error) {
            console.warn('Proof submission failed:', error);
        }
    }

    addRareFind(proof) {
        const rareFinds = document.getElementById('rare-finds');
        const rareList = document.getElementById('rare-list');
        
        if (!rareFinds || !rareList) return;

        rareFinds.style.display = 'block';
        
        const item = document.createElement('div');
        item.className = 'rare-item';
        item.style.color = proof.color;
        item.innerHTML = `
            <div>${proof.rarity} ${proof.pattern.toUpperCase()}</div>
            <div style="font-size: 8px; opacity: 0.7;">${proof.hash.substring(0, 16)}... (+${proof.points})</div>
        `;
        
        rareList.insertBefore(item, rareList.firstChild);
        
        // Keep only last 5 finds
        while (rareList.children.length > 5) {
            rareList.removeChild(rareList.lastChild);
        }
    }

    showProofAnimation(element, proof) {
        const rect = element.getBoundingClientRect();
        const float = document.createElement('div');
        float.className = 'proof-float';
        float.textContent = `+${proof.points} ⚡`;
        float.style.left = (rect.left + rect.width / 2) + 'px';
        float.style.top = (rect.top + rect.height / 2) + 'px';
        
        document.body.appendChild(float);
        setTimeout(() => float.remove(), 2000);
    }

    updateCurrentTarget(element, type) {
        const targetElement = document.getElementById('current-target');
        if (!targetElement) return;

        if (element && type) {
            const target = this.getMiningData(element, type);
            targetElement.textContent = `${type}: ${target.substring(0, 30)}${target.length > 30 ? '...' : ''}`;
        } else {
            targetElement.textContent = 'Hover over content to mine';
        }
    }

    updateDashboardStats() {
        const elapsed = (Date.now() - this.sessionStats.startTime) / 1000;
        const hashRate = Math.floor(this.sessionStats.hashes / elapsed);

        document.getElementById('hash-rate').textContent = `${hashRate} H/s`;
        document.getElementById('session-hashes').textContent = this.sessionStats.hashes.toLocaleString();
        document.getElementById('session-proofs').textContent = this.sessionStats.proofs.toLocaleString();
        document.getElementById('session-points').textContent = this.sessionStats.points.toLocaleString();
    }

    toggleMining() {
        const button = document.getElementById('mining-toggle');
        const isEnabled = button.textContent === 'ON';
        
        button.textContent = isEnabled ? 'OFF' : 'ON';
        button.style.background = isEnabled ? '#dc3545' : '#28a745';
        
        if (isEnabled) {
            // Stop all active mining
            this.activeMiningTargets.clear();
            document.querySelectorAll('.mining-active').forEach(el => {
                el.classList.remove('mining-active');
            });
        }
    }

    toggleDashboard() {
        const content = document.getElementById('dashboard-content');
        const button = document.getElementById('dashboard-collapse');
        
        if (content.style.display === 'none') {
            content.style.display = 'block';
            button.textContent = '_';
        } else {
            content.style.display = 'none';
            button.textContent = '+';
        }
    }

    isMiningEnabled() {
        return document.getElementById('mining-toggle')?.textContent === 'ON';
    }

    generateMiningId() {
        return Date.now() + '_' + Math.random().toString(36).substring(7);
    }

    async sha256(message) {
        const msgBuffer = new TextEncoder().encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    // Form mining methods
    setupFormMining() {
        this.observeFormChanges();
        this.setupFormMiningButtons();
    }

    observeFormChanges() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const forms = node.querySelectorAll ? node.querySelectorAll('form') : [];
                        forms.forEach(form => this.enhanceForm(form));
                        
                        if (node.tagName === 'FORM') {
                            this.enhanceForm(node);
                        }
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
        
        // Enhance existing forms
        document.querySelectorAll('form').forEach(form => this.enhanceForm(form));
    }

    enhanceForm(form) {
        if (form.dataset.haichanEnhanced) return;
        form.dataset.haichanEnhanced = 'true';

        // Add mining section for forms that need PoW
        const powFields = form.querySelectorAll('input[name*="pow_"]');
        if (powFields.length > 0) {
            this.addFormMiningUI(form);
        }
    }

    addFormMiningUI(form) {
        const existingMining = form.querySelector('.form-mining-section');
        if (existingMining) return;

        const miningSection = document.createElement('div');
        miningSection.className = 'form-mining-section';
        miningSection.innerHTML = `
            <div class="form-mining-header">
                <span class="mining-label">⛏️ Proof of Work</span>
                <button type="button" class="start-mining-btn">Start Mining</button>
            </div>
            <div class="mining-status" id="form-mining-status-${form.id || 'form'}">Ready to mine</div>
            <div class="mining-progress">
                <div class="progress-bar" id="form-progress-${form.id || 'form'}"></div>
            </div>
        `;

        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            .form-mining-section {
                background: var(--ib-panel);
                border: 2px solid var(--ib-border);
                padding: 12px;
                margin: 10px 0;
                border-radius: 4px;
            }
            .form-mining-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 8px;
            }
            .mining-label {
                font-weight: bold;
                color: var(--ib-accent);
            }
            .start-mining-btn {
                background: var(--ib-accent);
                color: var(--ib-bg);
                border: none;
                padding: 4px 8px;
                border-radius: 3px;
                cursor: pointer;
                font-size: 10px;
            }
            .mining-status {
                font-size: 11px;
                color: var(--ib-text-muted);
                margin-bottom: 4px;
            }
            .mining-progress {
                height: 6px;
                background: var(--ib-bg);
                border: 1px solid var(--ib-border);
                overflow: hidden;
            }
            .progress-bar {
                height: 100%;
                background: var(--ib-accent);
                width: 0%;
                transition: width 0.3s ease;
            }
        `;
        
        if (!document.head.querySelector('#form-mining-styles')) {
            style.id = 'form-mining-styles';
            document.head.appendChild(style);
        }

        // Insert before submit button
        const submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');
        if (submitBtn) {
            submitBtn.parentNode.insertBefore(miningSection, submitBtn);
        } else {
            form.appendChild(miningSection);
        }

        this.setupFormMiningButton(form, miningSection);
    }

    setupFormMiningButton(form, miningSection) {
        const button = miningSection.querySelector('.start-mining-btn');
        const statusEl = miningSection.querySelector('.mining-status');
        const progressEl = miningSection.querySelector('.progress-bar');
        const submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');

        button.addEventListener('click', async () => {
            button.disabled = true;
            button.textContent = 'Mining...';
            statusEl.textContent = 'Mining proof of work...';

            try {
                const proof = await this.mineFormProof(form);
                
                if (proof) {
                    // Set hidden fields
                    this.setFormField(form, 'pow_nonce', proof.nonce);
                    this.setFormField(form, 'pow_hash', proof.hash);
                    this.setFormField(form, 'pow_challenge_id', proof.challengeId);

                    button.textContent = '✅ Complete';
                    button.style.background = '#28a745';
                    statusEl.textContent = `Proof found: ${proof.hash.substring(0, 16)}...`;
                    progressEl.style.width = '100%';
                    progressEl.style.background = '#28a745';

                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.style.background = '#28a745';
                    }
                } else {
                    throw new Error('Mining failed');
                }
            } catch (error) {
                button.textContent = '❌ Failed';
                button.style.background = '#dc3545';
                statusEl.textContent = 'Mining failed. Try again.';
                button.disabled = false;
            }
        });

        // Disable submit initially if PoW required
        if (submitBtn && !form.querySelector('input[name="pow_hash"]')?.value) {
            submitBtn.disabled = true;
            submitBtn.style.background = '#6c757d';
        }
    }

    async mineFormProof(form) {
        const challengeId = this.generateChallengeId();
        const formData = new FormData(form);
        const content = formData.get('content') || formData.get('reply_content') || formData.get('title') || '';
        const data = `form:${content.substring(0, 50)}:${challengeId}`;
        
        let nonce = 0;
        const maxAttempts = 50000;
        
        for (let i = 0; i < maxAttempts; i++) {
            const input = `${data}:${nonce}`;
            const hash = await this.sha256(input);
            
            if (hash.startsWith('21e8')) {
                return {
                    hash,
                    nonce,
                    challengeId,
                    data: input
                };
            }
            
            nonce++;
            
            // Update progress
            if (i % 1000 === 0) {
                const progress = (i / maxAttempts) * 100;
                const progressEl = form.querySelector('.progress-bar');
                if (progressEl) progressEl.style.width = progress + '%';
                
                // Yield control
                await new Promise(resolve => setTimeout(resolve, 1));
            }
        }
        
        return null;
    }

    generateChallengeId() {
        return Array.from(crypto.getRandomValues(new Uint8Array(16)), 
            b => b.toString(16).padStart(2, '0')).join('');
    }

    setFormField(form, name, value) {
        let field = form.querySelector(`input[name="${name}"]`);
        if (!field) {
            field = document.createElement('input');
            field.type = 'hidden';
            field.name = name;
            form.appendChild(field);
        }
        field.value = value;
    }

    setupFormMiningButtons() {
        // Setup existing mining buttons
        document.querySelectorAll('.start-form-mining').forEach(button => {
            if (button.dataset.unified) return;
            button.dataset.unified = 'true';
            
            button.addEventListener('click', async (e) => {
                e.preventDefault();
                const form = button.closest('form');
                if (form) {
                    await this.handleFormMining(form, button);
                }
            });
        });
    }

    async handleFormMining(form, button) {
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Mining...';
        
        try {
            const proof = await this.mineFormProof(form);
            
            if (proof) {
                this.setFormField(form, 'pow_nonce', proof.nonce);
                this.setFormField(form, 'pow_hash', proof.hash);
                
                button.textContent = '✅ Complete';
                button.style.background = '#28a745';
                
                const submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.style.background = '#28a745';
                }
            }
        } catch (error) {
            button.textContent = '❌ Failed';
            button.style.background = '#dc3545';
            setTimeout(() => {
                button.textContent = originalText;
                button.style.background = '';
                button.disabled = false;
            }, 3000);
        }
    }

    // Auto-mining for content areas
    startContentMining(element) {
        if (!this.isMiningEnabled()) return;
        
        const content = element.value || element.textContent;
        if (!content || content.length < 10) return;
        
        const miningId = this.generateMiningId();
        this.activeMiningTargets.set(miningId, {
            element,
            type: 'content-auto',
            startTime: Date.now()
        });
        
        this.mineTarget(miningId, element, 'content-auto');
    }
}

// Global initialization
function initializeHaichanUnified() {
    // Clean up any existing miners to prevent conflicts
    if (window.haichanMiner) {
        console.log('Replacing existing mining system with unified system');
    }
    
    window.haichanUnified = new HaichanUnified();
    window.haichanMiner = window.haichanUnified; // Backward compatibility
    
    // Expose global functions for backward compatibility
    window.generateFormProof = () => window.haichanUnified.mineFormProof(document.activeElement?.closest('form'));
}

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeHaichanUnified);
} else {
    initializeHaichanUnified();
}

// Reinitialize when new content is added
const reinitialize = () => {
    if (window.haichanUnified) {
        window.haichanUnified.makeMineable('img:not(.mineable)', 'image');
        window.haichanUnified.makeMineable('.post:not(.mineable), .catalog-thread:not(.mineable)', 'content');
        window.haichanUnified.setupTextAreaMining();
        window.haichanUnified.setupFormMiningButtons();
    }
};

// Listen for dynamic content changes
const observer = new MutationObserver(reinitialize);
observer.observe(document.body, { childList: true, subtree: true });