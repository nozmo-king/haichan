const crypto = require('crypto');

// From the iOS app CryptoUtils
const challenge = '7edccbeca2a65a9229662867e8f442f336a9d9bb5b4c05db21401303bffce9e91758295207';
const privateKey = '7e8d2cba75475e1cacd547b6ac25ce593fa623ae2a75543e1c52352e44209d3c';

console.log('Challenge:', challenge);
console.log('Private key:', privateKey);

// Hash the challenge with SHA256 (same as iOS app line 77)
const challengeHash = crypto.createHash('sha256').update(challenge).digest();
console.log('Challenge hash:', challengeHash.toString('hex'));

// Use Node.js built-in ECDSA signing with secp256k1
try {
    // Create private key object
    const privateKeyObject = crypto.createPrivateKey({
        key: Buffer.from('3077020101042020' + privateKey + 'a00a06082a8648ce3d030107a144034200041ad596df9d30c72b81b0521f6640cb1d756a804a1a2c7ecf7972cae55c1a7bef', 'hex'),
        format: 'der',
        type: 'sec1'
    });

    // Sign the hash
    const signature = crypto.sign('sha256', challengeHash, privateKeyObject);
    console.log('DER signature:', signature.toString('hex'));

    // Parse DER signature to get r and s values (similar to iOS parseDERSignature)
    const der = signature;
    let index = 0;

    // Skip SEQUENCE tag and length
    if (der[index] !== 0x30) throw new Error('Invalid DER format');
    index += 2; // Skip 0x30 and sequence length

    // Parse R
    if (der[index] !== 0x02) throw new Error('Invalid R component');
    index += 1;
    const rLength = der[index];
    index += 1;
    let rData = der.slice(index, index + rLength);
    index += rLength;

    // Remove leading zero if present
    if (rData[0] === 0x00 && rData.length > 32) {
        rData = rData.slice(1);
    }

    // Parse S
    if (der[index] !== 0x02) throw new Error('Invalid S component');
    index += 1;
    const sLength = der[index];
    index += 1;
    let sData = der.slice(index, index + sLength);

    // Remove leading zero if present
    if (sData[0] === 0x00 && sData.length > 32) {
        sData = sData.slice(1);
    }

    // Pad to 32 bytes and convert to hex (like iOS line 62-63)
    const rHex = rData.toString('hex').padStart(64, '0');
    const sHex = sData.toString('hex').padStart(64, '0');
    const sigHex = rHex + sHex;

    console.log('R:', rHex);
    console.log('S:', sHex);
    console.log('Final signature:', sigHex);

    // Test the login
    const curlCmd = `curl -s -X POST http://127.0.0.1:8001/api/auth/login \\
    -H "Content-Type: application/json" \\
    -d '{"user_id":23,"challenge":"${challenge}","signature":"${sigHex}"}'`;

    console.log('\nLogin curl command:');
    console.log(curlCmd);

} catch (error) {
    console.error('Signing error:', error.message);

    // Fallback: Let's try to see what the secp256k1-keygen.js can help with
    console.log('\nTrying to use the existing JavaScript keygen...');

    // Create the signature manually using same approach as the keygen
    const { Secp256k1KeyGenerator } = require('../../generate-keypair.cjs');

    // We need to implement signing in the keygen
    console.log('Public key from private:', Secp256k1KeyGenerator.generatePublicKey(privateKey));
}