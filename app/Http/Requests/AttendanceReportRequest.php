<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceReportRequest extends FormRequest
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
        return [
            'from_date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:2020-01-01'
            ],
            'to_date' => [
                'required',
                'date',
                'after_or_equal:from_date',
                'before_or_equal:today',
                function ($attribute, $value, $fail) {
                    $fromDate = \Carbon\Carbon::parse($this->from_date);
                    $toDate = \Carbon\Carbon::parse($value);
                    $daysDiff = $fromDate->diffInDays($toDate);
                    
                    if ($daysDiff > 90) {
                        $fail('Date range cannot exceed 90 days for performance reasons.');
                    }
                }
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
            'section_id' => [
                'nullable',
                'exists:sections,id',
                function ($attribute, $value, $fail) {
                    if ($value && $this->class_id) {
                        $section = \App\Models\Section::where('id', $value)
                            ->where('class_id', $this->class_id)
                            ->where('is_active', true)
                            ->first();
                        
                        if (!$section) {
                            $fail('Selected section does not belong to the selected class or is not active.');
                        }
                    }
                }
            ],
            'student_id' => [
                'nullable',
                'exists:students,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $student = \App\Models\Student::find($value);
                        if (!$student || !$student->is_active) {
                            $fail('Selected student is not active.');
                        }
                        
                        if ($this->class_id && $student->class_id != $this->class_id) {
                            $fail('Selected student does not belong to the selected class.');
                        }
                    }
                }
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'from_date.required' => 'From date is required.',
            'from_date.date' => 'Please provide a valid from date.',
            'from_date.before_or_equal' => 'From date cannot be in the future.',
            'from_date.after_or_equal' => 'From date cannot be before 2020.',
            'to_date.required' => 'To date is required.',
            'to_date.date' => 'Please provide a valid to date.',
            'to_date.after_or_equal' => 'To date must be after or equal to from date.',
            'to_date.before_or_equal' => 'To date cannot be in the future.',
            'class_id.exists' => 'Selected class does not exist.',
            'section_id.exists' => 'Selected section does not exist.',
            'student_id.exists' => 'Selected student does not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'from_date' => 'from date',
            'to_date' => 'to date',
            'class_id' => 'class',
            'section_id' => 'section',
            'student_id' => 'student',
        ];
    }

    /**
     * Get the validated data with default values.
     */
    public function validated(): array
    {
        $validated = parent::validated();
        
        // Set default values for optional fields
        $validated['class_id'] = $validated['class_id'] ?? null;
        $validated['section_id'] = $validated['section_id'] ?? null;
        $validated['student_id'] = $validated['student_id'] ?? null;
        
        return $validated;
    }
}
