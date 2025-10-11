/**
 * Simple Working Mining System
 * Replaces the broken mining brain with basic functionality
 */

class SimpleMiner {
    constructor() {
        this.isActive = false;
        this.currentTarget = null;
        this.stats = {
            totalHashes: 0,
            proofs: 0,
            points: 0
        };
        
        console.log('🔨 Simple Miner: Initialized');
        this.setupMouseoverMining();
    }
    
    setupMouseoverMining() {
        // Mine on hover for posts and threads
        document.addEventListener('mouseover', (e) => {
            const target = e.target.closest('[data-mine-type]');
            if (target && this.currentTarget !== target) {
                this.startMining(target);
            }
        });
        
        document.addEventListener('mouseout', (e) => {
            const target = e.target.closest('[data-mine-type]');
            if (target && this.currentTarget === target) {
                this.stopMining();
            }
        });
    }
    
    async startMining(element) {
        this.currentTarget = element;
        const mineType = element.dataset.mineType;
        const threadId = element.dataset.threadId;
        const postId = element.dataset.postId;
        const boardCode = element.dataset.boardCode;
        
        if (!mineType) return;
        
        console.log(`🔨 Mining ${mineType}:`, {threadId, postId, boardCode});
        
        try {
            await this.mineTarget(mineType, threadId, postId, boardCode);
        } catch (error) {
            console.error('Mining failed:', error);
        }
    }
    
    stopMining() {
        this.currentTarget = null;
    }
    
    async mineTarget(type, threadId, postId, boardCode) {
        // Simple mining for basic patterns
        const targetPatterns = ['21', '21e', '111', '666', '777'];
        const pattern = targetPatterns[Math.floor(Math.random() * targetPatterns.length)];
        
        const data = `${type}-${threadId || postId}-${Date.now()}`;
        let nonce = Math.floor(Math.random() * 1000000);
        
        // Quick mining loop (limited to prevent freezing)
        for (let i = 0; i < 1000; i++) {
            const hash = await this.sha256(data + '-' + (nonce + i));
            
            if (hash.startsWith(pattern)) {
                console.log(`⚡ Found proof! Pattern: ${pattern}, Hash: ${hash}`);
                
                // Submit proof to server
                await this.submitProof({
                    hash: hash,
                    nonce: nonce + i,
                    data: data,
                    pattern: pattern,
                    points: this.calculatePoints(pattern),
                    target_type: type,
                    target_id: threadId || postId
                });
                
                this.stats.proofs++;
                this.stats.points += this.calculatePoints(pattern);
                break;
            }
            
            this.stats.totalHashes++;
        }
    }
    
    async sha256(text) {
        const encoder = new TextEncoder();
        const data = encoder.encode(text);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }
    
    calculatePoints(pattern) {
        const pointMap = {
            '21': 0.1,
            '21e': 0.5,
            '21e8': 100,
            '111': 400,
            '666': 666,
            '777': 777,
            '000': 500
        };
        return pointMap[pattern] || 0.1;
    }
    
    async submitProof(proof) {
        try {
            const response = await fetch('/api/proof-submissions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(proof)
            });
            
            if (response.ok) {
                console.log('✅ Proof submitted successfully');
                this.showSuccessEffect();
            } else {
                console.log('❌ Proof submission failed');
            }
        } catch (error) {
            console.error('Proof submission error:', error);
        }
    }
    
    showSuccessEffect() {
        // Simple visual feedback
        if (this.currentTarget) {
            const original = this.currentTarget.style.boxShadow;
            this.currentTarget.style.boxShadow = '0 0 10px #4CAF50';
            setTimeout(() => {
                if (this.currentTarget) {
                    this.currentTarget.style.boxShadow = original;
                }
            }, 500);
        }
    }
    
    getStats() {
        return this.stats;
    }
}

// Initialize simple miner when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.simpleMiner = new SimpleMiner();
    });
} else {
    window.simpleMiner = new SimpleMiner();
}

// Backup initialization
setTimeout(() => {
    if (!window.simpleMiner) {
        window.simpleMiner = new SimpleMiner();
    }
}, 1000);