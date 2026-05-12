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
            $table->foreignId('challan_id')->nullable()->constrained('fee_challans')->onDelete('set null');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->date('payment_date');
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_method'); // cash, bank_transfer, online, cheque
            $table->string('transaction_id')->nullable();
            $table->string('receipt_number')->unique();
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id']);
            $table->index(['payment_date']);
            $table->index(['receipt_number']);
            $table->index(['challan_id']);
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
