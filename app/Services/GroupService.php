<?php

namespace App\Services;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GroupService
{
    /**
     * Get all groups for a user.
     */
    public function getAllForUser(User $user)
    {
        return $user->groups()
            ->withCount('customers')
            ->latest()
            ->get();
    }

    /**
     * Create a new group.
     */
    public function create(User $user, array $data): Group
    {
        return DB::transaction(function () use ($user, $data) {
            $group = $user->groups()->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            if (isset($data['customer_ids']) && !empty($data['customer_ids'])) {
                $group->customers()->attach($data['customer_ids']);
            }

            return $group->loadCount('customers');
        });
    }

    /**
     * Update an existing group.
     */
    public function update(Group $group, array $data): Group
    {
        return DB::transaction(function () use ($group, $data) {
            $group->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            if (isset($data['customer_ids'])) {
                $group->customers()->sync($data['customer_ids']);
            }

            return $group->loadCount('customers');
        });
    }

    /**
     * Delete a group.
     */
    public function delete(Group $group): bool
    {
        return DB::transaction(function () use ($group) {
            $group->customers()->detach();
            $group->campaigns()->detach();
            return $group->delete();
        });
    }

    /**
     * Get group with customers.
     */
    public function getWithCustomers(Group $group, int $perPage = null)
    {
        $perPage = $perPage ?? config('campaign.pagination.groups_per_page', 50);
        return $group->customers()
            ->latest()
            ->paginate($perPage);
    }
}
