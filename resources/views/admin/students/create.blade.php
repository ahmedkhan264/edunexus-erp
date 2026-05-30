@extends('layouts.app')

@section('title', 'Add Student')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Add New Student</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <h5>Personal Information</h5>
                                <hr>

                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                                    @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password (leave blank for auto-generate)</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="admission_number" class="form-label">Admission Number *</label>
                                    <input type="text" class="form-control @error('admission_number') is-invalid @enderror" id="admission_number" name="admission_number" value="{{ old('admission_number') }}" required>
                                    @error('admission_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="class_id" class="form-label">Class *</label>
                                            <select class="form-control @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                                                <option value="">Select Class</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="roll_number" class="form-label">Roll Number</label>
                                            <input type="number" class="form-control @error('roll_number') is-invalid @enderror" id="roll_number" name="roll_number" value="{{ old('roll_number') }}">
                                            @error('roll_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="gender" class="form-label">Gender *</label>
                                    <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                        <option value="">Select</option>
                                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                                    @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="blood_group" class="form-label">Blood Group</label>
                                    <input type="text" class="form-control @error('blood_group') is-invalid @enderror" id="blood_group" name="blood_group" value="{{ old('blood_group') }}">
                                    @error('blood_group') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address') }}</textarea>
                                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="profile_photo" class="form-label">Profile Photo</label>
                                    <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo" accept="image/*">
                                    @error('profile_photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                        <option value="Enrolled" {{ old('status') == 'Enrolled' ? 'selected' : '' }}>Enrolled</option>
                                        <option value="Graduated" {{ old('status') == 'Graduated' ? 'selected' : '' }}>Graduated</option>
                                        <option value="Suspended" {{ old('status') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                        <option value="Withdrawn" {{ old('status') == 'Withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5>Academic & Parent Information</h5>
                                <hr>

                                <div class="mb-3">
                                    <label for="previous_school" class="form-label">Previous School</label>
                                    <input type="text" class="form-control @error('previous_school') is-invalid @enderror" id="previous_school" name="previous_school" value="{{ old('previous_school') }}">
                                    @error('previous_school') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="previous_gpa" class="form-label">Previous GPA</label>
                                    <input type="number" step="0.01" class="form-control @error('previous_gpa') is-invalid @enderror" id="previous_gpa" name="previous_gpa" value="{{ old('previous_gpa') }}">
                                    @error('previous_gpa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <h6 class="mt-3">Father's Information</h6>
                                <div class="mb-3">
                                    <label for="father_name" class="form-label">Father Name</label>
                                    <input type="text" class="form-control" id="father_name" name="father_name" value="{{ old('father_name') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="father_phone" class="form-label">Father Phone</label>
                                    <input type="text" class="form-control" id="father_phone" name="father_phone" value="{{ old('father_phone') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="father_occupation" class="form-label">Occupation</label>
                                    <input type="text" class="form-control" id="father_occupation" name="father_occupation" value="{{ old('father_occupation') }}">
                                </div>

                                <h6 class="mt-3">Mother's Information</h6>
                                <div class="mb-3">
                                    <label for="mother_name" class="form-label">Mother Name</label>
                                    <input type="text" class="form-control" id="mother_name" name="mother_name" value="{{ old('mother_name') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="mother_phone" class="form-label">Mother Phone</label>
                                    <input type="text" class="form-control" id="mother_phone" name="mother_phone" value="{{ old('mother_phone') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="mother_occupation" class="form-label">Occupation</label>
                                    <input type="text" class="form-control" id="mother_occupation" name="mother_occupation" value="{{ old('mother_occupation') }}">
                                </div>

                                <h6 class="mt-3">Guardian Information (if different)</h6>
                                <div class="mb-3">
                                    <label for="guardian_name" class="form-label">Guardian Name</label>
                                    <input type="text" class="form-control" id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="guardian_phone" class="form-label">Guardian Phone</label>
                                    <input type="text" class="form-control" id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="guardian_relation" class="form-label">Relation</label>
                                    <input type="text" class="form-control" id="guardian_relation" name="guardian_relation" value="{{ old('guardian_relation') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="parent_address" class="form-label">Parent/Guardian Address</label>
                                    <textarea class="form-control" id="parent_address" name="parent_address" rows="2">{{ old('parent_address') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Save Student</button>
                            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
