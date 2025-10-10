<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];

    /**
     * Get the user that owns the group.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The customers that belong to the group.
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_group')
            ->withTimestamps();
    }

    /**
     * The campaigns that use this group.
     */
    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campaign_group')
            ->withTimestamps();
    }

    /**
     * Get the count of customers in this group.
     */
    public function getCustomersCountAttribute(): int
    {
        return $this->customers()->count();
    }
}
