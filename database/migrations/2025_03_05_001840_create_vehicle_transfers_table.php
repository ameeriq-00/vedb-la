// database/migrations/xxxx_xx_xx_create_vehicle_transfers_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicle_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained(); // User who created the transfer
            $table->string('recipient_name');
            $table->string('recipient_entity'); // الجهة المستلمة
            $table->string('assigned_to')->nullable(); // منسب إلى
            $table->date('receive_date');
            $table->date('return_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_external')->default(false); // For external entities
            $table->foreignId('destination_directorate_id')->nullable()->constrained('directorates');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_transfers');
    }
};