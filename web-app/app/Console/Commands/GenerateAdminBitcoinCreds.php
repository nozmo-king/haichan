<?php

namespace App\Console\Commands;

use App\Models\BitcoinAuth;
use App\Models\AllowedPublicKey;
use Illuminate\Console\Command;

class GenerateAdminBitcoinCreds extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'admin:bitcoin-creds';

    /**
     * The description of the console command.
     */
    protected $description = 'Generate Bitcoin credentials for admin account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔑 Generating Bitcoin credentials for admin account...');
        
        // Generate a proper secp256k1 private key (32 bytes)
        $privateKey = bin2hex(random_bytes(32));
        
        // For this demo, we'll use a simplified public key derivation
        // In production, you'd use proper secp256k1 library
        $publicKey = '02' . substr(hash('sha256', $privateKey), 0, 62);
        
        // Generate Bitcoin address (simplified format for demo)
        // In production, you'd use proper RIPEMD-160 + SHA-256 + Base58Check
        $addressHash = substr(hash('sha256', $publicKey), 0, 32);
        // Simple Base58-like encoding (demo only - use proper Base58 in production)
        $base58chars = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $bitcoinAddress = '1';
        for ($i = 0; $i < 26; $i++) {
            $bitcoinAddress .= $base58chars[hexdec(substr($addressHash, $i * 2, 2)) % 58];
        }
        
        $this->info("Private Key: $privateKey");
        $this->info("Public Key: $publicKey");
        $this->info("Bitcoin Address: $bitcoinAddress");
        
        // Update the existing admin account
        $adminUser = BitcoinAuth::where('username', 'jcb')->first();
        
        if (!$adminUser) {
            $this->error('Admin user "jcb" not found. Run database seeder first.');
            return;
        }
        
        // Update admin credentials
        $adminUser->update([
            'public_key' => $publicKey,
            'address' => $bitcoinAddress,
        ]);
        
        // Update the allowed public key
        $allowedKey = AllowedPublicKey::where('public_key', $adminUser->getOriginal('public_key'))->first();
        if ($allowedKey) {
            $allowedKey->update(['public_key' => $publicKey]);
        }
        
        $this->info('✅ Admin Bitcoin credentials updated successfully!');
        $this->info('');
        $this->info('📋 ADMIN LOGIN CREDENTIALS:');
        $this->info('Username: jcb');
        $this->info('Password: @!Qtrin0pz');
        $this->info('');
        $this->info('🔐 BITCOIN CREDENTIALS:');
        $this->info("Private Key: $privateKey");
        $this->info("Public Key: $publicKey");
        $this->info("Bitcoin Address: $bitcoinAddress");
        $this->info('');
        $this->info('⚠️  IMPORTANT: Save the private key securely!');
        $this->info('This private key allows you to recover your account.');
        
        return 0;
    }
}