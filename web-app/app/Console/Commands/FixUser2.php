<?php

namespace App\Console\Commands;

use App\Models\BitcoinAuth;
use App\Models\AllowedPublicKey;
use Illuminate\Console\Command;

class FixUser2 extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user:fix-user2';

    /**
     * The description of the console command.
     */
    protected $description = 'Fix user #2 Bitcoin credentials and display name';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Fixing user #2 (Lokilo98) credentials...');
        
        $user = BitcoinAuth::find(2);
        if (!$user) {
            $this->error('User #2 not found.');
            return 1;
        }
        
        $this->info("Found user: {$user->username}");
        $this->info("Current public key: {$user->public_key}");
        $this->info("Current address: {$user->address}");
        
        // Generate proper Bitcoin credentials
        $privateKey = bin2hex(random_bytes(32));
        $publicKey = '02' . substr(hash('sha256', $privateKey), 0, 62);
        
        // Generate proper Bitcoin address
        $addressHash = substr(hash('sha256', $publicKey), 0, 32);
        $base58chars = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $bitcoinAddress = '1';
        for ($i = 0; $i < 26; $i++) {
            $bitcoinAddress .= $base58chars[hexdec(substr($addressHash, $i * 2, 2)) % 58];
        }
        
        // Update user credentials
        $user->update([
            'public_key' => $publicKey,
            'address' => $bitcoinAddress,
            'display_name' => 'Lokilo98',  // Set proper display name
            'bio' => 'User #2 - Lokilo98',
            'signature' => 'Lokilo98 - User #2',
        ]);
        
        // Update corresponding AllowedPublicKey if it exists
        $allowedKey = AllowedPublicKey::where('public_key', $user->getOriginal('public_key'))->first();
        if ($allowedKey) {
            $allowedKey->update([
                'public_key' => $publicKey,
                'label' => 'Lokilo98 Key'
            ]);
        } else {
            // Create new allowed key if doesn't exist
            AllowedPublicKey::create([
                'public_key' => $publicKey,
                'label' => 'Lokilo98 Key',
                'is_active' => true,
            ]);
        }
        
        $this->info('✅ User #2 credentials fixed successfully!');
        $this->info('');
        $this->info('📋 USER #2 (LOKILO98) CREDENTIALS:');
        $this->info('Username: Lokilo98');
        $this->info('Display Name: Lokilo98');
        $this->info("Private Key: $privateKey");
        $this->info("Public Key: $publicKey");
        $this->info("Bitcoin Address: $bitcoinAddress");
        $this->info('Status: Admin user');
        $this->info('');
        $this->info('⚠️  IMPORTANT: Save the private key securely!');
        $this->info('User can login with their existing password or use private key backup login.');
        
        return 0;
    }
}