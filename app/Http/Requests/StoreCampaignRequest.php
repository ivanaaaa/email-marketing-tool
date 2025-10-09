<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
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
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'email_template_id' => 'email template',
            'group_ids' => 'groups',
            'scheduled_at' => 'scheduled date',
        ];
    }
}
