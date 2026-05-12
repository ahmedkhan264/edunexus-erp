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
        Schema::create('fee_challans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('challan_number', 50)->unique(); // auto-generated
            $table->string('academic_year', 20);
            $table->integer('month'); // 1-12
            $table->decimal('total_amount', 10, 2);
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid', 'partially_paid', 'overdue', 'waived'])->default('pending');
            $table->decimal('late_fine_applied', 10, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('generated_by')->constrained('users')->onDelete('set null');
            
            // Indexes for performance
            $table->index('student_id');
            $table->index('challan_number');
            $table->index('academic_year');
            $table->index('status');
            $table->index('due_date');
            $table->index(['student_id', 'academic_year', 'month']);
            $table->index(['status', 'due_date']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_challans');
    }
};
