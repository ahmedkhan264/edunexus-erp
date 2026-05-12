<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequestRequest extends FormRequest
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
            'attendance_id' => [
                'required',
                'exists:attendance,id',
                function ($attribute, $value, $fail) {
                    $attendance = \App\Models\Attendance::find($value);
                    
                    // Check if teacher is assigned to the student's class
                    if (auth()->user()->hasRole('teacher')) {
                        $isAssigned = \App\Models\TeacherClassAssignment::where('teacher_id', auth()->id())
                            ->where('class_id', $attendance->class_id)
                            ->where('is_active', true)
                            ->exists();
                        
                        if (!$isAssigned) {
                            $fail('You are not assigned to this student\'s class.');
                        }
                    }
                    
                    // Check if attendance date is not future
                    if ($attendance && $attendance->date > now()->format('Y-m-d')) {
                        $fail('Cannot request correction for future dates.');
                    }
                }
            ],
            'requested_status' => [
                'required',
                'in:present,absent,late',
                function ($attribute, $value, $fail) {
                    $attendance = \App\Models\Attendance::find($this->attendance_id);
                    
                    // Ensure requested status is different from current status
                    if ($attendance && $attendance->status === $value) {
                        $fail('Requested status must be different from current status.');
                    }
                }
            ],
            'reason' => [
                'required',
                'string',
                'min:10',
                'max:1000'
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'attendance_id.required' => 'Attendance record is required.',
            'attendance_id.exists' => 'Selected attendance record does not exist.',
            'requested_status.required' => 'Requested status is required.',
            'requested_status.in' => 'Invalid requested status.',
            'reason.required' => 'Reason is required.',
            'reason.min' => 'Reason must be at least 10 characters long.',
            'reason.max' => 'Reason cannot exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'attendance_id' => 'attendance record',
            'requested_status' => 'requested status',
            'reason' => 'reason for correction',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if there's already a pending request for this attendance
            $existingRequest = \App\Models\AttendanceCorrectionRequest::where('attendance_id', $this->attendance_id)
                ->where('status', 'pending')
                ->first();
            
            if ($existingRequest) {
                $validator->errors()->add('attendance_id', 'A correction request for this attendance record is already pending approval.');
            }
        });
    }
}
