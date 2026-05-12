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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('employee_code', 20)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('cnic', 15)->unique();
            $table->date('date_of_birth');
            $table->string('gender', 10);
            $table->string('phone_number', 20);
            $table->string('email', 150);
            $table->text('address');
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('postal_code', 20);
            $table->string('country', 100);
            $table->string('nationality', 100);
            $table->string('blood_group', 10)->nullable();
            $table->string('religion', 50)->nullable();
            
            // Professional Information
            $table->string('qualification', 200);
            $table->string('specialization', 200)->nullable();
            $table->string('experience_years', 10)->default('0');
            $table->string('previous_institution', 200)->nullable();
            $table->date('joining_date');
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->string('employment_type', 50)->default('permanent'); // permanent, contract, part-time
            
            // Status and Security
            $table->boolean('is_active')->default(true);
            $table->date('resignation_date')->nullable();
            $table->string('resignation_reason', 500)->nullable();
            $table->text('notes')->nullable();
            
            // Profile and Documents
            $table->string('profile_image')->nullable();
            $table->json('documents')->nullable(); // Store document paths
            
            // Indexes for better performance
            $table->index('user_id');
            $table->index('employee_code');
            $table->index('cnic');
            $table->index('email');
            $table->index('is_active');
            $table->index('joining_date');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
