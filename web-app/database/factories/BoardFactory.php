<?php

namespace Database\Factories;

use App\Models\Board;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * FACTORY DISABLED - No fake/dummy board content allowed
 * All boards must be manually created with real, purposeful content
 */
class BoardFactory extends Factory
{
    protected $model = Board::class;

    public function definition(): array
    {
        // FACTORY DISABLED - Real boards only
        throw new \Exception('Board factory disabled - use real board content only. No fake/dummy data allowed.');
    }
}