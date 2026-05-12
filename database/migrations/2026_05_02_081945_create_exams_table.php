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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->string('section');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('exam_date');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration_minutes');
            $table->integer('total_marks');
            $table->integer('passing_marks');
            $table->enum('exam_type', ['midterm', 'final', 'quiz', 'assignment', 'practical']);
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->text('instructions')->nullable();
            $table->boolean('allow_retake')->default(false);
            $table->integer('max_attempts')->default(1);
            $table->timestamps();
            
            $table->index(['class_id', 'section', 'exam_date']);
            $table->index(['subject_id', 'exam_date']);
            $table->index(['teacher_id', 'exam_date']);
            $table->index(['exam_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
