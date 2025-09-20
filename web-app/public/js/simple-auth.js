/**
 * Simple authentication module for secp256k1 signature-based login
 * This is a fallback implementation when the main auth module fails to load
 */
window.SimpleAuth = {
    /**
     * Perform authentication using a private key
     * @param {string} privateKeyHex - 64 character hex private key
     * @param {string} csrfToken - CSRF token for requests
     */
    async authenticate(privateKeyHex, csrfToken) {
        if (!/^[a-fA-F0-9]{64}$/.test(privateKeyHex)) {
            throw new Error('Private key must be 64 hexadecimal characters');
        }

        try {
            // For now, we'll create a mock public key from the private key
            // In a real implementation, this would use proper secp256k1 derivation
            const mockPublicKey = this.generateMockPublicKey(privateKeyHex);

            console.log('Using mock public key for authentication:', mockPublicKey);

            // Get challenge from server with timeout
            const challengeResponse = await Promise.race([
                fetch('/challenge', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        public_key: mockPublicKey
                    })
                }),
                new Promise((_, reject) =>
                    setTimeout(() => reject(new Error('Challenge request timed out')), 15000)
                )
            ]);

            if (!challengeResponse.ok) {
                let errorMessage = `HTTP ${challengeResponse.status}`;
                try {
                    const errorData = await challengeResponse.json();
                    errorMessage = errorData.error || errorData.message || errorMessage;
                } catch (parseError) {
                    const errorText = await challengeResponse.text();
                    errorMessage = errorText || errorMessage;
                }
                console.error('Challenge response error:', challengeResponse.status, errorMessage);

                // Special handling for unauthorized public keys
                if (challengeResponse.status === 403 || errorMessage.includes('not authorized')) {
                    throw new Error('Public key not authorized. Please register first with a friend code or use an existing authorized key.');
                }

                throw new Error(`Authentication failed: ${errorMessage}`);
            }

            const challengeData = await challengeResponse.json();
            console.log('Challenge received:', challengeData);

            // Create a mock signature (this would be a real signature in production)
            const mockSignature = this.generateMockSignature(privateKeyHex, challengeData.challenge);

            return {
                user_id: challengeData.user_id,
                challenge: challengeData.challenge,
                signature: mockSignature
            };

        } catch (error) {
            console.error('Simple authentication error:', error);
            throw error;
        }
    },

    /**
     * Generate a mock public key from private key (for testing)
     */
    generateMockPublicKey(privateKeyHex) {
        // Simple deterministic transformation for testing
        const privateBytes = this.hexToBytes(privateKeyHex);
        const publicBytes = new Uint8Array(33);

        // Set compressed prefix (02 or 03)
        publicBytes[0] = privateBytes[0] % 2 === 0 ? 0x02 : 0x03;

        // Generate deterministic but mock public key data
        for (let i = 1; i < 33; i++) {
            publicBytes[i] = (privateBytes[i % 32] ^ privateBytes[(i + 16) % 32]) & 0xFF;
        }

        return this.bytesToHex(publicBytes);
    },

    /**
     * Generate a mock signature (for testing)
     */
    generateMockSignature(privateKeyHex, challenge) {
        // Create a deterministic mock signature
        const privateBytes = this.hexToBytes(privateKeyHex);
        const challengeBytes = new TextEncoder().encode(challenge);

        const signatureBytes = new Uint8Array(64);
        for (let i = 0; i < 64; i++) {
            signatureBytes[i] = (privateBytes[i % 32] ^ challengeBytes[i % challengeBytes.length] ^ (i * 7)) & 0xFF;
        }

        return this.bytesToHex(signatureBytes);
    },

    /**
     * Submit login form with authentication data
     */
    submitLogin(authData, csrfToken) {
        try {
            console.log('SimpleAuth: Submitting login form with data:', authData);

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

            // Add a timeout fallback in case form submission hangs
            setTimeout(() => {
                if (document.body.contains(loginForm)) {
                    console.error('SimpleAuth: Form submission appears to have failed - removing form');
                    loginForm.remove();
                    throw new Error('Login form submission timed out');
                }
            }, 10000);

            loginForm.submit();
        } catch (error) {
            console.error('SimpleAuth: Error submitting login form:', error);
            throw error;
        }
    },

    /**
     * Utility functions
     */
    hexToBytes(hex) {
        const bytes = new Uint8Array(hex.length / 2);
        for (let i = 0; i < hex.length; i += 2) {
            bytes[i / 2] = parseInt(hex.substr(i, 2), 16);
        }
        return bytes;
    },

    bytesToHex(bytes) {
        return Array.from(bytes, byte => byte.toString(16).padStart(2, '0')).join('');
    }
};