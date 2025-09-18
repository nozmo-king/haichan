<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AllowedPublicKey;

class SetupAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:setup {public_key?} {--label=Initial Admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up the initial admin by adding their public key to the allowed list';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if there are already admin keys
        $existingKeys = AllowedPublicKey::count();

        if ($existingKeys > 0) {
            $this->error("Admin setup has already been completed. {$existingKeys} public key(s) already exist.");
            $this->info("To add more keys, log in and use the web interface at /admin");
            return 1;
        }

        // Get public key from argument or prompt
        $publicKey = $this->argument('public_key');

        if (!$publicKey) {
            $this->info('Setting up initial admin access...');
            $this->info('You need a secp256k1 public key (33 bytes, compressed format).');
            $this->info('Format: 02xxxxxxx... or 03xxxxxxx... (66 hex characters)');
            $this->newLine();

            $publicKey = $this->ask('Enter your secp256k1 public key');

            if (!$publicKey) {
                $this->error('Public key is required');
                return 1;
            }
        }

        // Clean and validate the public key
        $publicKey = strtolower(trim($publicKey));

        if (!preg_match('/^(02|03)[a-f0-9]{64}$/', $publicKey)) {
            $this->error('Invalid public key format.');
            $this->info('Expected: 66 hex characters starting with 02 or 03');
            $this->info('Example: 0279be667ef9dcbbac55a06295ce870b07029bfcdb2dce28d959f2815b16f81798');
            return 1;
        }

        // Get label
        $label = $this->option('label');
        if (!$label && !$this->argument('public_key')) {
            $label = $this->ask('Enter a label for this key (optional)', 'Initial Admin');
        }

        try {
            // Create the admin key
            $allowedKey = AllowedPublicKey::create([
                'public_key' => $publicKey,
                'label' => $label,
                'is_active' => true
            ]);

            $this->info('✅ Admin setup completed successfully!');
            $this->newLine();
            $this->info("Public Key: {$allowedKey->public_key}");
            $this->info("Label: {$allowedKey->label}");
            $this->info("Status: Active");
            $this->newLine();
            $this->info('You can now log in at /login using your private key.');
            $this->info('After logging in, visit /admin to manage the system.');

            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to create admin key: ' . $e->getMessage());
            return 1;
        }
    }
}
