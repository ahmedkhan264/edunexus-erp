@extends('layouts.app')

@section('title', 'Request Attendance Correction')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Request Attendance Correction</h1>
            <p class="text-muted mb-0">Submit correction requests for attendance records</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6" id="currentDate"></span>
        </div>
    </div>

    <!-- Correction Request Form -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Attendance Details</h5>
        </div>
        <div class="card-body">
            <form id="correctionForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                        <select class="form-select" id="class_id" name="class_id" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }} (Grade {{ $class->grade_level }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="section_id" class="form-label">Section</label>
                        <select class="form-select" id="section_id" name="section_id">
                            <option value="">All Sections</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="subject_id" class="form-label">Subject</label>
                        <select class="form-select" id="subject_id" name="subject_id">
                            <option value="">All Subjects</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-outline-primary" id="loadAttendanceBtn">
                            <i class="fas fa-search me-2"></i>Load Attendance Records
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Records Table -->
    <div class="card mt-4" id="attendanceRecordsCard" style="display: none;">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Attendance Records</h5>
                <div>
                    <span class="badge bg-light text-dark" id="totalRecords">0 Records</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="80">Roll No</th>
                            <th>Student Name</th>
                            <th width="120">Current Status</th>
                            <th width="120">Requested Status</th>
                            <th width="300">Reason</th>
                            <th width="100">Action</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody">
                        <!-- Attendance records will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted" id="correctionSummary"></small>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" id="resetBtn">
                        <i class="fas fa-redo me-2"></i>Reset
                    </button>
                    <button type="button" class="btn btn-primary ms-2" id="submitRequestsBtn" disabled>
                        <i class="fas fa-paper-plane me-2"></i>Submit Requests
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- My Correction Requests -->
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">My Recent Correction Requests</h5>
        </div>
        <div class="card-body">
            @if($myRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Old Status</th>
                                <th>New Status</th>
                                <th>Reason</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myRequests as $request)
                                <tr>
                                    <td>{{ $request->attendance->date ?? 'N/A' }}</td>
                                    <td>{{ $request->student->user->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $request->current_status === 'present' ? 'success' : ($request->current_status === 'absent' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($request->current_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $request->requested_status === 'present' ? 'success' : ($request->requested_status === 'absent' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($request->requested_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($request->reason, 50) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $request->getStatusBadgeColor() }}">
                                            {{ $request->getStatusDisplay() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No correction requests submitted yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="mt-2">Processing...</div>
</div>

<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    color: white;
}

.status-badge.present {
    background-color: #28a745;
}

.status-badge.absent {
    background-color: #dc3545;
}

.status-badge.late {
    background-color: #ffc107;
    color: #000;
}

.reason-textarea {
    min-width: 250px;
    min-height: 60px;
}

.correction-row {
    transition: background-color 0.2s;
}

.correction-row:hover {
    background-color: #f8f9fa;
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const correctionForm = document.getElementById('correctionForm');
    const loadAttendanceBtn = document.getElementById('loadAttendanceBtn');
    const submitRequestsBtn = document.getElementById('submitRequestsBtn');
    const resetBtn = document.getElementById('resetBtn');
    const attendanceRecordsCard = document.getElementById('attendanceRecordsCard');
    const attendanceTableBody = document.getElementById('attendanceTableBody');
    const loadingOverlay = document.getElementById('loadingOverlay');
    
    let attendanceData = [];
    let correctionRequests = {};

    // Set current date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date').value = today;
    document.getElementById('date').max = today; // Prevent future dates
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });

    // Load sections when class changes
    document.getElementById('class_id').addEventListener('change', function() {
        const classId = this.value;
        if (classId) {
            loadSections(classId);
            loadSubjects(classId);
        } else {
            document.getElementById('section_id').innerHTML = '<option value="">All Sections</option>';
            document.getElementById('subject_id').innerHTML = '<option value="">All Subjects</option>';
        }
    });

    // Load attendance records button
    loadAttendanceBtn.addEventListener('click', loadAttendanceRecords);
    
    // Submit correction requests button
    submitRequestsBtn.addEventListener('click', submitCorrectionRequests);
    
    // Reset button
    resetBtn.addEventListener('click', resetForm);

    function loadSections(classId) {
        fetch(`/teacher/attendance/corrections/sections/${classId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const sectionSelect = document.getElementById('section_id');
                    sectionSelect.innerHTML = '<option value="">All Sections</option>';
                    data.sections.forEach(section => {
                        sectionSelect.innerHTML += `<option value="${section.id}">${section.name}</option>`;
                    });
                }
            })
            .catch(error => console.error('Error loading sections:', error));
    }

    function loadSubjects(classId) {
        fetch(`/teacher/attendance/corrections/subjects`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ class_id: classId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const subjectSelect = document.getElementById('subject_id');
                    subjectSelect.innerHTML = '<option value="">All Subjects</option>';
                    data.subjects.forEach(subject => {
                        subjectSelect.innerHTML += `<option value="${subject.id}">${subject.name}</option>`;
                    });
                }
            })
            .catch(error => console.error('Error loading subjects:', error));
    }

    function loadAttendanceRecords() {
        const classId = document.getElementById('class_id').value;
        const date = document.getElementById('date').value;

        if (!classId || !date) {
            alert('Please select class and date');
            return;
        }

        showLoading();
        
        fetch(`/teacher/attendance/corrections/get-records?class_id=${classId}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    attendanceData = data.students;
                    renderAttendanceTable();
                    attendanceRecordsCard.style.display = 'block';
                    updateSummary();
                } else {
                    alert(data.message || 'Error loading attendance records');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error loading attendance records:', error);
                alert('Error loading attendance records');
            });
    }

    function renderAttendanceTable() {
        attendanceTableBody.innerHTML = '';
        correctionRequests = {};

        attendanceData.forEach(student => {
            const row = document.createElement('tr');
            row.className = 'correction-row';
            row.innerHTML = `
                <td>${student.roll_number || '-'}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                            <i class="fas fa-user text-primary"></i>
                        </div>
                        <div>
                            <div class="fw-medium">${student.name}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge status-badge ${student.current_status}">
                        ${student.current_status.charAt(0).toUpperCase() + student.current_status.slice(1)}
                    </span>
                </td>
                <td>
                    <select class="form-select form-select-sm" data-student-id="${student.id}" data-attendance-id="${student.attendance_id}">
                        <option value="">Select Status</option>
                        <option value="present" ${student.current_status === 'present' ? 'disabled' : ''}>Present</option>
                        <option value="absent" ${student.current_status === 'absent' ? 'disabled' : ''}>Absent</option>
                        <option value="late" ${student.current_status === 'late' ? 'disabled' : ''}>Late</option>
                    </select>
                </td>
                <td>
                    <textarea class="form-control form-control-sm reason-textarea" 
                              placeholder="Reason for correction (min 10 characters)" 
                              data-student-id="${student.id}"
                              data-attendance-id="${student.attendance_id}"></textarea>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary request-btn" 
                            data-student-id="${student.id}"
                            data-attendance-id="${student.attendance_id}"
                            disabled>
                        <i class="fas fa-plus"></i> Request
                    </button>
                </td>
            `;
            attendanceTableBody.appendChild(row);
        });

        // Add event listeners
        document.querySelectorAll('select[data-student-id]').forEach(select => {
            select.addEventListener('change', function() {
                const studentId = this.dataset.studentId;
                updateRequestButton(studentId);
            });
        });

        document.querySelectorAll('textarea[data-student-id]').forEach(textarea => {
            textarea.addEventListener('input', function() {
                const studentId = this.dataset.studentId;
                updateRequestButton(studentId);
            });
        });

        document.querySelectorAll('.request-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const studentId = this.dataset.studentId;
                addCorrectionRequest(studentId);
            });
        });

        document.getElementById('totalRecords').textContent = `${attendanceData.length} Records`;
    }

    function updateRequestButton(studentId) {
        const select = document.querySelector(`select[data-student-id="${studentId}"]`);
        const textarea = document.querySelector(`textarea[data-student-id="${studentId}"]`);
        const btn = document.querySelector(`.request-btn[data-student-id="${studentId}"]`);
        
        const isValid = select.value && textarea.value.trim().length >= 10;
        btn.disabled = !isValid;
        
        if (isValid) {
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-primary');
        } else {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-primary');
        }
    }

    function addCorrectionRequest(studentId) {
        const select = document.querySelector(`select[data-student-id="${studentId}"]`);
        const textarea = document.querySelector(`textarea[data-student-id="${studentId}"]`);
        const btn = document.querySelector(`.request-btn[data-student-id="${studentId}"]`);
        
        correctionRequests[studentId] = {
            attendance_id: select.dataset.attendanceId,
            requested_status: select.value,
            reason: textarea.value.trim()
        };
        
        // Disable the row
        select.disabled = true;
        textarea.disabled = true;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-check"></i> Added';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-success');
        
        updateSummary();
    }

    function updateSummary() {
        const requestCount = Object.keys(correctionRequests).length;
        document.getElementById('correctionSummary').innerHTML = `
            <span class="badge bg-info me-1">Requests: ${requestCount}</span>
        `;
        
        submitRequestsBtn.disabled = requestCount === 0;
    }

    function submitCorrectionRequests() {
        if (Object.keys(correctionRequests).length === 0) {
            alert('Please add at least one correction request');
            return;
        }

        const requests = Object.values(correctionRequests);
        let submitted = 0;
        let errors = 0;

        showLoading();

        // Submit each request individually
        Promise.all(requests.map(requestData => {
            return fetch('/teacher/attendance/corrections', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    submitted++;
                } else {
                    errors++;
                    console.error('Request error:', data.message);
                }
            })
            .catch(error => {
                errors++;
                console.error('Network error:', error);
            });
        }))
        .then(() => {
            hideLoading();
            
            if (submitted > 0) {
                showToast('Success', `${submitted} correction request(s) submitted successfully!`, 'success');
            }
            
            if (errors > 0) {
                showToast('Error', `${errors} request(s) failed to submit.`, 'error');
            }
            
            // Reload the page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        });
    }

    function resetForm() {
        correctionForm.reset();
        document.getElementById('date').value = today;
        attendanceRecordsCard.style.display = 'none';
        submitRequestsBtn.disabled = true;
        attendanceData = [];
        correctionRequests = {};
    }

    function showLoading() {
        loadingOverlay.style.display = 'flex';
    }

    function hideLoading() {
        loadingOverlay.style.display = 'none';
    }

    function showToast(title, message, type) {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <strong>${title}:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
});
</script>
@endpush
