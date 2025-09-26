// EMERGENCY REBUILD - ULTRA SIMPLE MINING SYSTEM
console.log('🔥 EMERGENCY MINING SYSTEM LOADING...');

class EmergencyMiner {
    constructor() {
        this.isActive = false;
        this.power = 30;
        this.hashCount = 0;
        this.proofs = 0;
        this.currentHash = '';
        this.targetType = 'global';
        this.targetId = 'emergency';
        this.nonce = 0;
        
        console.log('⚡ EMERGENCY MINER CREATED');
        this.init();
    }
    
    init() {
        // Get saved power
        const saved = localStorage.getItem('emergency_power');
        if (saved) {
            this.power = parseInt(saved);
        }
        
        // Get saved proofs
        const proofs = localStorage.getItem('emergency_proofs');
        if (proofs) {
            this.proofs = parseInt(proofs);
        }
        
        console.log('🎯 POWER LEVEL:', this.power);
        
        // Auto-start if power > 0
        if (this.power > 0) {
            setTimeout(() => this.start(), 1000);
        }
        
        this.updateDashboard();
        this.setupSlider();
    }
    
    setupSlider() {
        setTimeout(() => {
            const slider = document.getElementById('dashboard-power-slider');
            if (slider) {
                console.log('🎚️ SLIDER FOUND, SETTING UP...');
                slider.value = Math.ceil(this.power / 10);
                
                // Remove all old listeners
                const newSlider = slider.cloneNode(true);
                slider.parentNode.replaceChild(newSlider, slider);
                
                // Add new listener
                newSlider.addEventListener('input', (e) => {
                    const level = parseInt(e.target.value);
                    this.power = level * 10;
                    localStorage.setItem('emergency_power', this.power.toString());
                    
                    console.log('🔥 EMERGENCY SLIDER CHANGED TO:', this.power + '%');
                    
                    if (this.power > 0) {
                        this.start();
                    } else {
                        this.stop();
                    }
                    
                    this.updateDashboard();
                });
                
                console.log('✅ EMERGENCY SLIDER CONNECTED');
            } else {
                console.log('❌ SLIDER NOT FOUND');
            }
        }, 1000);
    }
    
    start() {
        if (this.isActive) return;
        
        console.log('🔥 STARTING EMERGENCY MINING AT', this.power + '%');
        this.isActive = true;
        this.startTime = Date.now();
        this.mine();
        this.updateDashboard();
    }
    
    stop() {
        console.log('⏹️ STOPPING MINING');
        this.isActive = false;
        this.updateDashboard();
    }
    
    async mine() {
        if (!this.isActive) return;
        
        console.log('⛏️ MINING STEP...');
        
        // Do 10-100 hashes based on power
        const batch = Math.max(10, this.power);
        
        for (let i = 0; i < batch && this.isActive; i++) {
            const data = `emergency:${this.targetType}:${this.targetId}:${this.nonce}`;
            this.currentHash = await this.sha256(data);
            this.hashCount++;
            this.nonce++;
            
            // Check for proof
            if (this.currentHash.startsWith('21e8')) {
                console.log('💎 PROOF FOUND!', this.currentHash);
                await this.submitProof(data, this.nonce - 1, this.currentHash);
                this.proofs++;
                localStorage.setItem('emergency_proofs', this.proofs.toString());
            }
        }
        
        this.updateDashboard();
        
        // Continue mining with delay based on power
        if (this.isActive) {
            const delay = Math.max(50, 200 - this.power);
            setTimeout(() => this.mine(), delay);
        }
    }
    
    async sha256(text) {
        const encoder = new TextEncoder();
        const data = encoder.encode(text);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }
    
    async submitProof(data, nonce, hash) {
        try {
            console.log('📤 SUBMITTING PROOF...');
            const response = await fetch('/api/submit-proof', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    hash: hash,
                    nonce: nonce,
                    data: data,
                    pattern: '21e8',
                    target_type: this.targetType,
                    target_id: this.targetId
                })
            });
            
            const result = await response.json();
            console.log('📥 PROOF RESPONSE:', result);
        } catch (error) {
            console.error('❌ SUBMIT ERROR:', error);
        }
    }
    
    getHashRate() {
        if (!this.startTime) return 0;
        const elapsed = (Date.now() - this.startTime) / 1000;
        return Math.floor(this.hashCount / elapsed);
    }
    
    updateDashboard() {
        console.log('🔄 UPDATING DASHBOARD...');
        
        // Update all dashboard elements
        this.updateElement('dashboard-target', `${this.targetType}:${this.targetId}`);
        this.updateElement('dashboard-hashrate', `${this.getHashRate()} H/s`);
        this.updateElement('dashboard-proofs', this.proofs.toString());
        this.updateElement('dashboard-current-hash', this.currentHash ? this.currentHash.substring(0, 16) + '...' : 'calculating...');
        this.updateElement('dashboard-status', this.isActive ? 'MINING' : 'IDLE');
        this.updateElement('power-level-display', Math.ceil(this.power / 10).toString());
        
        console.log('📊 STATUS:', this.isActive ? 'MINING' : 'IDLE', '| POWER:', this.power + '%', '| RATE:', this.getHashRate(), 'H/s');
    }
    
    updateElement(id, value) {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = value;
        } else {
            console.log('❌ ELEMENT NOT FOUND:', id);
        }
    }
}

// Initialize immediately when script loads
console.log('🚀 INITIALIZING EMERGENCY MINER...');
window.emergencyMiner = new EmergencyMiner();

// Also set as haichanMiner for compatibility
window.haichanMiner = window.emergencyMiner;

document.addEventListener('DOMContentLoaded', () => {
    console.log('📄 DOM LOADED, MINER READY');
    if (window.emergencyMiner) {
        // Try multiple times to connect slider
        setTimeout(() => window.emergencyMiner.setupSlider(), 500);
        setTimeout(() => window.emergencyMiner.setupSlider(), 1000);
        setTimeout(() => window.emergencyMiner.setupSlider(), 2000);
    }
});

// Also try after window load
window.addEventListener('load', () => {
    console.log('🌐 WINDOW LOADED, CONNECTING SLIDER...');
    if (window.emergencyMiner) {
        window.emergencyMiner.setupSlider();
    }
});

console.log('✅ EMERGENCY MINING SYSTEM LOADED');