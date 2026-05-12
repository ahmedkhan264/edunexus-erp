@extends('layouts.app')

@section('title', 'Edit Student')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Edit Student</h4>
            <p class="text-muted mb-0">Update student information for {{ $student->full_name }}</p>
        </div>
        <div>
            <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Students
            </a>
        </div>
    </div>

    <!-- Student Info Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    @if($student->profile_image)
                        <img src="{{ asset('storage/' . $student->profile_image) }}" 
                             alt="{{ $student->full_name }}" class="rounded-circle" width="60" height="60">
                    @else
                        <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            {{ strtoupper(substr($student->first_name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="col">
                    <h5 class="mb-1">{{ $student->full_name }}</h5>
                    <p class="text-muted mb-0">
                        Student ID: {{ $student->student_id }} | 
                        Class: {{ $student->class ? $student->class->name : 'Not Assigned' }} |
                        Status: {!! $student->status_badge !!}
                    </p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-2"></i>View Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <!-- Student Information Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fas fa-user-graduate me-2"></i>Student Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                   id="first_name" name="first_name" value="{{ old('first_name', $student->first_name) }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                   id="last_name" name="last_name" value="{{ old('last_name', $student->last_name) }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                   id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth->format('Y-m-d')) }}" required>
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender', $student->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" 
                                   id="phone_number" name="phone_number" value="{{ old('phone_number', $student->phone_number) }}" required>
                            @error('phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $student->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} (Grade {{ $class->grade_level }})
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="admission_date" class="form-label">Admission Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('admission_date') is-invalid @enderror" 
                                   id="admission_date" name="admission_date" value="{{ old('admission_date', $student->admission_date->format('Y-m-d')) }}" required>
                            @error('admission_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="enrolled" {{ old('status', $student->status) == 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                                <option value="graduated" {{ old('status', $student->status) == 'graduated' ? 'selected' : '' }}>Graduated</option>
                                <option value="suspended" {{ old('status', $student->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="withdrawn" {{ old('status', $student->status) == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="is_active" class="form-label">Active Status</label>
                            <div class="form-check">
                                <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                       type="checkbox" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', $student->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Student is active
                                </label>
                            </div>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="graduation_date" class="form-label">Graduation Date</label>
                            <input type="date" class="form-control @error('graduation_date') is-invalid @enderror" 
                                   id="graduation_date" name="graduation_date" 
                                   value="{{ old('graduation_date', $student->graduation_date ? $student->graduation_date->format('Y-m-d') : '') }}">
                            @error('graduation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="previous_school_gpa" class="form-label">Previous School GPA</label>
                            <input type="number" step="0.01" min="0" max="4" class="form-control @error('previous_school_gpa') is-invalid @enderror" 
                                   id="previous_school_gpa" name="previous_school_gpa" value="{{ old('previous_school_gpa', $student->previous_school_gpa) }}">
                            @error('previous_school_gpa')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="previous_school_name" class="form-label">Previous School Name</label>
                            <input type="text" class="form-control @error('previous_school_name') is-invalid @enderror" 
                                   id="previous_school_name" name="previous_school_name" value="{{ old('previous_school_name', $student->previous_school_name) }}">
                            @error('previous_school_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profile Photo</label>
                            <input type="file" class="form-control @error('profile_image') is-invalid @enderror" 
                                   id="profile_image" name="profile_image" accept="image/*">
                            <small class="text-muted">Allowed formats: JPEG, PNG. Max size: 2MB</small>
                            @if($student->profile_image)
                                <div class="mt-2">
                                    <small class="text-muted">Current: 
                                        <a href="{{ asset('storage/' . $student->profile_image) }}" target="_blank">View Image</a>
                                    </small>
                                </div>
                            @endif
                            @error('profile_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes', $student->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>Address Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="address" class="form-label">Street Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="2" required>{{ old('address', $student->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city', $student->city) }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="postal_code" class="form-label">Postal Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" name="postal_code" value="{{ old('postal_code', $student->postal_code) }}" required>
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="state" class="form-label">State/Province <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                   id="state" name="state" value="{{ old('state', $student->state) }}" required>
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                   id="country" name="country" value="{{ old('country', $student->country) }}" required>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nationality') is-invalid @enderror" 
                                   id="nationality" name="nationality" value="{{ old('nationality', $student->nationality) }}" required>
                            @error('nationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="religion" class="form-label">Religion</label>
                                    <input type="text" class="form-control @error('religion') is-invalid @enderror" 
                                           id="religion" name="religion" value="{{ old('religion', $student->religion) }}">
                                    @error('religion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="blood_group" class="form-label">Blood Group</label>
                                    <select class="form-select @error('blood_group') is-invalid @enderror" id="blood_group" name="blood_group">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+" {{ old('blood_group', $student->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ old('blood_group', $student->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('blood_group', $student->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ old('blood_group', $student->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="O+" {{ old('blood_group', $student->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ old('blood_group', $student->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>
                                        <option value="AB+" {{ old('blood_group', $student->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ old('blood_group', $student->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                    </select>
                                    @error('blood_group')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Contact Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">
                    <i class="fas fa-phone-alt me-2"></i>Emergency Contact
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="emergency_contact_name" class="form-label">Contact Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                   id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $student->emergency_contact_name) }}" required>
                            @error('emergency_contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="emergency_contact_phone" class="form-label">Contact Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                   id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $student->emergency_contact_phone) }}" required>
                            @error('emergency_contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="emergency_contact_relation" class="form-label">Relation <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('emergency_contact_relation') is-invalid @enderror" 
                                   id="emergency_contact_relation" name="emergency_contact_relation" value="{{ old('emergency_contact_relation', $student->emergency_contact_relation) }}" required>
                            @error('emergency_contact_relation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parent/Guardian Information Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i class="fas fa-users me-2"></i>Parent/Guardian Information
                </h6>
            </div>
            <div class="card-body">
                <!-- Father Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary mb-3">Father Information</h6>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="father_name" class="form-label">Father Name</label>
                            <input type="text" class="form-control @error('father_name') is-invalid @enderror" 
                                   id="father_name" name="father_name" value="{{ old('father_name', optional($student->parentProfile)->father_name) }}">
                            @error('father_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="father_cnic" class="form-label">Father CNIC</label>
                            <input type="text" class="form-control @error('father_cnic') is-invalid @enderror" 
                                   id="father_cnic" name="father_cnic" value="{{ old('father_cnic', optional($student->parentProfile)->father_cnic) }}" placeholder="XXXXX-XXXXXXX-X">
                            @error('father_cnic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="father_phone" class="form-label">Father Phone</label>
                            <input type="tel" class="form-control @error('father_phone') is-invalid @enderror" 
                                   id="father_phone" name="father_phone" value="{{ old('father_phone', optional($student->parentProfile)->father_phone) }}">
                            @error('father_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="father_occupation" class="form-label">Father Occupation</label>
                            <input type="text" class="form-control @error('father_occupation') is-invalid @enderror" 
                                   id="father_occupation" name="father_occupation" value="{{ old('father_occupation', optional($student->parentProfile)->father_occupation) }}">
                            @error('father_occupation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Mother Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-danger mb-3">Mother Information</h6>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="mother_name" class="form-label">Mother Name</label>
                            <input type="text" class="form-control @error('mother_name') is-invalid @enderror" 
                                   id="mother_name" name="mother_name" value="{{ old('mother_name', optional($student->parentProfile)->mother_name) }}">
                            @error('mother_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="mother_cnic" class="form-label">Mother CNIC</label>
                            <input type="text" class="form-control @error('mother_cnic') is-invalid @enderror" 
                                   id="mother_cnic" name="mother_cnic" value="{{ old('mother_cnic', optional($student->parentProfile)->mother_cnic) }}" placeholder="XXXXX-XXXXXXX-X">
                            @error('mother_cnic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="mother_phone" class="form-label">Mother Phone</label>
                            <input type="tel" class="form-control @error('mother_phone') is-invalid @enderror" 
                                   id="mother_phone" name="mother_phone" value="{{ old('mother_phone', optional($student->parentProfile)->mother_phone) }}">
                            @error('mother_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="mother_occupation" class="form-label">Mother Occupation</label>
                            <input type="text" class="form-control @error('mother_occupation') is-invalid @enderror" 
                                   id="mother_occupation" name="mother_occupation" value="{{ old('mother_occupation', optional($student->parentProfile)->mother_occupation) }}">
                            @error('mother_occupation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Guardian Information -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-info mb-3">Guardian Information (if applicable)</h6>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="guardian_name" class="form-label">Guardian Name</label>
                            <input type="text" class="form-control @error('guardian_name') is-invalid @enderror" 
                                   id="guardian_name" name="guardian_name" value="{{ old('guardian_name', optional($student->parentProfile)->guardian_name) }}">
                            @error('guardian_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="guardian_cnic" class="form-label">Guardian CNIC</label>
                            <input type="text" class="form-control @error('guardian_cnic') is-invalid @enderror" 
                                   id="guardian_cnic" name="guardian_cnic" value="{{ old('guardian_cnic', optional($student->parentProfile)->guardian_cnic) }}" placeholder="XXXXX-XXXXXXX-X">
                            @error('guardian_cnic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="guardian_phone" class="form-label">Guardian Phone</label>
                            <input type="tel" class="form-control @error('guardian_phone') is-invalid @enderror" 
                                   id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone', optional($student->parentProfile)->guardian_phone) }}">
                            @error('guardian_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="guardian_occupation" class="form-label">Guardian Occupation</label>
                            <input type="text" class="form-control @error('guardian_occupation') is-invalid @enderror" 
                                   id="guardian_occupation" name="guardian_occupation" value="{{ old('guardian_occupation', optional($student->parentProfile)->guardian_occupation) }}">
                            @error('guardian_occupation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="guardian_relation" class="form-label">Guardian Relation</label>
                            <input type="text" class="form-control @error('guardian_relation') is-invalid @enderror" 
                                   id="guardian_relation" name="guardian_relation" value="{{ old('guardian_relation', optional($student->parentProfile)->guardian_relation) }}">
                            @error('guardian_relation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="guardian_address" class="form-label">Guardian Address</label>
                            <textarea class="form-control @error('guardian_address') is-invalid @enderror" 
                                      id="guardian_address" name="guardian_address" rows="2">{{ old('guardian_address', optional($student->parentProfile)->guardian_address) }}</textarea>
                            @error('guardian_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input @error('is_primary_guardian') is-invalid @enderror" 
                                       type="checkbox" id="is_primary_guardian" name="is_primary_guardian" value="1"
                                       {{ old('is_primary_guardian', optional($student->parentProfile)->is_primary_guardian) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_primary_guardian">
                                    Primary Guardian
                                </label>
                            </div>
                            @error('is_primary_guardian')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times me-2"></i>Cancel
            </a>
            <div>
                <button type="reset" class="btn btn-outline-warning me-2">
                    <i class="fas fa-redo me-2"></i>Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Student
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

    // Apply CNIC formatting to CNIC fields
    document.getElementById('father_cnic').addEventListener('input', function() {
        formatCnic(this);
    });
    
    document.getElementById('mother_cnic').addEventListener('input', function() {
        formatCnic(this);
    });
    
    document.getElementById('guardian_cnic').addEventListener('input', function() {
        formatCnic(this);
    });

    // Phone number formatting
    function formatPhoneNumber(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length > 0) {
            input.value = value;
        }
    }

    // Apply phone formatting to phone fields
    document.querySelectorAll('input[type="tel"]').forEach(input => {
        input.addEventListener('input', function() {
            formatPhoneNumber(this);
        });
    });

    // Set max date to today for date fields
    document.getElementById('date_of_birth').max = new Date().toISOString().split('T')[0];
    document.getElementById('admission_date').max = new Date().toISOString().split('T')[0];
    document.getElementById('graduation_date').max = new Date().toISOString().split('T')[0];

    // Show/hide graduation date based on status
    document.getElementById('status').addEventListener('change', function() {
        const graduationDateField = document.getElementById('graduation_date').closest('.mb-3');
        if (this.value === 'graduated') {
            graduationDateField.style.display = 'block';
        } else {
            graduationDateField.style.display = 'none';
        }
    });

    // Initialize visibility based on current status
    document.addEventListener('DOMContentLoaded', function() {
        const statusField = document.getElementById('status');
        const graduationDateField = document.getElementById('graduation_date').closest('.mb-3');
        if (statusField.value !== 'graduated') {
            graduationDateField.style.display = 'none';
        }
    });

    // Auto-calculate age when date of birth changes
    document.getElementById('date_of_birth').addEventListener('change', function() {
        const birthDate = new Date(this.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        console.log('Student Age:', age);
    });
</script>
@endsection
