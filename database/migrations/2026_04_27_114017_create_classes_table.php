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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('class_code')->unique();
            $table->string('section');
            $table->integer('grade_level');
            $table->integer('capacity')->default(30);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index(['grade_level', 'section']);
            $table->index(['is_active']);
            $table->index(['teacher_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
