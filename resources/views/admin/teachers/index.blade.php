@extends('layouts.app')

@section('title', 'Teachers')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Teachers</h4>
            <p class="text-muted mb-0">Manage teaching staff and faculty members</p>
        </div>
        <div>
            <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Teacher
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.teachers.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Search by name, code, CNIC...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select" id="gender" name="gender">
                            <option value="">All Genders</option>
                            @foreach($genders as $gender)
                                <option value="{{ $gender }}" {{ request('gender') == $gender ? 'selected' : '' }}>
                                    {{ $gender }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="employment_type" class="form-label">Employment Type</label>
                        <select class="form-select" id="employment_type" name="employment_type">
                            <option value="">All Types</option>
                            @foreach($employmentTypes as $type)
                                <option value="{{ $type }}" {{ request('employment_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                            <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Teachers Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($teachers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Employee Code</th>
                                <th>Name</th>
                                <th>CNIC</th>
                                <th>Qualification</th>
                                <th>Subjects</th>
                                <th>Classes</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teachers as $teacher)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $teacher->employee_code }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                {!! $teacher->profile_avatar !!}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $teacher->full_name }}</div>
                                                <small class="text-muted">{{ $teacher->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small>{{ $teacher->cnic }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $teacher->qualification }}</small>
                                        @if($teacher->specialization)
                                            <br><small class="text-muted">{{ $teacher->specialization }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $teacher->subject_names ?: 'Not Assigned' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $teacher->class_names ?: 'Not Assigned' }}</small>
                                    </td>
                                    <td>
                                        {!! $teacher->status_badge !!}
                                        <br>{!! $teacher->employment_type_badge !!}
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.teachers.show', $teacher) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View Profile">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.teachers.edit', $teacher) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(auth()->user()->role->slug === 'super_admin' || auth()->user()->role->slug === 'principal')
                                                <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" 
                                                      class="d-inline" onsubmit="return confirm('Are you sure you want to delete this teacher?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $teachers->firstItem() }} to {{ $teachers->lastItem() }} 
                        of {{ $teachers->total() }} teachers
                    </div>
                    {{ $teachers->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Teachers Found</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'gender', 'employment_type', 'status']))
                            No teachers match your search criteria. 
                            <a href="{{ route('admin.teachers.index') }}" class="btn btn-link p-0">Clear filters</a>
                        @else
                            No teachers have been added yet. 
                            <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add First Teacher
                            </a>
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto-submit form when filters change
    document.querySelectorAll('#gender, #employment_type, #status').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });

    // Search on Enter key
    document.getElementById('search').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.form.submit();
        }
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endsection
