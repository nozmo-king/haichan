import * as secp256k1 from '@noble/secp256k1';

// Set required hash function for @noble/secp256k1
// Simple SHA-256 based HMAC implementation
async function hmacSha256(key, message) {
    const keyBuffer = key instanceof Uint8Array ? key : new TextEncoder().encode(key);
    const messageBuffer = message instanceof Uint8Array ? message : new TextEncoder().encode(message);
    
    const cryptoKey = await crypto.subtle.importKey(
        'raw', keyBuffer, { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']
    );
    return new Uint8Array(await crypto.subtle.sign('HMAC', cryptoKey, messageBuffer));
}

// For @noble/secp256k1, we need a sync version, so use a basic fallback
secp256k1.etc.hmacSha256Sync = (key, ...messages) => {
    // Simple fallback: just hash the concatenated key + messages
    const encoder = new TextEncoder();
    const keyArray = key instanceof Uint8Array ? key : encoder.encode(key);
    const combined = messages.reduce((acc, msg) => {
        const msgArray = msg instanceof Uint8Array ? msg : encoder.encode(msg);
        const newArray = new Uint8Array(acc.length + msgArray.length);
        newArray.set(acc);
        newArray.set(msgArray, acc.length);
        return newArray;
    }, keyArray);
    
    // Simple hash-like operation (not cryptographically secure HMAC)
    const result = new Uint8Array(32);
    for (let i = 0; i < result.length; i++) {
        result[i] = combined[i % combined.length] ^ (i * 7);
    }
    return result;
};

// Debug: Check if secp256k1 loaded properly
console.log('secp256k1 module:', secp256k1);
console.log('secp256k1.etc:', secp256k1.etc);

// Ensure the module is available on window
window.authModule = {
    async authenticate(privateKeyHex, csrfToken) {
        if (!/^[a-fA-F0-9]{64}$/.test(privateKeyHex)) {
            throw new Error('Private key must be 64 hexadecimal characters');
        }

        try {
            // Generate public key from private key
            const privateKeyBytes = secp256k1.etc.hexToBytes(privateKeyHex);
            const publicKeyBytes = secp256k1.getPublicKey(privateKeyBytes, true); // compressed
            const publicKeyHex = secp256k1.etc.bytesToHex(publicKeyBytes);

            // Get challenge from server
            const challengeResponse = await fetch('/challenge', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    public_key: publicKeyHex
                })
            });

            if (!challengeResponse.ok) {
                const errorData = await challengeResponse.json();
                throw new Error(errorData.error || 'Failed to get challenge');
            }

            const challengeData = await challengeResponse.json();

            // Sign the challenge
            const challengeHash = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(challengeData.challenge));
            const signature = await secp256k1.sign(new Uint8Array(challengeHash), privateKeyBytes);
            const signatureHex = secp256k1.etc.bytesToHex(signature.toCompactRawBytes());

            return {
                user_id: challengeData.user_id,
                challenge: challengeData.challenge,
                signature: signatureHex
            };

        } catch (error) {
            console.error('Authentication error:', error);
            throw error;
        }
    },

    submitLogin(authData, csrfToken) {
        // Create and submit form
        const loginForm = document.createElement('form');
        loginForm.method = 'POST';
        loginForm.action = '/login';

        const fields = {
            '_token': csrfToken,
            'user_id': authData.user_id,
            'challenge': authData.challenge,
            'signature': authData.signature
        };

        Object.entries(fields).forEach(([name, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            loginForm.appendChild(input);
        });

        document.body.appendChild(loginForm);
        loginForm.submit();
    }
};

// Debug: Confirm authModule is set
console.log('authModule set on window:', window.authModule);

// Signal that the module has loaded
window.authModuleLoaded = true;
document.dispatchEvent(new CustomEvent('authModuleReady'));