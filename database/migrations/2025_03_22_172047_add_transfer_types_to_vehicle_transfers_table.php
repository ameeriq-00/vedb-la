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
            $table->boolean('is_ownership_transfer')->default(false)->after('return_date');
            $table->boolean('is_referral')->default(false)->after('is_ownership_transfer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_transfers', function (Blueprint $table) {
            $table->dropColumn(['is_ownership_transfer', 'is_referral']);
        });
    }
};