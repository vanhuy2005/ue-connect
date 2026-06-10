<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MentorAccessActionRequest extends FormRequest
{
    public function authorize()
    {
        $user = $this->user();

        return (bool) ($user && ($user->can('manage_mentor_access') || $user->can('manage_permissions')));
    }

    public function rules()
    {
        return [
            'action' => ['required', 'string', 'in:approve,reject,request_more_info,grant,revoke,pause,under_review'],
            'reason' => ['nullable', 'string'],
            'instruction' => ['nullable', 'string'],
        ];
    }
}
