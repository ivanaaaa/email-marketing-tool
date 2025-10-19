<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_template_id',
        'name',
        'status',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'sent_count',
        'failed_count',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
    ];

    /**
     * Get the user that owns the campaign.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the email template for the campaign.
     */
    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    /**
     * The groups targeted by this campaign.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'campaign_group')
            ->withTimestamps();
    }

    /**
     * Check if campaign is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if campaign is scheduled for future.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_at?->isFuture();
    }

    /**
     * Check if campaign is currently processing.
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if campaign is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if campaign has failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if campaign is ready to be processed.
     * (Scheduled and time has arrived)
     */
    public function isReadyToProcess(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_at?->isPast();
    }

    /**
     * Check if campaign can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'scheduled']);
    }

    /**
     * Check if campaign can be sent.
     */
    public function canBeSent(): bool
    {
        return in_array($this->status, ['draft', 'scheduled']);
    }

    /**
     * Check if campaign can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Get campaign progress percentage.
     */
    public function getProgressPercentage(): int
    {
        if ($this->total_recipients === 0) {
            return 0;
        }

        return (int) (($this->sent_count / $this->total_recipients) * 100);
    }

    /**
     * Scope to get campaigns ready for processing.
     */
    public function scopeReadyToProcess($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }
}
