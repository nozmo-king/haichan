<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Board::create([
            'code' => 'gen',
            'name' => 'General',
            'description' => 'General discussion and random topics'
        ]);

        \App\Models\Board::create([
            'code' => 'film',
            'name' => 'Film & Television',
            'description' => 'Movies, TV shows, and cinematic discussion'
        ]);

        \App\Models\Board::create([
            'code' => 'biz',
            'name' => 'Business & Finance',
            'description' => 'Business, economics, and financial discussion'
        ]);

        \App\Models\Board::create([
            'code' => 'lit',
            'name' => 'Literature',
            'description' => 'Books, writing, and literary discussion'
        ]);

        \App\Models\Board::create([
            'code' => 'x',
            'name' => 'Paranormal',
            'description' => 'Paranormal, conspiracy theories, and unexplained phenomena'
        ]);

        \App\Models\Board::create([
            'code' => 'meta',
            'name' => 'Meta',
            'description' => 'Site discussion, feedback, and meta topics'
        ]);

        \App\Models\Board::create([
            'code' => 'mu',
            'name' => 'Music',
            'description' => 'Music discussion, sharing, and reviews'
        ]);
    }
}
