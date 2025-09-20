const crypto = require('crypto');

// Challenge from the API response
const challenge = '7edccbeca2a65a9229662867e8f442f336a9d9bb5b4c05db21401303bffce9e91758295207';
const privateKey = '7e8d2cba75475e1cacd547b6ac25ce593fa623ae2a75543e1c52352e44209d3c';

// Try to load secp256k1 from the installed location
let secp256k1;
try {
    secp256k1 = require('@noble/secp256k1');
    // Set up HMAC for secp256k1
    secp256k1.utils.hmacSha256Sync = (key, ...msgs) => {
        const hmac = crypto.createHmac('sha256', key);
        msgs.forEach(msg => hmac.update(msg));
        return hmac.digest();
    };
} catch (err) {
    console.error('Could not load secp256k1:', err.message);
    process.exit(1);
}

console.log('Challenge:', challenge);
console.log('Private key:', privateKey);

// Hash the challenge with SHA256 (same as iOS app)
const msgHash = crypto.createHash('sha256').update(challenge).digest();
console.log('Challenge hash:', msgHash.toString('hex'));

// Sign with secp256k1
const signature = secp256k1.sign(msgHash, privateKey);

// Convert to the format expected by the server (r||s as hex)
const r = signature.r.toString(16).padStart(64, '0');
const s = signature.s.toString(16).padStart(64, '0');
const sigHex = r + s;

console.log('Signature r:', r);
console.log('Signature s:', s);
console.log('Full signature:', sigHex);

// Now create the curl command for login
const curlCmd = `curl -s -X POST http://127.0.0.1:8001/api/auth/login \\
    -H "Content-Type: application/json" \\
    -d '{"user_id":23,"challenge":"${challenge}","signature":"${sigHex}"}'`;

console.log('\nLogin curl command:');
console.log(curlCmd);