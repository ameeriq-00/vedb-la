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
        Schema::table('vehicle_transfers', function (Blueprint $table) {
            // إضافة رقم هوية المستلم لتتبع استلام شخص واحد لعدة عجلات
            $table->string('recipient_id_number')->nullable()->after('recipient_name');
            $table->string('recipient_phone')->nullable()->after('recipient_id_number');
            $table->foreignId('completed_by')->nullable()->after('user_id')
                  ->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_transfers', function (Blueprint $table) {
            $table->dropForeign(['completed_by']);
            $table->dropColumn(['recipient_id_number', 'recipient_phone', 'completed_by']);
        });
    }
};