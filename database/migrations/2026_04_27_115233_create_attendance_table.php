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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'holiday'])->default('present');
            $table->datetime('check_in_time')->nullable();
            $table->datetime('check_out_time')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('remarks')->nullable();
            $table->enum('attendance_method', ['manual', 'barcode', 'api'])->default('manual');
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index(['user_id', 'date']);
            $table->index(['class_id', 'date']);
            $table->index('status');
            $table->index('date');
            $table->index('attendance_method');
            
            // Unique constraint to prevent duplicate attendance records
            $table->unique(['user_id', 'class_id', 'date'], 'attendance_unique_record');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
