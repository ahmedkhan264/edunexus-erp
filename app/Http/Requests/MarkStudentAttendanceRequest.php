<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MarkStudentAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole(['teacher', 'admin', 'principal', 'super_admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'class_id' => [
                'required',
                'exists:classes,id',
                function ($attribute, $value, $fail) {
                    // Check if teacher is assigned to this class
                    if (auth()->user()->hasRole('teacher')) {
                        $isAssigned = \App\Models\TeacherClassAssignment::where('teacher_id', auth()->id())
                            ->where('class_id', $value)
                            ->where('is_active', true)
                            ->exists();
                        
                        if (!$isAssigned) {
                            $fail('You are not assigned to this class.');
                        }
                    }
                }
            ],
            'section_id' => [
                'nullable',
                'exists:sections,id'
            ],
            'subject_id' => [
                'nullable',
                'exists:subjects,id'
            ],
            'date' => [
                'required',
                'date',
                'before_or_equal:today',
                function ($attribute, $value, $fail) {
                    // Don't allow attendance for future dates
                    if ($value > now()->format('Y-m-d')) {
                        $fail('Cannot mark attendance for future dates.');
                    }
                }
            ],
            'attendance' => [
                'required',
                'array',
                'min:1'
            ],
            'attendance.*.student_id' => [
                'required',
                'exists:students,id'
            ],
            'attendance.*.status' => [
                'required',
                'in:present,absent,late'
            ],
            'attendance.*.remarks' => [
                'nullable',
                'string',
                'max:255'
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'class_id.required' => 'Class selection is required.',
            'class_id.exists' => 'Selected class does not exist.',
            'section_id.exists' => 'Selected section does not exist.',
            'subject_id.exists' => 'Selected subject does not exist.',
            'date.required' => 'Date is required.',
            'date.date' => 'Please provide a valid date.',
            'date.before_or_equal' => 'Cannot mark attendance for future dates.',
            'attendance.required' => 'Attendance data is required.',
            'attendance.array' => 'Invalid attendance data format.',
            'attendance.min' => 'At least one student attendance must be marked.',
            'attendance.*.student_id.required' => 'Student ID is required.',
            'attendance.*.student_id.exists' => 'Student does not exist.',
            'attendance.*.status.required' => 'Attendance status is required.',
            'attendance.*.status.in' => 'Invalid attendance status.',
            'attendance.*.remarks.max' => 'Remarks cannot exceed 255 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'class_id' => 'class',
            'section_id' => 'section',
            'subject_id' => 'subject',
            'date' => 'attendance date',
            'attendance' => 'attendance records',
            'attendance.*.student_id' => 'student',
            'attendance.*.status' => 'attendance status',
            'attendance.*.remarks' => 'remarks',
        ];
    }
}
