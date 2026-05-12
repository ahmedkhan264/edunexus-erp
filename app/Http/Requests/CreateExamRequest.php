<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateExamRequest extends FormRequest
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
            'section' => 'required|string|max:10',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date|after:today',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'total_marks' => 'required|integer|min:1|max:1000',
            'passing_marks' => 'required|integer|min:0|max:total_marks',
            'exam_type' => 'required|in:midterm,final,quiz,assignment,practical',
            'instructions' => 'nullable|string|max:2000',
            'allow_retake' => 'boolean',
            'max_attempts' => 'required|integer|min:1|max:5'
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Exam title is required.',
            'title.max' => 'Exam title may not be greater than 255 characters.',
            'description.max' => 'Description may not be greater than 1000 characters.',
            'class_id.required' => 'Class is required.',
            'class_id.exists' => 'Selected class is invalid.',
            'section.required' => 'Section is required.',
            'section.max' => 'Section may not be greater than 10 characters.',
            'subject_id.required' => 'Subject is required.',
            'subject_id.exists' => 'Selected subject is invalid.',
            'exam_date.required' => 'Exam date is required.',
            'exam_date.after' => 'Exam date must be after today.',
            'start_time.required' => 'Start time is required.',
            'end_time.required' => 'End time is required.',
            'end_time.after' => 'End time must be after start time.',
            'duration_minutes.required' => 'Duration is required.',
            'duration_minutes.min' => 'Duration must be at least 15 minutes.',
            'duration_minutes.max' => 'Duration may not be greater than 480 minutes.',
            'total_marks.required' => 'Total marks is required.',
            'total_marks.min' => 'Total marks must be at least 1.',
            'total_marks.max' => 'Total marks may not be greater than 1000.',
            'passing_marks.required' => 'Passing marks is required.',
            'passing_marks.min' => 'Passing marks must be at least 0.',
            'passing_marks.max' => 'Passing marks may not be greater than total marks.',
            'exam_type.required' => 'Exam type is required.',
            'exam_type.in' => 'Selected exam type is invalid.',
            'instructions.max' => 'Instructions may not be greater than 2000 characters.',
            'max_attempts.required' => 'Maximum attempts is required.',
            'max_attempts.min' => 'Maximum attempts must be at least 1.',
            'max_attempts.max' => 'Maximum attempts may not be greater than 5.'
        ];
    }
}
