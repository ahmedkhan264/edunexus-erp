<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StudentListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow admin and principal roles to access student list
        return auth()->check() && in_array(auth()->user()->role->slug, ['super_admin', 'principal', 'admin']);
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
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'status' => ['nullable', 'string', 'in:enrolled,graduated,suspended,withdrawn'],
            'gender' => ['nullable', 'string', 'in:Male,Female'],
            'per_page' => ['nullable', 'integer', 'in:15,25,50,100'],
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
            'class_id.exists' => 'Selected class is invalid.',
            'status.in' => 'Selected status is invalid.',
            'gender.in' => 'Selected gender is invalid.',
            'per_page.in' => 'Selected per page option is invalid.',
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
            'class_id' => 'class',
            'status' => 'status',
            'gender' => 'gender',
            'per_page' => 'per page',
        ];
    }
}
