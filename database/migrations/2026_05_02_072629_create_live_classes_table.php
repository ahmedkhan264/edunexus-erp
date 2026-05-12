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
        Schema::create('live_classes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->string('section');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->string('meeting_link');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration'); // in minutes
            $table->boolean('notification_sent')->default(false);
            $table->timestamps();
            
            $table->index(['class_id', 'section', 'start_time']);
            $table->index(['teacher_id', 'start_time']);
            $table->index(['subject_id', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_classes');
    }
};
