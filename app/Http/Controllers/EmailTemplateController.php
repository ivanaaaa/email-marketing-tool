<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmailTemplateRequest;
use App\Http\Requests\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailTemplateController extends Controller
{
    use AuthorizesRequests;

    /**
     * @param EmailTemplateService $emailTemplateService
     */
    public function __construct(
        private EmailTemplateService $emailTemplateService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $templates = $this->emailTemplateService->getAllForUser($request->user());

        return Inertia::render('EmailTemplates/Index', [
            'templates' => $templates,
            'placeholders' => EmailTemplate::getAvailablePlaceholders(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('EmailTemplates/Create', [
            'placeholders' => EmailTemplate::getAvailablePlaceholders(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmailTemplateRequest $request)
    {
        try {
            $template = $this->emailTemplateService->create(
                $request->user(),
                $request->validated()
            );

            return redirect()
                ->route('email-templates.index')
                ->with('success', 'Email template created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('email-templates.index')
                ->with('error', 'Failed to create email template: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmailTemplate $emailTemplate)
    {
        $this->authorize('view', $emailTemplate);

        return Inertia::render('EmailTemplates/Show', [
            'template' => $emailTemplate,
            'placeholders' => EmailTemplate::getAvailablePlaceholders(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, EmailTemplate $emailTemplate)
    {
        $this->authorize('update', $emailTemplate);

        return Inertia::render('EmailTemplates/Edit', [
            'template' => $emailTemplate,
            'placeholders' => EmailTemplate::getAvailablePlaceholders(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmailTemplateRequest $request, EmailTemplate $emailTemplate)
    {
        $this->authorize('update', $emailTemplate);

        try {
            $this->emailTemplateService->update(
                $emailTemplate,
                $request->validated()
            );

            return redirect()
                ->route('email-templates.index')
                ->with('success', 'Email template updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('email-templates.index')
                ->with('error', 'Failed to update email template: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        $this->authorize('delete', $emailTemplate);

        try {
            $this->emailTemplateService->delete($emailTemplate);

            return redirect()
                ->route('email-templates.index')
                ->with('success', 'Email template deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('email-templates.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Preview template with sample data.
     */
    public function preview(EmailTemplate $emailTemplate)
    {
        $this->authorize('view', $emailTemplate);

        $sampleData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'full_name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'sex' => 'male',
            'birth_date' => '1990-01-01',
        ];

        try {
            $preview = $this->emailTemplateService->preview($emailTemplate, $sampleData);
            return response()->json($preview);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate preview'], 500);
        }
    }
}
