// database/migrations/xxxx_xx_xx_create_vehicle_statuses_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicle_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->string('status_type'); // seizure_status, final_degree_status, authentication_status, valuation_status
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('letter_number')->nullable();
            $table->date('letter_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_statuses');
    }
};