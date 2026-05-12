<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Unique;

class StudentCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow admin and principal roles to create students
        return auth()->check() && in_array(auth()->user()->role->slug, ['super_admin', 'principal', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Student Information
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:15 years ago'],
            'gender' => ['required', 'string', 'in:Male,Female'],
            'phone_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'nationality' => ['required', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:50'],
            'blood_group' => ['nullable', 'string', 'max:10', 'in:A+,A-,B+,B-,O+,O-,AB+,AB-'],
            
            // Academic Information
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'admission_date' => ['required', 'date', 'before_or_equal:today'],
            'previous_school_gpa' => ['nullable', 'decimal:2', 'min:0', 'max:4'],
            'previous_school_name' => ['nullable', 'string', 'max:200'],
            
            // Emergency Contact
            'emergency_contact_name' => ['required', 'string', 'max:100'],
            'emergency_contact_phone' => ['required', 'string', 'max:20'],
            'emergency_contact_relation' => ['required', 'string', 'max:50'],
            
            // Parent/Guardian Information
            'father_name' => ['nullable', 'string', 'max:100'],
            'father_cnic' => ['nullable', 'string', 'max:15', 'regex:/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/', 'unique:parent_profiles,father_cnic'],
            'father_phone' => ['nullable', 'string', 'max:20'],
            'father_occupation' => ['nullable', 'string', 'max:100'],
            'father_email' => ['nullable', 'email', 'max:100'],
            
            'mother_name' => ['nullable', 'string', 'max:100'],
            'mother_cnic' => ['nullable', 'string', 'max:15', 'regex:/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/', 'unique:parent_profiles,mother_cnic'],
            'mother_phone' => ['nullable', 'string', 'max:20'],
            'mother_occupation' => ['nullable', 'string', 'max:100'],
            'mother_email' => ['nullable', 'email', 'max:100'],
            
            'guardian_name' => ['nullable', 'string', 'max:100'],
            'guardian_cnic' => ['nullable', 'string', 'max:15', 'regex:/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/', 'unique:parent_profiles,guardian_cnic'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'guardian_occupation' => ['nullable', 'string', 'max:100'],
            'guardian_email' => ['nullable', 'email', 'max:100'],
            'guardian_relation' => ['nullable', 'string', 'max:50'],
            'guardian_address' => ['nullable', 'string', 'max:500'],
            'is_primary_guardian' => ['nullable', 'boolean'],
            
            // Profile Image
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'], // 2MB max
            
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
            'date_of_birth.after' => 'Student must be at least 15 years old.',
            'email.unique' => 'This email is already registered.',
            'class_id.exists' => 'Selected class is invalid.',
            'father_cnic.regex' => 'Father CNIC format is invalid. Use format: XXXXX-XXXXXXX-X',
            'mother_cnic.regex' => 'Mother CNIC format is invalid. Use format: XXXXX-XXXXXXX-X',
            'guardian_cnic.regex' => 'Guardian CNIC format is invalid. Use format: XXXXX-XXXXXXX-X',
            'father_cnic.unique' => 'Father CNIC is already registered.',
            'mother_cnic.unique' => 'Mother CNIC is already registered.',
            'guardian_cnic.unique' => 'Guardian CNIC is already registered.',
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
            'phone_number' => 'phone number',
            'class_id' => 'class',
            'emergency_contact_name' => 'emergency contact name',
            'emergency_contact_phone' => 'emergency contact phone',
            'emergency_contact_relation' => 'emergency contact relation',
            'father_name' => 'father name',
            'father_cnic' => 'father CNIC',
            'father_phone' => 'father phone',
            'father_occupation' => 'father occupation',
            'father_email' => 'father email',
            'mother_name' => 'mother name',
            'mother_cnic' => 'mother CNIC',
            'mother_phone' => 'mother phone',
            'mother_occupation' => 'mother occupation',
            'mother_email' => 'mother email',
            'guardian_name' => 'guardian name',
            'guardian_cnic' => 'guardian CNIC',
            'guardian_phone' => 'guardian phone',
            'guardian_occupation' => 'guardian occupation',
            'guardian_email' => 'guardian email',
            'guardian_relation' => 'guardian relation',
            'guardian_address' => 'guardian address',
            'is_primary_guardian' => 'primary guardian',
            'profile_image' => 'profile image',
        ];
    }
}
