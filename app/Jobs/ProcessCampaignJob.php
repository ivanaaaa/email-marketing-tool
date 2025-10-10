<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\EmailSendingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Campaign $campaign
    ) {}

    /**
     * Execute the job.
     */
    public function handle(EmailSendingService $emailSendingService): void
    {
        Log::info('Processing campaign', ['campaign_id' => $this->campaign->id]);

        try {
            $emailSendingService->processCampaign($this->campaign);

            Log::info('Campaign processed successfully', [
                'campaign_id' => $this->campaign->id,
                'sent_count' => $this->campaign->sent_count,
                'failed_count' => $this->campaign->failed_count,
            ]);
        } catch (\Exception $e) {
            Log::error('Campaign processing failed', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage(),
            ]);

            $this->campaign->update(['status' => 'failed']);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Campaign job failed permanently', [
            'campaign_id' => $this->campaign->id,
            'error' => $exception->getMessage(),
        ]);

        $this->campaign->update(['status' => 'failed']);
    }
}
