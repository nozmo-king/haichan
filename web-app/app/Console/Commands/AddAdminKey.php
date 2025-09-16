<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AllowedPublicKey;

class AddAdminKey extends Command
{
    protected $signature = 'admin:add-key {public_key} {--label=Admin Key}';
    protected $description = 'Add a secp256k1 public key for admin access';

    public function handle()
    {
        $publicKey = $this->argument('public_key');
        $label = $this->option('label');

        // Validate public key format (compressed secp256k1)
        if (!preg_match('/^(02|03)[a-f0-9]{64}$/i', $publicKey)) {
            $this->error('Invalid secp256k1 public key format. Must be 66 hex characters starting with 02 or 03.');
            return 1;
        }

        // Check if key already exists
        if (AllowedPublicKey::where('public_key', strtolower($publicKey))->exists()) {
            $this->error('This public key already exists in the database.');
            return 1;
        }

        // Add the key
        AllowedPublicKey::create([
            'public_key' => strtolower($publicKey),
            'label' => $label,
            'is_active' => true,
        ]);

        $this->info("Admin key added successfully!");
        $this->info("Public Key: {$publicKey}");
        $this->info("Label: {$label}");
        $this->info("You can now access /admin after authenticating with this key.");

        return 0;
    }
}