<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'email',
        'first_name',
        'last_name',
        'sex',
        'birth_date',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    /**
     * Get the user that owns the customer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The groups that belong to the customer.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'customer_group')
            ->withTimestamps();
    }

    /**
     * Get the customer's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get customer data for email placeholder replacement.
     */
    public function getPlaceholderData(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'sex' => $this->sex ?? '',
            'birth_date' => $this->birth_date?->format('Y-m-d') ?? '',
        ];
    }

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}
