<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Services\GroupService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{

    use AuthorizesRequests;

    /**
     * @param CustomerService $customerService
     * @param GroupService $groupService
     */
    public function __construct(
        private CustomerService $customerService,
        private GroupService $groupService
    ) {}

    /**
     * Display a listing of the customers.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $customers = $request->has('search')
            ? $this->customerService->search($user, $request->get('search'))
            : $this->customerService->getAllForUser($user);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'search' => $request->get('search', ''),
        ]);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(Request $request)
    {
        $groups = $this->groupService->getAllForUser($request->user());

        return Inertia::render('Customers/Create', [
            'groups' => $groups,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $this->customerService->create(
            $request->user(),
            $request->validated()
        );

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the customer.
     */
    public function edit(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $groups = $this->groupService->getAllForUser($request->user());

        return Inertia::render('Customers/Edit', [
            'customer' => $customer->load('groups'),
            'groups' => $groups,
        ]);
    }

    /**
     * Update the specified resource in storage for customer.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $this->customerService->update(
            $customer,
            $request->validated()
        );

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource for customer from storage.
     */
    public function destroy(Request $request, Customer $customer)
    {
        $this->authorize('delete', $customer);

        $this->customerService->delete($customer);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
