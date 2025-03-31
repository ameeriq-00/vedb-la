// database/migrations/xxxx_xx_xx_create_edit_requests_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('edit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained(); // Who requested
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Who approved
            $table->string('field_name');
            $table->text('old_value')->nullable();
            $table->text('new_value');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('approval_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('edit_requests');
    }
};