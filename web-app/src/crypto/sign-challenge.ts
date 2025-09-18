#!/usr/bin/env node

import * as secp from '@noble/secp256k1';
import { createHash, createHmac, randomBytes } from 'crypto';

// Set up HMAC for secp256k1 - direct assignment
(secp.utils as any).hmacSha256Sync = (key: Uint8Array, data: Uint8Array): Uint8Array => {
  return createHmac('sha256', key).update(data).digest();
};

// Set up randomBytes if needed
(secp.utils as any).randomBytes = (bytesLength: number): Uint8Array => {
  return randomBytes(bytesLength);
};

if (process.argv.length < 4) {
  console.error('Usage: npx tsx sign-challenge.ts <private_key_hex> <challenge>');
  process.exit(1);
}

const privateKeyHex = process.argv[2];
const challenge = process.argv[3];

async function signChallenge() {
  try {
    // Validate private key
    const privateKeyBytes = Buffer.from(privateKeyHex, 'hex');
    if (privateKeyBytes.length !== 32) {
      throw new Error('Private key must be 32 bytes (64 hex characters)');
    }

    if (!secp.utils.isValidPrivateKey(privateKeyBytes)) {
      throw new Error('Invalid private key');
    }

    // Hash the challenge with SHA256
    const challengeHash = createHash('sha256').update(challenge).digest();

    // Sign the hash (async version)
    const signature = await secp.sign(challengeHash, privateKeyBytes);

    // Convert signature to hex format (r||s, 64 bytes each)
    const rHex = signature.r.toString(16).padStart(64, '0');
    const sHex = signature.s.toString(16).padStart(64, '0');
    const signatureHex = rHex + sHex;

    console.log('Challenge:', challenge);
    console.log('Challenge hash:', challengeHash.toString('hex'));
    console.log('Signature (r||s):', signatureHex);
    console.log('R:', rHex);
    console.log('S:', sHex);

  } catch (error) {
    console.error('Error signing challenge:', error instanceof Error ? error.message : 'Unknown error');
    process.exit(1);
  }
}

signChallenge();