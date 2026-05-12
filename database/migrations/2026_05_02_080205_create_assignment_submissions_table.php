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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->text('content')->nullable();
            $table->integer('marks_obtained')->nullable();
            $table->text('feedback')->nullable();
            $table->dateTime('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['assignment_id', 'student_id']);
            $table->index(['assignment_id', 'submitted_at']);
            $table->index(['student_id', 'submitted_at']);
            $table->index(['graded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
