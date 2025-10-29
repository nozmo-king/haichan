<?php

namespace Database\Factories;

use App\Models\Thread;
use App\Models\Board;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * FACTORY DISABLED - No fake/dummy thread content allowed
 * All threads must be created by real users with authentic content
 */
class ThreadFactory extends Factory
{
    protected $model = Thread::class;

    public function definition(): array
    {
        // FACTORY DISABLED - Real user threads only
        throw new \Exception('Thread factory disabled - use real user content only. No fake/dummy data allowed.');
    }
}