#!/usr/bin/env node

import * as secp from '@noble/secp256k1';
import { randomBytes } from 'crypto';

export interface Keypair {
  privateKey: string;
  publicKey: string;
}

export class SecureKeypairGenerator {
  static generateKeypair(): Keypair {
    try {
      const privateKeyBytes = this.generateSecurePrivateKey();
      const privateKey = Buffer.from(privateKeyBytes).toString('hex');
      
      const publicKeyPoint = secp.getPublicKey(privateKeyBytes, true);
      const publicKey = Buffer.from(publicKeyPoint).toString('hex');
      
      return { privateKey, publicKey };
    } catch (error) {
      throw new Error(`Failed to generate keypair: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  static generateKeypairs(count: number): Keypair[] {
    if (!Number.isInteger(count) || count < 1 || count > 100) {
      throw new Error('Count must be an integer between 1 and 100');
    }
    
    const keypairs: Keypair[] = [];
    for (let i = 0; i < count; i++) {
      keypairs.push(this.generateKeypair());
    }
    return keypairs;
  }

  private static generateSecurePrivateKey(): Uint8Array {
    let privateKey: Uint8Array;
    
    do {
      privateKey = randomBytes(32);
    } while (!secp.utils.isValidPrivateKey(privateKey));
    
    return privateKey;
  }
}

const args = process.argv.slice(2);
const count = args.length > 0 ? parseInt(args[0]) : 1;

if (isNaN(count) || count < 1 || count > 100) {
  console.error('Invalid count. Please specify a number between 1 and 100.');
  process.exit(1);
}

const keypairs = SecureKeypairGenerator.generateKeypairs(count);

keypairs.forEach((keypair, index) => {
  console.log(`Keypair ${index + 1}:`);
  console.log(`Private Key: ${keypair.privateKey}`);
  console.log(`Public Key:  ${keypair.publicKey}`);
  console.log('');
});

console.log('All keypairs generated successfully!');
