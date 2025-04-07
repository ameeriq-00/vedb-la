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
        if (Schema::hasTable('activity_log') && !Schema::hasColumn('activity_log', 'event')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->string('event')->nullable()->after('log_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('activity_log') && Schema::hasColumn('activity_log', 'event')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->dropColumn('event');
            });
        }
    }
};
