<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BitcoinAddressGenerator;

class MigrateBitcoinAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitcoin:migrate-addresses 
                           {--dry-run : Show what would be migrated without making changes}
                           {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing fake Bitcoin addresses to real ones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Bitcoin Address Migration Tool');
        $this->newLine();

        // Check existing users
        $users = \App\Models\BitcoinAuth::whereNotNull('address')
            ->where('address', '!=', '')
            ->get();

        if ($users->isEmpty()) {
            $this->info('No users found with Bitcoin addresses.');
            return 0;
        }

        $realCount = 0;
        $fakeCount = 0;

        // Analyze existing addresses
        foreach ($users as $user) {
            if (BitcoinAddressGenerator::isRealBitcoinAddress($user->address)) {
                $realCount++;
            } else {
                $fakeCount++;
            }
        }

        $this->table(
            ['Status', 'Count'], 
            [
                ['Real Bitcoin Addresses', $realCount],
                ['Fake Addresses (need migration)', $fakeCount],
                ['Total Users', count($users)]
            ]
        );

        if ($fakeCount === 0) {
            $this->info('✅ All users already have real Bitcoin addresses!');
            return 0;
        }

        if ($this->option('dry-run')) {
            $this->info("🔍 DRY RUN: Would migrate $fakeCount fake addresses to real Bitcoin addresses");
            return 0;
        }

        // Confirm migration
        if (!$this->option('force')) {
            if (!$this->confirm("⚠️  This will replace $fakeCount fake Bitcoin addresses with real ones. Continue?")) {
                $this->info('Migration cancelled.');
                return 0;
            }
        }

        // Perform migration
        $this->info('🚀 Starting migration...');
        $progressBar = $this->output->createProgressBar($fakeCount);

        $results = BitcoinAddressGenerator::migrateExistingUsers();

        $progressBar->finish();
        $this->newLine(2);

        // Show results
        $this->table(
            ['Result', 'Count'],
            [
                ['Successfully Migrated', $results['migrated']],
                ['Failed', $results['failed']],
                ['Total Processed', $results['total']]
            ]
        );

        if ($results['migrated'] > 0) {
            $this->info("✅ Successfully migrated {$results['migrated']} users to real Bitcoin addresses!");
            $this->newLine();
            $this->warn('⚠️  IMPORTANT: Users will need to use their old private keys to log in.');
            $this->warn('   Their addresses changed but authentication still uses the original private keys.');
        }

        if ($results['failed'] > 0) {
            $this->error("❌ Failed to migrate {$results['failed']} users. Check logs for details.");
        }

        return $results['failed'] > 0 ? 1 : 0;
    }
}
