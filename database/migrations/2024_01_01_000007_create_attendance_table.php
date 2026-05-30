<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'holiday'])->default('absent');
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users');
            $table->timestamps();

            // Unique constraint to prevent duplicate attendance per user per day per class
            $table->unique(['user_id', 'date', 'class_id'], 'attendance_unique_per_day');
            
            // Indexes for performance
            $table->index('date');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance');
    }
};
