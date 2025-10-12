<?php

namespace Tests\Unit;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EmailTemplateService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EmailTemplateService();
        $this->user = User::factory()->create();
    }

    public function test_can_create_email_template()
    {
        $data = [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body' => 'Test Body',
        ];

        $template = $this->service->create($this->user, $data);

        $this->assertInstanceOf(EmailTemplate::class, $template);
        $this->assertEquals('Test Template', $template->name);
        $this->assertEquals($this->user->id, $template->user_id);
    }

    public function test_can_update_email_template()
    {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => 'Updated Name',
            'subject' => 'Updated Subject',
            'body' => 'Updated Body',
        ];

        $updatedTemplate = $this->service->update($template, $updateData);

        $this->assertEquals('Updated Name', $updatedTemplate->name);
        $this->assertEquals('Updated Subject', $updatedTemplate->subject);
    }

    public function test_can_preview_template_with_placeholders()
    {
        $template = EmailTemplate::factory()->create([
            'user_id' => $this->user->id,
            'subject' => 'Hello {first_name}!',
            'body' => 'Welcome {full_name}!',
        ]);

        $sampleData = [
            'first_name' => 'John',
            'full_name' => 'John Doe',
        ];

        $preview = $this->service->preview($template, $sampleData);

        $this->assertEquals('Hello John!', $preview['subject']);
        $this->assertEquals('Welcome John Doe!', $preview['body']);
    }

    public function test_cannot_delete_template_used_in_campaigns()
    {
        $template = EmailTemplate::factory()->create(['user_id' => $this->user->id]);

        // Create a campaign using this template
        \App\Models\Campaign::factory()->create([
            'user_id' => $this->user->id,
            'email_template_id' => $template->id,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete template that is used in campaigns.');

        $this->service->delete($template);
    }
}
