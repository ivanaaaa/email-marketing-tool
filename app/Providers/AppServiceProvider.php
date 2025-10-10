<?php

namespace App\Providers;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\Group;
use App\Policies\CampaignPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\EmailTemplatePolicy;
use App\Policies\GroupPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Group::class, GroupPolicy::class);
        Gate::policy(EmailTemplate::class, EmailTemplatePolicy::class);
        Gate::policy(Campaign::class, CampaignPolicy::class);
    }
}
