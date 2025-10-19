<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    /**
     * Get all customers for a user with pagination.
     */
    public function getAllForUser(User $user, int $perPage = null)
    {
        $perPage = $perPage ?? config('campaign.pagination.customers_per_page', 50);
        return $user->customers()
            ->with('groups')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new customer.
     */
    public function create(User $user, array $data): Customer
    {
        return DB::transaction(function () use ($user, $data) {
            $customer = $user->customers()->create([
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'sex' => $data['sex'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
            ]);

            if (isset($data['group_ids']) && !empty($data['group_ids'])) {
                $customer->groups()->attach($data['group_ids']);
            }

            return $customer->load('groups');
        });
    }

    /**
     * Update an existing customer.
     */
    public function update(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            $customer->update([
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'sex' => $data['sex'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
            ]);

            if (isset($data['group_ids'])) {
                $customer->groups()->sync($data['group_ids']);
            }

            return $customer->load('groups');
        });
    }

    /**
     * Delete a customer.
     */
    public function delete(Customer $customer): bool
    {
        return DB::transaction(function () use ($customer) {
            $customer->groups()->detach();
            return $customer->delete();
        });
    }

    /**
     * Bulk import customers from array.
     */
    public function bulkImport(User $user, array $customersData): int
    {
        $imported = 0;

        DB::transaction(function () use ($user, $customersData, &$imported) {
            foreach ($customersData as $customerData) {
                try {
                    $user->customers()->create([
                        'email' => $customerData['email'],
                        'first_name' => $customerData['first_name'],
                        'last_name' => $customerData['last_name'],
                        'sex' => $customerData['sex'] ?? null,
                        'birth_date' => $customerData['birth_date'] ?? null,
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    // Skip duplicates or invalid entries
                    continue;
                }
            }
        });

        return $imported;
    }

    /**
     * Search customers by query.
     */
    public function search(User $user, string $query, int $perPage = null)
    {
        $perPage = $perPage ?? config('campaign.pagination.customers_per_page', 50);
        return $user->customers()
            ->with('groups')
            ->where(function ($q) use ($query) {
                $q->where('email', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->latest()
            ->paginate($perPage);
    }
}
