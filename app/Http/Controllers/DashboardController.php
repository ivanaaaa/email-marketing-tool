
<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Group;
use App\Models\EmailTemplate;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Get counts
        $totalCustomers = $user->customers()->count();
        $totalGroups = $user->groups()->count();
        $totalTemplates = $user->emailTemplates()->count();
        $totalCampaigns = $user->campaigns()->count();

        // Get new customers this month
        $newCustomersThisMonth = $user->customers()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Get recent customers (limit to 5)
        $recentCustomers = $user->customers()
            ->latest()
            ->take(5)
            ->get(['id', 'first_name', 'last_name', 'email', 'created_at']);

        // Get recent campaigns
        $recentCampaigns = $user->campaigns()
            ->with('emailTemplate:id,name')
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'status', 'email_template_id', 'sent_count', 'total_recipients', 'created_at']);

        // Campaign statistics
        $campaignStats = [
            'draft' => $user->campaigns()->where('status', 'draft')->count(),
            'scheduled' => $user->campaigns()->where('status', 'scheduled')->count(),
            'processing' => $user->campaigns()->where('status', 'processing')->count(),
            'completed' => $user->campaigns()->where('status', 'completed')->count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalCustomers' => $totalCustomers,
                'totalGroups' => $totalGroups,
                'totalTemplates' => $totalTemplates,
                'totalCampaigns' => $totalCampaigns,
                'newCustomersThisMonth' => $newCustomersThisMonth,
            ],
            'recentCustomers' => $recentCustomers,
            'recentCampaigns' => $recentCampaigns,
            'campaignStats' => $campaignStats,
        ]);
    }
}
