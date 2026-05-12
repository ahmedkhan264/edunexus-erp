@extends('layouts.app')

@section('title', 'Mark Student Attendance')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Mark Student Attendance</h1>
            <p class="text-muted mb-0">Mark attendance for your assigned classes</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6" id="currentDate"></span>
        </div>
    </div>

    <!-- Attendance Form -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Attendance Details</h5>
        </div>
        <div class="card-body">
            <form id="attendanceForm">
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
                        <button type="button" class="btn btn-outline-primary" id="loadStudentsBtn">
                            <i class="fas fa-users me-2"></i>Load Students
                        </button>
                        <button type="button" class="btn btn-success ms-2" id="markAllPresentBtn" disabled>
                            <i class="fas fa-check-circle me-2"></i>Mark All Present
                        </button>
                        <button type="button" class="btn btn-danger ms-2" id="markAllAbsentBtn" disabled>
                            <i class="fas fa-times-circle me-2"></i>Mark All Absent
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card mt-4" id="studentsCard" style="display: none;">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Students List</h5>
                <div>
                    <span class="badge bg-light text-dark" id="totalStudents">0 Students</span>
                    <span class="badge bg-warning ms-1" id="markedCount">0 Marked</span>
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
                            <th width="120">Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        <!-- Students will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted" id="attendanceSummary"></small>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" id="resetBtn">
                        <i class="fas fa-redo me-2"></i>Reset
                    </button>
                    <button type="button" class="btn btn-primary ms-2" id="saveAttendanceBtn" disabled>
                        <i class="fas fa-save me-2"></i>Save Attendance
                    </button>
                </div>
            </div>
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

.status-btn {
    min-width: 80px;
}

.status-btn.present {
    background-color: #28a745;
    border-color: #28a745;
}

.status-btn.absent {
    background-color: #dc3545;
    border-color: #dc3545;
}

.status-btn.late {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}

.status-btn:not(.active) {
    background-color: #6c757d;
    border-color: #6c757d;
}

.remarks-input {
    min-width: 200px;
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const attendanceForm = document.getElementById('attendanceForm');
    const loadStudentsBtn = document.getElementById('loadStudentsBtn');
    const markAllPresentBtn = document.getElementById('markAllPresentBtn');
    const markAllAbsentBtn = document.getElementById('markAllAbsentBtn');
    const saveAttendanceBtn = document.getElementById('saveAttendanceBtn');
    const resetBtn = document.getElementById('resetBtn');
    const studentsCard = document.getElementById('studentsCard');
    const studentsTableBody = document.getElementById('studentsTableBody');
    const loadingOverlay = document.getElementById('loadingOverlay');
    
    let studentsData = [];
    let attendanceData = {};

    // Set current date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date').value = today;
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

    // Load students button
    loadStudentsBtn.addEventListener('click', loadStudents);
    
    // Mark all present/absent buttons
    markAllPresentBtn.addEventListener('click', () => markAllStatus('present'));
    markAllAbsentBtn.addEventListener('click', () => markAllStatus('absent'));
    
    // Save attendance button
    saveAttendanceBtn.addEventListener('click', saveAttendance);
    
    // Reset button
    resetBtn.addEventListener('click', resetForm);

    function loadSections(classId) {
        fetch(`/teacher/attendance/students/sections/${classId}`)
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
        fetch(`/teacher/attendance/students/subjects`, {
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

    function loadStudents() {
        const classId = document.getElementById('class_id').value;
        const sectionId = document.getElementById('section_id').value;
        const date = document.getElementById('date').value;

        if (!classId || !date) {
            alert('Please select class and date');
            return;
        }

        showLoading();
        
        fetch(`/teacher/attendance/students/get-students?class_id=${classId}&section_id=${sectionId}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    studentsData = data.students;
                    renderStudentsTable();
                    studentsCard.style.display = 'block';
                    markAllPresentBtn.disabled = false;
                    markAllAbsentBtn.disabled = false;
                    updateSummary();
                } else {
                    alert('Error loading students');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error loading students:', error);
                alert('Error loading students');
            });
    }

    function renderStudentsTable() {
        studentsTableBody.innerHTML = '';
        attendanceData = {};

        studentsData.forEach(student => {
            attendanceData[student.id] = {
                status: student.current_status || '',
                remarks: student.current_remarks || ''
            };

            const row = document.createElement('tr');
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
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm status-btn present ${attendanceData[student.id].status === 'present' ? 'active' : ''}" 
                                data-student-id="${student.id}" data-status="present">
                            <i class="fas fa-check"></i> Present
                        </button>
                        <button type="button" class="btn btn-sm status-btn absent ${attendanceData[student.id].status === 'absent' ? 'active' : ''}" 
                                data-student-id="${student.id}" data-status="absent">
                            <i class="fas fa-times"></i> Absent
                        </button>
                        <button type="button" class="btn btn-sm status-btn late ${attendanceData[student.id].status === 'late' ? 'active' : ''}" 
                                data-student-id="${student.id}" data-status="late">
                            <i class="fas fa-clock"></i> Late
                        </button>
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm remarks-input" 
                           placeholder="Optional remarks" 
                           data-student-id="${student.id}"
                           value="${attendanceData[student.id].remarks || ''}">
                </td>
            `;
            studentsTableBody.appendChild(row);
        });

        // Add event listeners to status buttons
        document.querySelectorAll('.status-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const studentId = this.dataset.studentId;
                const status = this.dataset.status;
                
                // Remove active class from all buttons for this student
                document.querySelectorAll(`.status-btn[data-student-id="${studentId}"]`).forEach(b => {
                    b.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update attendance data
                attendanceData[studentId].status = status;
                
                updateSummary();
            });
        });

        // Add event listeners to remarks inputs
        document.querySelectorAll('.remarks-input').forEach(input => {
            input.addEventListener('input', function() {
                const studentId = this.dataset.studentId;
                attendanceData[studentId].remarks = this.value;
            });
        });

        document.getElementById('totalStudents').textContent = `${studentsData.length} Students`;
    }

    function markAllStatus(status) {
        document.querySelectorAll('.status-btn').forEach(btn => {
            if (btn.dataset.status === status) {
                btn.click();
            }
        });
    }

    function updateSummary() {
        const markedCount = Object.values(attendanceData).filter(a => a.status).length;
        const presentCount = Object.values(attendanceData).filter(a => a.status === 'present').length;
        const absentCount = Object.values(attendanceData).filter(a => a.status === 'absent').length;
        const lateCount = Object.values(attendanceData).filter(a => a.status === 'late').length;
        
        document.getElementById('markedCount').textContent = `${markedCount} Marked`;
        document.getElementById('attendanceSummary').innerHTML = `
            <span class="badge bg-success me-1">Present: ${presentCount}</span>
            <span class="badge bg-danger me-1">Absent: ${absentCount}</span>
            <span class="badge bg-warning">Late: ${lateCount}</span>
        `;
        
        saveAttendanceBtn.disabled = markedCount === 0;
    }

    function saveAttendance() {
        const formData = new FormData(attendanceForm);
        const attendanceArray = [];

        Object.keys(attendanceData).forEach(studentId => {
            if (attendanceData[studentId].status) {
                attendanceArray.push({
                    student_id: studentId,
                    status: attendanceData[studentId].status,
                    remarks: attendanceData[studentId].remarks
                });
            }
        });

        if (attendanceArray.length === 0) {
            alert('Please mark attendance for at least one student');
            return;
        }

        formData.append('attendance', JSON.stringify(attendanceArray));

        showLoading();

        fetch('/teacher/attendance/students/mark', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    if (data.absent_count > 0) {
                        showToast('SMS Sent', `${data.absent_count} absence SMS notifications queued`, 'info');
                    }
                    setTimeout(() => {
                        loadStudents(); // Reload to show updated status
                    }, 2000);
                } else {
                    showToast('Error', data.message || 'Failed to save attendance', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error saving attendance:', error);
                showToast('Error', 'Failed to save attendance', 'error');
            });
    }

    function resetForm() {
        attendanceForm.reset();
        document.getElementById('date').value = today;
        studentsCard.style.display = 'none';
        markAllPresentBtn.disabled = true;
        markAllAbsentBtn.disabled = true;
        saveAttendanceBtn.disabled = true;
        studentsData = [];
        attendanceData = {};
    }

    function showLoading() {
        loadingOverlay.style.display = 'flex';
    }

    function hideLoading() {
        loadingOverlay.style.display = 'none';
    }

    function showToast(title, message, type) {
        // Simple toast notification (you can replace with a proper toast library)
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
