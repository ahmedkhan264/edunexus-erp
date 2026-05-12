<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TeacherListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow admin, principal, and HR manager roles to view teacher list
        return auth()->check() && in_array(auth()->user()->role->slug, ['super_admin', 'principal', 'admin', 'hr_manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'string', 'in:Male,Female'],
            'qualification' => ['nullable', 'string', 'max:100'],
            'employment_type' => ['nullable', 'string', 'in:permanent,contract,part-time'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'search.max' => 'Search term must not exceed 100 characters.',
            'gender.in' => 'Selected gender is invalid.',
            'qualification.max' => 'Qualification filter must not exceed 100 characters.',
            'employment_type.in' => 'Selected employment type is invalid.',
            'status.in' => 'Selected status is invalid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'search' => 'search term',
            'gender' => 'gender',
            'qualification' => 'qualification',
            'employment_type' => 'employment type',
            'status' => 'status',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Sanitize search input
            if ($this->has('search')) {
                $this->merge([
                    'search' => trim($this->input('search'))
                ]);
            }
        });
    }
}
