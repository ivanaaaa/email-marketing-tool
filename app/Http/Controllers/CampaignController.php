<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\UpdateCampaignRequest;
use App\Models\Campaign;
use App\Services\CampaignService;
use App\Services\EmailTemplateService;
use App\Services\GroupService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignController extends Controller
{
    use AuthorizesRequests;

    /**
     * @param CampaignService $campaignService
     * @param EmailTemplateService $emailTemplateService
     * @param GroupService $groupService
     */
    public function __construct(
        private CampaignService $campaignService,
        private EmailTemplateService $emailTemplateService,
        private GroupService $groupService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $campaigns = $this->campaignService->getAllForUser($request->user());

        return Inertia::render('Campaigns/Index', [
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $templates = $this->emailTemplateService->getAllForUser($request->user());
        $groups = $this->groupService->getAllForUser($request->user());

        return Inertia::render('Campaigns/Create', [
            'templates' => $templates,
            'groups' => $groups,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCampaignRequest $request)
    {
        $this->campaignService->create(
            $request->user(),
            $request->validated()
        );

        return redirect()
            ->route('campaigns.index')
            ->with('success', 'Campaign created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Campaign $campaign)
    {
        $this->authorize('view', $campaign);

        $statistics = $this->campaignService->getStatistics($campaign);

        return Inertia::render('Campaigns/Show', [
            'campaign' => $campaign->load(['emailTemplate', 'groups']),
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Campaign $campaign)
    {
        $this->authorize('update', $campaign);

        $templates = $this->emailTemplateService->getAllForUser($request->user());
        $groups = $this->groupService->getAllForUser($request->user());

        return Inertia::render('Campaigns/Edit', [
            'campaign' => $campaign->load(['emailTemplate', 'groups']),
            'templates' => $templates,
            'groups' => $groups,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCampaignRequest $request, Campaign $campaign)
    {
        $this->authorize('update', $campaign);

        try {
            $this->campaignService->update(
                $campaign,
                $request->validated()
            );

            return redirect()
                ->route('campaigns.index')
                ->with('success', 'Campaign updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('campaigns.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        $this->authorize('delete', $campaign);

        try {
            $this->campaignService->delete($campaign);

            return redirect()
                ->route('campaigns.index')
                ->with('success', 'Campaign deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('campaigns.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Schedule a campaign for future sending.
     */
    public function schedule(Request $request, Campaign $campaign)
    {
        $this->authorize('update', $campaign);

        $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        try {
            $scheduledAt = new \DateTime($request->input('scheduled_at'));
            $this->campaignService->schedule($campaign, $scheduledAt);

            return redirect()
                ->route('campaigns.show', $campaign)
                ->with('success', 'Campaign scheduled successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('campaigns.show', $campaign)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Send campaign immediately.
     */
    public function sendNow(Campaign $campaign)
    {
        $this->authorize('update', $campaign);

        try {
            $this->campaignService->sendNow($campaign);

            return redirect()
                ->route('campaigns.show', $campaign)
                ->with('success', 'Campaign is being sent now.');
        } catch (\Exception $e) {
            return redirect()
                ->route('campaigns.show', $campaign)
                ->with('error', $e->getMessage());
        }
    }
}
