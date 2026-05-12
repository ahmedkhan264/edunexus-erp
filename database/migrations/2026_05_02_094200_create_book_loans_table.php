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
        Schema::create('book_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['issued', 'returned', 'overdue'])->default('issued');
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('returned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index(['book_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'due_date']);
            $table->index(['issue_date']);
            $table->index(['return_date']);
            
            // Unique constraint to prevent duplicate active loans
            $table->unique(['book_id', 'user_id', 'return_date'], 'unique_active_loan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_loans');
    }
};
