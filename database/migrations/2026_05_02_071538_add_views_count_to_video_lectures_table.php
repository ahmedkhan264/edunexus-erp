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
        Schema::table('video_lectures', function (Blueprint $table) {
            $table->integer('views_count')->default(0)->after('duration');
            $table->index('views_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_lectures', function (Blueprint $table) {
            $table->dropIndex(['views_count']);
            $table->dropColumn('views_count');
        });
    }
};
