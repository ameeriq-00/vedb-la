// database/migrations/xxxx_xx_xx_create_directorates_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('directorates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('special'); // special or general
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('directorates');
    }
};