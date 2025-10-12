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
    public function processCampaign(Campaign $campaign): void
    {
        Log::info('🔵 Processing campaign', [
            'campaign_id' => $campaign->id,
            'email_template_id' => $campaign->email_template_id,
            'group_ids' => $campaign->group_ids
        ]);
        try {
            $campaign->update(['status' => 'processing']);
            $groupIds = $campaign->groups->pluck('id')->toArray();

            if (empty($groupIds)) {
                throw new EmailSendingException('Campaign has no target groups assigned');
            }

            $sentCount = 0;
            $failedCount = 0;
            $hasErrors = false;

            Customer::whereHas('groups', function ($query) use ($groupIds) {
                $query->whereIn('groups.id', $groupIds);
            })
                ->distinct()
                ->chunk(100, function ($customers) use ($campaign, &$sentCount, &$failedCount, &$hasErrors) {
                    foreach ($customers as $customer) {
                        try {
                            $this->sendEmailToCustomer($campaign, $customer);
                            $sentCount++;
                            sleep(1); // Throttle to 1 email/second for Mailtrap
                        } catch (\Exception $e) {
                            $failedCount++;
                            $hasErrors = true;
                            Log::error('Email sending failed', [
                                'campaign_id' => $campaign->id,
                                'customer_id' => $customer->id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }

                        if (($sentCount + $failedCount) % 10 === 0) {
                            $this->updateCampaignProgress($campaign, $sentCount, $failedCount);
                        }
                    }
                });

            $this->completeCampaign($campaign, $sentCount, $failedCount, $hasErrors);
        } catch (\Exception $e) {
            $campaign->update(['status' => 'failed']);
            Log::error('🔴 Campaign processing failed', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
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

        Log::info('Sending email', [
            'campaign_id' => $campaign->id,
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'subject' => $subject
        ]);

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
                'failed_count' => $failedCount
            ]);
        });
    }

    private function completeCampaign(Campaign $campaign, int $sentCount, int $failedCount, bool $hasErrors): void
    {
        DB::transaction(function () use ($campaign, $sentCount, $failedCount, $hasErrors) {
            $status = $hasErrors && $sentCount === 0 ? 'failed' : 'completed';
            $campaign->update([
                'status' => $status,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'sent_at' => now()
            ]);
            Log::info('🔵 Campaign completed', [
                'campaign_id' => $campaign->id,
                'status' => $status,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount
            ]);
        });
    }
}
