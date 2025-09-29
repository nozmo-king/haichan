#!/usr/bin/env node

import { createHash } from 'crypto';
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

  // WARNING: This is a development/testing script only
  // DO NOT use this in production - use proper secp256k1 cryptography
  
  console.error('ERROR: This script uses insecure signing methods!');
  console.error('This is for development/testing purposes only.');
  console.error('Do not use in production. Use proper secp256k1 library instead.');
  
  process.exit(1);
  
  // Code disabled for security reasons
  // Use proper secp256k1 library like @noble/secp256k1 or secp256k1-node

} catch (error) {
  console.error('Error signing challenge:', error instanceof Error ? error.message : 'Unknown error');
  process.exit(1);
}