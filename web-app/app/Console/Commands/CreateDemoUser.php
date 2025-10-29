<?php

namespace App\Console\Commands;

use App\Models\BitcoinAuth;
use App\Models\AllowedPublicKey;
use App\Models\User;
use Illuminate\Console\Command;

class CreateDemoUser extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user:create-demo';

    /**
     * The description of the console command.
     */
    protected $description = 'Create a demo user with Bitcoin credentials';

    /**
     * COMMAND DISABLED - Execute the console command.
     */
    public function handle()
    {
        $this->error('❌ DEMO USER CREATION DISABLED');
        $this->error('This is a LIVE PRODUCTION SYSTEM.');
        $this->error('NO DEMO/FAKE/TEST USERS ALLOWED.');
        $this->info('');
        $this->info('For admin users: Use php artisan make:admin-user');
        $this->info('Regular users: Register at /register with real Bitcoin keys');
        $this->info('Anonymous users: Auto-generate real keys on first visit');
        
        return 1; // Exit with error code
        
        // OLD CODE DISABLED:
        // $this->info('🔑 Creating demo user with Bitcoin credentials...');
        
        /*
        ALL DEMO USER CREATION CODE DISABLED
        
        This was creating fake users with dummy data which is strictly forbidden.
        No demo users, no fake content, no placeholder accounts allowed.
        
        Original code disabled for production use.
        */
    }
}