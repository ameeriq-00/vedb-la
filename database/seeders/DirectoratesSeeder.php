<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Directorate;

class DirectoratesSeeder extends Seeder
{
    public function run()
    {
        $directorates = [
            ['name' => 'المقر العام', 'type' => 'general'],
            ['name' => 'التحقيقات الخاصة', 'type' => 'special'],
            ['name' => 'مخدرات الكرخ', 'type' => 'special'],
            ['name' => 'مخدرات الرصافة', 'type' => 'special'],
            ['name' => 'مخدرات ديالى', 'type' => 'special'],
            ['name' => 'مخدرات نينوى', 'type' => 'special'],
            ['name' => 'مخدرات الانبار', 'type' => 'special'],
            ['name' => 'مخدرات صلاح الدين', 'type' => 'special'],
            ['name' => 'مخدرات كركوك', 'type' => 'special'],
            ['name' => 'مخدرات واسط', 'type' => 'special'],
            ['name' => 'مخدرات بابل', 'type' => 'special'],
            ['name' => 'مخدرات كربلاء', 'type' => 'special'],
            ['name' => 'مخدرات النجف', 'type' => 'special'],
            ['name' => 'مخدرات الديوانية', 'type' => 'special'],
            ['name' => 'مخدرات ذي قار', 'type' => 'special'],
            ['name' => 'مخدرات ميسان', 'type' => 'special'],
            ['name' => 'مخدرات المثنى', 'type' => 'special'],
            ['name' => 'مخدرات البصرة', 'type' => 'special'],
            ['name' => 'التحقيق المالي والموازي', 'type' => 'special'],
        ];

        foreach ($directorates as $directorate) {
            Directorate::create($directorate);
        }
    }
}