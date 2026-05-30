<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Role;
use App\Models\Subject;
use App\Models\SchoolClass;
use Faker\Factory as Faker;

class TeacherSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $teacherRole = Role::where('name', 'Teacher')->first();
        $subjects = Subject::all();
        $classes = SchoolClass::all();

        for ($i = 1; $i <= 8; $i++) {
            $user = User::create([
                'name' => $faker->name,
                'email' => "teacher{$i}@edunexus.com",
                'password' => bcrypt('password'),
                'role_id' => $teacherRole->id,
            ]);

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'employee_code' => 'TCH' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'cnic' => $faker->numerify('#####-#######-#'),
                'gender' => $faker->randomElement(['Male', 'Female']),
                'date_of_birth' => $faker->date('Y-m-d', '-25 years'),
                'phone_number' => $faker->phoneNumber,
                'email' => "teacher{$i}@edunexus.com",
                'address' => $faker->address,
                'city' => $faker->city,
                'state' => $faker->state,
                'postal_code' => $faker->postcode,
                'country' => 'Pakistan',
                'nationality' => 'Pakistani',
                'qualification' => $faker->randomElement(['B.Ed', 'M.Ed', 'PhD']),
                'experience_years' => (string) $faker->numberBetween(1, 20),
                'employment_type' => $faker->randomElement(['Permanent', 'Contract', 'Probation']),
                'basic_salary' => $faker->numberBetween(30000, 80000),
                'joining_date' => $faker->date('Y-m-d', '-2 years'),
            ]);

            // Assign 2-3 random subjects
            $teacher->subjects()->sync($subjects->random(rand(2, 3))->pluck('id')->toArray());
            // Assign 1-2 random classes
            $teacher->classes()->sync($classes->random(rand(1, 2))->pluck('id')->toArray());
        }
    }
}
