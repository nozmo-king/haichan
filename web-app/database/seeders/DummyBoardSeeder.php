<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Board;
use App\Models\BitcoinAuth;

class DummyBoardSeeder extends Seeder
{
    public function run(): void
    {
        Board::updateOrCreate(
            ['code' => 'd'],
            [
                'name' => '/d/ - Dummy',
                'description' => 'Testing board - no auth required. Perfect for testing without accounts!',
            ]
        );

        BitcoinAuth::updateOrCreate(
            ['address' => 'DUMMY_TESTER_ADDRESS'],
            [
                'public_key' => 'dummy_public_key_for_testing',
                'username' => 'DummyTester',
                'display_name' => 'Dummy Tester',
                'bio' => 'Auto-generated test user for /d/ board',
                'mining_power' => 1.0,
                'total_pow_points' => 0,
                'invite_code' => 'DUMMY000000',
                'mining_streak' => 0,
                'level' => 1,
                'is_banned' => false,
                'is_admin' => false,
                'is_moderator' => false,
            ]
        );

        $this->command->info('✓ Created /d/ - Dummy board and Dummy Tester user');
    }
}
