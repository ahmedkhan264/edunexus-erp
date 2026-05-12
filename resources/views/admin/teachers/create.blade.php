@extends('layouts.app')

@section('title', 'Add Teacher')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Add New Teacher</h4>
            <p class="text-muted mb-0">Complete the registration form to enroll a new teacher</p>
        </div>
        <div>
            <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Teachers
            </a>
        </div>
    </div>

    <!-- Teacher Registration Form -->
    <form method="POST" action="{{ route('admin.teachers.store') }}" enctype="multipart/form-data">
        @csrf
        
        <!-- Personal Information Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fas fa-user me-2"></i>Personal Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                   id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                   id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                   id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="cnic" class="form-label">CNIC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('cnic') is-invalid @enderror" 
                                   id="cnic" name="cnic" value="{{ old('cnic') }}" placeholder="XXXXX-XXXXXXX-X" required>
                            @error('cnic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" 
                                   id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required>
                            @error('phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profile Photo</label>
                            <input type="file" class="form-control @error('profile_image') is-invalid @enderror" 
                                   id="profile_image" name="profile_image" accept="image/*">
                            <small class="text-muted">Allowed formats: JPEG, PNG. Max size: 2MB</small>
                            @error('profile_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Street Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="2" required>{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city') }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="postal_code" class="form-label">Postal Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required>
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="state" class="form-label">State/Province <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                   id="state" name="state" value="{{ old('state') }}" required>
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                   id="country" name="country" value="{{ old('country') ?? 'Pakistan' }}" required>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nationality') is-invalid @enderror" 
                                   id="nationality" name="nationality" value="{{ old('nationality') ?? 'Pakistani' }}" required>
                            @error('nationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="blood_group" class="form-label">Blood Group</label>
                                    <select class="form-select @error('blood_group') is-invalid @enderror" id="blood_group" name="blood_group">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                                        <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                    </select>
                                    @error('blood_group')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="religion" class="form-label">Religion</label>
                                    <input type="text" class="form-control @error('religion') is-invalid @enderror" 
                                           id="religion" name="religion" value="{{ old('religion') ?? 'Islam' }}">
                                    @error('religion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Information Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i class="fas fa-graduation-cap me-2"></i>Professional Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="qualification" class="form-label">Qualification <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('qualification') is-invalid @enderror" 
                                   id="qualification" name="qualification" value="{{ old('qualification') }}" required>
                            @error('qualification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control @error('specialization') is-invalid @enderror" 
                                   id="specialization" name="specialization" value="{{ old('specialization') }}">
                            @error('specialization')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="experience_years" class="form-label">Experience (Years) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('experience_years') is-invalid @enderror" 
                                   id="experience_years" name="experience_years" value="{{ old('experience_years') }}" min="0" max="50" required>
                            @error('experience_years')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="previous_institution" class="form-label">Previous Institution</label>
                            <input type="text" class="form-control @error('previous_institution') is-invalid @enderror" 
                                   id="previous_institution" name="previous_institution" value="{{ old('previous_institution') }}">
                            @error('previous_institution')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="joining_date" class="form-label">Joining Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('joining_date') is-invalid @enderror" 
                                   id="joining_date" name="joining_date" value="{{ old('joining_date') ?? now()->format('Y-m-d') }}" required>
                            @error('joining_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="employment_type" class="form-label">Employment Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('employment_type') is-invalid @enderror" id="employment_type" name="employment_type" required>
                                <option value="">Select Type</option>
                                <option value="permanent" {{ old('employment_type') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                                <option value="contract" {{ old('employment_type') == 'contract' ? 'selected' : '' }}>Contract</option>
                                <option value="part-time" {{ old('employment_type') == 'part-time' ? 'selected' : '' }}>Part-Time</option>
                            </select>
                            @error('employment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="basic_salary" class="form-label">Basic Salary (PKR) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('basic_salary') is-invalid @enderror" 
                                   id="basic_salary" name="basic_salary" value="{{ old('basic_salary') }}" min="0" step="0.01" required>
                            @error('basic_salary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignments Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-tasks me-2"></i>Subject & Class Assignments
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Subjects</label>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                @foreach($subjects as $subject)
                                    <div class="form-check">
                                        <input class="form-check-input @error('subjects.*') is-invalid @enderror" 
                                               type="checkbox" id="subject_{{ $subject->id }}" name="subjects[]" value="{{ $subject->id }}">
                                        <label class="form-check-label" for="subject_{{ $subject->id }}">
                                            {{ $subject->name }} ({{ $subject->code }})
                                            <small class="text-muted">{{ $subject->department ? ' - ' . $subject->department : '' }}</small>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('subjects.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('assignments')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Classes</label>
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                @foreach($classes as $class)
                                    <div class="form-check">
                                        <input class="form-check-input @error('classes.*') is-invalid @enderror" 
                                               type="checkbox" id="class_{{ $class->id }}" name="classes[]" value="{{ $class->id }}">
                                        <label class="form-check-label" for="class_{{ $class->id }}">
                                            {{ $class->name }} (Grade {{ $class->grade_level }})
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('classes.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <small class="text-muted">Select at least one subject or class for the teacher assignment.</small>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times me-2"></i>Cancel
            </a>
            <div>
                <button type="reset" class="btn btn-outline-warning me-2">
                    <i class="fas fa-redo me-2"></i>Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Add Teacher
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    // CNIC formatting
    function formatCnic(input) {
        let value = input.value.replace(/\D/g, '');
        let formattedValue = '';
        
        if (value.length >= 5) {
            formattedValue = value.substring(0, 5);
            if (value.length >= 12) {
                formattedValue += '-' + value.substring(5, 12);
                if (value.length > 12) {
                    formattedValue += '-' + value.substring(12, 13);
                }
            } else {
                formattedValue += '-' + value.substring(5);
            }
        } else {
            formattedValue = value;
        }
        
        input.value = formattedValue;
    }

    // Apply CNIC formatting to CNIC field
    document.getElementById('cnic').addEventListener('input', function() {
        formatCnic(this);
    });

    // Phone number formatting
    function formatPhoneNumber(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length > 0) {
            input.value = value;
        }
    }

    // Apply phone formatting to phone field
    document.getElementById('phone_number').addEventListener('input', function() {
        formatPhoneNumber(this);
    });

    // Set max date to today for date fields
    document.getElementById('date_of_birth').max = new Date().toISOString().split('T')[0];
    document.getElementById('joining_date').max = new Date().toISOString().split('T')[0];

    // Auto-calculate age when date of birth changes
    document.getElementById('date_of_birth').addEventListener('change', function() {
        const birthDate = new Date(this.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        console.log('Teacher Age:', age);
    });

    // Salary validation based on employment type
    document.getElementById('employment_type').addEventListener('change', function() {
        const salaryInput = document.getElementById('basic_salary');
        const salaryField = salaryInput.closest('.mb-3');
        
        // Remove existing validation messages
        const existingError = salaryField.querySelector('.text-danger');
        if (existingError) {
            existingError.remove();
        }
        
        if (this.value === 'part-time') {
            salaryInput.max = '50000';
            salaryInput.placeholder = 'Max: PKR 50,000 for part-time';
        } else {
            salaryInput.removeAttribute('max');
            salaryInput.placeholder = '';
        }
    });
</script>
@endsection
