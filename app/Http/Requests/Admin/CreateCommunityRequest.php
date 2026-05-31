<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateCommunityRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->can('manage_communities');
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'in:draft,active,inactive,suspended,archived'],
        ];
    }
}
