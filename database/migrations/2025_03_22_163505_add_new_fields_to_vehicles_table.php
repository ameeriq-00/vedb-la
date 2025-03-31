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
        Schema::table('vehicles', function (Blueprint $table) {
            // تعديل تسلسل مراحل العجلات المصادرة
            // إضافة مراحل الإهداء والترقيم الحكومي
            $table->enum('donation_status', ['غير مهداة', 'مهداة'])->default('غير مهداة')->after('valuation_status');
            $table->string('donation_letter_number')->nullable()->after('donation_status');
            $table->date('donation_letter_date')->nullable()->after('donation_letter_number');
            $table->string('donation_entity')->nullable()->after('donation_letter_date');
            
            $table->enum('government_registration_status', ['غير مرقمة', 'مرقمة'])->default('غير مرقمة')->after('donation_entity');
            $table->string('registration_letter_number')->nullable()->after('government_registration_status');
            $table->date('registration_letter_date')->nullable()->after('registration_letter_number');
            $table->string('government_registration_number')->nullable()->after('registration_letter_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'donation_status',
                'donation_letter_number',
                'donation_letter_date',
                'donation_entity',
                'government_registration_status',
                'registration_letter_number',
                'registration_letter_date',
                'government_registration_number',
            ]);
        });
    }
};