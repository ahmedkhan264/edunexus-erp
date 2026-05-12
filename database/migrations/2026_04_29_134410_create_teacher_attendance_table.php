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
        Schema::create('teacher_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'late', 'absent'])->default('present');
            $table->integer('late_minutes')->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users');
            $table->enum('attendance_method', ['manual', 'system'])->default('system');
            $table->timestamps();
            
            $table->unique(['teacher_id', 'date']);
            $table->index(['date', 'status']);
            $table->index(['teacher_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance');
    }
};
