<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['pubkey_hex' => '02abcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890'],
            []
        );

        // You can add more demo users here if needed
    }
}
