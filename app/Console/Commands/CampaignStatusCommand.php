<?php
// app/Console/Commands/CampaignStatusCommand.php

namespace App\Console\Commands;

use App\Models\Campaign;
use Illuminate\Console\Command;

class CampaignStatusCommand extends Command
{
    protected $signature = 'campaign:status {--all : Show all campaigns}';
    protected $description = 'Display campaign status information';

    public function handle()
    {
        $campaigns = $this->option('all')
            ? Campaign::with('emailTemplate')->get()
            : Campaign::readyToProcess()->with('emailTemplate')->get();

        if ($campaigns->isEmpty()) {
            $this->info('No campaigns found.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Status', 'Template', 'Progress', 'Checks'],
            $campaigns->map(function ($campaign) {
                $checks = [];

                if ($campaign->isDraft()) $checks[] = 'Draft';
                if ($campaign->isScheduled()) $checks[] = 'Scheduled (future)';
                if ($campaign->isReadyToProcess()) $checks[] = 'Ready to Process!';
                if ($campaign->isProcessing()) $checks[] = 'Processing';
                if ($campaign->isCompleted()) $checks[] = 'Completed';
                if ($campaign->isFailed()) $checks[] = 'Failed';

                if ($campaign->canBeEdited()) $checks[] = 'Can Edit';
                if ($campaign->canBeSent()) $checks[] = 'Can Send';
                if ($campaign->canBeDeleted()) $checks[] = 'Can Delete';

                return [
                    $campaign->id,
                    $campaign->name,
                    $campaign->status,
                    $campaign->emailTemplate?->name ?? 'N/A',
                    $campaign->getProgressPercentage() . '%',
                    implode(', ', $checks),
                ];
            })
        );

        return self::SUCCESS;
    }
}
