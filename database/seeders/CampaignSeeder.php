<?php
// database/seeders/CampaignSeeder.php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
        ]);

        $template = EmailTemplate::factory()->create([
            'user_id' => $user->id,
            'name' => 'Demo Template',
            'subject' => 'Hello {{first_name}}!',
            'body' => 'Welcome {{full_name}}!',
        ]);

        $group = Group::factory()->create([
            'user_id' => $user->id,
            'name' => 'Demo Group',
        ]);

        $customers = Customer::factory()->count(10)->create([
            'user_id' => $user->id,
        ]);

        $group->customers()->attach($customers);

        // Create campaigns in different states
        $draftCampaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'email_template_id' => $template->id,
            'name' => 'Draft Campaign',
            'status' => 'draft',
        ]);
        $draftCampaign->groups()->attach($group);

        // ✅ USE isDraft() to verify
        if ($draftCampaign->isDraft()) {
            $this->command->info("✓ Draft campaign created: {$draftCampaign->name}");
        }

        $scheduledCampaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'email_template_id' => $template->id,
            'name' => 'Scheduled Campaign',
            'status' => 'scheduled',
            'scheduled_at' => now()->addHour(),
        ]);
        $scheduledCampaign->groups()->attach($group);

        // ✅ USE isScheduled() to verify
        if ($scheduledCampaign->isScheduled()) {
            $this->command->info("✓ Scheduled campaign created: {$scheduledCampaign->name}");
        }

        $readyCampaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'email_template_id' => $template->id,
            'name' => 'Ready Campaign',
            'status' => 'scheduled',
            'scheduled_at' => now()->subMinute(),
        ]);
        $readyCampaign->groups()->attach($group);

        // ✅ USE isReadyToProcess() to verify
        if ($readyCampaign->isReadyToProcess()) {
            $this->command->info("✓ Ready campaign created: {$readyCampaign->name}");
        }

        $completedCampaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'email_template_id' => $template->id,
            'name' => 'Completed Campaign',
            'status' => 'completed',
            'sent_at' => now(),
            'total_recipients' => 10,
            'sent_count' => 10,
        ]);
        $completedCampaign->groups()->attach($group);

        // ✅ USE isCompleted() to verify
        if ($completedCampaign->isCompleted()) {
            $this->command->info("✓ Completed campaign created: {$completedCampaign->name}");
        }

        // ✅ USE emailTemplate relationship
        $this->command->info("All campaigns use template: {$template->name}");
    }
}
