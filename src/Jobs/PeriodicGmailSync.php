<?php

namespace Webkul\Google\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Webkul\Google\Models\Account;
use Webkul\Google\Services\GmailService;

class PeriodicGmailSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job may run.
     */
    public int $timeout = 600;

    /**
     * Execute the job.
     */
    public function handle(GmailService $gmailService): void
    {
        try {
            Log::info("Starting periodic Gmail sync for all accounts");

            // Get all accounts that have Gmail permissions
            $accounts = Account::whereNotNull('token')
                ->get()
                ->filter(function ($account) {
                    return $account->hasGmailPermissions();
                });

            $totalSynced = 0;

            foreach ($accounts as $account) {
                try {
                    Log::info("Syncing Gmail for account {$account->id}");

                    // Sync recent messages (last 24 hours)
                    $options = [
                        'maxResults' => 50,
                        'query' => 'newer_than:1d'
                    ];

                    $syncedCount = $gmailService->syncMessages($account, $options);
                    $totalSynced += $syncedCount;

                    // Update the account's last sync timestamp
                    $account->update([
                        'gmail_last_sync_at' => now(),
                    ]);

                    Log::info("Gmail sync completed for account {$account->id}. Synced {$syncedCount} messages.");

                } catch (\Exception $e) {
                    Log::error("Gmail sync failed for account {$account->id}: " . $e->getMessage());
                    continue; // Continue with other accounts
                }
            }

            Log::info("Periodic Gmail sync completed. Total messages synced: {$totalSynced}");

        } catch (\Exception $e) {
            Log::error("Periodic Gmail sync failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Periodic Gmail sync job failed: " . $exception->getMessage());
    }
}
