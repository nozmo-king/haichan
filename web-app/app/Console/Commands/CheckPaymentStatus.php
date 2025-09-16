<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use Carbon\Carbon;

class CheckPaymentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:check-status {--timeout-minutes=120 : Mark payments as failed after this many minutes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check payment status and mark expired payments as failed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeoutMinutes = $this->option('timeout-minutes');
        
        $this->info('Checking payment status...');

        // Find pending payments that have expired
        $expiredPayments = Payment::where('status', 'pending')
            ->where('expires_at', '<', Carbon::now())
            ->get();

        $expiredCount = 0;

        foreach ($expiredPayments as $payment) {
            $this->line("Marking payment {$payment->id} as failed (expired at {$payment->expires_at})");
            
            $payment->update(['status' => 'failed']);
            
            // Also mark associated subscription as cancelled if it was pending
            if ($payment->subscription && $payment->subscription->status === 'pending') {
                $payment->subscription->update(['status' => 'cancelled']);
                $this->line("Cancelled associated subscription {$payment->subscription->id}");
            }
            
            $expiredCount++;
        }

        // Find payments that have been pending for too long (regardless of expires_at)
        $stalePendingPayments = Payment::where('status', 'pending')
            ->where('created_at', '<', Carbon::now()->subMinutes($timeoutMinutes))
            ->get();

        $staleCount = 0;

        foreach ($stalePendingPayments as $payment) {
            if (!$expiredPayments->contains($payment)) { // Don't double-process
                $this->line("Marking stale payment {$payment->id} as failed (pending for over {$timeoutMinutes} minutes)");
                
                $payment->update(['status' => 'failed']);
                
                if ($payment->subscription && $payment->subscription->status === 'pending') {
                    $payment->subscription->update(['status' => 'cancelled']);
                    $this->line("Cancelled associated subscription {$payment->subscription->id}");
                }
                
                $staleCount++;
            }
        }

        $totalProcessed = $expiredCount + $staleCount;

        if ($totalProcessed > 0) {
            $this->info("Processing complete. Marked {$totalProcessed} payments as failed ({$expiredCount} expired, {$staleCount} stale).");
        } else {
            $this->info('No payments needed status updates.');
        }

        // Show summary of current payment status
        $pendingCount = Payment::where('status', 'pending')->count();
        $confirmedCount = Payment::where('status', 'confirmed')->count();
        $failedCount = Payment::where('status', 'failed')->count();

        $this->info("Current payment status: {$pendingCount} pending, {$confirmedCount} confirmed, {$failedCount} failed");

        return Command::SUCCESS;
    }
}
