<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole(['admin', 'principal', 'super_admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $subjectId = $this->route('subject')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:subjects,name,' . $subjectId
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                'unique:subjects,code,' . $subjectId,
                'regex:/^[A-Z0-9\-_]+$/'
            ],
            'class_id' => [
                'nullable',
                'exists:classes,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $class = \App\Models\SchoolClass::find($value);
                        if (!$class || !$class->is_active) {
                            $fail('Selected class is not active.');
                        }
                    }
                }
            ],
            'teacher_id' => [
                'nullable',
                'exists:teachers,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $teacher = \App\Models\Teacher::find($value);
                        if (!$teacher || !$teacher->is_active) {
                            $fail('Selected teacher is not active.');
                        }
                    }
                }
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'credit_hours' => [
                'nullable',
                'integer',
                'min:1',
                'max:20'
            ],
            'weekly_periods' => [
                'nullable',
                'integer',
                'min:1',
                'max:50'
            ],
            'passing_marks' => [
                'nullable',
                'integer',
                'min:0',
                'max:100'
            ],
            'total_marks' => [
                'nullable',
                'integer',
                'min:1',
                'max:200'
            ],
            'is_practical' => [
                'boolean'
            ],
            'is_active' => [
                'boolean'
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Subject name is required.',
            'name.unique' => 'Subject name already exists.',
            'code.required' => 'Subject code is required.',
            'code.unique' => 'Subject code already exists.',
            'code.regex' => 'Subject code must contain only uppercase letters, numbers, hyphens, and underscores.',
            'class_id.exists' => 'Selected class does not exist.',
            'teacher_id.exists' => 'Selected teacher does not exist.',
            'credit_hours.min' => 'Credit hours must be at least 1.',
            'credit_hours.max' => 'Credit hours cannot exceed 20.',
            'weekly_periods.min' => 'Weekly periods must be at least 1.',
            'weekly_periods.max' => 'Weekly periods cannot exceed 50.',
            'passing_marks.min' => 'Passing marks cannot be negative.',
            'passing_marks.max' => 'Passing marks cannot exceed 100.',
            'total_marks.min' => 'Total marks must be at least 1.',
            'total_marks.max' => 'Total marks cannot exceed 200.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'subject name',
            'code' => 'subject code',
            'class_id' => 'class',
            'teacher_id' => 'teacher',
            'description' => 'description',
            'credit_hours' => 'credit hours',
            'weekly_periods' => 'weekly periods',
            'passing_marks' => 'passing marks',
            'total_marks' => 'total marks',
            'is_practical' => 'practical subject',
            'is_active' => 'active status',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $passingMarks = $this->input('passing_marks');
            $totalMarks = $this->input('total_marks');
            
            if ($passingMarks !== null && $totalMarks !== null && $passingMarks > $totalMarks) {
                $validator->errors()->add('passing_marks', 'Passing marks cannot be greater than total marks.');
            }
        });
    }
}
