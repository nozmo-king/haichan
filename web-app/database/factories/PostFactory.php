<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * FACTORY DISABLED - No fake/dummy post content allowed
 * All posts must be created by real users with authentic content
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        // FACTORY DISABLED - Real user posts only
        throw new \Exception('Post factory disabled - use real user content only. No fake/dummy data allowed.');
    }
}