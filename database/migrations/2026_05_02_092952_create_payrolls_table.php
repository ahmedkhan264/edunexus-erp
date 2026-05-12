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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('allowances', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            $table->integer('working_days');
            $table->integer('present_days')->default(0);
            $table->integer('absent_days')->default(0);
            $table->integer('late_days')->default(0);
            $table->integer('leave_days')->default(0);
            $table->enum('status', ['draft', 'finalized'])->default('draft');
            $table->text('remarks')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate payroll for same employee, month, year
            $table->unique(['user_id', 'month', 'year']);
            
            // Indexes
            $table->index(['month', 'year']);
            $table->index(['status', 'processed_at']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
