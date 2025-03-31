// database/migrations/xxxx_xx_xx_create_attachments_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable'); // For different types of attachments (vehicle, status, etc)
            $table->string('type'); // vehicle_image, seizure_letter, confiscation_letter, etc
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->string('file_size');
            $table->integer('sort_order')->default(0);
            $table->foreignId('user_id')->constrained(); // Who uploaded
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attachments');
    }
};