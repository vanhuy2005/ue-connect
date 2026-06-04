<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VerificationActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('review_verification');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:approve,reject,need_more_information,mark_conflict,suspend_suspicious,edit_before_approve'],
            'reason' => ['nullable', 'string', 'min:5', 'max:2000', 'required_if:action,reject,mark_conflict,suspend_suspicious'],
            'instruction' => ['nullable', 'string', 'min:5', 'max:2000', 'required_if:action,need_more_information'],
            'corrected_fields' => ['nullable', 'array'],
            'notify_user' => ['nullable', 'boolean'],
        ];
    }
}
