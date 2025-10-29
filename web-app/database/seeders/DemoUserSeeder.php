<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * SEEDER DISABLED - NO DUMMY/DEMO USERS ALLOWED
 * 
 * This is a live production system for 256 real users.
 * All user accounts must be created through proper registration
 * with real Bitcoin credentials and authentic proof-of-work mining.
 * 
 * NO FAKE DATA, NO DEMO ACCOUNTS, NO PLACEHOLDER CONTENT.
 */
class DemoUserSeeder extends Seeder
{
    /**
     * SEEDER DISABLED - Real users only
     */
    public function run(): void
    {
        $this->command->error('❌ DEMO USER SEEDER DISABLED');
        $this->command->error('This is a live production system.');
        $this->command->error('All users must register with real Bitcoin credentials.');
        $this->command->error('NO DUMMY/FAKE/DEMO ACCOUNTS ALLOWED.');
        $this->command->info('');
        $this->command->info('To create admin user, use: php artisan make:admin-user');
        $this->command->info('Users register at: /register with real Bitcoin keys');
        
        throw new \Exception('Demo user seeder disabled - live system requires real users only');
    }
}
