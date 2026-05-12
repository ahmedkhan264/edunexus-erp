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
        Schema::create('assignment_submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_submission_id')->constrained('assignment_submissions')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->integer('size');
            $table->timestamps();
            
            $table->index(['assignment_submission_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submission_files');
    }
};
