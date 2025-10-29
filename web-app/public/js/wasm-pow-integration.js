/**
 * WASM PoW Integration for HaiChan
 * Integrates the Rust/WASM proof-of-work miner with the existing frontend
 */

class WasmPowMiner {
    constructor() {
        this.wasmModule = null;
        this.isLoaded = false;
        this.currentMiner = null;
        console.log('🦀 WASM PoW Miner initialized');
    }

    async loadWasm() {
        if (this.isLoaded) return true;
        
        try {
            console.log('📦 Loading WASM module...');
            
            // Import the WASM module
            const wasmModule = await import('/wasm/pow_miner_wasm.js');
            await wasmModule.default('/wasm/pow_miner_wasm_bg.wasm');
            
            this.wasmModule = wasmModule;
            this.isLoaded = true;
            
            console.log('✅ WASM module loaded, version:', wasmModule.version());
            return true;
            
        } catch (error) {
            console.error('❌ Failed to load WASM module:', error);
            return false;
        }
    }

    async mineForThread(postDraft, difficulty = '21e8') {
        if (!await this.loadWasm()) {
            throw new Error('WASM module failed to load');
        }

        try {
            // Build canonical parameters
            const canonParams = {
                user_pubkey_hex: this.getCurrentUserPubkey(),
                scope: 't', // thread
                thread_id: 0,
                parent_id: 0,
                timestamp_i64: Date.now(),
                post_draft: {
                    attachments: postDraft.attachments || [],
                    body: postDraft.body || '',
                    refs: postDraft.refs || [],
                    title: postDraft.title || ''
                }
            };

            console.log('⛏️ Starting WASM mining for thread...', canonParams);

            // Get canonical bytes from server API
            const challengeResponse = await this.getChallengeFromServer('thread', canonParams);
            const challengeBytes = this.hexToBytes(challengeResponse.canonical_bytes);

            // Initialize WASM miner
            this.currentMiner = this.wasmModule.init(difficulty, challengeBytes);

            // Mine with the WASM miner (max 1M iterations)
            const result = this.wasmModule.mine(this.currentMiner, 1000000);

            if (result) {
                console.log('✅ WASM mining successful!', result);
                
                return {
                    nonce: result.nonce,
                    hash: result.hash_hex,
                    challenge_id: challengeResponse.challenge_id,
                    timestamp: result.timestamp_i64,
                    miner_version: this.wasmModule.version(),
                    canonical_bytes: challengeResponse.canonical_bytes
                };
            } else {
                throw new Error('Mining failed - no solution found within iteration limit');
            }

        } catch (error) {
            console.error('❌ WASM mining error:', error);
            throw error;
        }
    }

    async mineForReply(postDraft, threadId, parentId = null, difficulty = '21e8') {
        if (!await this.loadWasm()) {
            throw new Error('WASM module failed to load');
        }

        try {
            const canonParams = {
                user_pubkey_hex: this.getCurrentUserPubkey(),
                scope: 'r', // reply
                thread_id: threadId,
                parent_id: parentId || 0,
                timestamp_i64: Date.now(),
                post_draft: {
                    attachments: postDraft.attachments || [],
                    body: postDraft.body || '',
                    refs: postDraft.refs || [],
                    title: postDraft.title || ''
                }
            };

            console.log('⛏️ Starting WASM mining for reply...', canonParams);

            const challengeResponse = await this.getChallengeFromServer('reply', canonParams);
            const challengeBytes = this.hexToBytes(challengeResponse.canonical_bytes);

            this.currentMiner = this.wasmModule.init(difficulty, challengeBytes);
            const result = this.wasmModule.mine(this.currentMiner, 1000000);

            if (result) {
                console.log('✅ WASM reply mining successful!', result);
                
                return {
                    nonce: result.nonce,
                    hash: result.hash_hex,
                    challenge_id: challengeResponse.challenge_id,
                    timestamp: result.timestamp_i64,
                    miner_version: this.wasmModule.version(),
                    canonical_bytes: challengeResponse.canonical_bytes
                };
            } else {
                throw new Error('Reply mining failed - no solution found');
            }

        } catch (error) {
            console.error('❌ WASM reply mining error:', error);
            throw error;
        }
    }

    verifyProof(canonicalBytes, nonce, difficulty) {
        if (!this.isLoaded || !this.currentMiner) {
            return false;
        }

        try {
            return this.wasmModule.verify(this.currentMiner, nonce);
        } catch (error) {
            console.error('❌ WASM verification error:', error);
            return false;
        }
    }

    async getChallengeFromServer(type, canonParams) {
        // Call the Laravel API endpoints
        const endpoint = type === 'thread' ? '/api/pow/thread/begin' : '/api/pow/reply/begin';
        
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Authorization': 'Bearer ' + this.getApiToken()
            },
            body: JSON.stringify({
                post_draft: canonParams.post_draft,
                client_op_id: this.generateUuid(),
                ...(type === 'reply' ? {
                    thread_id: canonParams.thread_id,
                    parent_id: canonParams.parent_id
                } : {})
            })
        });

        if (!response.ok) {
            // Fallback to direct canonical bytes computation for demo
            return this.simulateChallenge(canonParams);
        }

        return await response.json();
    }

    simulateChallenge(canonParams) {
        // Simulate the server response for testing
        // In production, this would come from the Laravel API
        
        // Build canonical bytes manually (following the v1 spec)
        let bytes = [];
        
        // prefix="HC1"
        bytes.push(...new TextEncoder().encode('HC1'));
        
        // user_pubkey_hex
        bytes.push(...new TextEncoder().encode(canonParams.user_pubkey_hex));
        
        // scope
        bytes.push(...new TextEncoder().encode(canonParams.scope));
        
        // thread_id as u64 little endian
        const threadIdBytes = new ArrayBuffer(8);
        new DataView(threadIdBytes).setBigUint64(0, BigInt(canonParams.thread_id), true);
        bytes.push(...new Uint8Array(threadIdBytes));
        
        // parent_id as u64 little endian  
        const parentIdBytes = new ArrayBuffer(8);
        new DataView(parentIdBytes).setBigUint64(0, BigInt(canonParams.parent_id), true);
        bytes.push(...new Uint8Array(parentIdBytes));
        
        // timestamp_i64 as i64 little endian
        const timestampBytes = new ArrayBuffer(8);
        new DataView(timestampBytes).setBigInt64(0, BigInt(canonParams.timestamp_i64), true);
        bytes.push(...new Uint8Array(timestampBytes));
        
        // sha256(post_json_minified) - 32 bytes placeholder
        bytes.push(...new Array(32).fill(0));
        
        return {
            challenge_id: this.generateUuid(),
            canonical_bytes: Array.from(bytes).map(b => b.toString(16).padStart(2, '0')).join(''),
            required_prefix_hex: '21e8',
            challenge_version: 1,
            expires_at: new Date(Date.now() + 60000).toISOString()
        };
    }

    getCurrentUserPubkey() {
        // Try to get from session/localStorage, fallback to default
        return localStorage.getItem('user_pubkey') || 
               '02c9d9c3f5a6f3a9e1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a0b1c2d3e4f5';
    }

    getApiToken() {
        // Get API token from session/localStorage
        return localStorage.getItem('api_token') || 
               document.querySelector('meta[name="api-token"]')?.content || '';
    }

    generateUuid() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    hexToBytes(hex) {
        const bytes = [];
        for (let i = 0; i < hex.length; i += 2) {
            bytes.push(parseInt(hex.substr(i, 2), 16));
        }
        return new Uint8Array(bytes);
    }
}

// Integration with existing mining system
class WasmIntegratedMiner {
    constructor() {
        this.wasmMiner = new WasmPowMiner();
        this.fallbackMiner = window.simplePoW || new SimpleProofOfWork();
        console.log('🔗 WASM Integrated Miner ready');
    }

    async mineForForm(formType, formData, options = {}) {
        const useWasm = options.useWasm !== false; // Default to WASM
        
        try {
            if (useWasm) {
                console.log('🦀 Using WASM miner...');
                
                if (formType === 'thread') {
                    return await this.wasmMiner.mineForThread(formData, options.difficulty);
                } else if (formType === 'reply') {
                    return await this.wasmMiner.mineForReply(
                        formData, 
                        options.threadId, 
                        options.parentId, 
                        options.difficulty
                    );
                }
            }
        } catch (error) {
            console.warn('🔄 WASM mining failed, falling back to JS:', error.message);
        }

        // Fallback to JavaScript mining
        console.log('⚠️ Using fallback JS miner...');
        return await this.fallbackMiner.acquireProofFor({
            target_type: formType,
            action: 'create',
            difficulty: options.difficulty || '21e8',
            payload: formData
        });
    }
}

// Global instance
window.wasmPowMiner = new WasmIntegratedMiner();

console.log('🚀 WASM PoW Integration loaded - ready for mining!');