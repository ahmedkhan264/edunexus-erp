@extends('layouts.app')

@section('title', 'Section Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Section Management</h1>
            <p class="text-muted mb-0">Manage class sections, capacity, and teacher assignments</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sectionModal">
            <i class="fas fa-plus me-2"></i>Add New Section
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $sections->total() }}</h4>
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
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $sections->sum('students_count') }}</h4>
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
                            <h4 class="mb-0">{{ $sections->whereNotNull('teacher_id')->count() }}</h4>
                            <p class="mb-0">Assigned Teachers</p>
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
                            <h4 class="mb-0">{{ $sections->where('is_active', true)->count() }}</h4>
                            <p class="mb-0">Active Sections</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Class Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sections.index') }}">
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
                        <a href="{{ route('admin.sections.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-2"></i>Clear
                        </a>
                    </div>
                    <div class="col-md-4">
                        <div class="text-end text-muted">
                            @if($selectedClassId)
                                Showing sections for: <strong>{{ $classes->where('id', $selectedClassId)->first()->name ?? 'Unknown Class' }}</strong>
                            @else
                                Showing all sections
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sections Table -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Sections List</h5>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search sections..." style="width: 200px;">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Section Name</th>
                            <th>Class</th>
                            <th>Teacher</th>
                            <th>Capacity</th>
                            <th>Students</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sectionsTableBody">
                        @forelse($sections as $section)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="fas fa-th text-primary"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $section->name }}</div>
                                            @if($section->description)
                                                <small class="text-muted">{{ Str::limit($section->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-info">{{ $section->class->name }}</span>
                                        <small class="text-muted ms-2">Grade {{ $section->class->grade_level }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($section->teacher)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-chalkboard-teacher text-success"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $section->teacher->user->name }}</div>
                                                <small class="text-muted">{{ $section->teacher->employee_id }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($section->capacity)
                                        <div class="progress" style="height: 6px;">
                                            <?php $percentage = ($section->students_count / $section->capacity) * 100; ?>
                                            <div class="progress-bar @if($percentage > 80) bg-danger @elseif($percentage > 60) bg-warning @else bg-success @endif" 
                                                 style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ $section->students_count }}/{{ $section->capacity }}</small>
                                    @else
                                        <span class="text-muted">No limit</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-users text-muted me-1"></i>
                                        <span class="fw-medium">{{ $section->students_count }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($section->room_number)
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-door-open text-muted me-1"></i>
                                            {{ $section->room_number }}
                                            @if($section->floor)
                                                <small class="text-muted ms-1">(Floor {{ $section->floor }})</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($section->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-section" 
                                                data-section-id="{{ $section->id }}" 
                                                data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info view-section" 
                                                data-section-id="{{ $section->id }}" 
                                                data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-section" 
                                                data-section-id="{{ $section->id }}" 
                                                data-section-name="{{ $section->name }}"
                                                data-student-count="{{ $section->students_count }}"
                                                data-bs-toggle="tooltip" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-th fa-2x text-muted mb-3"></i>
                                    <div class="text-muted">No sections found</div>
                                    <button type="button" class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#sectionModal">
                                        Add First Section
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($sections->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $sections->firstItem() }} to {{ $sections->lastItem() }} of {{ $sections->total() }} entries
                    </div>
                    {{ $sections->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Add/Edit Section Modal -->
<div class="modal fade" id="sectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="sectionModalTitle">Add New Section</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="sectionForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="sectionId" name="section_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Section Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required placeholder="e.g., A, B, C">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                <select class="form-select" id="class_id" name="class_id" required>
                                    <option value="">Select Class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }} (Grade {{ $class->grade_level }})</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="teacher_id" class="form-label">Section Teacher</label>
                                <select class="form-select" id="teacher_id" name="teacher_id">
                                    <option value="">Select Teacher (Optional)</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control" id="capacity" name="capacity" min="1" max="100" placeholder="e.g., 30">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="room_number" class="form-label">Room Number</label>
                                <input type="text" class="form-control" id="room_number" name="room_number" placeholder="e.g., 101">
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
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Optional description about the section..."></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">
                                Active Section
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Section
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Section Details Modal -->
<div class="modal fade" id="viewSectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Section Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="sectionDetails">
                <!-- Section details will be loaded here -->
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
                <p>Are you sure you want to delete the section <strong id="deleteSectionName"></strong>?</p>
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
    const sectionModal = document.getElementById('sectionModal');
    const sectionForm = document.getElementById('sectionForm');
    const deleteModal = document.getElementById('deleteModal');
    const viewSectionModal = document.getElementById('viewSectionModal');
    const classSelect = document.getElementById('class_id');
    const teacherSelect = document.getElementById('teacher_id');
    
    // Load teachers when class is selected
    classSelect.addEventListener('change', function() {
        const classId = this.value;
        if (classId) {
            fetch(`/admin/sections/by-class/${classId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update teacher options based on class
                        teacherSelect.innerHTML = '<option value="">Select Teacher (Optional)</option>';
                        // You can add logic here to load teachers for the selected class
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    });
    
    // Add/Edit Section
    document.querySelectorAll('.edit-section').forEach(button => {
        button.addEventListener('click', function() {
            const sectionId = this.dataset.sectionId;
            
            fetch(`/admin/sections/${sectionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const sectionData = data.section;
                        document.getElementById('sectionModalTitle').textContent = 'Edit Section';
                        document.getElementById('sectionId').value = sectionData.id;
                        document.getElementById('name').value = sectionData.name;
                        document.getElementById('class_id').value = sectionData.class_id;
                        document.getElementById('teacher_id').value = sectionData.teacher_id || '';
                        document.getElementById('capacity').value = sectionData.capacity || '';
                        document.getElementById('room_number').value = sectionData.room_number || '';
                        document.getElementById('floor').value = sectionData.floor || '';
                        document.getElementById('description').value = sectionData.description || '';
                        document.getElementById('is_active').checked = sectionData.is_active;
                        
                        sectionModal.show();
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });
    
    // View Section Details
    document.querySelectorAll('.view-section').forEach(button => {
        button.addEventListener('click', function() {
            const sectionId = this.dataset.sectionId;
            
            fetch(`/admin/sections/${sectionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const sectionData = data.section;
                        const detailsHtml = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Basic Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Section Name:</strong></td>
                                            <td>${sectionData.name}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Class:</strong></td>
                                            <td>${sectionData.class.name} (Grade ${sectionData.class.grade_level})</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>${sectionData.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Physical Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Room Number:</strong></td>
                                            <td>${sectionData.room_number || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Floor:</strong></td>
                                            <td>${sectionData.floor ? `Floor ${sectionData.floor}` : 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Capacity:</strong></td>
                                            <td>${sectionData.capacity || 'No limit'}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <h6>Teacher Assignment</h6>
                                    ${sectionData.teacher ? `
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="fas fa-chalkboard-teacher text-success"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">${sectionData.teacher.user.name}</div>
                                                <small class="text-muted">${sectionData.teacher.employee_id}</small>
                                            </div>
                                        </div>
                                    ` : '<p class="text-muted">No teacher assigned</p>'}
                                </div>
                                <div class="col-md-6">
                                    <h6>Student Statistics</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <i class="fas fa-users text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">${sectionData.students_count} Students</div>
                                            <small class="text-muted">Currently enrolled</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ${sectionData.description ? `
                                <div class="mt-3">
                                    <h6>Description</h6>
                                    <p>${sectionData.description}</p>
                                </div>
                            ` : ''}
                            <div class="mt-3">
                                <h6>Occupancy Rate</h6>
                                <div class="progress" style="height: 20px;">
                                    ${sectionData.capacity ? `
                                        <div class="progress-bar ${sectionData.students_count > sectionData.capacity * 0.8 ? 'bg-danger' : sectionData.students_count > sectionData.capacity * 0.6 ? 'bg-warning' : 'bg-success'}" 
                                             style="width: ${Math.min((sectionData.students_count / sectionData.capacity) * 100, 100)}%">
                                            ${Math.round((sectionData.students_count / sectionData.capacity) * 100)}% Full
                                        </div>
                                    ` : `
                                        <div class="progress-bar bg-info" style="width: 100%">
                                            No Capacity Limit
                                        </div>
                                    `}
                                </div>
                            </div>
                        `;
                        
                        document.getElementById('sectionDetails').innerHTML = detailsHtml;
                        viewSectionModal.show();
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });
    
    // Delete Section
    document.querySelectorAll('.delete-section').forEach(button => {
        button.addEventListener('click', function() {
            const sectionId = this.dataset.sectionId;
            const sectionName = this.dataset.sectionName;
            const studentCount = this.dataset.studentCount;
            
            document.getElementById('deleteSectionName').textContent = sectionName;
            
            if (studentCount > 0) {
                document.getElementById('deleteWarning').textContent = 
                    `Warning: This section has ${studentCount} student(s). You cannot delete a section with active students.`;
                document.getElementById('confirmDelete').disabled = true;
                document.getElementById('confirmDelete').classList.add('disabled');
            } else {
                document.getElementById('deleteWarning').textContent = '';
                document.getElementById('confirmDelete').disabled = false;
                document.getElementById('confirmDelete').classList.remove('disabled');
            }
            
            document.getElementById('confirmDelete').onclick = function() {
                fetch(`/admin/sections/${sectionId}`, {
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
                        alert(data.message || 'Error deleting section');
                    }
                })
                .catch(error => console.error('Error:', error));
            };
            
            deleteModal.show();
        });
    });
    
    // Reset form when modal is hidden
    sectionModal.addEventListener('hidden.bs.modal', function() {
        sectionForm.reset();
        document.getElementById('sectionModalTitle').textContent = 'Add New Section';
        document.getElementById('sectionId').value = '';
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    });
    
    // Handle form submission
    sectionForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const sectionId = document.getElementById('sectionId').value;
        const url = sectionId ? `/admin/sections/${sectionId}` : '/admin/sections';
        const method = sectionId ? 'PUT' : 'POST';
        
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
                sectionModal.hide();
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
                    alert(data.message || 'Error saving section');
                }
            }
        })
        .catch(error => console.error('Error:', error));
    });
    
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#sectionsTableBody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
});
</script>
@endpush
