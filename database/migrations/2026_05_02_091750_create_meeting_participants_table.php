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
        Schema::create('meeting_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('attendance_status', ['present', 'absent', 'pending'])->default('pending');
            $table->timestamp('attended_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate participants
            $table->unique(['meeting_id', 'user_id']);
            
            // Indexes
            $table->index(['meeting_id', 'attendance_status']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_participants');
    }
};
