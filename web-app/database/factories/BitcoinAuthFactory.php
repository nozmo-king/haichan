<?php

namespace Database\Factories;

use App\Models\BitcoinAuth;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * FACTORY DISABLED - No fake/dummy Bitcoin data allowed
 * All Bitcoin authentication must use real, generated credentials
 */
class BitcoinAuthFactory extends Factory
{
    protected $model = BitcoinAuth::class;

    public function definition(): array
    {
        // FACTORY DISABLED - Real Bitcoin auth only
        throw new \Exception('BitcoinAuth factory disabled - use real Bitcoin credentials only. No fake/dummy data allowed.');
    }
}