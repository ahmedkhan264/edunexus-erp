<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RoleRedirectService
{
    /**
     * Get the dashboard route for the authenticated user based on their role
     */
    public static function getDashboardRoute(): string
    {
        if (!Auth::check()) {
            return '/login';
        }

        $user = Auth::user();

        if (!$user->isActive()) {
            return '/login';
        }

        return match($user->role->slug) {
            'super_admin' => '/admin/dashboard',
            'principal' => '/principal/dashboard',
            'admin' => '/admin/dashboard',
            'teacher' => '/teacher/dashboard',
            'student' => '/student/dashboard',
            'parent' => '/parent/dashboard',
            'accountant' => '/accountant/dashboard',
            'hr_manager' => '/hr/dashboard',
            'librarian' => '/library/dashboard',
            'timetable_coordinator' => '/timetable/dashboard',
            default => '/login'
        };
    }

    /**
     * Get the dashboard route for a specific user
     */
    public static function getDashboardRouteForUser(User $user): string
    {
        if (!$user->isActive()) {
            return '/login';
        }

        return match($user->role->slug) {
            'super_admin' => '/admin/dashboard',
            'principal' => '/principal/dashboard',
            'admin' => '/admin/dashboard',
            'teacher' => '/teacher/dashboard',
            'student' => '/student/dashboard',
            'parent' => '/parent/dashboard',
            'accountant' => '/accountant/dashboard',
            'hr_manager' => '/hr/dashboard',
            'librarian' => '/library/dashboard',
            'timetable_coordinator' => '/timetable/dashboard',
            default => '/login'
        };
    }

    /**
     * Get role-based navigation menu items
     */
    public static function getNavigationItems(): array
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();
        $roleSlug = $user->role->slug;

        return match($roleSlug) {
            'super_admin', 'principal', 'admin' => [
                [
                    'title' => 'Dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'route' => 'admin.dashboard',
                    'active' => request()->routeIs('admin.dashboard')
                ],
                [
                    'title' => 'Students',
                    'icon' => 'fas fa-users',
                    'route' => 'admin.students.index',
                    'active' => request()->routeIs('admin.students.*'),
                    'submenu' => [
                        ['title' => 'All Students', 'route' => 'admin.students.index'],
                        ['title' => 'Add Student', 'route' => 'admin.students.create'],
                        ['title' => 'Student Reports', 'route' => 'reports.student-attendance']
                    ]
                ],
                [
                    'title' => 'Teachers',
                    'icon' => 'fas fa-chalkboard-teacher',
                    'route' => 'admin.teachers.index',
                    'active' => request()->routeIs('admin.teachers.*'),
                    'submenu' => [
                        ['title' => 'All Teachers', 'route' => 'admin.teachers.index'],
                        ['title' => 'Add Teacher', 'route' => 'admin.teachers.create'],
                        ['title' => 'Teacher Attendance', 'route' => 'hr.teacher-attendance.index']
                    ]
                ],
                [
                    'title' => 'Attendance',
                    'icon' => 'fas fa-calendar-check',
                    'route' => 'reports.student-attendance',
                    'active' => request()->routeIs('reports.student-attendance*'),
                    'submenu' => [
                        ['title' => 'Student Attendance', 'route' => 'reports.student-attendance'],
                        ['title' => 'Attendance Report', 'route' => 'reports.student-attendance.pdf'],
                        ['title' => 'Teacher Attendance', 'route' => 'hr.teacher-attendance.index']
                    ]
                ],
                [
                    'title' => 'Fee Management',
                    'icon' => 'fas fa-money-bill-wave',
                    'route' => 'admin.students.index',
                    'active' => false,
                    'submenu' => [
                        ['title' => 'Fee Collection', 'route' => 'admin.students.index'],
                        ['title' => 'Fee Reports', 'route' => 'reports.dashboard'],
                        ['title' => 'Payment History', 'route' => 'admin.students.index']
                    ]
                ],
                [
                    'title' => 'Academics',
                    'icon' => 'fas fa-graduation-cap',
                    'route' => 'admin.classes.index',
                    'active' => request()->routeIs('admin.classes.*') || request()->routeIs('admin.subjects.*'),
                    'submenu' => [
                        ['title' => 'Classes & Sections', 'route' => 'admin.classes.index'],
                        ['title' => 'Subjects', 'route' => 'admin.subjects.index'],
                        ['title' => 'Assign Subjects', 'route' => 'admin.subjects.assign']
                    ]
                ],
                [
                    'title' => 'HR & Finance',
                    'icon' => 'fas fa-user-tie',
                    'route' => 'hr.dashboard',
                    'active' => request()->routeIs('hr.*'),
                    'submenu' => [
                        ['title' => 'Employee Management', 'route' => 'hr.dashboard'],
                        ['title' => 'Teacher Attendance', 'route' => 'hr.teacher-attendance.index'],
                        ['title' => 'Leave Management', 'route' => 'hr.dashboard']
                    ]
                ],
                [
                    'title' => 'Library',
                    'icon' => 'fas fa-book',
                    'route' => 'library.books.index',
                    'active' => request()->routeIs('library.*'),
                    'submenu' => [
                        ['title' => 'Book Catalog', 'route' => 'library.books.index'],
                        ['title' => 'Issue/Return', 'route' => 'library.loans.issue-return'],
                        ['title' => 'Library Dashboard', 'route' => 'library.dashboard']
                    ]
                ],
                [
                    'title' => 'Reports',
                    'icon' => 'fas fa-chart-bar',
                    'route' => 'reports.dashboard',
                    'active' => request()->routeIs('reports.*'),
                    'submenu' => [
                        ['title' => 'Report Dashboard', 'route' => 'reports.dashboard'],
                        ['title' => 'Attendance Reports', 'route' => 'reports.student-attendance'],
                        ['title' => 'Fee Reports', 'route' => 'reports.dashboard']
                    ]
                ],
                [
                    'title' => 'Settings',
                    'icon' => 'fas fa-cog',
                    'route' => 'settings.index',
                    'active' => request()->routeIs('settings.*'),
                    'submenu' => [
                        ['title' => 'System Settings', 'route' => 'settings.index'],
                        ['title' => 'Configuration', 'route' => 'settings.index'],
                        ['title' => 'Backup & Restore', 'route' => 'settings.export']
                    ]
                ]
            ],
            'teacher' => [
                [
                    'title' => 'Dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'route' => 'teacher.dashboard',
                    'active' => request()->routeIs('teacher.dashboard')
                ],
                [
                    'title' => 'My Classes',
                    'icon' => 'fas fa-chalkboard',
                    'route' => 'teacher.attendance.students',
                    'active' => request()->routeIs('teacher.attendance.*')
                ],
                [
                    'title' => 'Attendance',
                    'icon' => 'fas fa-calendar-check',
                    'route' => 'teacher.attendance.students',
                    'active' => request()->routeIs('teacher.attendance.*')
                ],
                [
                    'title' => 'LMS',
                    'icon' => 'fas fa-book-open',
                    'route' => 'teacher.lms.dashboard',
                    'active' => request()->routeIs('teacher.lms.*'),
                    'submenu' => [
                        ['title' => 'Lessons', 'route' => 'teacher.lms.lessons.create'],
                        ['title' => 'Video Lectures', 'route' => 'student.videos.index'],
                        ['title' => 'Live Classes', 'route' => 'teacher.live-classes.create']
                    ]
                ],
                [
                    'title' => 'Assignments',
                    'icon' => 'fas fa-tasks',
                    'route' => 'teacher.assignments.index',
                    'active' => request()->routeIs('teacher.assignments.*')
                ],
                [
                    'title' => 'Results',
                    'icon' => 'fas fa-chart-line',
                    'route' => 'teacher.assignments.results.index',
                    'active' => request()->routeIs('teacher.assignments.results.*')
                ],
                [
                    'title' => 'My Tasks',
                    'icon' => 'fas fa-clipboard-list',
                    'route' => 'teacher.dashboard',
                    'active' => false
                ]
            ],
            'student' => [
                [
                    'title' => 'Dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'route' => 'student.dashboard',
                    'active' => request()->routeIs('student.dashboard')
                ],
                [
                    'title' => 'My Timetable',
                    'icon' => 'fas fa-calendar',
                    'route' => 'student.dashboard',
                    'active' => false
                ],
                [
                    'title' => 'LMS',
                    'icon' => 'fas fa-book-open',
                    'route' => 'student.videos.index',
                    'active' => request()->routeIs('student.videos.*') || request()->routeIs('student.assignments.*'),
                    'submenu' => [
                        ['title' => 'Video Lectures', 'route' => 'student.videos.index'],
                        ['title' => 'Live Classes', 'route' => 'student.live-classes.index'],
                        ['title' => 'Assignments', 'route' => 'student.assignments.index']
                    ]
                ],
                [
                    'title' => 'Results',
                    'icon' => 'fas fa-chart-line',
                    'route' => 'student.assignments.results.index',
                    'active' => request()->routeIs('student.assignments.results.*') || request()->routeIs('student.exam-results.*')
                ],
                [
                    'title' => 'Attendance',
                    'icon' => 'fas fa-calendar-check',
                    'route' => 'student.dashboard',
                    'active' => false
                ],
                [
                    'title' => 'Fee Status',
                    'icon' => 'fas fa-money-bill',
                    'route' => 'student.dashboard',
                    'active' => false
                ]
            ],
            'parent' => [
                [
                    'title' => 'Dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'route' => 'parent.dashboard',
                    'active' => request()->routeIs('parent.dashboard')
                ],
                [
                    'title' => 'Child\'s Attendance',
                    'icon' => 'fas fa-calendar-check',
                    'route' => 'parent.dashboard',
                    'active' => false
                ],
                [
                    'title' => 'Child\'s Results',
                    'icon' => 'fas fa-chart-line',
                    'route' => 'parent.dashboard',
                    'active' => false
                ],
                [
                    'title' => 'Fee Status',
                    'icon' => 'fas fa-money-bill',
                    'route' => 'parent.dashboard',
                    'active' => false
                ],
                [
                    'title' => 'Notifications',
                    'icon' => 'fas fa-bell',
                    'route' => 'parent.dashboard',
                    'active' => false
                ]
            ],
            'accountant' => [
                [
                    'title' => 'Dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'route' => 'accountant.dashboard',
                    'active' => request()->routeIs('accountant.dashboard')
                ],
                [
                    'title' => 'Fee Management',
                    'icon' => 'fas fa-money-bill-wave',
                    'route' => 'admin.students.index',
                    'active' => false,
                    'submenu' => [
                        ['title' => 'Fee Collection', 'route' => 'admin.students.index'],
                        ['title' => 'Fee Reports', 'route' => 'reports.dashboard'],
                        ['title' => 'Payment History', 'route' => 'admin.students.index']
                    ]
                ],
                [
                    'title' => 'Finance',
                    'icon' => 'fas fa-chart-pie',
                    'route' => 'accountant.dashboard',
                    'active' => false,
                    'submenu' => [
                        ['title' => 'Financial Reports', 'route' => 'reports.dashboard'],
                        ['title' => 'Income & Expenses', 'route' => 'accountant.dashboard']
                    ]
                ],
                [
                    'title' => 'Reports',
                    'icon' => 'fas fa-file-invoice',
                    'route' => 'reports.dashboard',
                    'active' => false
                ]
            ],
            'hr_manager' => [
                [
                    'title' => 'Dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'route' => 'hr.dashboard',
                    'active' => request()->routeIs('hr.dashboard')
                ],
                [
                    'title' => 'Employees',
                    'icon' => 'fas fa-users',
                    'route' => 'admin.teachers.index',
                    'active' => false,
                    'submenu' => [
                        ['title' => 'All Teachers', 'route' => 'admin.teachers.index'],
                        ['title' => 'Add Teacher', 'route' => 'admin.teachers.create'],
                        ['title' => 'Teacher Attendance', 'route' => 'hr.teacher-attendance.index']
                    ]
                ],
                [
                    'title' => 'Leave Management',
                    'icon' => 'fas fa-calendar-alt',
                    'route' => 'hr.dashboard',
                    'active' => false
                ],
                [
                    'title' => 'Payroll',
                    'icon' => 'fas fa-money-check-alt',
                    'route' => 'hr.dashboard',
                    'active' => false,
                    'submenu' => [
                        ['title' => 'Teacher Attendance', 'route' => 'hr.teacher-attendance.index'],
                        ['title' => 'Payroll Reports', 'route' => 'hr.dashboard']
                    ]
                ],
                [
                    'title' => 'HR Reports',
                    'icon' => 'fas fa-chart-bar',
                    'route' => 'hr.dashboard',
                    'active' => false
                ]
            ],
            'librarian' => [
                [
                    'title' => 'Dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'route' => 'library.dashboard',
                    'active' => request()->routeIs('library.dashboard')
                ],
                [
                    'title' => 'Books',
                    'icon' => 'fas fa-book',
                    'route' => 'library.books.index',
                    'active' => request()->routeIs('library.books.*'),
                    'submenu' => [
                        ['title' => 'Book Catalog', 'route' => 'library.books.index'],
                        ['title' => 'Add Book', 'route' => 'library.books.create'],
                        ['title' => 'Book Search', 'route' => 'library.books.search']
                    ]
                ],
                [
                    'title' => 'Circulation',
                    'icon' => 'fas fa-exchange-alt',
                    'route' => 'library.loans.issue-return',
                    'active' => request()->routeIs('library.loans.*'),
                    'submenu' => [
                        ['title' => 'Issue/Return', 'route' => 'library.loans.issue-return'],
                        ['title' => 'Book Loans', 'route' => 'library.loans.index'],
                        ['title' => 'Loan Statistics', 'route' => 'library.loans.statistics']
                    ]
                ],
                [
                    'title' => 'Reports',
                    'icon' => 'fas fa-chart-bar',
                    'route' => 'library.dashboard',
                    'active' => false
                ]
            ],
            'timetable_coordinator' => [
                [
                    'title' => 'Dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'route' => 'timetable.dashboard',
                    'active' => request()->routeIs('timetable.dashboard')
                ],
                [
                    'title' => 'Timetable',
                    'icon' => 'fas fa-calendar-alt',
                    'route' => 'timetable.dashboard',
                    'active' => false,
                    'submenu' => [
                        ['title' => 'Create Timetable', 'route' => 'timetable.dashboard'],
                        ['title' => 'View Timetable', 'route' => 'timetable.dashboard'],
                        ['title' => 'Clash Report', 'route' => 'timetable.dashboard']
                    ]
                ],
                [
                    'title' => 'Time Slots',
                    'icon' => 'fas fa-clock',
                    'route' => 'timetable.dashboard',
                    'active' => false
                ],
                [
                    'title' => 'Rooms',
                    'icon' => 'fas fa-door-open',
                    'route' => 'timetable.dashboard',
                    'active' => false
                ]
            ],
            default => []
        };
    }

    /**
     * Check if user has permission to access a specific route/feature
     */
    public static function hasPermission(string $feature): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        $roleSlug = $user->role->slug;

        $permissions = [
            'super_admin' => ['*'],
            'principal' => ['dashboard', 'reports', 'students', 'teachers', 'attendance', 'fee_management', 'academics'],
            'admin' => ['dashboard', 'students', 'teachers', 'attendance', 'fee_management', 'academics', 'library'],
            'teacher' => ['dashboard', 'my_classes', 'attendance', 'lms', 'assignments', 'results', 'tasks'],
            'student' => ['dashboard', 'timetable', 'lms', 'results', 'attendance', 'fee_status'],
            'parent' => ['dashboard', 'child_attendance', 'child_results', 'fee_status', 'notifications'],
            'accountant' => ['dashboard', 'fee_management', 'finance', 'reports'],
            'hr_manager' => ['dashboard', 'employees', 'leave_management', 'payroll', 'hr_reports'],
            'librarian' => ['dashboard', 'books', 'circulation', 'reports'],
            'timetable_coordinator' => ['dashboard', 'timetable', 'time_slots', 'rooms']
        ];

        $userPermissions = $permissions[$roleSlug] ?? [];

        return in_array('*', $userPermissions) || in_array($feature, $userPermissions);
    }
}
