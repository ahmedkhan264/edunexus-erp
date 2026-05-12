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
        Schema::create('parent_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('father_name')->nullable();
            $table->string('father_cnic', 15)->nullable();
            $table->string('father_phone', 20)->nullable();
            $table->string('father_occupation', 100)->nullable();
            $table->string('father_email')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_cnic', 15)->nullable();
            $table->string('mother_phone', 20)->nullable();
            $table->string('mother_occupation', 100)->nullable();
            $table->string('mother_email')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_cnic', 15)->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->string('guardian_occupation', 100)->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_relation', 50)->nullable();
            $table->text('guardian_address')->nullable();
            $table->boolean('is_primary_guardian')->default(false);
            $table->text('notes')->nullable();
            
            // Indexes for better performance
            $table->index('student_id');
            $table->index('user_id');
            $table->index('father_cnic');
            $table->index('mother_cnic');
            $table->index('guardian_cnic');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_profiles');
    }
};
