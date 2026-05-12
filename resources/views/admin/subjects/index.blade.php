@extends('layouts.app')

@section('title', 'Subject Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Subject Management</h1>
            <p class="text-muted mb-0">Manage academic subjects, class assignments, and teacher allocations</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subjectModal">
            <i class="fas fa-plus me-2"></i>Add New Subject
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $subjects->total() }}</h4>
                            <p class="mb-0">Total Subjects</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-book fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $subjects->whereNotNull('class_id')->count() }}</h4>
                            <p class="mb-0">Assigned to Classes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-school fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $subjects->whereNotNull('teacher_id')->count() }}</h4>
                            <p class="mb-0">Teacher Assigned</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chalkboard-teacher fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $subjects->where('is_practical', true)->count() }}</h4>
                            <p class="mb-0">Practical Subjects</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-flask fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Class Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.subjects.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="classFilter" class="form-label">Filter by Class</label>
                        <select class="form-select" id="classFilter" name="class_id">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} (Grade {{ $class->grade_level }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-2"></i>Clear
                        </a>
                    </div>
                    <div class="col-md-4">
                        <div class="text-end text-muted">
                            @if($selectedClassId)
                                Showing subjects for: <strong>{{ $classes->where('id', $selectedClassId)->first()->name ?? 'Unknown Class' }}</strong>
                            @else
                                Showing all subjects
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Subjects Table -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Subjects List</h5>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search subjects..." style="width: 200px;">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Subject Name</th>
                            <th>Code</th>
                            <th>Class</th>
                            <th>Teacher</th>
                            <th>Periods</th>
                            <th>Marks</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="subjectsTableBody">
                        @forelse($subjects as $subject)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="fas fa-book text-primary"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $subject->name }}</div>
                                            @if($subject->description)
                                                <small class="text-muted">{{ Str::limit($subject->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $subject->code }}</span>
                                </td>
                                <td>
                                    @if($subject->class)
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-info">{{ $subject->class->name }}</span>
                                            <small class="text-muted ms-2">Grade {{ $subject->class->grade_level }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($subject->teacher)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-chalkboard-teacher text-success"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $subject->teacher->user->name }}</div>
                                                <small class="text-muted">{{ $subject->teacher->employee_id }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($subject->weekly_periods)
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock text-muted me-1"></i>
                                            <span class="fw-medium">{{ $subject->weekly_periods }}/week</span>
                                            @if($subject->credit_hours)
                                                <small class="text-muted ms-1">({{ $subject->credit_hours }} credits)</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </td>
                                <td>
                                    @if($subject->total_marks)
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium">{{ $subject->total_marks }}</span>
                                            @if($subject->passing_marks)
                                                <small class="text-muted">Pass: {{ $subject->passing_marks }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </td>
                                <td>
                                    @if($subject->is_practical)
                                        <span class="badge bg-warning">Practical</span>
                                    @else
                                        <span class="badge bg-info">Theory</span>
                                    @endif
                                </td>
                                <td>
                                    @if($subject->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-subject" 
                                                data-subject-id="{{ $subject->id }}" 
                                                data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info view-subject" 
                                                data-subject-id="{{ $subject->id }}" 
                                                data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success assign-subject" 
                                                data-subject-id="{{ $subject->id }}" 
                                                data-bs-toggle="tooltip" title="Assign to Class">
                                            <i class="fas fa-link"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-subject" 
                                                data-subject-id="{{ $subject->id }}" 
                                                data-subject-name="{{ $subject->name }}"
                                                data-has-assignments="{{ $subject->class_id || $subject->teacher_id ? 'true' : 'false' }}"
                                                data-bs-toggle="tooltip" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-book fa-2x text-muted mb-3"></i>
                                    <div class="text-muted">No subjects found</div>
                                    <button type="button" class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#subjectModal">
                                        Add First Subject
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($subjects->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $subjects->firstItem() }} to {{ $subjects->lastItem() }} of {{ $subjects->total() }} entries
                    </div>
                    {{ $subjects->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Add/Edit Subject Modal -->
<div class="modal fade" id="subjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="subjectModalTitle">Add New Subject</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="subjectForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="subjectId" name="subject_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Subject Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label">Subject Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" required placeholder="e.g., MATH101">
                                <small class="form-text text-muted">Use uppercase letters, numbers, hyphens, underscores only</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="class_id" class="form-label">Assign to Class</label>
                                <select class="form-select" id="class_id" name="class_id">
                                    <option value="">Select Class (Optional)</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }} (Grade {{ $class->grade_level }})</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="teacher_id" class="form-label">Assign Teacher</label>
                                <select class="form-select" id="teacher_id" name="teacher_id">
                                    <option value="">Select Teacher (Optional)</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="credit_hours" class="form-label">Credit Hours</label>
                                <input type="number" class="form-control" id="credit_hours" name="credit_hours" min="1" max="20">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="weekly_periods" class="form-label">Weekly Periods</label>
                                <input type="number" class="form-control" id="weekly_periods" name="weekly_periods" min="1" max="50">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="passing_marks" class="form-label">Passing Marks</label>
                                <input type="number" class="form-control" id="passing_marks" name="passing_marks" min="0" max="100">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="total_marks" class="form-label">Total Marks</label>
                                <input type="number" class="form-control" id="total_marks" name="total_marks" min="1" max="200">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Optional description about the subject..."></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_practical" name="is_practical">
                                    <label class="form-check-label" for="is_practical">
                                        Practical Subject
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                    <label class="form-check-label" for="is_active">
                                        Active Subject
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Subject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Subject Details Modal -->
<div class="modal fade" id="viewSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Subject Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="subjectDetails">
                <!-- Subject details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Subject Modal -->
<div class="modal fade" id="assignSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Assign Subject to Class</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignSubjectForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="assignSubjectId" name="subject_id">
                    
                    <div class="mb-3">
                        <label for="assignClassId" class="form-label">Class <span class="text-danger">*</span></label>
                        <select class="form-select" id="assignClassId" name="class_id" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }} (Grade {{ $class->grade_level }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="assignTeacherId" class="form-label">Teacher</label>
                        <select class="form-select" id="assignTeacherId" name="teacher_id">
                            <option value="">Select Teacher (Optional)</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-link me-2"></i>Assign Subject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the subject <strong id="deleteSubjectName"></strong>?</p>
                <p class="text-warning" id="deleteWarning"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const subjectModal = document.getElementById('subjectModal');
    const subjectForm = document.getElementById('subjectForm');
    const deleteModal = document.getElementById('deleteModal');
    const viewSubjectModal = document.getElementById('viewSubjectModal');
    const assignSubjectModal = document.getElementById('assignSubjectModal');
    const assignSubjectForm = document.getElementById('assignSubjectForm');
    const classSelect = document.getElementById('class_id');
    const teacherSelect = document.getElementById('teacher_id');
    
    // Add/Edit Subject
    document.querySelectorAll('.edit-subject').forEach(button => {
        button.addEventListener('click', function() {
            const subjectId = this.dataset.subjectId;
            
            fetch(`/admin/subjects/${subjectId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const subjectData = data.subject;
                        document.getElementById('subjectModalTitle').textContent = 'Edit Subject';
                        document.getElementById('subjectId').value = subjectData.id;
                        document.getElementById('name').value = subjectData.name;
                        document.getElementById('code').value = subjectData.code;
                        document.getElementById('class_id').value = subjectData.class_id || '';
                        document.getElementById('teacher_id').value = subjectData.teacher_id || '';
                        document.getElementById('credit_hours').value = subjectData.credit_hours || '';
                        document.getElementById('weekly_periods').value = subjectData.weekly_periods || '';
                        document.getElementById('passing_marks').value = subjectData.passing_marks || '';
                        document.getElementById('total_marks').value = subjectData.total_marks || '';
                        document.getElementById('description').value = subjectData.description || '';
                        document.getElementById('is_practical').checked = subjectData.is_practical;
                        document.getElementById('is_active').checked = subjectData.is_active;
                        
                        subjectModal.show();
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });
    
    // View Subject Details
    document.querySelectorAll('.view-subject').forEach(button => {
        button.addEventListener('click', function() {
            const subjectId = this.dataset.subjectId;
            
            fetch(`/admin/subjects/${subjectId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const subjectData = data.subject;
                        const detailsHtml = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Basic Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Subject Name:</strong></td>
                                            <td>${subjectData.name}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Subject Code:</strong></td>
                                            <td><span class="badge bg-secondary">${subjectData.code}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Type:</strong></td>
                                            <td>${subjectData.is_practical ? '<span class="badge bg-warning">Practical</span>' : '<span class="badge bg-info">Theory</span>'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>${subjectData.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Academic Details</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Credit Hours:</strong></td>
                                            <td>${subjectData.credit_hours || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Weekly Periods:</strong></td>
                                            <td>${subjectData.weekly_periods || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Marks:</strong></td>
                                            <td>${subjectData.total_marks || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Passing Marks:</strong></td>
                                            <td>${subjectData.passing_marks || 'N/A'}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <h6>Class Assignment</h6>
                                    ${subjectData.class ? `
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="fas fa-school text-info"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">${subjectData.class.name}</div>
                                                <small class="text-muted">Grade ${subjectData.class.grade_level}</small>
                                            </div>
                                        </div>
                                    ` : '<p class="text-muted">Not assigned to any class</p>'}
                                </div>
                                <div class="col-md-6">
                                    <h6>Teacher Assignment</h6>
                                    ${subjectData.teacher ? `
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="fas fa-chalkboard-teacher text-success"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">${subjectData.teacher.user.name}</div>
                                                <small class="text-muted">${subjectData.teacher.employee_id}</small>
                                            </div>
                                        </div>
                                    ` : '<p class="text-muted">No teacher assigned</p>'}
                                </div>
                            </div>
                            ${subjectData.description ? `
                                <div class="mt-3">
                                    <h6>Description</h6>
                                    <p>${subjectData.description}</p>
                                </div>
                            ` : ''}
                        `;
                        
                        document.getElementById('subjectDetails').innerHTML = detailsHtml;
                        viewSubjectModal.show();
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });
    
    // Assign Subject
    document.querySelectorAll('.assign-subject').forEach(button => {
        button.addEventListener('click', function() {
            const subjectId = this.dataset.subjectId;
            document.getElementById('assignSubjectId').value = subjectId;
            assignSubjectModal.show();
        });
    });
    
    // Delete Subject
    document.querySelectorAll('.delete-subject').forEach(button => {
        button.addEventListener('click', function() {
            const subjectId = this.dataset.subjectId;
            const subjectName = this.dataset.subjectName;
            const hasAssignments = this.dataset.hasAssignments === 'true';
            
            document.getElementById('deleteSubjectName').textContent = subjectName;
            
            if (hasAssignments) {
                document.getElementById('deleteWarning').textContent = 
                    'Warning: This subject is assigned to a class or teacher. Please remove assignments first.';
                document.getElementById('confirmDelete').disabled = true;
                document.getElementById('confirmDelete').classList.add('disabled');
            } else {
                document.getElementById('deleteWarning').textContent = '';
                document.getElementById('confirmDelete').disabled = false;
                document.getElementById('confirmDelete').classList.remove('disabled');
            }
            
            document.getElementById('confirmDelete').onclick = function() {
                fetch(`/admin/subjects/${subjectId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        deleteModal.hide();
                        location.reload();
                    } else {
                        alert(data.message || 'Error deleting subject');
                    }
                })
                .catch(error => console.error('Error:', error));
            };
            
            deleteModal.show();
        });
    });
    
    // Reset form when modal is hidden
    subjectModal.addEventListener('hidden.bs.modal', function() {
        subjectForm.reset();
        document.getElementById('subjectModalTitle').textContent = 'Add New Subject';
        document.getElementById('subjectId').value = '';
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    });
    
    // Handle form submission
    subjectForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const subjectId = document.getElementById('subjectId').value;
        const url = subjectId ? `/admin/subjects/${subjectId}` : '/admin/subjects';
        const method = subjectId ? 'PUT' : 'POST';
        
        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'X-HTTP-Method-Override': method
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                subjectModal.hide();
                location.reload();
            } else {
                // Handle validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        const input = document.getElementById(key);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.nextElementSibling;
                            if (feedback && feedback.classList.contains('invalid-feedback')) {
                                feedback.textContent = data.errors[key][0];
                            }
                        }
                    });
                } else {
                    alert(data.message || 'Error saving subject');
                }
            }
        })
        .catch(error => console.error('Error:', error));
    });
    
    // Handle assign form submission
    assignSubjectForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('/admin/subjects/assign', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                assignSubjectModal.hide();
                location.reload();
            } else {
                alert(data.message || 'Error assigning subject');
            }
        })
        .catch(error => console.error('Error:', error));
    });
    
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#subjectsTableBody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
});
</script>
@endpush
