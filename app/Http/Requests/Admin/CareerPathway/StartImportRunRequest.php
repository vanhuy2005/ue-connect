<?php

namespace App\Http\Requests\Admin\CareerPathway;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StartImportRunRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Middleware handles auth
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'path' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Additional validation hooks.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $path = $this->input('path');

            // Validate safe path: prevent directory traversal outside allowed dirs
            if (str_contains($path, '..')) {
                $validator->errors()->add('path', 'Invalid path: directory traversal is not allowed.');
            }

            // Optional: Check if directory actually exists
            if (! empty($path) && ! str_contains($path, '..') && ! is_dir(base_path($path))) {
                $validator->errors()->add('path', 'The specified path does not exist in the base directory.');
            }
        });
    }
}
