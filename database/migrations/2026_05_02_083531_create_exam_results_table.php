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
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->integer('marks_obtained');
            $table->integer('total_marks');
            $table->decimal('percentage', 5, 2);
            $table->string('grade', 2);
            $table->enum('status', ['pass', 'fail', 'absent']);
            $table->text('remarks')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('graded_at')->nullable();
            $table->integer('attempt_number')->default(1);
            $table->timestamps();
            
            $table->unique(['exam_id', 'student_id', 'attempt_number']);
            $table->index(['exam_id', 'status']);
            $table->index(['student_id', 'exam_id']);
            $table->index(['status', 'grade']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
