<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TeacherAttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SalarySlipController;
use App\Http\Controllers\LibraryDashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookLoanController;
use App\Http\Controllers\ReportDashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\EmployeeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login')->name('login.post');
    Route::post('/logout', 'logout')->name('logout');
});

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Main Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
  Route::prefix('admin')
    ->name('admin.')
    ->middleware('role:Admin,Principal,Super Admin')
    ->group(function () {

        // Admin Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Student Management
        |--------------------------------------------------------------------------
        */
        Route::resource('students', StudentController::class);
        Route::get('/students/print', [StudentController::class, 'printIndex'])->name('students.print');

        /*
        |--------------------------------------------------------------------------
        | Teacher Management
        |--------------------------------------------------------------------------
        */
        Route::resource('teachers', TeacherController::class);

        /*
        |--------------------------------------------------------------------------
        | Student Attendance
        |--------------------------------------------------------------------------
        */
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/mark', [AttendanceController::class, 'mark'])->name('attendance.mark');
        Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
    });

    /*
    |--------------------------------------------------------------------------
    | HR Routes
    |--------------------------------------------------------------------------
    */
   Route::prefix('hr')
    ->name('hr.')
    ->middleware('role:HR Manager,Admin')
    ->group(function () {

        // HR Dashboard
        Route::get('/dashboard', [App\Http\Controllers\HRDashboardController::class, 'index'])->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Employee Management
        |--------------------------------------------------------------------------
        */
        Route::resource('employees', EmployeeController::class);

        /*
        |--------------------------------------------------------------------------
        | Leave Requests
        |--------------------------------------------------------------------------
        */
        Route::resource('leave-requests', LeaveRequestController::class);

        /*
        |--------------------------------------------------------------------------
        | Payroll
        |--------------------------------------------------------------------------
        */
        Route::resource('payroll', PayrollController::class);
        Route::get('salary-slips/{teacher}/{month}/{year}', [SalarySlipController::class, 'download'])->name('salary-slip.download');

        /*
        |--------------------------------------------------------------------------
        | Teacher Attendance
        |--------------------------------------------------------------------------
        */
        Route::resource('teacher-attendance', TeacherAttendanceController::class);
        
        // Custom routes for teacher attendance
        Route::get('teacher-attendance/{id}/details', [TeacherAttendanceController::class, 'details'])->name('teacher-attendance.details');
        Route::get('teacher-attendance/manual/create', [TeacherAttendanceController::class, 'manualCreate'])->name('teacher-attendance.manual.create');
        Route::post('teacher-attendance/manual/store', [TeacherAttendanceController::class, 'manualStore'])->name('teacher-attendance.manual.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Library Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('library')
        ->name('library.')
        ->middleware('role:Librarian,Admin')
        ->group(function () {
            Route::get('/dashboard', [LibraryDashboardController::class, 'index'])->name('dashboard');
            Route::resource('books', BookController::class);
            Route::resource('loans', BookLoanController::class);
            Route::post('loans/{loan}/return', [BookLoanController::class, 'returnBook'])->name('loans.return');
        });

    /*
    |--------------------------------------------------------------------------
    | Reports Routes
    |--------------------------------------------------------------------------
    */
  Route::prefix('reports')
    ->name('reports.')
    ->middleware('role:admin,principal,hr_manager,librarian')
    ->group(function () {
        Route::get('/dashboard', [ReportDashboardController::class, 'index'])->name('dashboard');
        Route::post('/refresh', [ReportDashboardController::class, 'refresh'])->name('dashboard.refresh'); // POST is safer for actions
        Route::get('/student-attendance', [ReportDashboardController::class, 'studentAttendance'])->name('student-attendance');
        Route::get('/teacher-attendance', [ReportDashboardController::class, 'teacherAttendance'])->name('teacher-attendance');
        Route::get('/fee-collection', [ReportDashboardController::class, 'feeCollection'])->name('fee-collection');
        Route::get('/library-usage', [ReportDashboardController::class, 'libraryUsage'])->name('library-usage');
        Route::get('/student-attendance/pdf', [ReportController::class, 'studentAttendancePdf'])->name('student-attendance.pdf');
    });
    /*
    |--------------------------------------------------------------------------
    | System Settings
    |--------------------------------------------------------------------------
    */
    Route::prefix('settings')
        ->name('settings.')
        ->middleware('role:Super Admin')
        ->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/', [SettingController::class, 'update'])->name('update');
        });

    /*
    |--------------------------------------------------------------------------
    | Teacher Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/teacher/dashboard', [DashboardController::class, 'teacherDashboard'])
        ->name('teacher.dashboard')
        ->middleware('role:Teacher');

    /*
    |--------------------------------------------------------------------------
    | Student Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/student/dashboard', [DashboardController::class, 'studentDashboard'])
        ->name('student.dashboard')
        ->middleware('role:Student');
});
Route::prefix('teacher')
    ->name('teacher.')
    ->middleware(['auth', 'role:Teacher'])
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'teacherDashboard'])
            ->name('dashboard');

        // Placeholder LMS routes
        Route::get('/lms/lessons/create', function () {
            return "Lesson creation coming soon.";
        })->name('lms.lessons.create');

        // Placeholder attendance routes
        Route::get('/attendance/students', function () {
            return "Mark student attendance coming soon.";
        })->name('attendance.students');

        Route::get('/attendance/checkin', function () {
            return "Teacher check-in/out coming soon.";
        })->name('attendance.checkin');

        Route::get('/attendance/corrections', function () {
            return "Request attendance correction coming soon.";
        })->name('attendance.corrections');
    });