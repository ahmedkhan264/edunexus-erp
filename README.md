<p align="center">
  <img src="https://via.placeholder.com/400x100/007bff/ffffff?text=EduNexus+ERP+%2B+LMS" alt="EduNexus Logo" width="400">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10-red" alt="Laravel 10">
  <img src="https://img.shields.io/badge/PHP-8.2-blue" alt="PHP 8.2">
  <img src="https://img.shields.io/badge/Database-SQLite-green" alt="SQLite">
  <img src="https://img.shields.io/badge/Bootstrap-5-purple" alt="Bootstrap 5">
  <img src="https://img.shields.io/badge/Status-Production%20Ready-brightgreen" alt="Production Ready">
</p>

# EduNexus ERP + LMS

A comprehensive school management system built with Laravel 10, featuring role-based authentication, student management, attendance tracking, and learning management capabilities.

## 🚀 Quick Setup

### Prerequisites
- PHP 8.2 or higher
- Composer 2.0 or higher
- MySQL 8.0 or higher (recommended for production)
- Node.js 18+ and NPM (for frontend assets)
- Git

### Installation Steps

#### 1. Clone the Repository
```bash
git clone <repository-url>
cd edunexus-erp
```

#### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install frontend dependencies
npm install
```

#### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### 4. Database Configuration
Edit your `.env` file with your MySQL credentials:

```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=edunexus_db
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password
```

#### 5. Create Database
```sql
CREATE DATABASE edunexus_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 6. Run Migrations and Seeders
```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Alternative: Run migrations separately
php artisan migrate
php artisan db:seed
```

#### 7. Build Frontend Assets
```bash
# Compile frontend assets
npm run build
```

#### 8. Start Development Server
```bash
php artisan serve
```

#### 9. Access the Application
- **URL**: `http://127.0.0.1:8000`
- **Login Credentials**:
  - **Admin**: `admin@edunexus.com` / `password`
  - **Teacher**: `teacher@edunexus.com` / `password`
  - **Student**: `student@edunexus.com` / `password`

### Production Deployment

#### 1. Server Requirements
- **PHP**: 8.2+ with required extensions
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **SSL Certificate**: Required for HTTPS

#### 2. Production Setup
```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Optimize configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set production environment
APP_ENV=production
APP_DEBUG=false

# Run production migrations
php artisan migrate --force

# Optimize application
php artisan optimize
```

#### 3. Queue Setup (Optional)
```bash
# Install supervisor for queue workers
# Configure queue worker in config/horizon.php

# Start queue worker
php artisan queue:work
```

## 📊 System Statistics

### Current Data
- **Users**: 23+ user accounts with different roles
- **Classes**: 36 classes (Grade 1-12 with sections)
- **Students**: 20+ students with complete profiles
- **Teachers**: Ready for teacher management with comprehensive profiles
- **Subjects**: Subject catalog ready for assignment
- **Attendance**: 144 attendance records for 30 days
- **Routes**: 70+ protected routes with proper middleware

### Database Tables

## 🎯 Features

### ✅ Completed Modules

#### 🔐 Authentication & Authorization
- **Multi-Role System**: Super Admin, Principal, Admin, Teacher, Student, Parent, Accountant, HR Manager, Librarian, Timetable Coordinator
- **Secure Login**: Role-based dashboard redirection
- **Middleware Protection**: Route-level security with proper access control
- **Responsive UI**: Modern login interface with EduNexus theme

#### 📊 Dashboard System
- **Admin Dashboard**: KPI cards, statistics, and charts
- **Role-Based Views**: Different dashboards for each user role
- **Responsive Layout**: Sidebar navigation with mobile support
- **EduNexus Theme**: Consistent blue, green, and white color scheme

#### 🏫 Class Management
- **Complete Class Structure**: Grade 1-12 with multiple sections (36 total classes)
- **Class Assignment**: Student-to-class relationships
- **Section Management**: Multiple sections per grade level
- **Academic Year Support**: Ready for year-based class management

#### 📋 Attendance System
- **Daily Tracking**: Present, Absent, Late, Holiday status
- **Class-Based Attendance**: Attendance by class and date
- **User Integration**: Links to student and teacher accounts
- **Historical Data**: Complete attendance history with analytics
- **Performance**: Optimized queries with proper indexing

#### 👥 Student Management (Complete CRUD)
- **Student List**: Advanced search, filtering, pagination, action buttons
- **Add Student**: Comprehensive two-column admission form
- **Edit Student**: Prefilled forms with status management
- **Student Profile**: 8 profile cards with comprehensive information
- **Parent Profiles**: Complete guardian information system
- **Profile Photos**: Image upload with validation
- **Status Tracking**: Enrolled, Graduated, Suspended, Withdrawn
- **Academic Records**: Previous school information and GPA tracking

#### 👨‍🏫 Teacher Management (Complete CRUD)
- **Teacher List**: Advanced search, filtering, pagination with employee codes
- **Add Teacher**: Comprehensive registration form with assignments
- **Teacher Profile**: Role-based profile with salary information
- **Subject Assignment**: Multi-select subjects with department information
- **Class Assignment**: Multi-select classes with grade/section details
- **Professional Information**: Qualification, experience, employment details
- **Salary Management**: Basic salary with employment type validation
- **User Account Creation**: Automatic teacher user account generation

### 🎨 UI/UX Features
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile
- **Modern Interface**: Bootstrap 5 with custom EduNexus styling
- **Form Validation**: Real-time validation with error messages
- **Smart Formatting**: Auto-formatting for CNIC and phone numbers
- **Interactive Elements**: Tooltips, modals, and dynamic content

### 🔒 Security Features
- **Role-Based Access Control**: Middleware protection for all routes
- **Input Validation**: Comprehensive form request validation
- **CSRF Protection**: All forms include CSRF tokens
- **File Upload Security**: Image validation with mime type and size restrictions
- **SQL Injection Prevention**: Eloquent ORM with parameter binding

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- SQLite (or MySQL/PostgreSQL)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd edunexus-erp
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start the server**
   ```bash
   php artisan serve
   ```

6. **Access the application**
   - URL: `http://127.0.0.1:8000`
   - Admin: `admin@edunexus.com` / `password`
   - Teacher: `teacher@edunexus.com` / `password`
   - Student: `student@edunexus.com` / `password`

## 📊 System Statistics

### Current Data
- **Users**: 23+ user accounts with different roles
- **Classes**: 36 classes (Grade 1-12 with sections)
- **Students**: 20+ students with complete profiles
- **Teachers**: Ready for teacher management with comprehensive profiles
- **Subjects**: Subject catalog ready for assignment
- **Attendance**: 144 attendance records for 30 days
- **Routes**: 57+ protected routes with proper middleware

### Database Tables
1. **users** - User authentication and role assignment
2. **roles** - Role definitions and permissions
3. **classes** - School classes with grade levels and sections
4. **students** - Complete student information and academic records
5. **parent_profiles** - Guardian and parent contact information
6. **teachers** - Complete teacher information and professional details
7. **subjects** - Subject catalog with department and grade information
8. **attendance** - Daily attendance tracking with status

## 🗄️ Database Schema

### Key Relationships
- User → Role (Many-to-One)
- Student → User (Many-to-One)
- Student → Class (Many-to-One)
- Student → ParentProfile (One-to-One)
- Teacher → User (Many-to-One)
- Teacher → Subject (Many-to-Many)
- Teacher → Class (Many-to-Many)
- Attendance → User (Many-to-One)
- Attendance → Class (Many-to-One)

### Performance Optimizations
- **Database Indexes**: Optimized queries for frequently accessed data
- **Eager Loading**: Prevent N+1 query problems
- **Pagination**: Large datasets split into manageable chunks

## 🎯 Roadmap (Upcoming Modules)

### Phase 2 - Academic Management
- [x] Teacher Management Module
- [ ] Fee Management with Challan Generation
- [ ] LMS Module (Lessons & Content)
- [ ] Video Lectures & Live Classes
- [ ] Assignment & Result/Exam Modules

### Phase 3 - Administrative
- [ ] HR & Salary Module
- [ ] Library Management System
- [ ] Timetable Module with Clash Detection
- [ ] Notification System
- [ ] Dashboard Views for All User Roles

## 🔧 Technical Stack

- **Backend**: Laravel 10, PHP 8.2
- **Frontend**: Blade Templates, Bootstrap 5, JavaScript
- **Database**: SQLite (easily migratable to MySQL/PostgreSQL)
- **Authentication**: Laravel's built-in authentication system
- **Validation**: Form requests with custom rules
- **File Storage**: Local storage with public access
- **Security**: OWASP best practices implementation

## 📁 Project Structure & Files Documentation

### 📂 Root Directory Structure

```
edunexus-erp/
├── app/                          # Main application directory
├── bootstrap/                     # Bootstrap files for framework
├── config/                        # Configuration files
├── database/                      # Database related files
├── public/                        # Public web directory
├── resources/                     # Views, CSS, JavaScript
├── routes/                        # Route definitions
├── storage/                       # File storage
├── tests/                         # Test files
├── vendor/                        # Composer dependencies
├── .env.example                   # Environment template
├── .gitignore                     # Git ignore file
├── artisan                        # Laravel CLI tool
├── composer.json                  # PHP dependencies
├── package.json                   # Node.js dependencies
├── README.md                      # Project documentation
└── webpack.mix.js                 # Asset compilation
```

### 📂 app/ - Application Core

#### 📁 app/Http/ - HTTP Layer
- **Controllers/** - MVC controllers
  - `AuthController.php` - Authentication management
  - `DashboardController.php` - Main dashboard logic
  - `StudentController.php` - Student CRUD operations
  - `TeacherController.php` - Teacher CRUD operations
  - `AttendanceController.php` - Attendance management
  - `LeaveRequestController.php` - Leave request management
  - `PayrollController.php` - Payroll processing
  - `SalarySlipController.php` - Salary slip generation
  - `LibraryDashboardController.php` - Library dashboard
  - `BookController.php` - Book catalog management
  - `BookLoanController.php` - Book loan management
  - `ReportDashboardController.php` - Reports and analytics
  - `SettingController.php` - System settings management
  - `Controller.php` - Base controller class

- **Middleware/** - HTTP middleware
  - `RoleMiddleware.php` - Role-based access control
  - `Authenticate.php` - Authentication middleware
  - `RedirectIfAuthenticated.php` - Redirect logic
  - `TrustProxies.php` - Proxy trust middleware
  - `TrimStrings.php` - String trimming
  - `ValidateSignature.php` - URL signature validation

- **Requests/** - Form request validation
  - `StoreStudentRequest.php` - Student creation validation
  - `UpdateStudentRequest.php` - Student update validation
  - `StoreTeacherRequest.php` - Teacher creation validation
  - `UpdateTeacherRequest.php` - Teacher update validation
  - `StoreBookRequest.php` - Book creation validation
  - `UpdateBookRequest.php` - Book update validation

#### 📁 app/Models/ - Eloquent Models
- `User.php` - User authentication and roles
- `Role.php` - Role definitions
- `SchoolClass.php` - Class management
- `Student.php` - Student information and relationships
- `ParentProfile.php` - Parent/guardian information
- `Teacher.php` - Teacher information and relationships
- `Subject.php` - Subject catalog
- `Attendance.php` - Attendance records
- `LeaveRequest.php` - Leave request management
- `Payroll.php` - Payroll processing
- `SalarySlip.php` - Salary slip records
- `Book.php` - Library book catalog
- `BookLoan.php` - Book loan management
- `Setting.php` - System configuration settings

#### 📁 app/Services/ - Business Logic
- `AttendanceService.php` - Attendance calculation logic
- `PayrollService.php` - Payroll processing logic
- `LibraryService.php` - Library management logic
- `ReportService.php` - Report generation logic

### 📂 database/ - Database Layer

#### 📁 database/migrations/ - Database Schema
- `2024_01_01_000000_create_users_table.php` - Users table
- `2024_01_01_000001_create_roles_table.php` - Roles table
- `2024_01_01_000002_create_classes_table.php` - Classes table
- `2024_01_01_000003_create_students_table.php` - Students table
- `2024_01_01_000004_create_parent_profiles_table.php` - Parent profiles
- `2024_01_01_000005_create_teachers_table.php` - Teachers table
- `2024_01_01_000006_create_subjects_table.php` - Subjects table
- `2024_01_01_000007_create_attendance_table.php` - Attendance table
- `2024_01_01_000008_create_leave_requests_table.php` - Leave requests
- `2024_01_01_000009_create_payrolls_table.php` - Payroll table
- `2024_01_01_000010_create_salary_slips_table.php` - Salary slips
- `2024_01_01_000011_create_books_table.php` - Books table
- `2024_01_01_000012_create_book_loans_table.php` - Book loans
- `2024_01_01_000013_create_settings_table.php` - Settings table
- `2024_01_01_000014_create_teacher_subject_table.php` - Teacher-subject pivot
- `2024_01_01_000015_create_teacher_class_table.php` - Teacher-class pivot

#### 📁 database/seeders/ - Sample Data
- `DatabaseSeeder.php` - Main seeder
- `RoleSeeder.php` - Role data seeding
- `UserSeeder.php` - User accounts seeding
- `ClassSeeder.php` - Class data seeding
- `StudentSeeder.php` - Student data seeding
- `TeacherSeeder.php` - Teacher data seeding
- `SubjectSeeder.php` - Subject data seeding
- `AttendanceSeeder.php` - Attendance data seeding
- `SettingSeeder.php` - Default settings seeding

#### 📁 database/factories/ - Model Factories
- `UserFactory.php` - User model factory
- `StudentFactory.php` - Student model factory
- `TeacherFactory.php` - Teacher model factory
- `BookFactory.php` - Book model factory

### 📂 resources/ - Frontend Assets

#### 📁 resources/views/ - Blade Templates
- **layouts/** - Layout templates
  - `app.blade.php` - Main application layout
  - `auth.blade.php` - Authentication layout
  - `guest.blade.php` - Guest layout

- **auth/** - Authentication views
  - `login.blade.php` - Login page
  - `register.blade.php` - Registration page
  - `passwords/` - Password reset views

- **admin/** - Admin panel views
  - `dashboard.blade.php` - Admin dashboard
  - `students/` - Student management views
    - `index.blade.php` - Student list
    - `create.blade.php` - Add student form
    - `edit.blade.php` - Edit student form
    - `show.blade.php` - Student profile
  - `teachers/` - Teacher management views
    - `index.blade.php` - Teacher list
    - `create.blade.php` - Add teacher form
    - `edit.blade.php` - Edit teacher form
    - `show.blade.php` - Teacher profile
  - `attendance/` - Attendance views
    - `index.blade.php` - Attendance records
    - `mark.blade.php` - Mark attendance

- **hr/** - HR management views
  - `dashboard.blade.php` - HR dashboard
  - `employees/` - Employee management views
    - `index.blade.php` - Employee list
  - `leave/` - Leave management views
    - `index.blade.php` - Leave requests
    - `create.blade.php` - Request leave
  - `payroll/` - Payroll views
    - `index.blade.php` - Payroll processing
    - `salary-slip.blade.php` - Salary slip PDF

- **library/** - Library management views
  - `dashboard.blade.php` - Library dashboard
  - `books/` - Book catalog views
    - `index.blade.php` - Book list
    - `create.blade.php` - Add book form
    - `edit.blade.php` - Edit book form
    - `show.blade.php` - Book details
  - `loans/` - Book loan views
    - `index.blade.php` - Loan history
    - `issue-return.blade.php` - Issue/return interface

- **reports/** - Reports views
  - `dashboard.blade.php` - Reports dashboard

- **settings/** - Settings views
  - `index.blade.php` - System settings

- **dashboard/** - User dashboard views
  - `admin.blade.php` - Admin dashboard
  - `teacher.blade.php` - Teacher dashboard
  - `student.blade.php` - Student dashboard

#### 📁 resources/css/ - Stylesheets
- `app.css` - Main application styles
- `bootstrap.css` - Bootstrap framework

#### 📁 resources/js/ - JavaScript
- `app.js` - Main application JavaScript
- `bootstrap.js` - Bootstrap initialization

### 📂 routes/ - Route Definitions

#### 📁 routes/web.php - Web Routes
- **Authentication routes** - Login, logout, registration
- **Admin routes** - Student, teacher, attendance management
- **HR routes** - Employee, leave, payroll management
- **Library routes** - Book catalog and loan management
- **Reports routes** - Dashboard and analytics
- **Settings routes** - System configuration
- **Dashboard routes** - Role-based dashboards

### 📂 public/ - Public Web Directory

#### 📁 public/uploads/ - File Uploads
- `students/` - Student profile photos
- `teachers/` - Teacher profile photos
- `books/` - Book cover images
- `documents/` - General document uploads

#### 📁 public/css/ - Compiled CSS
- `app.css` - Compiled application styles

#### 📁 public/js/ - Compiled JavaScript
- `app.js` - Compiled application JavaScript

### 📂 storage/ - Application Storage

#### 📁 storage/app/public/ - Public Storage
- `uploads/` - Symlink to public/uploads

#### 📁 storage/framework/ - Framework Files
- `cache/` - Application cache
- `sessions/` - Session files
- `views/` - Compiled views

#### 📁 storage/logs/ - Log Files
- `laravel.log` - Application error logs

## 🔍 Quality Assurance

- **Code Standards**: PSR-4 autoloading and Laravel conventions
- **Testing Ready**: Structure supports unit and feature testing
- **Documentation**: Comprehensive inline documentation
- **Error Handling**: Graceful error handling with user-friendly messages
- **Performance**: Optimized queries and efficient data loading

## 📝 API Documentation

### Authentication Endpoints
- `POST /login` - User login
- `POST /logout` - User logout

### Student Management
- `GET /admin/students` - List students with filters
- `GET /admin/students/create` - Add student form
- `POST /admin/students` - Store new student
- `GET /admin/students/{student}` - View student profile
- `GET /admin/students/{student}/edit` - Edit student form
- `PUT /admin/students/{student}` - Update student
- `DELETE /admin/students/{student}` - Delete student

### Teacher Management
- `GET /admin/teachers` - List teachers with filters
- `GET /admin/teachers/create` - Add teacher form
- `POST /admin/teachers` - Store new teacher
- `GET /admin/teachers/{teacher}` - View teacher profile
- `GET /admin/teachers/{teacher}/edit` - Edit teacher form
- `PUT /admin/teachers/{teacher}` - Update teacher
- `DELETE /admin/teachers/{teacher}` - Delete teacher

### Attendance Management
- `GET /admin/attendance` - View attendance records
- `POST /admin/attendance/mark` - Mark attendance

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions:
- Email: support@edunexus.com
- Documentation: [Project Wiki](wiki-link)
- Issues: [GitHub Issues](issues-link)

---

**Built with ❤️ using Laravel 10**

© 2024 EduNexus ERP + LMS. All rights reserved.
