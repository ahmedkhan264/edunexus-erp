@extends('layouts.app')

@section('title', 'Class Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Class Management</h1>
            <p class="text-muted mb-0">Manage school classes, sections, and student assignments</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#classModal">
            <i class="fas fa-plus me-2"></i>Add New Class
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $classes->total() }}</h4>
                            <p class="mb-0">Total Classes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-school fa-2x opacity-75"></i>
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
                            <h4 class="mb-0">{{ $classes->sum('students_count') }}</h4>
                            <p class="mb-0">Total Students</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x opacity-75"></i>
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
                            <h4 class="mb-0">{{ $classes->sum('sections_count') }}</h4>
                            <p class="mb-0">Total Sections</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-th fa-2x opacity-75"></i>
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
                            <h4 class="mb-0">{{ $classes->where('is_active', true)->count() }}</h4>
                            <p class="mb-0">Active Classes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Classes Table -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Classes List</h5>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search classes..." style="width: 200px;">
                    <select class="form-select form-select-sm" id="gradeFilter" style="width: 150px;">
                        <option value="">All Grades</option>
                        @for($grade = 1; $grade <= 12; $grade++)
                            <option value="{{ $grade }}">Grade {{ $grade }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Class Name</th>
                            <th>Grade Level</th>
                            <th>Section</th>
                            <th>Room</th>
                            <th>Capacity</th>
                            <th>Students</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="classesTableBody">
                        @forelse($classes as $class)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="fas fa-school text-primary"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $class->name }}</div>
                                            @if($class->description)
                                                <small class="text-muted">{{ Str::limit($class->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">Grade {{ $class->grade_level }}</span>
                                </td>
                                <td>
                                    @if($class->section)
                                        <span class="badge bg-secondary">{{ $class->section }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($class->room_number)
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-door-open text-muted me-1"></i>
                                            {{ $class->room_number }}
                                            @if($class->floor)
                                                <small class="text-muted ms-1">(Floor {{ $class->floor }})</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($class->capacity)
                                        <div class="progress" style="height: 6px;">
                                            <?php $percentage = ($class->students_count / $class->capacity) * 100; ?>
                                            <div class="progress-bar @if($percentage > 80) bg-danger @elseif($percentage > 60) bg-warning @else bg-success @endif" 
                                                 style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ $class->students_count }}/{{ $class->capacity }}</small>
                                    @else
                                        <span class="text-muted">No limit</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-users text-muted me-1"></i>
                                        <span class="fw-medium">{{ $class->students_count }}</span>
                                        @if($class->sections_count > 0)
                                            <small class="text-muted ms-1">({{ $class->sections_count }} sections)</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($class->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-class" 
                                                data-class-id="{{ $class->id }}" 
                                                data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info view-class" 
                                                data-class-id="{{ $class->id }}" 
                                                data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-class" 
                                                data-class-id="{{ $class->id }}" 
                                                data-class-name="{{ $class->name }}"
                                                data-student-count="{{ $class->students_count }}"
                                                data-bs-toggle="tooltip" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-school fa-2x text-muted mb-3"></i>
                                    <div class="text-muted">No classes found</div>
                                    <button type="button" class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#classModal">
                                        Add First Class
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($classes->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $classes->firstItem() }} to {{ $classes->lastItem() }} of {{ $classes->total() }} entries
                    </div>
                    {{ $classes->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Add/Edit Class Modal -->
<div class="modal fade" id="classModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="classModalTitle">Add New Class</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="classForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="classId" name="class_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Class Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="grade_level" class="form-label">Grade Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="grade_level" name="grade_level" required>
                                    <option value="">Select Grade</option>
                                    @for($grade = 1; $grade <= 12; $grade++)
                                        <option value="{{ $grade }}">Grade {{ $grade }}</option>
                                    @endfor
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="section" class="form-label">Section</label>
                                <input type="text" class="form-control" id="section" name="section" placeholder="e.g., A, B, C">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="room_number" class="form-label">Room Number</label>
                                <input type="text" class="form-control" id="room_number" name="room_number" placeholder="e.g., 101">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control" id="capacity" name="capacity" min="1" max="100" placeholder="e.g., 30">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="floor" class="form-label">Floor</label>
                                <select class="form-select" id="floor" name="floor">
                                    <option value="">Select Floor</option>
                                    @for($floor = 1; $floor <= 10; $floor++)
                                        <option value="{{ $floor }}">Floor {{ $floor }}</option>
                                    @endfor
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Optional description about the class..."></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">
                                Active Class
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Class
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Class Details Modal -->
<div class="modal fade" id="viewClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Class Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="classDetails">
                <!-- Class details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
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
                <p>Are you sure you want to delete the class <strong id="deleteClassName"></strong>?</p>
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
    const classModal = document.getElementById('classModal');
    const classForm = document.getElementById('classForm');
    const deleteModal = document.getElementById('deleteModal');
    const viewClassModal = document.getElementById('viewClassModal');
    
    // Add/Edit Class
    document.querySelectorAll('.edit-class').forEach(button => {
        button.addEventListener('click', function() {
            const classId = this.dataset.classId;
            
            fetch(`/admin/classes/${classId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const classData = data.class;
                        document.getElementById('classModalTitle').textContent = 'Edit Class';
                        document.getElementById('classId').value = classData.id;
                        document.getElementById('name').value = classData.name;
                        document.getElementById('grade_level').value = classData.grade_level;
                        document.getElementById('section').value = classData.section || '';
                        document.getElementById('room_number').value = classData.room_number || '';
                        document.getElementById('capacity').value = classData.capacity || '';
                        document.getElementById('floor').value = classData.floor || '';
                        document.getElementById('description').value = classData.description || '';
                        document.getElementById('is_active').checked = classData.is_active;
                        
                        classModal.show();
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });
    
    // View Class Details
    document.querySelectorAll('.view-class').forEach(button => {
        button.addEventListener('click', function() {
            const classId = this.dataset.classId;
            
            fetch(`/admin/classes/${classId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const classData = data.class;
                        const detailsHtml = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Basic Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Class Name:</strong></td>
                                            <td>${classData.name}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Grade Level:</strong></td>
                                            <td>Grade ${classData.grade_level}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Section:</strong></td>
                                            <td>${classData.section || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>${classData.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Physical Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Room Number:</strong></td>
                                            <td>${classData.room_number || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Floor:</strong></td>
                                            <td>${classData.floor ? `Floor ${classData.floor}` : 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Capacity:</strong></td>
                                            <td>${classData.capacity || 'No limit'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Students:</strong></td>
                                            <td>${classData.students_count}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            ${classData.description ? `
                                <div class="mt-3">
                                    <h6>Description</h6>
                                    <p>${classData.description}</p>
                                </div>
                            ` : ''}
                            <div class="mt-3">
                                <h6>Statistics</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h4>${classData.students_count}</h4>
                                                <small>Total Students</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-info text-white">
                                            <div class="card-body text-center">
                                                <h4>${classData.sections_count}</h4>
                                                <small>Sections</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h4>${classData.capacity ? Math.round((classData.students_count / classData.capacity) * 100) + '%' : 'N/A'}</h4>
                                                <small>Occupancy</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        document.getElementById('classDetails').innerHTML = detailsHtml;
                        viewClassModal.show();
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });
    
    // Delete Class
    document.querySelectorAll('.delete-class').forEach(button => {
        button.addEventListener('click', function() {
            const classId = this.dataset.classId;
            const className = this.dataset.className;
            const studentCount = this.dataset.studentCount;
            
            document.getElementById('deleteClassName').textContent = className;
            
            if (studentCount > 0) {
                document.getElementById('deleteWarning').textContent = 
                    `Warning: This class has ${studentCount} student(s). You cannot delete a class with active students.`;
                document.getElementById('confirmDelete').disabled = true;
                document.getElementById('confirmDelete').classList.add('disabled');
            } else {
                document.getElementById('deleteWarning').textContent = '';
                document.getElementById('confirmDelete').disabled = false;
                document.getElementById('confirmDelete').classList.remove('disabled');
            }
            
            document.getElementById('confirmDelete').onclick = function() {
                fetch(`/admin/classes/${classId}`, {
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
                        alert(data.message || 'Error deleting class');
                    }
                })
                .catch(error => console.error('Error:', error));
            };
            
            deleteModal.show();
        });
    });
    
    // Reset form when modal is hidden
    classModal.addEventListener('hidden.bs.modal', function() {
        classForm.reset();
        document.getElementById('classModalTitle').textContent = 'Add New Class';
        document.getElementById('classId').value = '';
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    });
    
    // Handle form submission
    classForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const classId = document.getElementById('classId').value;
        const url = classId ? `/admin/classes/${classId}` : '/admin/classes';
        const method = classId ? 'PUT' : 'POST';
        
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
                classModal.hide();
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
                    alert(data.message || 'Error saving class');
                }
            }
        })
        .catch(error => console.error('Error:', error));
    });
    
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#classesTableBody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Grade filter
    document.getElementById('gradeFilter').addEventListener('change', function() {
        const grade = this.value;
        const rows = document.querySelectorAll('#classesTableBody tr');
        
        rows.forEach(row => {
            if (grade === '') {
                row.style.display = '';
            } else {
                const gradeCell = row.querySelector('td:nth-child(2)');
                if (gradeCell && gradeCell.textContent.includes(`Grade ${grade}`)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });
});
</script>
@endpush
