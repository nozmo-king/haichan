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
            'code' => 'mov',
            'name' => 'Movies & TV',
            'description' => 'Movies, television shows, and entertainment'
        ]);

        \App\Models\Board::create([
            'code' => 'etc',
            'name' => 'Et Cetera',
            'description' => 'Everything else that doesn\'t fit elsewhere'
        ]);

        \App\Models\Board::create([
            'code' => 'biz',
            'name' => 'Business & Finance',
            'description' => 'Business, economics, and financial discussion'
        ]);
    }
}
