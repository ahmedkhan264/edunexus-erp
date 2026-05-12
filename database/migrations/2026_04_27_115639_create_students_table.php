<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->string('student_id')->unique(); // Unique student registration number
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('gender', 10);
            $table->string('phone_number', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('Pakistan');
            $table->string('nationality', 100)->default('Pakistani');
            $table->string('religion', 50)->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relation', 50)->nullable();
            $table->date('admission_date');
            $table->string('admission_number')->unique();
            $table->decimal('previous_school_gpa', 3, 2)->nullable();
            $table->string('previous_school_name', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('graduation_date')->nullable();
            $table->string('status', 20)->default('enrolled'); // enrolled, graduated, suspended, withdrawn
            $table->text('notes')->nullable();
            $table->string('profile_image')->nullable();
            $table->json('documents')->nullable(); // Store document paths as JSON
            
            // Indexes for better performance
            $table->index('student_id');
            $table->index('admission_number');
            $table->index('class_id');
            $table->index('status');
            $table->index('is_active');
            $table->index('admission_date');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
