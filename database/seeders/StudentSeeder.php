<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\SchoolClass;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $studentRole = Role::where('name', 'Student')->first();
        $classes = SchoolClass::all();

        for ($i = 1; $i <= 25; $i++) {
            $user = User::create([
                'name' => $faker->name,
                'email' => "student{$i}@edunexus.com",
                'password' => bcrypt('password'),
                'role_id' => $studentRole->id,
            ]);

            $class = $classes->random();

            $student = Student::create([
                'user_id' => $user->id,
                'student_id' => 'STU' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'admission_number' => 'ADM' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'class_id' => $class->id,
                'gender' => $faker->randomElement(['Male', 'Female']),
                'date_of_birth' => $faker->date('Y-m-d', '-10 years'),
                'blood_group' => $faker->randomElement(['A+', 'B+', 'O+', 'AB+']),
                'phone_number' => $faker->phoneNumber,
                'address' => $faker->address,
                'status' => 'enrolled',
                'admission_date' => $faker->date('Y-m-d', '-2 years'),
                'previous_school_name' => $faker->company,
                'previous_school_gpa' => $faker->randomFloat(2, 2.5, 4.0),
            ]);

            ParentProfile::create([
                'student_id' => $student->id,
                'father_name' => $faker->name('male'),
                'father_phone' => $faker->phoneNumber,
                'father_occupation' => $faker->jobTitle,
                'mother_name' => $faker->name('female'),
                'mother_phone' => $faker->phoneNumber,
                'mother_occupation' => $faker->jobTitle,
                'guardian_name' => $faker->name,
                'guardian_phone' => $faker->phoneNumber,
                'guardian_relation' => $faker->randomElement(['Father', 'Mother', 'Uncle']),
                'guardian_address' => $faker->address,
            ]);
        }
    }
}
