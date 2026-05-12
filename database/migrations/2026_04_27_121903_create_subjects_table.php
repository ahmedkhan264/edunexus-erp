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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->string('grade_level', 20)->nullable(); // For which grades this subject is taught
            $table->boolean('is_active')->default(true);
            $table->integer('credits')->default(1);
            $table->string('department', 100)->nullable();
            
            // Indexes
            $table->index('name');
            $table->index('code');
            $table->index('grade_level');
            $table->index('is_active');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
