<?php

namespace App\Console\Commands;

use App\Services\FriendCodeService;
use Illuminate\Console\Command;

class CleanupExpiredFriendCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'friend-codes:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired and unused friend codes';

    public function __construct(
        private FriendCodeService $friendCodeService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting friend code cleanup...');

        $deletedCount = $this->friendCodeService->cleanupExpiredCodes();

        $this->info("Cleaned up {$deletedCount} expired friend codes.");

        return Command::SUCCESS;
    }
}
