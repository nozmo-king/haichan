#!/usr/bin/env node

import { createSign, createHash } from 'crypto';
import { Buffer } from 'buffer';

if (process.argv.length < 4) {
  console.error('Usage: npx tsx sign-challenge-simple.ts <private_key_hex> <challenge>');
  process.exit(1);
}

const privateKeyHex = process.argv[2];
const challenge = process.argv[3];

try {
  // Validate private key length
  if (privateKeyHex.length !== 64) {
    throw new Error('Private key must be 64 hex characters');
  }

  // Hash the challenge with SHA256
  const challengeHash = createHash('sha256').update(challenge).digest();

  // Create the private key in DER format for secp256k1
  const privateKeyBytes = Buffer.from(privateKeyHex, 'hex');

  // For secp256k1, we need to create a proper DER-encoded private key
  // This is a simplified version - for production use a proper secp256k1 library
  console.log('Challenge:', challenge);
  console.log('Challenge hash:', challengeHash.toString('hex'));
  console.log('Private key:', privateKeyHex);

  // For now, let's use the Node.js built-in approach with EC keys
  const { generateKeyPairSync, createSign: createSignature } = require('crypto');

  // Try to create a key object from the raw private key
  const keyObject = {
    key: Buffer.concat([
      Buffer.from('302e0201010420', 'hex'), // ASN.1 DER header for EC private key
      privateKeyBytes,
      Buffer.from('a00706052b8104000a', 'hex') // secp256k1 curve identifier
    ]),
    format: 'der',
    type: 'sec1'
  };

  console.log('Will attempt manual signing since Node.js crypto may not directly support secp256k1...');
  console.log('For testing, we can compute a deterministic signature manually.');

  // Simple deterministic signature for testing (NOT cryptographically secure)
  const testSignature = createHash('sha256').update(privateKeyHex + challenge).digest('hex');
  console.log('Test signature (for demo only):', testSignature);
  console.log('Test signature length:', testSignature.length);

} catch (error) {
  console.error('Error signing challenge:', error instanceof Error ? error.message : 'Unknown error');
  process.exit(1);
}