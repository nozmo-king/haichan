<?php

namespace Database\Seeders;

use App\Models\AllowedPublicKey;
use App\Models\FriendCode;
use App\Models\User;
use Illuminate\Database\Seeder;
use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Random\RandomGeneratorFactory;

class InitialUserSeeder extends Seeder
{
    /**
     * Seed the database with an initial user and their friend code.
     */
    public function run(): void
    {
        // Check if an initial admin user already exists
        $existingAdmin = AllowedPublicKey::where('label', 'Initial Admin User')->first();
        if ($existingAdmin) {
            $this->command->warn('Initial admin user already exists. Skipping creation.');

            return;
        }

        // Generate a secp256k1 keypair
        $generator = EccFactory::getSecgCurves()->generator256k1();
        $randomGenerator = RandomGeneratorFactory::getRandomGenerator();

        // Generate private key
        $privateKey = $generator->createPrivateKey($randomGenerator);
        $publicKeyPoint = $privateKey->getPublicKey()->getPoint();

        // Get compressed public key format (33 bytes)
        $x = $publicKeyPoint->getX();
        $y = $publicKeyPoint->getY();

        // Determine if y is even or odd
        $prefix = gmp_mod($y, gmp_init(2)) == 0 ? '02' : '03';

        // Create compressed public key (prefix + x coordinate)
        $xHex = str_pad(gmp_strval($x, 16), 64, '0', STR_PAD_LEFT);
        $compressedPublicKey = $prefix.$xHex;

        // Get private key as hex
        $privateKeyHex = str_pad(gmp_strval($privateKey->getSecret(), 16), 64, '0', STR_PAD_LEFT);

        // Create allowed public key entry
        $allowedKey = AllowedPublicKey::create([
            'public_key' => $compressedPublicKey,
            'label' => 'Initial Admin User',
            'is_active' => true,
        ]);

        // Create user
        $user = User::create([
            'allowed_public_key_id' => $allowedKey->id,
        ]);

        // Generate a friend code for this user (32 characters, matching FriendCodeService)
        $friendCode = \Illuminate\Support\Str::random(32);

        FriendCode::create([
            'user_id' => $user->id,
            'code' => $friendCode,
            'expires_at' => now()->addDays(365), // Long expiry for initial code
        ]);

        $this->command->info('Initial user created successfully!');
        $this->command->info('Public Key: '.$compressedPublicKey);
        $this->command->info('Private Key: '.$privateKeyHex);
        $this->command->info('Friend Code: '.$friendCode);
        $this->command->warn('IMPORTANT: Save these credentials securely! The private key will not be stored.');
    }
}
