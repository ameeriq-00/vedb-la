<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Directorate;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على المديريات
        $generalDirectorate = Directorate::where('name', 'المقر العام')->first();
        $specialDirectorate = Directorate::where('name', 'التحقيقات الخاصة')->first();
        $financialDirectorate = Directorate::where('name', 'التحقيق المالي والموازي')->first();
        
        // إنشاء المشرف (المدير)
        $admin = User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'directorate_id' => $generalDirectorate->id,
        ]);
        $admin->assignRole('admin');
        
        // إنشاء المدقق
        $verifier = User::create([
            'name' => 'المدقق',
            'email' => 'verifier@example.com',
            'password' => Hash::make('password'),
            'directorate_id' => $financialDirectorate->id,
        ]);
        $verifier->assignRole('verifier');
        
        // إنشاء مدخل البيانات
        $dataEntry = User::create([
            'name' => 'مدخل البيانات',
            'email' => 'data@example.com',
            'password' => Hash::make('password'),
            'directorate_id' => $specialDirectorate->id,
        ]);
        $dataEntry->assignRole('data_entry');
        
        // إنشاء مستخدم الآليات
        $vehiclesDept = User::create([
            'name' => 'مسؤول الآليات',
            'email' => 'vehicles@example.com',
            'password' => Hash::make('password'),
            'directorate_id' => $generalDirectorate->id,
        ]);
        $vehiclesDept->assignRole('vehicles_dept');
        
        // إنشاء المستلم
        $recipient = User::create([
            'name' => 'المستلم',
            'email' => 'recipient@example.com',
            'password' => Hash::make('password'),
            'directorate_id' => Directorate::where('name', 'مخدرات بغداد')->first()->id ?? $specialDirectorate->id,
        ]);
        $recipient->assignRole('recipient');
    }
}