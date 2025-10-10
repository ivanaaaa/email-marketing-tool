<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
use App\Services\CustomerService;
use App\Services\GroupService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GroupController extends Controller
{
    use AuthorizesRequests;


    /**
     * @param GroupService $groupService
     * @param CustomerService $customerService
     */
    public function __construct(
        private GroupService $groupService,
        private CustomerService $customerService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $groups = $this->groupService->getAllForUser($request->user());

        return Inertia::render('Groups/Index', [
            'groups' => $groups,
        ]);
    }

    /**
     * Show the form for creating a new resource for group.
     */
    public function create(Request $request)
    {
        $customers = $this->customerService->getAllForUser($request->user(), 1000);

        return Inertia::render('Groups/Create', [
            'customers' => $customers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request)
    {
        $this->groupService->create(
            $request->user(),
            $request->validated()
        );

        return redirect()
            ->route('groups.index')
            ->with('success', 'Group created successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
        $this->authorize('view', $group);

        $customers = $this->groupService->getWithCustomers($group);

        return Inertia::render('Groups/Show', [
            'group' => $group->loadCount('customers'),
            'customers' => $customers,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Group $group)
    {
        $this->authorize('update', $group);

        $customers = $this->customerService->getAllForUser($request->user(), 1000);

        return Inertia::render('Groups/Edit', [
            'group' => $group->load('customers'),
            'customers' => $customers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        $this->authorize('update', $group);

        $this->groupService->update(
            $group,
            $request->validated()
        );

        return redirect()
            ->route('groups.index')
            ->with('success', 'Group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        $this->authorize('delete', $group);

        $this->groupService->delete($group);

        return redirect()
            ->route('groups.index')
            ->with('success', 'Group deleted successfully.');
    }
}
