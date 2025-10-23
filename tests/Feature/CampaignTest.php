<?php
// tests/Feature/CampaignTest.php

namespace Tests\Feature;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\Group;
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

    public function test_campaign_uses_enum_correctly()
    {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        // ✅ CREATE WITH ENUM
        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
            'status' => CampaignStatus::DRAFT,
        ]);

        // ✅ ASSERT ENUM TYPE
        $this->assertInstanceOf(CampaignStatus::class, $campaign->status);
        $this->assertEquals(CampaignStatus::DRAFT, $campaign->status);
        $this->assertEquals('draft', $campaign->status->value);
        $this->assertEquals('Draft', $campaign->status->label());
        $this->assertEquals('bg-gray-500', $campaign->status->color());
    }

    public function test_enum_helper_methods()
    {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        // ✅ TEST DRAFT STATUS
        $draftCampaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
            'status' => CampaignStatus::DRAFT,
        ]);

        $this->assertTrue($draftCampaign->isDraft());
        $this->assertTrue($draftCampaign->canBeEdited());
        $this->assertTrue($draftCampaign->canBeSent());
        $this->assertTrue($draftCampaign->canBeDeleted());
        $this->assertFalse($draftCampaign->status->isFinal());

        // ✅ TEST COMPLETED STATUS
        $completedCampaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
            'status' => CampaignStatus::COMPLETED,
            'sent_at' => now(),
        ]);

        $this->assertTrue($completedCampaign->isCompleted());
        $this->assertFalse($completedCampaign->canBeEdited());
        $this->assertFalse($completedCampaign->canBeSent());
        $this->assertFalse($completedCampaign->canBeDeleted());
        $this->assertTrue($completedCampaign->status->isFinal());
    }

    public function test_enum_values_method()
    {
        $values = CampaignStatus::values();

        $this->assertIsArray($values);
        $this->assertCount(5, $values);
        $this->assertContains('draft', $values);
        $this->assertContains('scheduled', $values);
        $this->assertContains('processing', $values);
        $this->assertContains('completed', $values);
        $this->assertContains('failed', $values);
    }

    public function test_enum_options_method()
    {
        $options = CampaignStatus::options();

        $this->assertIsArray($options);
        $this->assertCount(5, $options);

        foreach ($options as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('color', $option);
        }
    }

    public function test_campaign_status_transitions()
    {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        $campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
            'status' => CampaignStatus::DRAFT,
        ]);

        // Draft → Scheduled
        $campaign->update(['status' => CampaignStatus::SCHEDULED]);
        $this->assertEquals(CampaignStatus::SCHEDULED, $campaign->fresh()->status);

        // Scheduled → Processing
        $campaign->update(['status' => CampaignStatus::PROCESSING]);
        $this->assertEquals(CampaignStatus::PROCESSING, $campaign->fresh()->status);

        // Processing → Completed
        $campaign->update(['status' => CampaignStatus::COMPLETED]);
        $this->assertEquals(CampaignStatus::COMPLETED, $campaign->fresh()->status);
        $this->assertTrue($campaign->fresh()->status->isFinal());
    }

    public function test_validation_rejects_invalid_status()
    {
        $this->actingAs($this->user);

        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);
        $group = Group::factory()->create(['user_id' => $this->user->id]);

        $response = $this->post(route('campaigns.store'), [
            'name' => 'Test Campaign',
            'email_template_id' => $template->id,
            'group_ids' => [$group->id],
            'status' => 'invalid_status', // ✅ INVALID VALUE
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_factory_states_use_enum()
    {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        // ✅ TEST FACTORY STATES
        $scheduled = Campaign::factory()->scheduled()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
        ]);
        $this->assertEquals(CampaignStatus::SCHEDULED, $scheduled->status);

        $processing = Campaign::factory()->processing()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
        ]);
        $this->assertEquals(CampaignStatus::PROCESSING, $processing->status);

        $completed = Campaign::factory()->completed()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
        ]);
        $this->assertEquals(CampaignStatus::COMPLETED, $completed->status);

        $failed = Campaign::factory()->failed()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
        ]);
        $this->assertEquals(CampaignStatus::FAILED, $failed->status);
    }
}
