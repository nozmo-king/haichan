const crypto = require('crypto');

// Challenge from API
const challenge = '7edccbeca2a65a9229662867e8f442f336a9d9bb5b4c05db21401303bffce9e91758295207';
const privateKey = '7e8d2cba75475e1cacd547b6ac25ce593fa623ae2a75543e1c52352e44209d3c';

console.log('🔐 Testing Authentication Flow with your private key');
console.log('=====================================================\n');

console.log('Challenge:', challenge);
console.log('Private key:', privateKey);

async function testAuthentication() {
    const secp256k1 = require('@noble/secp256k1');

    // Set up the HMAC function as done in auth.js
    secp256k1.etc.hmacSha256Sync = (key, ...messages) => {
        const encoder = new TextEncoder();
        const keyArray = key instanceof Uint8Array ? key : encoder.encode(key);
        const combined = messages.reduce((acc, msg) => {
            const msgArray = msg instanceof Uint8Array ? msg : encoder.encode(msg);
            const newArray = new Uint8Array(acc.length + msgArray.length);
            newArray.set(acc);
            newArray.set(msgArray, acc.length);
            return newArray;
        }, keyArray);

        const result = new Uint8Array(32);
        for (let i = 0; i < result.length; i++) {
            result[i] = combined[i % combined.length] ^ (i * 7);
        }
        return result;
    };

    try {
        // Generate public key (like auth.js line 49-51)
        const privateKeyBytes = secp256k1.etc.hexToBytes(privateKey);
        const publicKeyBytes = secp256k1.getPublicKey(privateKeyBytes, true); // compressed
        const publicKeyHex = secp256k1.etc.bytesToHex(publicKeyBytes);

        console.log('Generated public key:', publicKeyHex);

        // Hash the challenge (like auth.js line 91)
        const challengeBytes = new TextEncoder().encode(challenge);
        const challengeHash = await crypto.webcrypto.subtle.digest('SHA-256', challengeBytes);
        console.log('Challenge hash:', Buffer.from(challengeHash).toString('hex'));

        // Sign the challenge (like auth.js line 92-93)
        const signature = await secp256k1.sign(new Uint8Array(challengeHash), privateKeyBytes);

        // The server expects r||s format (64 bytes each), not compact format
        // Get r and s values from signature
        const r = signature.r;
        const s = signature.s;

        // Convert to hex and pad to 64 characters (32 bytes each)
        const rHex = r.toString(16).padStart(64, '0');
        const sHex = s.toString(16).padStart(64, '0');
        const signatureHex = rHex + sHex;

        console.log('Signature r:', rHex);
        console.log('Signature s:', sHex);
        console.log('Signature (r||s):', signatureHex);
        console.log('\n✅ Successfully created signature!');

        // Create the login curl command
        const curlCmd = `curl -s -X POST http://127.0.0.1:8001/api/auth/login \\
    -H "Content-Type: application/json" \\
    -d '{"user_id":23,"challenge":"${challenge}","signature":"${signatureHex}"}'`;

        console.log('\n📋 Login curl command:');
        console.log(curlCmd);

        return signatureHex;

    } catch (error) {
        console.error('❌ Error:', error.message);
        console.error(error.stack);
        throw error;
    }
}

testAuthentication();