@extends('layouts.app')

@section('title', 'Student List')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Student Management</h4>
            <p class="text-muted mb-0">Manage and view all enrolled students</p>
        </div>
        <div>
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Student
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-print me-2"></i>Print
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Students</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_students']) }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Enrolled</h6>
                            <h3 class="mb-0">{{ number_format($stats['enrolled_students']) }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Graduated</h6>
                            <h3 class="mb-0">{{ number_format($stats['graduated_students']) }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Active</h6>
                            <h3 class="mb-0">{{ number_format($stats['active_students']) }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filters
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.students.index') }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-4">
                    <label for="search" class="form-label">Search Students</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ $search }}" placeholder="Search by name, ID, email, phone...">
                    </div>
                </div>

                <!-- Class Filter -->
                <div class="col-md-2">
                    <label for="class_id" class="form-label">Class</label>
                    <select class="form-select" id="class_id" name="class_id">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ $status == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Gender Filter -->
                <div class="col-md-2">
                    <label for="gender" class="form-label">Gender</label>
                    <select class="form-select" id="gender" name="gender">
                        <option value="">All Genders</option>
                        @foreach($genderOptions as $value => $label)
                            <option value="{{ $value }}" {{ $gender == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Per Page -->
                <div class="col-md-2">
                    <label for="per_page" class="form-label">Per Page</label>
                    <select class="form-select" id="per_page" name="per_page">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Actions -->
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary ms-2">
                        <i class="fas fa-times me-2"></i>Clear
                    </a>
                    <a href="{{ route('admin.students.print') }}?{{ request()->getQueryString() }}" 
                       class="btn btn-outline-primary ms-2" target="_blank">
                        <i class="fas fa-print me-2"></i>Print Results
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Student List
                @if($students->total() > 0)
                    <span class="badge bg-primary ms-2">{{ $students->total() }}</span>
                @endif
            </h6>
        </div>
        <div class="card-body">
            @if($students->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Gender</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Admission Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $student->student_id }}</div>
                                        <small class="text-muted">{{ $student->admission_number }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $student->full_name }}</div>
                                                <small class="text-muted">{{ $student->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($student->class)
                                            <div class="fw-bold">{{ $student->class->name }}</div>
                                            <small class="text-muted">Grade {{ $student->class->grade_level }}</small>
                                        @else
                                            <span class="text-muted">Not Assigned</span>
                                        @endif
                                    </td>
                                    <td>{!! $student->gender_badge !!}</td>
                                    <td>
                                        <div class="small">
                                            <div>{{ $student->formatted_phone }}</div>
                                            <div class="text-muted">{{ $student->email }}</div>
                                        </div>
                                    </td>
                                    <td>{!! $student->status_badge !!}</td>
                                    <td>{{ $student->formatted_admission_date }}</td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.students.show', $student) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.students.edit', $student) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete('{{ route('admin.students.destroy', $student) }}')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} 
                        of {{ $students->total() }} entries
                    </div>
                    <div>
                        {{ $students->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No students found</h5>
                    <p class="text-muted">Try adjusting your search criteria or add new students.</p>
                    <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add First Student
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this student? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Delete confirmation
    function confirmDelete(url) {
        document.getElementById('deleteForm').action = url;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // Auto-submit filters when changing select dropdowns
    document.querySelectorAll('select').forEach(select => {
        if (select.id !== 'per_page') {
            select.addEventListener('change', function() {
                this.closest('form').submit();
            });
        }
    });

    // Per page change handler
    document.getElementById('per_page').addEventListener('change', function() {
        this.closest('form').submit();
    });

    // Search with debounce
    let searchTimeout;
    document.getElementById('search').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            this.closest('form').submit();
        }, 500);
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endsection
