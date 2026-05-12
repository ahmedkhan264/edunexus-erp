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
        Schema::create('teacher_class_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('academic_year', 9); // e.g., 2025-2026
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['teacher_id', 'class_id', 'subject_id', 'academic_year']);
            $table->index(['academic_year', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_class_assignments');
    }
};
