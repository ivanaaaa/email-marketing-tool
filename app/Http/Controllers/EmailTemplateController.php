<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmailTemplateRequest;
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
        $template = $this->emailTemplateService->create(
            $request->user(),
            $request->validated()
        );

        return redirect()
            ->route('email-templates.index')
            ->with('success', 'Email template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $this->authorize('update', $emailTemplate);

        $this->emailTemplateService->update(
            $emailTemplate,
            $request->validated()
        );

        return redirect()
            ->route('email-templates.index')
            ->with('success', 'Email template updated successfully.');
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
            'first_name' => 'User',
            'last_name' => 'Example',
            'full_name' => 'User Example',
            'email' => 'user.example@example.com',
            'sex' => 'female',
            'birth_date' => '2000-01-01',
        ];

        $preview = $this->emailTemplateService->preview($emailTemplate, $sampleData);

        return response()->json($preview);
    }
}
