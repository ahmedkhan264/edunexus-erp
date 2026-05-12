<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TeacherCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow admin, principal, and HR manager roles to create teachers
        return auth()->check() && in_array(auth()->user()->role->slug, ['super_admin', 'principal', 'admin', 'hr_manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Personal Information
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:65 years ago'],
            'gender' => ['required', 'string', 'in:Male,Female'],
            'cnic' => ['required', 'string', 'max:15', 'regex:/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/', 'unique:teachers,cnic'],
            'phone_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email', 'unique:teachers,email'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'nationality' => ['required', 'string', 'max:100'],
            'blood_group' => ['nullable', 'string', 'max:10', 'in:A+,A-,B+,B-,O+,O-,AB+,AB-'],
            'religion' => ['nullable', 'string', 'max:50'],
            
            // Professional Information
            'qualification' => ['required', 'string', 'max:200'],
            'specialization' => ['nullable', 'string', 'max:200'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:50'],
            'previous_institution' => ['nullable', 'string', 'max:200'],
            'joining_date' => ['required', 'date', 'before_or_equal:today'],
            'basic_salary' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'employment_type' => ['required', 'string', 'in:permanent,contract,part-time'],
            
            // Profile and Documents
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'], // 2MB max
            
            // Assignments
            'subjects' => ['nullable', 'array', 'min:1'],
            'subjects.*' => ['integer', 'exists:subjects,id'],
            'classes' => ['nullable', 'array', 'min:1'],
            'classes.*' => ['integer', 'exists:classes,id'],
            
            // Notes
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'date_of_birth.after' => 'Teacher must be less than 65 years old.',
            'gender.required' => 'Gender is required.',
            'cnic.required' => 'CNIC is required.',
            'cnic.regex' => 'CNIC format is invalid. Use format: XXXXX-XXXXXXX-X',
            'cnic.unique' => 'This CNIC is already registered.',
            'phone_number.required' => 'Phone number is required.',
            'email.required' => 'Email is required.',
            'email.unique' => 'This email is already registered.',
            'address.required' => 'Address is required.',
            'city.required' => 'City is required.',
            'state.required' => 'State is required.',
            'postal_code.required' => 'Postal code is required.',
            'country.required' => 'Country is required.',
            'nationality.required' => 'Nationality is required.',
            'qualification.required' => 'Qualification is required.',
            'experience_years.required' => 'Experience years is required.',
            'experience_years.max' => 'Experience cannot exceed 50 years.',
            'joining_date.required' => 'Joining date is required.',
            'joining_date.before_or_equal' => 'Joining date cannot be in the future.',
            'basic_salary.required' => 'Basic salary is required.',
            'basic_salary.numeric' => 'Basic salary must be a number.',
            'basic_salary.max' => 'Basic salary is too high.',
            'employment_type.required' => 'Employment type is required.',
            'subjects.min' => 'At least one subject must be selected.',
            'classes.min' => 'At least one class must be selected.',
            'profile_image.mimes' => 'Profile image must be a JPEG or PNG file.',
            'profile_image.max' => 'Profile image must not be larger than 2MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'date_of_birth' => 'date of birth',
            'gender' => 'gender',
            'cnic' => 'CNIC',
            'phone_number' => 'phone number',
            'email' => 'email',
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'postal_code' => 'postal code',
            'country' => 'country',
            'nationality' => 'nationality',
            'blood_group' => 'blood group',
            'religion' => 'religion',
            'qualification' => 'qualification',
            'specialization' => 'specialization',
            'experience_years' => 'experience years',
            'previous_institution' => 'previous institution',
            'joining_date' => 'joining date',
            'basic_salary' => 'basic salary',
            'employment_type' => 'employment type',
            'profile_image' => 'profile image',
            'subjects' => 'subjects',
            'classes' => 'classes',
            'notes' => 'notes',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate that at least one subject or class is selected
            if (!$this->has('subjects') && !$this->has('classes')) {
                $validator->errors()->add('assignments', 'At least one subject or class must be assigned to the teacher.');
            }
            
            // Validate salary based on employment type
            if ($this->input('employment_type') === 'part-time' && $this->input('basic_salary') > 50000) {
                $validator->errors()->add('basic_salary', 'Part-time teacher salary cannot exceed PKR 50,000.');
            }
        });
    }
}
