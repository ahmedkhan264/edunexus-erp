<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ClassRequest extends FormRequest
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
        $classId = $this->route('class')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:classes,name,' . $classId
            ],
            'grade_level' => [
                'required',
                'integer',
                'between:1,12'
            ],
            'section' => [
                'nullable',
                'string',
                'max:50'
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
            'name.required' => 'Class name is required.',
            'name.unique' => 'Class name already exists.',
            'grade_level.required' => 'Grade level is required.',
            'grade_level.between' => 'Grade level must be between 1 and 12.',
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
            'name' => 'class name',
            'grade_level' => 'grade level',
            'section' => 'section',
            'capacity' => 'capacity',
            'room_number' => 'room number',
            'floor' => 'floor',
            'description' => 'description',
            'is_active' => 'active status',
        ];
    }
}
