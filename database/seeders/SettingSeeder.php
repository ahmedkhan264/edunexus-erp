<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            ['key' => 'school_name', 'value' => 'EduNexus ERP', 'title' => 'School Name'],
            ['key' => 'school_email', 'value' => 'info@edunexus.com', 'title' => 'School Email'],
            ['key' => 'school_phone', 'value' => '+1234567890', 'title' => 'School Phone'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
