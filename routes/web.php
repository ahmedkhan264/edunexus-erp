<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Admin routes
    Route::middleware('role:super_admin,principal,admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Student Management routes
        Route::get('/students', [App\Http\Controllers\StudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [App\Http\Controllers\StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [App\Http\Controllers\StudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}', [App\Http\Controllers\StudentController::class, 'show'])->name('students.show');
        Route::get('/students/{student}/edit', [App\Http\Controllers\StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [App\Http\Controllers\StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [App\Http\Controllers\StudentController::class, 'destroy'])->name('students.destroy');
        Route::get('/students/print', [App\Http\Controllers\StudentController::class, 'print'])->name('students.print');
        
        // Teacher Management routes
        Route::get('/teachers', [App\Http\Controllers\TeacherController::class, 'index'])->name('teachers.index');
        Route::get('/teachers/create', [App\Http\Controllers\TeacherController::class, 'create'])->name('teachers.create');
        Route::post('/teachers', [App\Http\Controllers\TeacherController::class, 'store'])->name('teachers.store');
        Route::get('/teachers/{teacher}', [App\Http\Controllers\TeacherController::class, 'show'])->name('teachers.show');
        Route::get('/teachers/{teacher}/edit', [App\Http\Controllers\TeacherController::class, 'edit'])->name('teachers.edit');
        Route::put('/teachers/{teacher}', [App\Http\Controllers\TeacherController::class, 'update'])->name('teachers.update');
        Route::delete('/teachers/{teacher}', [App\Http\Controllers\TeacherController::class, 'destroy'])->name('teachers.destroy');
        
        // Class Management routes
        Route::get('/classes', [App\Http\Controllers\ClassRoomController::class, 'index'])->name('classes.index');
        Route::post('/classes', [App\Http\Controllers\ClassRoomController::class, 'store'])->name('classes.store');
        Route::get('/classes/{class}', [App\Http\Controllers\ClassRoomController::class, 'show'])->name('classes.show');
        Route::put('/classes/{class}', [App\Http\Controllers\ClassRoomController::class, 'update'])->name('classes.update');
        Route::delete('/classes/{class}', [App\Http\Controllers\ClassRoomController::class, 'destroy'])->name('classes.destroy');
        
        // Section Management routes
        Route::get('/sections', [App\Http\Controllers\SectionController::class, 'index'])->name('sections.index');
        Route::post('/sections', [App\Http\Controllers\SectionController::class, 'store'])->name('sections.store');
        Route::get('/sections/{section}', [App\Http\Controllers\SectionController::class, 'show'])->name('sections.show');
        Route::put('/sections/{section}', [App\Http\Controllers\SectionController::class, 'update'])->name('sections.update');
        Route::delete('/sections/{section}', [App\Http\Controllers\SectionController::class, 'destroy'])->name('sections.destroy');
        Route::get('/sections/by-class/{classId}', [App\Http\Controllers\SectionController::class, 'getByClass'])->name('sections.by-class');
        
        // Subject Management routes
        Route::get('/subjects', [App\Http\Controllers\SubjectController::class, 'index'])->name('subjects.index');
        Route::post('/subjects', [App\Http\Controllers\SubjectController::class, 'store'])->name('subjects.store');
        Route::get('/subjects/{subject}', [App\Http\Controllers\SubjectController::class, 'show'])->name('subjects.show');
        Route::put('/subjects/{subject}', [App\Http\Controllers\SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [App\Http\Controllers\SubjectController::class, 'destroy'])->name('subjects.destroy');
        Route::get('/subjects/by-class/{classId}', [App\Http\Controllers\SubjectController::class, 'getByClass'])->name('subjects.by-class');
        Route::post('/subjects/assign', [App\Http\Controllers\SubjectController::class, 'assignToClass'])->name('subjects.assign');
        
        // Fee Management routes
        Route::prefix('fees')->name('fees.')->group(function () {
            Route::get('/challans', [App\Http\Controllers\FeeChallanController::class, 'index'])->name('challans.index');
            Route::get('/challans/create', [App\Http\Controllers\FeeChallanController::class, 'create'])->name('challans.create');
            Route::post('/challans', [App\Http\Controllers\FeeChallanController::class, 'store'])->name('challans.store');
            Route::get('/challans/{challan}', [App\Http\Controllers\FeeChallanController::class, 'show'])->name('challans.show');
            Route::get('/challans/{challan}/edit', [App\Http\Controllers\FeeChallanController::class, 'edit'])->name('challans.edit');
            Route::put('/challans/{challan}', [App\Http\Controllers\FeeChallanController::class, 'update'])->name('challans.update');
            Route::delete('/challans/{challan}', [App\Http\Controllers\FeeChallanController::class, 'destroy'])->name('challans.destroy');
            
            Route::get('/payments', [App\Http\Controllers\FeePaymentController::class, 'index'])->name('payments.index');
            Route::post('/payments', [App\Http\Controllers\FeePaymentController::class, 'store'])->name('payments.store');
            Route::get('/payments/{payment}', [App\Http\Controllers\FeePaymentController::class, 'show'])->name('payments.show');
        });
    });
    
    // Reports routes (Admin, Principal, Super Admin)
    Route::middleware('role:admin,principal,super_admin')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/student-attendance', [App\Http\Controllers\StudentAttendanceReportController::class, 'index'])->name('student-attendance');
        Route::get('/student-attendance/export/pdf', [App\Http\Controllers\StudentAttendanceReportController::class, 'exportPdf'])->name('student-attendance.pdf');
        Route::get('/student-attendance/export/excel', [App\Http\Controllers\StudentAttendanceReportController::class, 'exportExcel'])->name('student-attendance.excel');
        Route::get('/student-attendance/chart-data', [App\Http\Controllers\StudentAttendanceReportController::class, 'getChartData'])->name('student-attendance.chart-data');
    });
    
    // Teacher routes
    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', function () {
            return view('teacher.dashboard');
        })->name('dashboard');
        
        // Teacher Attendance routes
        Route::get('/attendance/students', [App\Http\Controllers\StudentAttendanceController::class, 'classForm'])->name('attendance.students');
        Route::post('/attendance/students/mark', [App\Http\Controllers\StudentAttendanceController::class, 'markClass'])->name('attendance.mark');
        Route::get('/attendance/students/get-students', [App\Http\Controllers\StudentAttendanceController::class, 'getStudents'])->name('attendance.get-students');
        Route::get('/attendance/students/sections/{classId}', [App\Http\Controllers\StudentAttendanceController::class, 'getSections'])->name('attendance.get-sections');
        Route::post('/attendance/students/subjects', [App\Http\Controllers\StudentAttendanceController::class, 'getSubjects'])->name('attendance.get-subjects');
        
        // Attendance Correction routes
        Route::get('/attendance/corrections', [App\Http\Controllers\AttendanceCorrectionController::class, 'index'])->name('attendance.corrections');
        Route::post('/attendance/corrections', [App\Http\Controllers\AttendanceCorrectionController::class, 'store'])->name('attendance.corrections.store');
        Route::get('/attendance/corrections/get-records', [App\Http\Controllers\AttendanceCorrectionController::class, 'getAttendanceRecords'])->name('attendance.corrections.get-records');
        Route::get('/attendance/corrections/sections/{classId}', [App\Http\Controllers\AttendanceCorrectionController::class, 'getSections'])->name('attendance.corrections.get-sections');
        Route::post('/attendance/corrections/subjects', [App\Http\Controllers\AttendanceCorrectionController::class, 'getSubjects'])->name('attendance.corrections.get-subjects');
        
        // Teacher Check-in/Check-out routes
        Route::get('/attendance/checkin', [App\Http\Controllers\TeacherAttendanceController::class, 'checkinPage'])->name('attendance.checkin');
        Route::post('/attendance/checkin', [App\Http\Controllers\TeacherAttendanceController::class, 'checkIn'])->name('attendance.checkin.process');
        Route::post('/attendance/checkout', [App\Http\Controllers\TeacherAttendanceController::class, 'checkOut'])->name('attendance.checkout.process');
        Route::get('/attendance/checkin/status', [App\Http\Controllers\TeacherAttendanceController::class, 'getCurrentStatus'])->name('attendance.checkin.status');
        Route::get('/attendance/checkin/timeline', [App\Http\Controllers\TeacherAttendanceController::class, 'getTimeline'])->name('attendance.checkin.timeline');
        
        // LMS Lesson routes
        Route::prefix('lms')->name('lms.')->group(function () {
            Route::get('/dashboard', function () {
                return view('teacher.lms.dashboard');
            })->middleware(['auth', 'role:teacher'])->name('dashboard');
            
            Route::get('/lessons/create', [App\Http\Controllers\LessonController::class, 'create'])->name('lessons.create');
            Route::post('/lessons', [App\Http\Controllers\LessonController::class, 'store'])->name('lessons.store');
        });
        
        // API routes for LMS
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/classes/{classId}/sections', [App\Http\Controllers\LessonController::class, 'getSections'])->name('classes.sections');
            Route::get('/classes/{classId}/subjects', [App\Http\Controllers\LessonController::class, 'getSubjects'])->name('classes.subjects');
        });
        
        // Live Class routes
        Route::prefix('live-classes')->name('live-classes.')->group(function () {
            Route::get('/create', [App\Http\Controllers\LiveClassController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\LiveClassController::class, 'store'])->name('store');
            Route::get('/sections/{classId}', [App\Http\Controllers\LiveClassController::class, 'getSections'])->name('sections');
            Route::get('/subjects/{classId}', [App\Http\Controllers\LiveClassController::class, 'getSubjects'])->name('subjects');
            Route::post('/check-clash', [App\Http\Controllers\LiveClassController::class, 'checkClash'])->name('check-clash');
        });
        
        // Assignment routes
        Route::prefix('assignments')->name('assignments.')->group(function () {
            Route::get('/', [App\Http\Controllers\AssignmentController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\AssignmentController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\AssignmentController::class, 'store'])->name('store');
            Route::get('/{assignment}/edit', [App\Http\Controllers\AssignmentController::class, 'edit'])->name('edit');
            Route::put('/{assignment}', [App\Http\Controllers\AssignmentController::class, 'update'])->name('update');
            Route::delete('/{assignment}', [App\Http\Controllers\AssignmentController::class, 'destroy'])->name('destroy');
            Route::get('/sections/{classId}', [App\Http\Controllers\AssignmentController::class, 'getSections'])->name('sections');
            Route::get('/subjects/{classId}', [App\Http\Controllers\AssignmentController::class, 'getSubjects'])->name('subjects');
            Route::get('/files/{fileId}/download', [App\Http\Controllers\AssignmentController::class, 'downloadFile'])->name('files.download');
            Route::get('/files/{fileId}/preview', [App\Http\Controllers\AssignmentController::class, 'previewFile'])->name('files.preview');
            
            // Assignment Grading routes
            Route::prefix('grading')->name('grading.')->group(function () {
                Route::get('/', [App\Http\Controllers\AssignmentGradingController::class, 'grade'])->name('index');
                Route::get('/{assignment}', [App\Http\Controllers\AssignmentGradingController::class, 'grade'])->name('grade');
                Route::get('/{assignment}/submission/{submission}', [App\Http\Controllers\AssignmentGradingController::class, 'showSubmission'])->name('submission');
                Route::post('/{assignment}/submission/{submission}', [App\Http\Controllers\AssignmentGradingController::class, 'gradeSubmission'])->name('submit');
                Route::post('/{assignment}/bulk-grade', [App\Http\Controllers\AssignmentGradingController::class, 'bulkGrade'])->name('bulk');
                Route::get('/submissions/files/{fileId}/download', [App\Http\Controllers\AssignmentGradingController::class, 'downloadFile'])->name('files.download');
                Route::get('/submissions/files/{fileId}/preview', [App\Http\Controllers\AssignmentGradingController::class, 'previewFile'])->name('files.preview');
                Route::get('/{assignment}/export', [App\Http\Controllers\AssignmentGradingController::class, 'exportGrades'])->name('export');
            });
            
            // Assignment Results routes
            Route::prefix('results')->name('results.')->group(function () {
                Route::get('/', [App\Http\Controllers\AssignmentResultsController::class, 'teacherResults'])->name('index');
                Route::get('/{assignment}', [App\Http\Controllers\AssignmentResultsController::class, 'showAssignmentResults'])->name('show');
                Route::post('/export', [App\Http\Controllers\AssignmentResultsController::class, 'exportResults'])->name('export');
                Route::get('/data', [App\Http\Controllers\AssignmentResultsController::class, 'getResultsData'])->name('data');
                Route::get('/download/{filename}', [App\Http\Controllers\AssignmentResultsController::class, 'downloadExport'])->name('download');
            });
            
            // Exam routes
            Route::prefix('exams')->name('exams.')->group(function () {
                Route::get('/', [App\Http\Controllers\ExamController::class, 'index'])->name('index');
                Route::get('/create', [App\Http\Controllers\ExamController::class, 'create'])->name('create');
                Route::post('/', [App\Http\Controllers\ExamController::class, 'store'])->name('store');
                Route::get('/{exam}', [App\Http\Controllers\ExamController::class, 'show'])->name('show');
                Route::get('/{exam}/edit', [App\Http\Controllers\ExamController::class, 'edit'])->name('edit');
                Route::put('/{exam}', [App\Http\Controllers\ExamController::class, 'update'])->name('update');
                Route::delete('/{exam}', [App\Http\Controllers\ExamController::class, 'destroy'])->name('destroy');
                Route::post('/{exam}/start', [App\Http\Controllers\ExamController::class, 'startExam'])->name('start');
                Route::post('/{exam}/end', [App\Http\Controllers\ExamController::class, 'endExam'])->name('end');
                Route::post('/{exam}/cancel', [App\Http\Controllers\ExamController::class, 'cancelExam'])->name('cancel');
                Route::get('/{exam}/results', [App\Http\Controllers\ExamController::class, 'results'])->name('results');
                Route::get('/{exam}/export', [App\Http\Controllers\ExamController::class, 'exportResults'])->name('export');
                Route::get('/sections', [App\Http\Controllers\ExamController::class, 'getSections'])->name('sections');
                Route::get('/subjects', [App\Http\Controllers\ExamController::class, 'getSubjects'])->name('subjects');
            });
        });
    });
    
    // Student routes
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', function () {
            return view('student.dashboard');
        })->name('dashboard');
        
        // Video Lectures
        Route::get('/videos', [App\Http\Controllers\LectureController::class, 'index'])->name('videos.index');
        
        // Live Classes
        Route::get('/live-classes', [App\Http\Controllers\LiveClassController::class, 'studentIndex'])->name('live-classes.index');
        
        // Assignments
        Route::prefix('assignments')->name('assignments.')->group(function () {
            Route::get('/', [App\Http\Controllers\AssignmentSubmissionController::class, 'index'])->name('index');
            Route::get('/{assignment}', [App\Http\Controllers\AssignmentSubmissionController::class, 'show'])->name('show');
            Route::get('/{assignment}/submit', [App\Http\Controllers\AssignmentSubmissionController::class, 'create'])->name('submit');
            Route::post('/{assignment}/submit', [App\Http\Controllers\AssignmentSubmissionController::class, 'store'])->name('store');
            Route::get('/submissions/files/{fileId}/download', [App\Http\Controllers\AssignmentSubmissionController::class, 'downloadFile'])->name('submissions.files.download');
            Route::get('/submissions/files/{fileId}/preview', [App\Http\Controllers\AssignmentSubmissionController::class, 'previewFile'])->name('submissions.files.preview');
            
            // Assignment Results routes
            Route::prefix('results')->name('results.')->group(function () {
                Route::get('/', [App\Http\Controllers\AssignmentResultsController::class, 'studentResults'])->name('index');
                Route::get('/{assignment}', [App\Http\Controllers\AssignmentResultsController::class, 'showAssignmentResults'])->name('show');
                Route::post('/export', [App\Http\Controllers\AssignmentResultsController::class, 'exportResults'])->name('export');
                Route::get('/data', [App\Http\Controllers\AssignmentResultsController::class, 'getResultsData'])->name('data');
                Route::get('/download/{filename}', [App\Http\Controllers\AssignmentResultsController::class, 'downloadExport'])->name('download');
            });
            
            // Exam routes
            Route::prefix('exams')->name('exams.')->group(function () {
                Route::get('/', [App\Http\Controllers\ExamController::class, 'studentIndex'])->name('index');
                Route::get('/{exam}', [App\Http\Controllers\ExamController::class, 'studentShow'])->name('show');
                Route::get('/{exam}/take', [App\Http\Controllers\ExamController::class, 'takeExam'])->name('take');
                Route::post('/{exam}/submit', [App\Http\Controllers\ExamController::class, 'submitExam'])->name('submit');
                Route::get('/{exam}/result', [App\Http\Controllers\ExamController::class, 'viewResult'])->name('result');
                Route::post('/export', [App\Http\Controllers\ExamController::class, 'exportStudentResults'])->name('export');
            });
            
            // Exam Results routes
            Route::prefix('exam-results')->name('exam-results.')->group(function () {
                Route::get('/', [App\Http\Controllers\StudentResultController::class, 'index'])->name('index');
                Route::get('/{exam}', [App\Http\Controllers\StudentResultController::class, 'show'])->name('show');
                Route::post('/export', [App\Http\Controllers\StudentResultController::class, 'export'])->name('export');
            });
        });
    
    // Parent routes
    Route::middleware('role:parent')->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\ParentDashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/child-data', [App\Http\Controllers\ParentDashboardController::class, 'childData'])->name('dashboard.child-data');
        
        // Attendance routes
        Route::get('/children/{student}/attendance', [App\Http\Controllers\ParentAttendanceController::class, 'show'])->name('attendance');
        
        // Fee routes
        Route::get('/children/{student}/fees', [App\Http\Controllers\ParentFeeController::class, 'show'])->name('fees');
        Route::get('/fees/challan/{challan}/download', [App\Http\Controllers\ParentFeeController::class, 'downloadChallan'])->name('fees.download-challan');
        Route::get('/children/{student}/fees/ledger/download', [App\Http\Controllers\ParentFeeController::class, 'downloadLedger'])->name('fees.download-ledger');
        
        // Results routes
        Route::get('/children/{student}/results', [App\Http\Controllers\ParentResultController::class, 'show'])->name('results');
        Route::get('/children/{student}/results/export', [App\Http\Controllers\ParentResultController::class, 'export'])->name('results.export');
    });
    
    // Shared routes (accessible by multiple roles)
    Route::middleware('auth')->group(function () {
        // Video Player (shared by students and teachers)
        Route::get('/videos/{lecture}', [App\Http\Controllers\LectureController::class, 'show'])->name('videos.show');
        Route::post('/videos/{lecture}/viewed', [App\Http\Controllers\LectureController::class, 'markViewed'])->name('videos.viewed');
        Route::get('/videos/{lecture}/stats', [App\Http\Controllers\LectureController::class, 'getStats'])->name('videos.stats');
        Route::get('/videos/{lecture}/url', [App\Http\Controllers\LectureController::class, 'getVideoUrl'])->name('videos.url');
    });
    
    // Accountant routes
    Route::middleware('role:accountant')->prefix('accountant')->name('accountant.')->group(function () {
        Route::get('/dashboard', function () {
            return view('accountant.dashboard');
        })->name('dashboard');
    });
    
    // HR Manager routes
    Route::middleware('role:hr_manager')->prefix('hr')->name('hr.')->group(function () {
        Route::get('/dashboard', function () {
            return view('hr.dashboard');
        })->name('dashboard');
        
        // Teacher Attendance routes
        Route::get('/teacher-attendance', [App\Http\Controllers\TeacherAttendanceController::class, 'hrIndex'])->name('teacher-attendance.index');
        Route::get('/teacher-attendance/manual', [App\Http\Controllers\TeacherAttendanceController::class, 'manualCreate'])->name('teacher-attendance.manual.create');
        Route::post('/teacher-attendance/manual', [App\Http\Controllers\TeacherAttendanceController::class, 'manualStore'])->name('teacher-attendance.manual.store');
        Route::get('/teacher-attendance/{attendance}', [App\Http\Controllers\TeacherAttendanceController::class, 'show'])->name('teacher-attendance.show');
        Route::get('/teacher-attendance/{attendance}/edit', [App\Http\Controllers\TeacherAttendanceController::class, 'edit'])->name('teacher-attendance.edit');
        Route::put('/teacher-attendance/{attendance}', [App\Http\Controllers\TeacherAttendanceController::class, 'update'])->name('teacher-attendance.update');
        Route::get('/teacher-attendance/recent-entries', [App\Http\Controllers\TeacherAttendanceController::class, 'recentEntries'])->name('teacher-attendance.recent-entries');
        
        // Teacher Attendance Correction routes
        Route::get('/teacher-attendance/corrections', [App\Http\Controllers\TeacherAttendanceCorrectionController::class, 'index'])->name('teacher-attendance.corrections');
        Route::post('/teacher-attendance/corrections/{id}/approve', [App\Http\Controllers\TeacherAttendanceCorrectionController::class, 'approve'])->name('teacher-attendance.corrections.approve');
        Route::post('/teacher-attendance/corrections/{id}/reject', [App\Http\Controllers\TeacherAttendanceCorrectionController::class, 'reject'])->name('teacher-attendance.corrections.reject');
        Route::post('/teacher-attendance/corrections/refresh', [App\Http\Controllers\TeacherAttendanceCorrectionController::class, 'refresh'])->name('teacher-attendance.corrections.refresh');
    });
    
    // Librarian routes
    Route::middleware('role:librarian')->prefix('library')->name('library.')->group(function () {
        Route::get('/dashboard', function () {
            return view('library.dashboard');
        })->name('dashboard');
    });
    
    // Timetable Coordinator routes
    Route::middleware('role:timetable_coordinator')->prefix('timetable')->name('timetable.')->group(function () {
        Route::get('/dashboard', function () {
            return view('timetable.dashboard');
        })->name('dashboard');
    });
    
    // Principal routes
    Route::middleware('role:principal')->prefix('principal')->name('principal.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\PrincipalDashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/refresh', [App\Http\Controllers\PrincipalDashboardController::class, 'refresh'])->name('dashboard.refresh');
        
        // Reports routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/fee-recovery', [App\Http\Controllers\PrincipalFeeReportController::class, 'index'])->name('fee-recovery');
            Route::get('/fee-recovery/pdf', [App\Http\Controllers\PrincipalFeeReportController::class, 'exportPdf'])->name('fee-recovery.pdf');
            Route::get('/fee-recovery/excel', [App\Http\Controllers\PrincipalFeeReportController::class, 'exportExcel'])->name('fee-recovery.excel');
            
            Route::get('/staff-performance', [App\Http\Controllers\StaffPerformanceController::class, 'index'])->name('staff-performance');
            Route::get('/staff-performance/pdf', [App\Http\Controllers\StaffPerformanceController::class, 'exportPdf'])->name('staff-performance.pdf');
            Route::get('/staff-performance/excel', [App\Http\Controllers\StaffPerformanceController::class, 'exportExcel'])->name('staff-performance.excel');
        });
        
        // Task Management routes
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', [App\Http\Controllers\TaskController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\TaskController::class, 'store'])->name('store');
            Route::put('/{task}', [App\Http\Controllers\TaskController::class, 'update'])->name('update');
            Route::patch('/{task}/status', [App\Http\Controllers\TaskController::class, 'updateStatus'])->name('update-status');
            Route::delete('/{task}', [App\Http\Controllers\TaskController::class, 'destroy'])->name('destroy');
            Route::get('/statistics', [App\Http\Controllers\TaskController::class, 'getStatistics'])->name('statistics');
        });
        
        // Teacher Attendance routes
        Route::get('/teacher-attendance', [App\Http\Controllers\PrincipalAttendanceController::class, 'index'])->name('teacher-attendance.index');
        Route::get('/teacher-attendance/deductions', [App\Http\Controllers\PrincipalAttendanceController::class, 'deductions'])->name('teacher-attendance.deductions');
        Route::post('/teacher-attendance/deductions/approve', [App\Http\Controllers\PrincipalAttendanceController::class, 'approveDeduction'])->name('teacher-attendance.deductions.approve');
        Route::post('/teacher-attendance/deductions/reject', [App\Http\Controllers\PrincipalAttendanceController::class, 'rejectDeduction'])->name('teacher-attendance.deductions.reject');
        Route::post('/teacher-attendance/deductions/bulk-approve', [App\Http\Controllers\PrincipalAttendanceController::class, 'bulkApproveDeductions'])->name('teacher-attendance.deductions.bulk-approve');
        Route::get('/teacher-attendance/stats', [App\Http\Controllers\PrincipalAttendanceController::class, 'getAttendanceStats'])->name('teacher-attendance.stats');
    });
    
    // HR Manager routes
    Route::middleware('role:hr_manager,principal,super_admin')->prefix('hr')->name('hr.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\HrDashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/refresh', [App\Http\Controllers\HrDashboardController::class, 'refresh'])->name('dashboard.refresh');
        
        // Teacher Attendance routes (Page 22 from spec)
        Route::get('/teacher-attendance', [App\Http\Controllers\TeacherAttendanceController::class, 'hrIndex'])
            ->name('teacher-attendance.index');
        
        // Optional: manual entry routes if needed
        Route::get('/teacher-attendance/manual', [App\Http\Controllers\TeacherAttendanceController::class, 'manualCreate'])
            ->name('teacher-attendance.manual');
        Route::post('/teacher-attendance/manual', [App\Http\Controllers\TeacherAttendanceController::class, 'manualStore'])
            ->name('teacher-attendance.store-manual');
    });
    
    // Librarian routes
    Route::middleware('role:librarian,admin,principal,super_admin')->prefix('library')->name('library.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\LibraryDashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/refresh', [App\Http\Controllers\LibraryDashboardController::class, 'refresh'])->name('dashboard.refresh');
        
        // Book management routes
        Route::get('/books', [App\Http\Controllers\BookController::class, 'index'])->name('books.index');
        Route::get('/books/create', [App\Http\Controllers\BookController::class, 'create'])->name('books.create');
        Route::post('/books', [App\Http\Controllers\BookController::class, 'store'])->name('books.store');
        Route::get('/books/{book}', [App\Http\Controllers\BookController::class, 'show'])->name('books.show');
        Route::get('/books/{book}/edit', [App\Http\Controllers\BookController::class, 'edit'])->name('books.edit');
        Route::put('/books/{book}', [App\Http\Controllers\BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{book}', [App\Http\Controllers\BookController::class, 'destroy'])->name('books.destroy');
        Route::get('/books/{book}/details', [App\Http\Controllers\BookController::class, 'getBookDetails'])->name('books.details');
        Route::post('/books/{book}/toggle-status', [App\Http\Controllers\BookController::class, 'toggleStatus'])->name('books.toggle-status');
        Route::get('/books/search', [App\Http\Controllers\BookController::class, 'searchBooks'])->name('books.search');
        Route::get('/books/statistics', [App\Http\Controllers\BookController::class, 'getStatistics'])->name('books.statistics');
        
        // Book loan routes
        Route::get('/loans', [App\Http\Controllers\BookLoanController::class, 'index'])->name('loans.index');
        Route::get('/loans/issue-return', [App\Http\Controllers\BookLoanController::class, 'issueReturn'])->name('loans.issue-return');
        Route::post('/loans/issue', [App\Http\Controllers\BookLoanController::class, 'issueBook'])->name('loans.issue');
        Route::post('/loans/{loan}/return', [App\Http\Controllers\BookLoanController::class, 'returnBook'])->name('loans.return');
        Route::get('/loans/{loan}/details', [App\Http\Controllers\BookLoanController::class, 'getLoanDetails'])->name('loans.details');
        Route::get('/loans/user', [App\Http\Controllers\BookLoanController::class, 'getUserLoans'])->name('loans.user');
        Route::get('/loans/book', [App\Http\Controllers\BookLoanController::class, 'getBookLoans'])->name('loans.book');
        Route::post('/loans/update-overdue', [App\Http\Controllers\BookLoanController::class, 'updateOverdueLoans'])->name('loans.update-overdue');
        Route::get('/loans/statistics', [App\Http\Controllers\BookLoanController::class, 'getStatistics'])->name('loans.statistics');
        Route::post('/loans/send-reminders', [App\Http\Controllers\BookLoanController::class, 'sendOverdueReminders'])->name('loans.send-reminders');
        Route::post('/loans/{loan}/extend', [App\Http\Controllers\BookLoanController::class, 'extendLoan'])->name('loans.extend');
    });
    
    // Reports routes
    Route::middleware('role:admin,principal,super_admin')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\ReportDashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/refresh', [App\Http\Controllers\ReportDashboardController::class, 'refresh'])->name('dashboard.refresh');
        Route::post('/export', [App\Http\Controllers\ReportDashboardController::class, 'export'])->name('export');
    });
    
    // Settings routes
    Route::middleware('role:super_admin,admin,principal')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\SettingController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\SettingController::class, 'update'])->name('update');
        Route::post('/{setting}', [App\Http\Controllers\SettingController::class, 'updateSingle'])->name('update-single');
        Route::post('/reset', [App\Http\Controllers\SettingController::class, 'resetDefaults'])->name('reset');
        Route::get('/export', [App\Http\Controllers\SettingController::class, 'export'])->name('export');
        Route::post('/import', [App\Http\Controllers\SettingController::class, 'import'])->name('import');
        Route::post('/clear-cache', [App\Http\Controllers\SettingController::class, 'clearCache'])->name('clear-cache');
        Route::get('/{setting}', [App\Http\Controllers\SettingController::class, 'getSetting'])->name('show');
        Route::post('/', [App\Http\Controllers\SettingController::class, 'store'])->name('store');
        Route::delete('/{setting}', [App\Http\Controllers\SettingController::class, 'destroy'])->name('destroy');
        Route::get('/statistics', [App\Http\Controllers\SettingController::class, 'statistics'])->name('statistics');
    });
    });
});
