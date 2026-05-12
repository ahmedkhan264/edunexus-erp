<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SectionRequest extends FormRequest
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
        $sectionId = $this->route('section')?->id;
        $classId = $this->input('class_id');

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($classId, $sectionId) {
                    if (!$classId) {
                        $fail('Class is required.');
                        return;
                    }
                    
                    $query = \App\Models\Section::where('class_id', $classId)
                        ->where('name', $value);
                    
                    if ($sectionId) {
                        $query->where('id', '!=', $sectionId);
                    }
                    
                    if ($query->exists()) {
                        $fail('Section name must be unique within the selected class.');
                    }
                }
            ],
            'class_id' => [
                'required',
                'exists:classes,id',
                function ($attribute, $value, $fail) {
                    $class = \App\Models\SchoolClass::find($value);
                    if (!$class || !$class->is_active) {
                        $fail('Selected class is not active.');
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
            'capacity' => [
                'nullable',
                'integer',
                'min:1',
                'max:100'
            ],
            'room_number' => [
                'nullable',
                'string',
                'max:50'
            ],
            'floor' => [
                'nullable',
                'integer',
                'min:1',
                'max:10'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
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
            'name.required' => 'Section name is required.',
            'name.max' => 'Section name may not exceed 50 characters.',
            'class_id.required' => 'Class selection is required.',
            'class_id.exists' => 'Selected class does not exist.',
            'teacher_id.exists' => 'Selected teacher does not exist.',
            'capacity.min' => 'Capacity must be at least 1.',
            'capacity.max' => 'Capacity cannot exceed 100 students.',
            'floor.min' => 'Floor must be at least 1.',
            'floor.max' => 'Floor cannot exceed 10.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'section name',
            'class_id' => 'class',
            'teacher_id' => 'teacher',
            'capacity' => 'capacity',
            'room_number' => 'room number',
            'floor' => 'floor',
            'description' => 'description',
            'is_active' => 'active status',
        ];
    }
}
