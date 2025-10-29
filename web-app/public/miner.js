import init, { begin_mine, verify_local } from './wasm/pow_miner_wasm.js';

let wasm_initialized = false;

async function ensureWasmInitialized() {
    if (!wasm_initialized) {
        try {
            await init('/wasm/pow_miner_wasm_bg.wasm');
            wasm_initialized = true;
            console.log('✅ WASM miner initialized successfully');
        } catch (error) {
            console.error('❌ WASM initialization failed:', error);
            throw new Error('WASM mining module failed to load: ' + error.message);
        }
    }
}

export async function beginMine(challengeBytes, requiredPrefixHex, maxIters) {
    await ensureWasmInitialized();
    const result = begin_mine(new Uint8Array(challengeBytes), requiredPrefixHex, maxIters);
    if (result) {
        return { nonce_u64: result[0], solved_hash_hex: result[1] };
    } else {
        return null;
    }
}

export async function verifyLocal(inputBytes, requiredPrefixHex, nonce_u64) {
    await ensureWasmInitialized();
    return verify_local(new Uint8Array(inputBytes), requiredPrefixHex, nonce_u64);
}