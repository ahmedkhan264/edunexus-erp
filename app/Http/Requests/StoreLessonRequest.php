<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
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
            'class_id' => 'required|exists:classes,id',
            'section' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'chapter' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,published',
            'files' => 'nullable|array|max:10',
            'files.*' => [
                'file',
                'mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,gif,mp4,mov,webm',
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
            'title.required' => 'Lesson title is required.',
            'title.max' => 'Lesson title may not be greater than 255 characters.',
            'class_id.required' => 'Class selection is required.',
            'class_id.exists' => 'Selected class is invalid.',
            'section.required' => 'Section selection is required.',
            'subject_id.required' => 'Subject selection is required.',
            'subject_id.exists' => 'Selected subject is invalid.',
            'chapter.max' => 'Chapter may not be greater than 100 characters.',
            'description.max' => 'Description may not be greater than 1000 characters.',
            'status.required' => 'Status selection is required.',
            'status.in' => 'Status must be either draft or published.',
            'files.max' => 'You may upload a maximum of 10 files.',
            'files.*.mimes' => 'Only PDF, DOC, DOCX, PPT, PPTX, JPG, JPEG, PNG, GIF, MP4, MOV, and WEBM files are allowed.',
            'files.*.max' => 'Each file may not be greater than 10MB.'
        ];
    }
}
