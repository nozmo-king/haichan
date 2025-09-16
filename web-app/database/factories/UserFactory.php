<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // User factory needs an allowed_public_key_id to work with the secp256k1 auth system
        // In testing, you should create AllowedPublicKey records first
        return [
            'allowed_public_key_id' => null, // Must be set manually or via relationships
            'last_challenge' => null,
            'challenge_expires_at' => null,
        ];
    }
}
