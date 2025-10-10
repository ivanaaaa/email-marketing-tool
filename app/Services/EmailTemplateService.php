<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmailTemplateService
{
    /**
     * Get all email templates for a user.
     */
    public function getAllForUser(User $user)
    {
        return $user->emailTemplates()
            ->latest()
            ->get();
    }

    /**
     * Create a new email template.
     */
    public function create(User $user, array $data): EmailTemplate
    {
        return $user->emailTemplates()->create([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'body' => $data['body'],
        ]);
    }

    /**
     * Update an existing email template.
     */
    public function update(EmailTemplate $template, array $data): EmailTemplate
    {
        $template->update([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'body' => $data['body'],
        ]);

        return $template;
    }

    /**
     * Delete an email template.
     */
    public function delete(EmailTemplate $template): bool
    {
        // Check if template is used in any campaigns
        if ($template->campaigns()->exists()) {
            throw new \Exception('Cannot delete template that is used in campaigns.');
        }

        return $template->delete();
    }

    /**
     * Preview template with sample data.
     */
    public function preview(EmailTemplate $template, array $sampleData): array
    {
        return [
            'subject' => $template->replaceSubjectPlaceholders($sampleData),
            'body' => $template->replaceBodyPlaceholders($sampleData),
        ];
    }
}
