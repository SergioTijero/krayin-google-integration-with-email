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

class SyncGmailMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job may run.
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Account $account,
        public array $options = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(GmailService $gmailService): void
    {
        try {
            Log::info("Starting Gmail sync for account {$this->account->id}");

            $syncedCount = $gmailService->syncMessages($this->account, $this->options);

            Log::info("Gmail sync completed for account {$this->account->id}. Synced {$syncedCount} messages.");

            // Update the account's last sync timestamp
            $this->account->update([
                'gmail_last_sync_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error("Gmail sync failed for account {$this->account->id}: " . $e->getMessage());
            
            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Gmail sync job failed for account {$this->account->id}: " . $exception->getMessage());
    }
}
