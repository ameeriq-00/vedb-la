// database/migrations/xxxx_xx_xx_create_vehicles_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('directorate_id')->constrained('directorates');
            $table->foreignId('user_id')->constrained('users');
            $table->string('type'); // confiscated or government
            
            // Common fields for both types
            $table->string('vehicle_type'); // نوع العجلة - car, truck, etc
            $table->string('vehicle_name')->nullable(); // اسم العجلة
            $table->string('model')->nullable(); // موديل العجلة
            $table->string('chassis_number')->nullable(); // رقم الشاصي
            $table->string('vehicle_number')->nullable(); // رقم العجلة
            $table->string('province')->nullable(); // المحافظة
            $table->string('color')->nullable(); // اللون
            $table->enum('vehicle_condition', ['صالحة', 'غير صالحة'])->default('صالحة'); // حالة العجلة
            $table->text('accessories')->nullable(); // الملحقات (JSON)
            $table->text('defects')->nullable(); // العوارض (JSON)
            $table->text('missing_parts')->nullable(); // النواقص

            // Confiscated vehicle specific fields
            $table->string('defendant_name')->nullable(); // اسم المتهم
            $table->string('legal_article')->nullable(); // المادة القانونية
            
            // Status fields for confiscated vehicles
            $table->enum('seizure_status', ['محجوزة', 'مفرج عنها', 'مصادرة'])->nullable();
            $table->string('seizure_letter_number')->nullable(); // عدد كتاب الحجز
            $table->date('seizure_letter_date')->nullable(); // تاريخ كتاب الحجز
            $table->string('release_decision_number')->nullable(); // عدد قرار الافراج
            $table->date('release_decision_date')->nullable(); // تاريخ قرار الافراج
            $table->string('confiscation_letter_number')->nullable(); // عدد كتاب المصادرة
            $table->date('confiscation_letter_date')->nullable(); // تاريخ كتاب المصادرة
            
            // Final degree status
            $table->enum('final_degree_status', ['غير مكتسبة', 'مكتسبة'])->default('غير مكتسبة');
            $table->string('decision_number')->nullable(); // عدد القرار
            $table->date('decision_date')->nullable(); // تاريخ القرار
            
            // Authentication status
            $table->enum('authentication_status', ['غير مصادق عليها', 'تمت المصادقة عليها'])->default('غير مصادق عليها');
            $table->string('authentication_number')->nullable(); // عدد المصادقة
            $table->date('authentication_date')->nullable(); // تاريخ المصادقة
            
            // Valuation status
            $table->enum('valuation_status', ['غير مثمنة', 'مثمنة'])->default('غير مثمنة');
            $table->decimal('valuation_amount', 12, 2)->nullable(); // مبلغ التثمين
            
            // Government vehicle specific fields
            $table->string('source')->nullable(); // وردت من
            $table->string('import_letter_number')->nullable(); // عدد الوارد
            $table->date('import_letter_date')->nullable(); // تاريخ الوارد
            
            $table->text('notes')->nullable(); // ملاحظات
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
};