<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'subject',
        'body',
    ];

    /**
     * Get the user that owns the template.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the campaigns using this template.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Replace placeholders in subject with customer data.
     */
    public function replaceSubjectPlaceholders(array $data): string
    {
        return $this->replacePlaceholders($this->subject, $data);
    }

    /**
     * Replace placeholders in body with customer data.
     */
    public function replaceBodyPlaceholders(array $data): string
    {
        return $this->replacePlaceholders($this->body, $data);
    }

    /**
     * Replace placeholders in text with provided data.
     * Supports: {{first_name}}, {{last_name}}, {{email}}, etc.
     */
    private function replacePlaceholders(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            $text = str_replace("{{".$key."}}", $value, $text);
        }

        return $text;
    }

    /**
     * Get available placeholders for templates.
     */
    public static function getAvailablePlaceholders(): array
    {
        return [
            '{{first_name}}' => 'Customer first name',
            '{{last_name}}' => 'Customer last name',
            '{{full_name}}' => 'Customer full name',
            '{{email}}' => 'Customer email',
            '{{sex}}' => 'Customer sex',
            '{{birth_date}}' => 'Customer birth date',
        ];
    }
}
