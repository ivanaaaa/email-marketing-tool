<?php

namespace App\Services;

use App\Exceptions\EmailSendingException;
use App\Models\Campaign;
use App\Models\Customer;
use App\Notifications\CampaignEmailNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailSendingService
{
    /**
     * Process campaign and send emails to all recipients.
     * @throws EmailSendingException
     */
    public function processCampaign(Campaign $campaign): void
    {
        // Update status to processing
        $campaign->update(['status' => 'processing']);

        $groupIds = $campaign->groups->pluck('id')->toArray();

        if (empty($groupIds)) {
            throw new EmailSendingException('Campaign has no target groups assigned');
        }

        $sentCount = 0;
        $failedCount = 0;

        // Process customers in chunks for memory efficiency
        Customer::whereHas('groups', function ($query) use ($groupIds) {
            $query->whereIn('groups.id', $groupIds);
        })
            ->distinct()
            ->chunk(100, function ($customers) use ($campaign, &$sentCount, &$failedCount) {
                foreach ($customers as $customer) {
                    try {
                        $this->sendEmailToCustomer($campaign, $customer);
                        $sentCount++;
                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error('Email sending failed', [
                            'campaign_id' => $campaign->id,
                            'customer_id' => $customer->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    // Update progress periodically
                    if (($sentCount + $failedCount) % 10 === 0) {
                        $this->updateCampaignProgress($campaign, $sentCount, $failedCount);
                    }
                }
            });

        // Final update
        $this->completeCampaign($campaign, $sentCount, $failedCount);
    }

    /**
     * Send email to a single customer using Laravel Notifications.
     * @throws EmailSendingException
     */
    private function sendEmailToCustomer(Campaign $campaign, Customer $customer): void
    {
        $template = $campaign->emailTemplate;

        if (!$template) {
            throw new EmailSendingException("Campaign {$campaign->id} has no email template assigned");
        }

        $placeholderData = $customer->getPlaceholderData();

        $subject = $template->replaceSubjectPlaceholders($placeholderData);
        $body = $template->replaceBodyPlaceholders($placeholderData);

        if (empty($subject) || empty($body)) {
            throw new EmailSendingException("Email subject or body is empty for customer {$customer->id}");
        }

        try {
            $customer->notify(new CampaignEmailNotification($subject, $body));
        } catch (\Exception $e) {
            throw new EmailSendingException(
                "Failed to send email to customer {$customer->id}: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Update campaign progress.
     */
    private function updateCampaignProgress(Campaign $campaign, int $sentCount, int $failedCount): void
    {
        DB::transaction(function () use ($campaign, $sentCount, $failedCount) {
            $campaign->update([
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
            ]);
        });
    }

    /**
     * Mark campaign as completed.
     */
    private function completeCampaign(Campaign $campaign, int $sentCount, int $failedCount): void
    {
        DB::transaction(function () use ($campaign, $sentCount, $failedCount) {
            $campaign->update([
                'status' => 'completed',
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'sent_at' => now(),
            ]);
        });
    }
}
