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
        Log::info('🔵 Processing ProcessCampaignJob', [
            'campaign_id' => $this->campaign->id,
            'email_template_id' => $this->campaign->email_template_id,
            'group_ids' => $this->campaign->group_ids,
            'queue_driver' => config('queue.default')
        ]);
        try {
            $emailSendingService->processCampaign($this->campaign);
            Log::info('🔵 Campaign processed successfully', ['campaign_id' => $this->campaign->id]);
        } catch (\Exception $e) {
            $this->campaign->update(['status' => 'failed']);
            Log::error('🔴 Campaign processing failed', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->fail($e);
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
