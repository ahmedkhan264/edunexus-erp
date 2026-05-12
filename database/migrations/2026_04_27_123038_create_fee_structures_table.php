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
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->string('academic_year', 20); // e.g., "2024-2025"
            $table->enum('fee_type', ['tuition', 'admission', 'exam', 'library', 'sports', 'transport', 'late_fine']);
            $table->decimal('amount', 10, 2);
            $table->boolean('is_optional')->default(false);
            $table->enum('payment_frequency', ['monthly', 'quarterly', 'yearly', 'one_time']);
            $table->integer('due_day'); // day of month for due date
            $table->decimal('late_fine_per_day', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Indexes for performance
            $table->index('class_id');
            $table->index('academic_year');
            $table->index('fee_type');
            $table->index('is_active');
            $table->index(['class_id', 'academic_year', 'fee_type']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
