<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;

class ProcessSubscriptionRenewals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-renewals {--dry-run : Show what would be processed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process subscription renewals and mark expired subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('Running in dry-run mode. No changes will be made.');
        }

        $this->info('Processing subscription renewals...');

        // Find subscriptions expiring within grace period
        $gracePeriodDays = config('subscription.grace_period_days', 7);
        $expiringSubscriptions = Subscription::where('status', 'active')
            ->where('expires_at', '<=', Carbon::now()->addDays($gracePeriodDays))
            ->get();

        $expiredCount = 0;
        $renewedCount = 0;

        foreach ($expiringSubscriptions as $subscription) {
            if ($subscription->expires_at->isPast()) {
                $this->line("Subscription {$subscription->id} has expired for user {$subscription->user_id}");
                
                if (!$isDryRun) {
                    $subscription->update(['status' => 'expired']);
                }
                $expiredCount++;
            } else {
                $this->line("Subscription {$subscription->id} expires soon: {$subscription->expires_at}");
                
                if ($subscription->auto_renew) {
                    $this->warn("Auto-renewal not implemented yet for subscription {$subscription->id}");
                }
            }
        }

        // Find subscriptions that should be marked as expired
        $pastDueSubscriptions = Subscription::where('status', 'active')
            ->where('expires_at', '<', Carbon::now())
            ->get();

        foreach ($pastDueSubscriptions as $subscription) {
            $this->error("Found past due subscription {$subscription->id} for user {$subscription->user_id}");
            
            if (!$isDryRun) {
                $subscription->update(['status' => 'expired']);
            }
            $expiredCount++;
        }

        if ($isDryRun) {
            $this->info("Dry run complete. Would have expired {$expiredCount} subscriptions and renewed {$renewedCount} subscriptions.");
        } else {
            $this->info("Processing complete. Expired {$expiredCount} subscriptions and renewed {$renewedCount} subscriptions.");
        }

        return Command::SUCCESS;
    }
}
