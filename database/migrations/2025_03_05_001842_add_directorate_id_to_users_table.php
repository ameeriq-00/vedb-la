// database/migrations/xxxx_xx_xx_add_directorate_id_to_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('directorate_id')->nullable()->constrained('directorates');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['directorate_id']);
            $table->dropColumn('directorate_id');
        });
    }
};