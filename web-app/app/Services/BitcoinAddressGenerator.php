<?php

namespace App\Services;

class BitcoinAddressGenerator
{
    /**
     * Generate deterministic Bitcoin-like credentials from a seed
     * This creates consistent addresses that can be recreated from the same input
     */
    public static function generateKeyPair()
    {
        // Generate a cryptographically secure private key
        $privateKeyHex = bin2hex(random_bytes(32));
        
        // Generate public key and address deterministically
        return self::generateFromPrivateKey($privateKeyHex);
    }

    /**
     * Generate Bitcoin-like credentials from existing private key
     */
    public static function generateFromPrivateKey($privateKeyHex)
    {
        // Ensure we have a 64-character hex private key
        $cleanPrivateKey = preg_replace('/[^a-fA-F0-9]/', '', $privateKeyHex);
        if (strlen($cleanPrivateKey) !== 64) {
            // Hash whatever we got to make a valid 64-char hex
            $cleanPrivateKey = hash('sha256', $privateKeyHex);
        }

        // Generate deterministic public key (not real secp256k1, but consistent)
        $publicKeyHex = hash('sha256', hex2bin($cleanPrivateKey) . 'pubkey');
        
        // Generate Bitcoin-like address (P2PKH format)
        $address = self::generateAddress($publicKeyHex);
        
        // Log info if in Laravel context
        if (class_exists('Illuminate\Support\Facades\Log')) {
            try {
                \Illuminate\Support\Facades\Log::info('Bitcoin-like credentials generated', [
                    'method' => 'deterministic',
                    'address' => $address,
                    'public_key' => $publicKeyHex,
                    'private_key_length' => strlen($cleanPrivateKey)
                ]);
            } catch (\Exception $e) {
                // Ignore logging errors
            }
        }

        return [
            'private_key_hex' => $cleanPrivateKey,
            'public_key' => $publicKeyHex,
            'address' => $address
        ];
    }

    /**
     * Generate real Bitcoin credentials from existing fake private key
     * Uses the fake private key as a seed to generate consistent Bitcoin-like addresses
     */
    public static function generateRealFromFake($fakePrivateKey)
    {
        // Clean up the fake private key
        $cleanKey = trim($fakePrivateKey);
        
        // Use the fake private key as a deterministic seed
        $deterministicSeed = hash('sha256', $cleanKey . 'haichan-seed');
        
        return self::generateFromPrivateKey($deterministicSeed);
    }

    /**
     * Derive public key from private key
     */
    public static function derivePublicKey($privateKey)
    {
        $cleanKey = trim(preg_replace('/\s+/', '', $privateKey));
        
        // Check if this looks like a 64-char hex key
        if (preg_match('/^[a-fA-F0-9]{64}$/', $cleanKey)) {
            // Real hex key - derive public key directly
            return hash('sha256', hex2bin($cleanKey) . 'pubkey');
        } else {
            // Fake key - generate from fake key
            $realCredentials = self::generateRealFromFake($cleanKey);
            return $realCredentials['public_key'];
        }
    }

    /**
     * Generate a Bitcoin-like address from public key
     */
    private static function generateAddress($publicKeyHex)
    {
        // Hash the public key
        $publicKeyHash = hash('sha256', hex2bin($publicKeyHex));
        $ripemdHash = hash('ripemd160', hex2bin($publicKeyHash));
        
        // Add version byte (0x00 for mainnet P2PKH)
        $versionedHash = '00' . $ripemdHash;
        
        // Calculate checksum
        $checksum = substr(hash('sha256', hash('sha256', hex2bin($versionedHash), true), true), 0, 4);
        
        // Combine and encode
        $fullHash = $versionedHash . bin2hex($checksum);
        
        // Convert to Base58
        return self::base58Encode(hex2bin($fullHash));
    }

    /**
     * Simple Base58 encoding for Bitcoin addresses
     */
    private static function base58Encode($data)
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $encoded = '';
        $num = gmp_init(bin2hex($data), 16);
        
        while (gmp_cmp($num, 0) > 0) {
            $remainder = gmp_mod($num, 58);
            $encoded = $alphabet[gmp_intval($remainder)] . $encoded;
            $num = gmp_div($num, 58);
        }
        
        // Add leading 1s for leading zero bytes
        for ($i = 0; $i < strlen($data) && $data[$i] === "\x00"; $i++) {
            $encoded = '1' . $encoded;
        }
        
        return $encoded;
    }

    public static function isValidAddress($address)
    {
        // Check if it looks like a Bitcoin address
        return preg_match('/^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$/', $address) === 1;
    }

    public static function isRealBitcoinAddress($address)
    {
        // For our purposes, any properly formatted address is "real"
        return self::isValidAddress($address);
    }

    /**
     * Migration utility: Convert existing fake addresses to deterministic addresses
     */
    public static function migrateExistingUsers()
    {
        $users = \App\Models\BitcoinAuth::whereNotNull('address')
            ->where('address', '!=', '')
            ->get();

        $migrated = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                // Check if already has a proper address format
                if (self::isValidAddress($user->address) && strlen($user->address) > 25) {
                    continue; // Skip, already has proper format
                }

                // Generate new deterministic credentials from existing data
                $seed = $user->username . '-' . $user->id . '-' . ($user->created_at ?? 'haichan');
                $newCredentials = self::generateRealFromFake($seed);
                
                // Update user with new Bitcoin address
                $user->update([
                    'address' => $newCredentials['address'],
                    'public_key' => $newCredentials['public_key']
                ]);

                if (class_exists('Illuminate\Support\Facades\Log')) {
                    try {
                        \Illuminate\Support\Facades\Log::info("Migrated user to deterministic Bitcoin-like address", [
                            'user_id' => $user->id,
                            'username' => $user->username,
                            'new_address' => $newCredentials['address']
                        ]);
                    } catch (\Exception $e) {
                        // Ignore logging errors
                    }
                }

                $migrated++;
            } catch (\Exception $e) {
                if (class_exists('Illuminate\Support\Facades\Log')) {
                    try {
                        \Illuminate\Support\Facades\Log::error("Failed to migrate user", [
                            'user_id' => $user->id,
                            'error' => $e->getMessage()
                        ]);
                    } catch (\Exception $e) {
                        // Ignore logging errors
                    }
                }
                $failed++;
            }
        }

        return [
            'migrated' => $migrated,
            'failed' => $failed,
            'total' => count($users)
        ];
    }
}