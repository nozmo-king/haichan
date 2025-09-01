#!/usr/bin/env node
"use strict";
/**
 * secp256k1 Keypair Generator
 *
 * Generates secp256k1 private/public key pairs for use with the forum authentication system.
 *
 * Usage:
 *   npx ts-node generate-keypair.ts
 *   node generate-keypair.js (if compiled)
 */
Object.defineProperty(exports, "__esModule", { value: true });
exports.Secp256k1KeyGenerator = void 0;
var crypto = require("crypto");
// Simple secp256k1 implementation
var Secp256k1KeyGenerator = /** @class */ (function () {
    function Secp256k1KeyGenerator() {
    }
    /**
     * Generate a cryptographically secure random private key
     */
    Secp256k1KeyGenerator.generatePrivateKey = function () {
        var privateKey;
        do {
            // Generate 32 random bytes
            var bytes = crypto.randomBytes(32);
            privateKey = BigInt('0x' + bytes.toString('hex'));
        } while (privateKey >= this.N || privateKey === 0n);
        return privateKey.toString(16).padStart(64, '0');
    };
    /**
     * Modular inverse using extended Euclidean algorithm
     */
    Secp256k1KeyGenerator.modInverse = function (a, m) {
        var _a, _b;
        if (a < 0n)
            a = (a % m + m) % m;
        var _c = [a, m], old_r = _c[0], r = _c[1];
        var _d = [1n, 0n], old_s = _d[0], s = _d[1];
        while (r !== 0n) {
            var quotient = old_r / r;
            _a = [r, old_r - quotient * r], old_r = _a[0], r = _a[1];
            _b = [s, old_s - quotient * s], old_s = _b[0], s = _b[1];
        }
        return old_s < 0n ? old_s + m : old_s;
    };
    /**
     * Point addition on secp256k1 curve
     */
    Secp256k1KeyGenerator.pointAdd = function (x1, y1, x2, y2) {
        if (x1 === x2) {
            if (y1 === y2) {
                // Point doubling
                var s_1 = (3n * x1 * x1 * this.modInverse(2n * y1, this.P)) % this.P;
                var x3_1 = (s_1 * s_1 - 2n * x1) % this.P;
                var y3_1 = (s_1 * (x1 - x3_1) - y1) % this.P;
                return [(x3_1 + this.P) % this.P, (y3_1 + this.P) % this.P];
            }
            else {
                // Points are inverses, return point at infinity
                return [0n, 0n];
            }
        }
        var s = ((y2 - y1) * this.modInverse(x2 - x1, this.P)) % this.P;
        var x3 = (s * s - x1 - x2) % this.P;
        var y3 = (s * (x1 - x3) - y1) % this.P;
        return [(x3 + this.P) % this.P, (y3 + this.P) % this.P];
    };
    /**
     * Scalar multiplication on secp256k1 curve
     */
    Secp256k1KeyGenerator.scalarMult = function (k, x, y) {
        if (k === 0n)
            return [0n, 0n];
        if (k === 1n)
            return [x, y];
        var result = [0n, 0n];
        var addend = [x, y];
        while (k > 0n) {
            if (k & 1n) {
                if (result[0] === 0n && result[1] === 0n) {
                    result = addend;
                }
                else {
                    result = this.pointAdd(result[0], result[1], addend[0], addend[1]);
                }
            }
            addend = this.pointAdd(addend[0], addend[1], addend[0], addend[1]);
            k >>= 1n;
        }
        return result;
    };
    /**
     * Generate public key from private key
     */
    Secp256k1KeyGenerator.generatePublicKey = function (privateKeyHex) {
        var privateKey = BigInt('0x' + privateKeyHex);
        // Multiply generator point by private key
        var _a = this.scalarMult(privateKey, this.Gx, this.Gy), x = _a[0], y = _a[1];
        // Return compressed public key (02 or 03 prefix + x coordinate)
        var prefix = y % 2n === 0n ? '02' : '03';
        var xHex = x.toString(16).padStart(64, '0');
        return prefix + xHex;
    };
    /**
     * Generate a complete keypair
     */
    Secp256k1KeyGenerator.generateKeypair = function () {
        var privateKey = this.generatePrivateKey();
        var publicKey = this.generatePublicKey(privateKey);
        return { privateKey: privateKey, publicKey: publicKey };
    };
    // secp256k1 curve parameters
    Secp256k1KeyGenerator.P = BigInt('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F');
    Secp256k1KeyGenerator.N = BigInt('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141');
    Secp256k1KeyGenerator.Gx = BigInt('0x79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798');
    Secp256k1KeyGenerator.Gy = BigInt('0x483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8');
    return Secp256k1KeyGenerator;
}());
exports.Secp256k1KeyGenerator = Secp256k1KeyGenerator;
// CLI Interface
function main() {
    console.log('🔐 secp256k1 Keypair Generator');
    console.log('================================\n');
    var args = process.argv.slice(2);
    var count = args.length > 0 ? parseInt(args[0]) : 1;
    if (isNaN(count) || count < 1 || count > 100) {
        console.error('❌ Invalid count. Please specify a number between 1 and 100.');
        process.exit(1);
    }
    for (var i = 0; i < count; i++) {
        var keypair = Secp256k1KeyGenerator.generateKeypair();
        console.log("Keypair ".concat(i + 1, ":"));
        console.log("Private Key: ".concat(keypair.privateKey));
        console.log("Public Key:  ".concat(keypair.publicKey));
        console.log('');
        // Validation
        var regeneratedPublicKey = Secp256k1KeyGenerator.generatePublicKey(keypair.privateKey);
        if (regeneratedPublicKey !== keypair.publicKey) {
            console.error('❌ Validation failed!');
            process.exit(1);
        }
    }
    console.log('✅ All keypairs generated successfully!');
    console.log('\n📋 Usage Instructions:');
    console.log('1. Copy the Public Key to the admin panel at /admin/keys');
    console.log('2. Give the Private Key to the user (keep it secret!)');
    console.log('3. User enters the Private Key in the login form');
    console.log('\n⚠️  Security Notes:');
    console.log('• Private keys should be transmitted securely');
    console.log('• Users should store private keys safely');
    console.log('• Lost private keys cannot be recovered');
}
// Run if called directly
if (require.main === module) {
    main();
}
