<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'class_id' => 'required|exists:classes,id',
            'section' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'due_date' => 'required|date|after:now',
            'total_marks' => 'required|integer|min:0|max:1000',
            'allow_resubmission' => 'boolean',
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
            'title.required' => 'Assignment title is required.',
            'title.max' => 'Assignment title may not be greater than 255 characters.',
            'class_id.required' => 'Class selection is required.',
            'class_id.exists' => 'Selected class is invalid.',
            'section.required' => 'Section selection is required.',
            'subject_id.required' => 'Subject selection is required.',
            'subject_id.exists' => 'Selected subject is invalid.',
            'due_date.required' => 'Due date is required.',
            'due_date.after' => 'Due date must be after current time.',
            'total_marks.required' => 'Total marks is required.',
            'total_marks.min' => 'Total marks must be at least 0.',
            'total_marks.max' => 'Total marks may not be greater than 1000.',
            'files.max' => 'You may upload a maximum of 5 files.',
            'files.*.mimes' => 'Only PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF, and ZIP files are allowed.',
            'files.*.max' => 'Each file may not be greater than 10MB.'
        ];
    }
}
