<?php
// tests/Feature/CampaignTest.php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_campaign_helper_methods()
    {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        // Create draft campaign
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
            'status' => 'draft',
            'scheduled_at' => null,
        ]);

        // ✅ USE isDraft()
        $this->assertTrue($campaign->isDraft());
        $this->assertFalse($campaign->isScheduled());
        $this->assertFalse($campaign->isProcessing());
        $this->assertFalse($campaign->isCompleted());

        // ✅ USE canBeEdited(), canBeSent(), canBeDeleted()
        $this->assertTrue($campaign->canBeEdited());
        $this->assertTrue($campaign->canBeSent());
        $this->assertTrue($campaign->canBeDeleted());
    }

    public function test_scheduled_campaign_helper_methods()
    {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
            'status' => 'scheduled',
            'scheduled_at' => now()->addHour(),
        ]);

        // ✅ USE isScheduled()
        $this->assertTrue($campaign->isScheduled());
        $this->assertFalse($campaign->isReadyToProcess());

        // ✅ USE canBeEdited()
        $this->assertTrue($campaign->canBeEdited());
        $this->assertFalse($campaign->canBeDeleted());
    }

    public function test_ready_to_process_scope()
    {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        // Campaign scheduled in the past
        $pastCampaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
            'status' => 'scheduled',
            'scheduled_at' => now()->subMinute(),
        ]);

        // Campaign scheduled in the future
        $futureCampaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
            'status' => 'scheduled',
            'scheduled_at' => now()->addHour(),
        ]);

        // ✅ USE scopeReadyToProcess
        $readyCampaigns = Campaign::readyToProcess()->get();

        $this->assertTrue($readyCampaigns->contains($pastCampaign));
        $this->assertFalse($readyCampaigns->contains($futureCampaign));

        // ✅ USE isReadyToProcess()
        $this->assertTrue($pastCampaign->isReadyToProcess());
        $this->assertFalse($futureCampaign->isReadyToProcess());
    }

    public function test_completed_campaign_helper_methods()
    {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
            'status' => 'completed',
            'sent_at' => now(),
        ]);

        // ✅ USE isCompleted()
        $this->assertTrue($campaign->isCompleted());
        $this->assertFalse($campaign->canBeEdited());
        $this->assertFalse($campaign->canBeSent());
        $this->assertFalse($campaign->canBeDeleted());
    }

    public function test_email_template_relationship()
    {
        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Welcome Email',
        ]);

        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
        ]);

        // ✅ USE emailTemplate relationship
        $this->assertNotNull($campaign->emailTemplate);
        $this->assertEquals('Welcome Email', $campaign->emailTemplate->name);
    }

    public function test_processing_campaign_cannot_be_edited()
    {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
            'status' => 'processing',
        ]);

        // ✅ USE isProcessing()
        $this->assertTrue($campaign->isProcessing());
        $this->assertFalse($campaign->canBeEdited());
        $this->assertFalse($campaign->canBeSent());
        $this->assertFalse($campaign->canBeDeleted());
    }
}
