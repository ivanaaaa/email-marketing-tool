<?php
// app/Enums/CampaignStatus.php

namespace App\Enums;

enum CampaignStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    /**
     * Get a human-readable label for the status.
     */
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::SCHEDULED => 'Scheduled',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
        };
    }

    /**
     * Get the color class for the status badge.
     */
    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'bg-gray-500',
            self::SCHEDULED => 'bg-blue-500',
            self::PROCESSING => 'bg-yellow-500',
            self::COMPLETED => 'bg-green-500',
            self::FAILED => 'bg-red-500',
        };
    }

    /**
     * Check if the campaign can be edited in this status.
     */
    public function canBeEdited(): bool
    {
        return in_array($this, [self::DRAFT, self::SCHEDULED]);
    }

    /**
     * Check if the campaign can be sent in this status.
     */
    public function canBeSent(): bool
    {
        return in_array($this, [self::DRAFT, self::SCHEDULED]);
    }

    /**
     * Check if the campaign can be deleted in this status.
     */
    public function canBeDeleted(): bool
    {
        return $this === self::DRAFT;
    }

    /**
     * Check if this is a final state (no further transitions).
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED]);
    }

    /**
     * Get all possible status values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all status options for dropdown.
     */
    public static function options(): array
    {
        return array_map(
            fn(self $status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ],
            self::cases()
        );
    }
}
