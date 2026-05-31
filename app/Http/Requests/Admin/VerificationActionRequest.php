<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VerificationActionRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->can('review_verification');
    }

    public function rules()
    {
        return [
            'action' => ['required', 'string', 'in:approve,reject,need_more_information,mark_conflict,suspend_suspicious,edit_before_approve'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'instruction' => ['nullable', 'string', 'required_if:action,need_more_information'],
            'corrected_fields' => ['nullable', 'array'],
            'notify_user' => ['nullable', 'boolean'],
        ];
    }
}
