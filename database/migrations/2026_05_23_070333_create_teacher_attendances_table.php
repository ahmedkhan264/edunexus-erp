<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('teacher_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day'])->default('present');
            $table->integer('late_minutes')->default(0);
            $table->decimal('working_hours', 5, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('marked_by')->nullable();
            $table->enum('attendance_method', ['system', 'manual'])->default('system');
            $table->timestamps();
            
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('marked_by')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['teacher_id', 'date']);
            $table->index(['date', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('teacher_attendances');
    }
};