<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get classes and admin user
        $classes = SchoolClass::take(10)->get();
        $admin = User::where('email', 'admin@edunexus.com')->first();
        
        // Sample student data
        $students = [
            [
                'first_name' => 'Ahmed',
                'last_name' => 'Khan',
                'gender' => 'Male',
                'date_of_birth' => '2008-03-15',
                'phone_number' => '+92 300 1234567',
                'email' => 'ahmed.khan@student.com',
                'address' => '123 Garden Town',
                'city' => 'Lahore',
                'state' => 'Punjab',
                'postal_code' => '54000',
                'blood_group' => 'O+',
                'emergency_contact_name' => 'Muhammad Khan',
                'emergency_contact_phone' => '+92 300 9876543',
                'emergency_contact_relation' => 'Father',
            ],
            [
                'first_name' => 'Fatima',
                'last_name' => 'Ali',
                'gender' => 'Female',
                'date_of_birth' => '2009-07-22',
                'phone_number' => '+92 321 2345678',
                'email' => 'fatima.ali@student.com',
                'address' => '456 Defense Road',
                'city' => 'Karachi',
                'state' => 'Sindh',
                'postal_code' => '75500',
                'blood_group' => 'A+',
                'emergency_contact_name' => 'Ali Ahmed',
                'emergency_contact_phone' => '+92 321 8765432',
                'emergency_contact_relation' => 'Father',
            ],
            [
                'first_name' => 'Muhammad',
                'last_name' => 'Hassan',
                'gender' => 'Male',
                'date_of_birth' => '2007-11-08',
                'phone_number' => '+92 333 3456789',
                'email' => 'muhammad.hassan@student.com',
                'address' => '789 University Road',
                'city' => 'Islamabad',
                'state' => 'Federal',
                'postal_code' => '44000',
                'blood_group' => 'B+',
                'emergency_contact_name' => 'Hassan Raza',
                'emergency_contact_phone' => '+92 333 7654321',
                'emergency_contact_relation' => 'Uncle',
            ],
            [
                'first_name' => 'Ayesha',
                'last_name' => 'Siddiqui',
                'gender' => 'Female',
                'date_of_birth' => '2010-01-30',
                'phone_number' => '+92 311 4567890',
                'email' => 'ayesha.siddiqui@student.com',
                'address' => '321 Mall Road',
                'city' => 'Rawalpindi',
                'state' => 'Punjab',
                'postal_code' => '46000',
                'blood_group' => 'AB+',
                'emergency_contact_name' => 'Siddiqui Ahmed',
                'emergency_contact_phone' => '+92 311 6543210',
                'emergency_contact_relation' => 'Brother',
            ],
            [
                'first_name' => 'Omar',
                'last_name' => 'Malik',
                'gender' => 'Male',
                'date_of_birth' => '2008-09-12',
                'phone_number' => '+92 300 5678901',
                'email' => 'omar.malik@student.com',
                'address' => '654 Cantonment Area',
                'city' => 'Peshawar',
                'state' => 'KPK',
                'postal_code' => '25000',
                'blood_group' => 'O-',
                'emergency_contact_name' => 'Malik Saqib',
                'emergency_contact_phone' => '+92 300 5432109',
                'emergency_contact_relation' => 'Father',
            ],
        ];
        
        $createdStudents = 0;
        
        foreach ($students as $index => $studentData) {
            // Create user account for each student
            $user = User::create([
                'name' => $studentData['first_name'] . ' ' . $studentData['last_name'],
                'email' => $studentData['email'],
                'password' => Hash::make('password'),
                'role_id' => 5, // Student role
                'is_active' => true,
            ]);
            
            // Generate unique student ID and admission number
            $studentId = 'STU' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $admissionNumber = 'ADM' . date('Y') . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            
            // Select a random class
            $class = $classes->random();
            
            // Create student record
            Student::create([
                'user_id' => $user->id,
                'class_id' => $class->id,
                'student_id' => $studentId,
                'admission_number' => $admissionNumber,
                'admission_date' => now()->subMonths(rand(6, 24))->subDays(rand(0, 30)),
                'first_name' => $studentData['first_name'],
                'last_name' => $studentData['last_name'],
                'date_of_birth' => $studentData['date_of_birth'],
                'gender' => $studentData['gender'],
                'phone_number' => $studentData['phone_number'],
                'email' => $studentData['email'],
                'address' => $studentData['address'],
                'city' => $studentData['city'],
                'state' => $studentData['state'],
                'postal_code' => $studentData['postal_code'],
                'country' => 'Pakistan',
                'nationality' => 'Pakistani',
                'religion' => 'Islam',
                'blood_group' => $studentData['blood_group'],
                'emergency_contact_name' => $studentData['emergency_contact_name'],
                'emergency_contact_phone' => $studentData['emergency_contact_phone'],
                'emergency_contact_relation' => $studentData['emergency_contact_relation'],
                'previous_school_gpa' => rand(250, 400) / 100,
                'previous_school_name' => 'Previous School ' . ($index + 1),
                'is_active' => true,
                'status' => 'enrolled',
                'documents' => [
                    'birth_certificate' => 'documents/birth_cert_' . ($index + 1) . '.pdf',
                    'previous_school_report' => 'documents/report_' . ($index + 1) . '.pdf',
                ],
            ]);
            
            $createdStudents++;
        }
        
        // Create additional students without user accounts for testing
        for ($i = 0; $i < 15; $i++) {
            $firstNames = ['Ali', 'Sara', 'Bilal', 'Zara', 'Usman', 'Mariam', 'Hamza', 'Aisha', 'Yusuf', 'Khadija'];
            $lastNames = ['Ahmed', 'Khan', 'Malik', 'Ali', 'Hussain', 'Siddiqui', 'Raza', 'Butt', 'Sheikh', 'Qureshi'];
            
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $gender = rand(0, 1) ? 'Male' : 'Female';
            
            // Create user account
            $user = User::create([
                'name' => $firstName . ' ' . $lastName,
                'email' => strtolower($firstName . '.' . $lastName . ($i + 6)) . '@student.com',
                'password' => Hash::make('password'),
                'role_id' => 5, // Student role
                'is_active' => true,
            ]);
            
            $studentId = 'STU' . str_pad($createdStudents + $i + 1, 4, '0', STR_PAD_LEFT);
            $admissionNumber = 'ADM' . date('Y') . str_pad($createdStudents + $i + 1, 4, '0', STR_PAD_LEFT);
            $class = $classes->random();
            
            Student::create([
                'user_id' => $user->id,
                'class_id' => $class->id,
                'student_id' => $studentId,
                'admission_number' => $admissionNumber,
                'admission_date' => now()->subMonths(rand(6, 24))->subDays(rand(0, 30)),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => now()->subYears(rand(10, 18))->subMonths(rand(0, 11))->subDays(rand(0, 28)),
                'gender' => $gender,
                'phone_number' => '+92 3' . rand(0, 3) . ' ' . rand(100000000, 999999999),
                'email' => strtolower($firstName . '.' . $lastName . ($i + 6)) . '@student.com',
                'address' => 'Sample Address ' . ($i + 1),
                'city' => 'Sample City',
                'state' => 'Sample State',
                'postal_code' => '00000',
                'country' => 'Pakistan',
                'nationality' => 'Pakistani',
                'religion' => 'Islam',
                'blood_group' => ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'][array_rand(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'])],
                'emergency_contact_name' => 'Emergency Contact ' . ($i + 1),
                'emergency_contact_phone' => '+92 3' . rand(0, 3) . ' ' . rand(100000000, 999999999),
                'emergency_contact_relation' => 'Father',
                'is_active' => true,
                'status' => 'enrolled',
            ]);
        }
        
        $this->command->info('Created ' . ($createdStudents + 15) . ' students with user accounts and student records');
    }
}
