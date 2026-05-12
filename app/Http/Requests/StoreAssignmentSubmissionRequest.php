<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole(['student']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => 'nullable|string|max:2000',
            'files' => 'nullable|array|max:5',
            'files.*' => [
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip',
                'max:10240' // 10MB per file
            ]
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'content.max' => 'Content may not be greater than 2000 characters.',
            'files.max' => 'You may upload a maximum of 5 files.',
            'files.*.mimes' => 'Only PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF, and ZIP files are allowed.',
            'files.*.max' => 'Each file may not be greater than 10MB.'
        ];
    }
}
