/**
 * Real secp256k1 key generation using Web Crypto API and proper elliptic curve cryptography
 * This now properly derives public keys from private keys to match server-side behavior
 */

// Simple secp256k1 implementation for key derivation
// This is a minimal implementation to match the server-side PHP mdanter/ecc behavior
const secp256k1 = {
    // secp256k1 curve parameters
    p: BigInt('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F'),
    n: BigInt('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141'),
    Gx: BigInt('0x79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798'),
    Gy: BigInt('0x483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8'),

    // Modular inverse using extended Euclidean algorithm
    modInverse(a, m) {
        if (a < 0n) a = (a % m + m) % m;
        let [old_r, r] = [a, m];
        let [old_s, s] = [1n, 0n];

        while (r !== 0n) {
            const quotient = old_r / r;
            [old_r, r] = [r, old_r - quotient * r];
            [old_s, s] = [s, old_s - quotient * s];
        }

        return old_s < 0n ? old_s + m : old_s;
    },

    // Point addition on elliptic curve
    pointAdd(px, py, qx, qy) {
        if (px === null) return [qx, qy];
        if (qx === null) return [px, py];
        if (px === qx) {
            if (py === qy) return this.pointDouble(px, py);
            return [null, null]; // Points are inverses
        }

        const s = ((qy - py) * this.modInverse(qx - px, this.p)) % this.p;
        const rx = (s * s - px - qx) % this.p;
        const ry = (s * (px - rx) - py) % this.p;

        return [rx < 0n ? rx + this.p : rx, ry < 0n ? ry + this.p : ry];
    },

    // Point doubling on elliptic curve
    pointDouble(px, py) {
        const s = ((3n * px * px) * this.modInverse(2n * py, this.p)) % this.p;
        const rx = (s * s - 2n * px) % this.p;
        const ry = (s * (px - rx) - py) % this.p;

        return [rx < 0n ? rx + this.p : rx, ry < 0n ? ry + this.p : ry];
    },

    // Scalar multiplication using double-and-add
    pointMultiply(k, px, py) {
        if (k === 0n) return [null, null];
        if (k === 1n) return [px, py];

        let result = [null, null];
        let addend = [px, py];

        while (k > 0n) {
            if (k & 1n) {
                result = this.pointAdd(result[0], result[1], addend[0], addend[1]);
            }
            addend = this.pointDouble(addend[0], addend[1]);
            k >>= 1n;
        }

        return result;
    },

    // Generate public key from private key
    generatePublicKey(privateKeyHex) {
        const privateKeyBigInt = BigInt('0x' + privateKeyHex);

        if (privateKeyBigInt <= 0n || privateKeyBigInt >= this.n) {
            throw new Error('Invalid private key');
        }

        const [px, py] = this.pointMultiply(privateKeyBigInt, this.Gx, this.Gy);

        if (px === null || py === null) {
            throw new Error('Point at infinity');
        }

        // Generate compressed public key
        const isEven = py % 2n === 0n;
        const prefix = isEven ? '02' : '03';
        const xHex = px.toString(16).padStart(64, '0');

        return prefix + xHex;
    }
};

window.Secp256k1KeyGen = {
    /**
     * Generate a secp256k1 keypair with properly derived public key
     * @returns {Promise<{privateKey: string, publicKey: string}>}
     */
    async generateKeyPair() {
        let attempts = 0;
        const maxAttempts = 10;

        while (attempts < maxAttempts) {
            try {
                // Generate cryptographically secure 32-byte private key
                const privateKeyArray = new Uint8Array(32);
                crypto.getRandomValues(privateKeyArray);

                // Check if key is valid (not zero, not greater than curve order)
                const privateKeyHex = Array.from(privateKeyArray)
                    .map(b => b.toString(16).padStart(2, '0'))
                    .join('');

                if (!this.isValidPrivateKey(privateKeyHex)) {
                    attempts++;
                    continue;
                }

                // Generate public key using proper secp256k1 math
                const publicKeyHex = secp256k1.generatePublicKey(privateKeyHex);

                return {
                    privateKey: privateKeyHex,
                    publicKey: publicKeyHex
                };

            } catch (error) {
                attempts++;
                if (attempts >= maxAttempts) {
                    throw new Error('Failed to generate valid key pair after multiple attempts');
                }
            }
        }

        throw new Error('Failed to generate valid key pair');
    },

    /**
     * Validate if a string is a valid hex private key
     */
    isValidPrivateKey(privateKeyHex) {
        if (!privateKeyHex || privateKeyHex.length !== 64) {
            return false;
        }

        if (!/^[a-fA-F0-9]{64}$/.test(privateKeyHex)) {
            return false;
        }

        const privateKeyBigInt = BigInt('0x' + privateKeyHex);

        // Must be greater than 0 and less than curve order
        return privateKeyBigInt > 0n && privateKeyBigInt < secp256k1.n;
    },

    /**
     * Validate if a string is a valid compressed public key
     */
    isValidPublicKey(publicKeyHex) {
        if (!publicKeyHex || publicKeyHex.length !== 66) {
            return false;
        }

        if (!/^[a-fA-F0-9]{66}$/.test(publicKeyHex)) {
            return false;
        }

        const prefix = publicKeyHex.substring(0, 2).toLowerCase();
        return prefix === '02' || prefix === '03';
    },

    /**
     * Generate public key from private key (for verification)
     */
    derivePublicKey(privateKeyHex) {
        if (!this.isValidPrivateKey(privateKeyHex)) {
            throw new Error('Invalid private key');
        }

        return secp256k1.generatePublicKey(privateKeyHex);
    },

    /**
     * Create public key from private key (alias for derivePublicKey)
     */
    async privateKeyToPublicKey(privateKeyHex) {
        return this.derivePublicKey(privateKeyHex);
    },

    /**
     * Sign a message using ECDSA with secp256k1
     */
    async signMessage(privateKeyHex, message) {
        if (!this.isValidPrivateKey(privateKeyHex)) {
            throw new Error('Invalid private key');
        }

        // Hash the message with SHA-256
        const messageBytes = new TextEncoder().encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', messageBytes);
        const hashArray = new Uint8Array(hashBuffer);

        // Convert hash to BigInt
        const z = BigInt('0x' + Array.from(hashArray).map(b => b.toString(16).padStart(2, '0')).join(''));

        // Private key as BigInt
        const d = BigInt('0x' + privateKeyHex);

        // Generate k (nonce) - in production this should be RFC 6979 deterministic
        // For now, using crypto.getRandomValues for testing
        let k, r, s;
        let attempts = 0;
        const maxAttempts = 100;

        while (attempts < maxAttempts) {
            // Generate random k
            const kArray = new Uint8Array(32);
            crypto.getRandomValues(kArray);
            k = BigInt('0x' + Array.from(kArray).map(b => b.toString(16).padStart(2, '0')).join(''));

            // Ensure k is valid (1 < k < n)
            if (k <= 1n || k >= secp256k1.n) {
                attempts++;
                continue;
            }

            // Calculate r = (k * G).x mod n
            const [kGx] = secp256k1.pointMultiply(k, secp256k1.Gx, secp256k1.Gy);
            r = kGx % secp256k1.n;

            if (r === 0n) {
                attempts++;
                continue;
            }

            // Calculate s = k^(-1) * (z + r * d) mod n
            const kInv = secp256k1.modInverse(k, secp256k1.n);
            s = (kInv * (z + r * d)) % secp256k1.n;

            if (s === 0n) {
                attempts++;
                continue;
            }

            // Ensure low s value (BIP-62 canonical signature)
            if (s > secp256k1.n / 2n) {
                s = secp256k1.n - s;
            }

            break;
        }

        if (attempts >= maxAttempts) {
            throw new Error('Failed to generate valid signature after multiple attempts');
        }

        // Convert r and s to 64-character hex strings
        const rHex = r.toString(16).padStart(64, '0');
        const sHex = s.toString(16).padStart(64, '0');

        return rHex + sHex;
    }
};