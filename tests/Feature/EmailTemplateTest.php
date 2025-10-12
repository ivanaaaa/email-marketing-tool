<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_view_email_templates_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('email-templates.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('EmailTemplates/Index'));
    }

    public function test_user_can_create_email_template()
    {
        $templateData = [
            'name' => 'Welcome Template',
            'subject' => 'Welcome {first_name}!',
            'body' => 'Hello {full_name}, welcome to our platform!',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('email-templates.store'), $templateData);

        $response->assertRedirect(route('email-templates.index'))
            ->assertSessionHas('success', 'Email template created successfully.');

        $this->assertDatabaseHas('email_templates', [
            'name' => 'Welcome Template',
            'subject' => 'Welcome {first_name}!',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_update_email_template()
    {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => 'Updated Template',
            'subject' => 'Updated Subject',
            'body' => 'Updated body content',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('email-templates.update', $template), $updateData);

        $response->assertRedirect(route('email-templates.index'))
            ->assertSessionHas('success', 'Email template updated successfully.');

        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'name' => 'Updated Template',
            'subject' => 'Updated Subject',
        ]);
    }

    public function test_user_can_preview_email_template()
    {
        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'subject' => 'Hello {first_name}!',
            'body' => 'Welcome {full_name} to our platform.',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('email-templates.preview', $template));

        $response->assertOk()
            ->assertJsonStructure(['subject', 'body']);
    }

    public function test_template_creation_requires_all_fields()
    {
        $response = $this->actingAs($this->user)
            ->post(route('email-templates.store'), []);

        $response->assertSessionHasErrors(['name', 'subject', 'body']);
    }

    public function test_user_cannot_access_other_users_templates()
    {
        $otherUser = User::factory()->create();
        $template = EmailTemplate::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get(route('email-templates.edit', $template));

        $response->assertForbidden();
    }
}
