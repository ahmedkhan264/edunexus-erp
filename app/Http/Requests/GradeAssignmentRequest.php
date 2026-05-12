<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GradeAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole(['teacher', 'admin', 'super_admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'marks_obtained' => 'required|integer|min:0|max:' . $this->route('assignment')->total_marks,
            'feedback' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'marks_obtained.required' => 'Marks obtained is required.',
            'marks_obtained.integer' => 'Marks obtained must be a number.',
            'marks_obtained.min' => 'Marks obtained cannot be negative.',
            'marks_obtained.max' => 'Marks obtained cannot exceed total marks.',
            'feedback.max' => 'Feedback may not be greater than 1000 characters.'
        ];
    }
}
