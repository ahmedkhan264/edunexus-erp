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
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challan_id')->constrained('fee_challans')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->dateTime('payment_date');
            $table->decimal('amount_paid', 10, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'credit_card', 'online']);
            $table->string('transaction_id', 100)->nullable(); // for online payments
            $table->string('receipt_number', 50)->unique(); // auto-generated
            $table->foreignId('received_by')->constrained('users')->onDelete('set null');
            $table->text('remarks')->nullable();
            
            // Indexes for performance
            $table->index('challan_id');
            $table->index('student_id');
            $table->index('receipt_number');
            $table->index('payment_date');
            $table->index('payment_method');
            $table->index(['student_id', 'payment_date']);
            $table->index(['challan_id', 'payment_date']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
