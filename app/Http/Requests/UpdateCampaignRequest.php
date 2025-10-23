<?php
// app/Http/Requests/UpdateCampaignRequest.php

namespace App\Http\Requests;

use App\Enums\CampaignStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email_template_id' => 'required|exists:email_templates,id',
            'group_ids' => 'required|array|min:1',
            'group_ids.*' => 'exists:groups,id',
            'scheduled_at' => 'nullable|date|after:now',
            'status' => ['nullable', Rule::in(CampaignStatus::values())],
        ];
    }


    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Campaign name is required.',
            'email_template_id.required' => 'Please select an email template.',
            'email_template_id.exists' => 'The selected email template does not exist.',
            'group_ids.required' => 'Please select at least one target group.',
            'group_ids.min' => 'Please select at least one target group.',
            'scheduled_at.after' => 'Scheduled time must be in the future.',
        ];
    }
}
