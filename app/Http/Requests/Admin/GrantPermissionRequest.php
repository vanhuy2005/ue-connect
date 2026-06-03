<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GrantPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('manage_permissions');
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'permission_key' => ['required', 'string'],
            'scope_type' => ['nullable', 'string'],
            'scope_id' => ['nullable', 'integer'],
            'reason' => ['required', 'string', 'min:10'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
        ];
    }
}
