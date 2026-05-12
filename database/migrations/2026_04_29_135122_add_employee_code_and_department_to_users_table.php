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
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_code', 50)->nullable()->after('email');
            $table->foreignId('department_id')->nullable()->after('employee_code')->constrained('departments')->onDelete('set null');
            $table->index(['employee_code']);
            $table->index(['department_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropIndex(['department_id']);
            $table->dropIndex(['employee_code']);
            $table->dropColumn(['department_id', 'employee_code']);
        });
    }
};
