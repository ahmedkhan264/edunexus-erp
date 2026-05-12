<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RejectCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole(['hr_manager', 'principal', 'admin', 'super_admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rejection_reason' => 'required|string|min:10|max:500'
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Rejection reason is required.',
            'rejection_reason.min' => 'Rejection reason must be at least 10 characters.',
            'rejection_reason.max' => 'Rejection reason may not be greater than 500 characters.'
        ];
    }
}
