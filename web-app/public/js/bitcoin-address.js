// Bitcoin address generation with proper Base58Check encoding

// Base58 alphabet (Bitcoin-specific, excludes 0OIl)
const BASE58_ALPHABET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

// Convert bytes to base58
function base58Encode(bytes) {
    const digits = [0];
    
    for (let i = 0; i < bytes.length; i++) {
        let carry = bytes[i];
        for (let j = 0; j < digits.length; j++) {
            carry += digits[j] << 8;
            digits[j] = carry % 58;
            carry = (carry / 58) | 0;
        }
        while (carry > 0) {
            digits.push(carry % 58);
            carry = (carry / 58) | 0;
        }
    }
    
    // Convert digits to base58 string
    let encoded = '';
    for (let i = digits.length - 1; i >= 0; i--) {
        encoded += BASE58_ALPHABET[digits[i]];
    }
    
    // Add leading '1's for leading zero bytes
    for (let i = 0; i < bytes.length && bytes[i] === 0; i++) {
        encoded = '1' + encoded;
    }
    
    return encoded;
}

// Generate Bitcoin address from public key
async function generateBitcoinAddress(publicKeyHex) {
    // Step 1: SHA-256 hash of the public key
    const publicKeyBytes = new Uint8Array(publicKeyHex.match(/.{1,2}/g).map(byte => parseInt(byte, 16)));
    const sha256Hash = await crypto.subtle.digest('SHA-256', publicKeyBytes);
    
    // Step 2: RIPEMD-160 hash
    const ripemd = new RIPEMD160();
    const hash160 = ripemd.digest(new Uint8Array(sha256Hash));
    
    // Step 3: Add version byte (0x00 for mainnet P2PKH addresses)
    const versionedPayload = new Uint8Array(21);
    versionedPayload[0] = 0x00; // Version byte for P2PKH
    versionedPayload.set(hash160, 1);
    
    // Step 4: Double SHA-256 for checksum
    const checksum1 = await crypto.subtle.digest('SHA-256', versionedPayload);
    const checksum2 = await crypto.subtle.digest('SHA-256', checksum1);
    const checksum = new Uint8Array(checksum2).slice(0, 4);
    
    // Step 5: Concatenate version + payload + checksum
    const addressBytes = new Uint8Array(25);
    addressBytes.set(versionedPayload);
    addressBytes.set(checksum, 21);
    
    // Step 6: Base58 encode
    return base58Encode(addressBytes);
}

// Compressed public key from private key (simplified for demo)
function getPublicKeyFromPrivate(privateKeyHex) {
    // This is a simplified version - real implementation needs secp256k1
    // For now, we'll use SHA-256 as a placeholder
    return crypto.subtle.digest('SHA-256', 
        new TextEncoder().encode(privateKeyHex)
    ).then(hash => {
        return Array.from(new Uint8Array(hash))
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');
    });
}

// Export for use
window.BitcoinAddress = {
    generate: generateBitcoinAddress,
    getPublicKey: getPublicKeyFromPrivate
};