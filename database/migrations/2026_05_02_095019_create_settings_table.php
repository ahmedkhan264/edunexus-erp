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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, number, boolean, json
            $table->string('category')->default('general'); // general, school, system, email, security
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Whether setting is visible to all users
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index(['category']);
            $table->index(['is_public']);
            $table->unique(['key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
