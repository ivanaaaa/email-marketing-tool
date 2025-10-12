<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\GroupController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('customers.index')->with('success', 'Welcome back!');
    }
    return redirect()->route('login');

})->name('home');

Route::middleware(['auth'])->group(function () {
    // Dashboard
//    Route::get('dashboard', function () {
//        return Inertia::render('Dashboard');
//    })->middleware(['auth', 'verified'])->name('dashboard');


    // Customers
    Route::resource('customers', CustomerController::class);

    // Groups
    Route::resource('groups', GroupController::class);

    // Email Templates
    Route::resource('email-templates', EmailTemplateController::class);
    Route::get('email-templates/{emailTemplate}/preview', [EmailTemplateController::class, 'preview'])
        ->name('email-templates.preview');

    // Campaigns
    Route::resource('campaigns', CampaignController::class);
    Route::post('campaigns/{campaign}/schedule', [CampaignController::class, 'schedule'])
        ->name('campaigns.schedule');
    Route::post('campaigns/{campaign}/send-now', [CampaignController::class, 'sendNow'])
        ->name('campaigns.send-now');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
