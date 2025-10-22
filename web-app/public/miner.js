import init, { begin_mine, verify_local } from './miner-wasm.js';

let wasmInitialized = false;

async function ensureWasmInitialized() {
    if (!wasmInitialized) {
        await init();
        wasmInitialized = true;
    }
}

export async function beginMine(challengeBytes, requiredPrefixHex, maxIters) {
    await ensureWasmInitialized();
    // challengeBytes is a Uint8Array
    // requiredPrefixHex is a string
    // maxIters is a number
    const result = begin_mine(new Uint8Array(challengeBytes), requiredPrefixHex, maxIters);
    if (result) {
        return { nonce_u64: result[0], solved_hash_hex: result[1] };
    } else {
        return null;
    }
}

export async function verifyLocal(canonicalBytes, nonceU64, requiredPrefixHex) {
    await ensureWasmInitialized();
    // canonicalBytes is a Uint8Array
    // nonceU64 is a number
    // requiredPrefixHex is a string
    return verify_local(new Uint8Array(canonicalBytes), nonceU64, requiredPrefixHex);
}
