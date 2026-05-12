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
        Schema::create('payroll_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_attendance_id')->constrained('teacher_attendance')->onDelete('cascade');
            $table->enum('deduction_type', ['late_arrival', 'early_departure', 'absenteeism', 'half_day', 'unauthorized_leave']);
            $table->decimal('deduction_amount', 10, 2);
            $table->integer('late_minutes')->default(0);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_remarks')->nullable();
            $table->timestamps();
            
            $table->index(['teacher_attendance_id', 'deduction_type']);
            $table->index(['status', 'deduction_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_deductions');
    }
};
