/**
 * Fallback Mining System
 * Always-working PoW implementation for critical functions
 */

class FallbackMining {
    constructor() {
        this.isActive = true;
        console.log('⚡ Fallback Mining initialized');
    }

    async sha256(message) {
        const msgBuffer = new TextEncoder().encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    async mineForChallenge(challengeData, difficulty = '21e8') {
        console.log('🔨 Fallback mining for difficulty:', difficulty);
        let nonce = 0;
        const maxAttempts = 1000000;
        const startTime = Date.now();
        
        while (nonce < maxAttempts) {
            const data = typeof challengeData === 'string' ? challengeData : JSON.stringify(challengeData);
            const fullData = data + ':' + nonce;
            const hash = await this.sha256(fullData);
            
            if (hash.toLowerCase().startsWith(difficulty.toLowerCase())) {
                const elapsed = Date.now() - startTime;
                console.log(`✅ Found valid hash in ${elapsed}ms after ${nonce} attempts`);
                return {
                    success: true,
                    nonce: nonce.toString(),
                    hash: hash,
                    attempts: nonce,
                    time: elapsed
                };
            }
            
            nonce++;
            
            // Yield control periodically
            if (nonce % 1000 === 0) {
                await new Promise(resolve => setTimeout(resolve, 1));
                this.updateStatus(`Mining: ${nonce} hashes...`);
            }
        }
        
        console.error('❌ Mining failed after max attempts');
        return {
            success: false,
            error: 'Max attempts reached'
        };
    }

    async acquireProofFor(payload) {
        console.log('🎯 Fallback acquireProofFor:', payload);
        
        try {
            // Get challenge from server
            const response = await fetch('/api/mining/challenges', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                throw new Error(`Challenge request failed: ${response.status}`);
            }

            const challenge = await response.json();
            
            if (!challenge.success) {
                throw new Error(challenge.message || 'Challenge failed');
            }

            console.log('📋 Got challenge:', challenge.token);

            // Mine the challenge
            const result = await this.mineForChallenge(
                challenge.canonical_payload,
                payload.difficulty || '21e8'
            );

            if (!result.success) {
                throw new Error('Mining failed: ' + result.error);
            }

            return {
                nonce: result.nonce,
                hash: result.hash,
                challenge_id: challenge.token
            };

        } catch (error) {
            console.error('❌ Fallback mining error:', error);
            
            // Ultra-fallback: mine without server challenge
            console.warn('⚠️ Using ultra-fallback mode (no server challenge)');
            const fallbackData = `${payload.action}:${payload.target_type}:${Date.now()}`;
            const result = await this.mineForChallenge(fallbackData, payload.difficulty || '21e8');
            
            if (result.success) {
                return {
                    nonce: result.nonce,
                    hash: result.hash,
                    challenge_id: null // No challenge ID in ultra-fallback
                };
            }
            
            throw error;
        }
    }

    updateStatus(message) {
        // Update any mining status elements on the page
        const statusElements = document.querySelectorAll('#mining-status, .mining-status');
        statusElements.forEach(el => {
            if (el) el.textContent = message;
        });
    }
}

// Global fallback mining instance
window.fallbackMining = new FallbackMining();

// Ensure mining is always available
window.addEventListener('load', () => {
    // If no mining systems are available, use fallback
    if (!window.haichanMiningBrain && !window.simplePoW) {
        console.warn('⚠️ No primary mining systems available, using fallback as main');
        window.haichanMiningBrain = window.fallbackMining;
        window.simplePoW = window.fallbackMining;
    }
    
    console.log('✅ Mining systems check:', {
        haichanMiningBrain: !!window.haichanMiningBrain,
        simplePoW: !!window.simplePoW,
        fallbackMining: !!window.fallbackMining
    });
});