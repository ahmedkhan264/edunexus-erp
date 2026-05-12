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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('isbn')->unique();
            $table->string('publisher')->nullable();
            $table->year('publication_year')->nullable();
            $table->string('category');
            $table->text('description')->nullable();
            $table->string('language', 50)->default('English');
            $table->integer('pages')->nullable();
            $table->integer('total_copies')->default(1);
            $table->string('location', 100)->nullable();
            $table->string('cover_image')->nullable();
            $table->enum('status', ['available', 'unavailable', 'maintenance'])->default('available');
            $table->text('notes')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index(['title', 'author']);
            $table->index(['category', 'status']);
            $table->index(['isbn']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
