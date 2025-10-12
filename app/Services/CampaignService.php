<?php

namespace App\Services;

use App\Jobs\ProcessCampaignJob;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CampaignService
{
    /**
     * Get all campaigns for a user.
     */
    public function getAllForUser(User $user)
    {
        return $user->campaigns()
            ->with(['emailTemplate', 'groups'])
            ->latest()
            ->get();
    }

    /**
     * Create a new campaign.
     */
    public function create(User $user, array $data): Campaign
    {
        return DB::transaction(function () use ($user, $data) {
            // Determine status based on scheduled_at
            $status = 'draft';
            $scheduledAt = $data['scheduled_at'] ?? null;

            if (!empty($scheduledAt)) {
                $status = 'scheduled';
            }

            $campaign = $user->campaigns()->create([
                'email_template_id' => $data['email_template_id'],
                'name' => $data['name'],
                'status' => $status,
                'scheduled_at' => $scheduledAt,
            ]);

            if (isset($data['group_ids']) && !empty($data['group_ids'])) {
                $campaign->groups()->attach($data['group_ids']);

                // Calculate total recipients
                $totalRecipients = $this->calculateTotalRecipients($data['group_ids']);
                $campaign->update(['total_recipients' => $totalRecipients]);
            }

            return $campaign->load(['emailTemplate', 'groups']);
        });
    }

    /**
     * Update an existing campaign.
     */
    /**
     * Update an existing campaign.
     */
    public function update(Campaign $campaign, array $data): Campaign
    {
        // Only allow updates if campaign is in draft or scheduled status
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            throw new \Exception('Cannot update campaign that is not in draft or scheduled status.');
        }

        return DB::transaction(function () use ($campaign, $data) {
            // Determine new status based on scheduled_at
            $status = 'draft';
            $scheduledAt = $data['scheduled_at'] ?? null;

            if (!empty($scheduledAt)) {
                $status = 'scheduled';
            }

            $campaign->update([
                'email_template_id' => $data['email_template_id'],
                'name' => $data['name'],
                'status' => $status,
                'scheduled_at' => $scheduledAt,
            ]);

            if (isset($data['group_ids'])) {
                $campaign->groups()->sync($data['group_ids']);

                // Recalculate total recipients
                $totalRecipients = $this->calculateTotalRecipients($data['group_ids']);
                $campaign->update(['total_recipients' => $totalRecipients]);
            }

            return $campaign->load(['emailTemplate', 'groups']);
        });
    }

    /**
     * Delete a campaign.
     */
    public function delete(Campaign $campaign): bool
    {
        // Only allow deletion if campaign is in draft status
        if ($campaign->status !== 'draft') {
            throw new \Exception('Cannot delete campaign that is not in draft status.');
        }

        return DB::transaction(function () use ($campaign) {
            $campaign->groups()->detach();
            return $campaign->delete();
        });
    }

    /**
     * Schedule a campaign for future sending.
     */
    public function schedule(Campaign $campaign, \DateTime $scheduledAt): Campaign
    {
        if ($campaign->status !== 'draft') {
            throw new \Exception('Can only schedule campaigns in draft status.');
        }

        $campaign->update([
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAt,
        ]);

        return $campaign;
    }

    /**
     * Send campaign immediately.
     */
    public function sendNow(Campaign $campaign): Campaign
    {
        Log::info('🔵 sendNow() METHOD CALLED', [
            'campaign_id' => $campaign->id,
            'current_status' => $campaign->status,
        ]);

        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            Log::error('🔴 Status check failed', [
                'campaign_id' => $campaign->id,
                'status' => $campaign->status,
            ]);
            throw new \Exception('Can only send campaigns in draft status.');
        }

        $campaign->update([
            'status' => 'scheduled',
            'scheduled_at' => now(),
        ]);

        Log::info('🔵 About to dispatch job', [
            'campaign_id' => $campaign->id,
            'queue_driver' => config('queue.default'),
        ]);
        // Dispatch job to process campaign immediately
        ProcessCampaignJob::dispatch($campaign);

        Log::info('Campaign dispatched for immediate sending', [
            'campaign_id' => $campaign->id,
            'campaign_name' => $campaign->name,
        ]);

        return $campaign;
    }

    /**
     * Calculate total recipients for given group IDs.
     */
    private function calculateTotalRecipients(array $groupIds): int
    {
        return DB::table('customer_group')
            ->whereIn('group_id', $groupIds)
            ->distinct('customer_id')
            ->count('customer_id');
    }

    /**
     * Get campaign statistics.
     */
    public function getStatistics(Campaign $campaign): array
    {
        return [
            'total_recipients' => $campaign->total_recipients,
            'sent_count' => $campaign->sent_count,
            'failed_count' => $campaign->failed_count,
            'pending_count' => $campaign->total_recipients - $campaign->sent_count - $campaign->failed_count,
            'progress_percentage' => $campaign->getProgressPercentage(),
            'status' => $campaign->status,
        ];
    }
}
