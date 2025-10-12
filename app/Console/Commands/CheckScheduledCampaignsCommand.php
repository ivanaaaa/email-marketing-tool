<?php

namespace App\Console\Commands;

use App\Jobs\ProcessCampaignJob;
use App\Models\Campaign;
use Illuminate\Console\Command;

class CheckScheduledCampaignsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:check-scheduled-campaigns-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for scheduled campaigns that are ready to be processed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $campaigns = Campaign::readyToProcess()->get();

        if ($campaigns->isEmpty()) {
            $this->info('No campaigns ready to process.');
            return self::SUCCESS;
        }

        foreach ($campaigns as $campaign) {
            $this->info("Dispatching campaign: {$campaign->name} (ID: {$campaign->id})");
            ProcessCampaignJob::dispatch($campaign);
        }

        $this->info("Dispatched {$campaigns->count()} campaign(s) for processing.");

        return self::SUCCESS;
    }
}
