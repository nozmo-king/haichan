const crypto = require('crypto');

// Same values as before
const challenge = '5da6aee4be48684b8003b84406e52b3b37dc7f107785375415fd86ec08865fe91758295901';
const privateKey = '7e8d2cba75475e1cacd547b6ac25ce593fa623ae2a75543e1c52352e44209d3c';

console.log('🍎 Mimicking iOS App Authentication Flow');
console.log('=========================================\n');

console.log('Challenge:', challenge);
console.log('Private key:', privateKey);
console.log('Expected public key: 021ad596df9d30c72b81b0521f6640cb1d756a804a1a2c7ecf7972cae55c1a7bef\n');

async function mimicIOSApp() {
    const secp256k1 = require('@noble/secp256k1');

    // Set up the HMAC function
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
        console.log('Step 1: Convert challenge to data (iOS line 73)');
        // iOS: guard let challengeData = challenge.data(using: .utf8)
        const challengeData = new TextEncoder().encode(challenge);
        console.log('Challenge data (hex):', Buffer.from(challengeData).toString('hex'));

        console.log('\nStep 2: Hash challenge data with SHA256 (iOS line 77)');
        // iOS: let hash = SHA256.hash(data: challengeData)
        const hash = crypto.createHash('sha256').update(challengeData).digest();
        console.log('SHA256 hash:', hash.toString('hex'));

        console.log('\nStep 3: Sign the hash (iOS line 55)');
        // iOS: let signature = try privateKey.signature(for: messageHash)
        // iOS passes the hash directly to the signing function, no double hashing
        const privateKeyBytes = secp256k1.etc.hexToBytes(privateKey);
        const signature = await secp256k1.sign(hash, privateKeyBytes);

        console.log('\nStep 4: Extract r and s values (iOS lines 62-65)');
        // iOS: let rHex = rData.hexString.paddingToLeft(upTo: 64, using: "0")
        // iOS: let sHex = sData.hexString.paddingToLeft(upTo: 64, using: "0")
        const r = signature.r;
        const s = signature.s;

        const rHex = r.toString(16).padStart(64, '0');
        const sHex = s.toString(16).padStart(64, '0');
        const signatureHex = rHex + sHex;

        console.log('r:', rHex);
        console.log('s:', sHex);
        console.log('Final signature (r||s):', signatureHex);

        // Test the login
        console.log('\n🧪 Testing login with iOS-style signature...');
        const curlCmd = `curl -s -X POST http://127.0.0.1:8001/api/auth/login \\
    -H "Content-Type: application/json" \\
    -d '{"user_id":23,"challenge":"${challenge}","signature":"${signatureHex}"}'`;

        console.log('\nCurl command:');
        console.log(curlCmd);

        return signatureHex;

    } catch (error) {
        console.error('❌ Error:', error);
        throw error;
    }
}

mimicIOSApp();