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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->string('section');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->string('chapter')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['class_id', 'section', 'status']);
            $table->index(['teacher_id', 'status']);
            $table->index(['subject_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
