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
        Schema::table('classes', function (Blueprint $table) {
            $table->string('name');
            $table->string('class_code')->unique();
            $table->string('section')->nullable();
            $table->integer('grade_level');
            $table->integer('capacity')->default(30);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Indexes for better performance
            $table->index('grade_level');
            $table->index('is_active');
            $table->index('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'class_code', 
                'section',
                'grade_level',
                'capacity',
                'description',
                'is_active',
                'teacher_id'
            ]);
            $table->dropIndex(['grade_level']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['teacher_id']);
        });
    }
};
