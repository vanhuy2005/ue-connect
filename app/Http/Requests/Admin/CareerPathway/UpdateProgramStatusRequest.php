<?php

namespace App\Http\Requests\Admin\CareerPathway;

use App\Enums\ProgramStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProgramStatusRequest extends FormRequest
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
            'status' => ['required', new Enum(ProgramStatus::class)],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Additional validation hooks.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $newStatus = $this->input('status');
            $currentStatus = $this->route('program')->status->value ?? $this->route('program')->status;

            $publicStatuses = [
                ProgramStatus::READY->value,
                ProgramStatus::READY_WITH_MISSING_DESCRIPTIONS->value,
            ];

            // If changing from non-public to public, reason is required
            if (! in_array($currentStatus, $publicStatuses) && in_array($newStatus, $publicStatuses)) {
                if (empty($this->input('reason'))) {
                    $validator->errors()->add('reason', 'A reason is required when making a program public-ready.');
                }
            }
        });
    }
}
