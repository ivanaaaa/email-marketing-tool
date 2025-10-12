<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_view_customers_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('customers.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('Customers/Index'));
    }

    public function test_user_can_create_customer()
    {
        $customerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'sex' => 'male',
            'birth_date' => '1990-01-01',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), $customerData);

        $response->assertRedirect(route('customers.index'))
            ->assertSessionHas('success', 'Customer created successfully.');

        $this->assertDatabaseHas('customers', [
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_create_customer_with_groups()
    {
        $group = Group::factory()->create(['user_id' => $this->user->id]);

        $customerData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'group_ids' => [$group->id],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), $customerData);

        $response->assertRedirect(route('customers.index'));

        $customer = Customer::where('email', 'jane.smith@example.com')->first();
        $this->assertTrue($customer->groups->contains($group));
    }

    public function test_user_can_update_customer()
    {
        $customer = Customer::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('customers.update', $customer), $updateData);

        $response->assertRedirect(route('customers.index'))
            ->assertSessionHas('success', 'Customer updated successfully.');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_can_delete_customer()
    {
        $customer = Customer::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('customers.destroy', $customer));

        $response->assertRedirect(route('customers.index'))
            ->assertSessionHas('success', 'Customer deleted successfully.');

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_user_can_search_customers()
    {
        Customer::factory()->create([
            'user_id' => $this->user->id,
            'first_name' => 'John',
            'email' => 'john@example.com'
        ]);

        Customer::factory()->create([
            'user_id' => $this->user->id,
            'first_name' => 'Jane',
            'email' => 'jane@example.com'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('customers.index', ['search' => 'John']));

        $response->assertOk();
        // Additional assertions would depend on the exact structure
    }

    public function test_user_cannot_access_other_users_customers()
    {
        $otherUser = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get(route('customers.edit', $customer));

        $response->assertForbidden();
    }

    public function test_customer_creation_requires_valid_email()
    {
        $customerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), $customerData);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_customer_creation_prevents_duplicate_email()
    {
        Customer::factory()->create([
            'user_id' => $this->user->id,
            'email' => 'duplicate@example.com'
        ]);

        $customerData = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'duplicate@example.com',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), $customerData);

        $response->assertSessionHasErrors(['email']);
    }
}
