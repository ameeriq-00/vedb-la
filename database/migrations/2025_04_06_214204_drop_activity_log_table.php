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
        Schema::dropIfExists('activity_log');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ترك هذا فارغًا لأنه سيتم إنشاء الجدول في الخطوة التالية
    }
};
