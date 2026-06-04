<?php

namespace App\Http\Requests\Admin;

use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;

class CreateAnnouncementRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->can('create', Announcement::class);
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:5000'],
            'type' => ['required', 'string'],
            'target' => ['nullable', 'array'],
            'priority' => ['nullable', 'string'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'pinned' => ['sometimes', 'boolean'],
        ];
    }
}
