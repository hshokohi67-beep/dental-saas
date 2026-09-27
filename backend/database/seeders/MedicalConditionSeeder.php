<?php

namespace Database\Seeders;

use App\Domain\Patients\Models\MedicalCondition;
use Illuminate\Database\Seeder;

/**
 * The 12 hard-coded comorbidities from the legacy system (business rules
 * §3.1), carried over as seed data for the new shared catalog.
 */
class MedicalConditionSeeder extends Seeder
{
    public function run(): void
    {
        $conditions = [
            ['key' => 'diabetes', 'label' => 'دیابت', 'alert_text' => 'کنترل قند خون قبل از درمان'],
            ['key' => 'hypertension', 'label' => 'فشار خون بالا', 'alert_text' => 'بررسی فشار قبل از تزریق'],
            ['key' => 'heart', 'label' => 'بیماری قلبی', 'alert_text' => 'مشاوره قلب قبل از جراحی'],
            ['key' => 'bleeding', 'label' => 'اختلال انعقاد خون', 'alert_text' => 'احتیاط در کشیدن دندان'],
            ['key' => 'asthma', 'label' => 'آسم', 'alert_text' => 'اسپری برونکودیلاتور آماده باشد'],
            ['key' => 'kidney', 'label' => 'بیماری کلیوی', 'alert_text' => 'دوز داروها را تنظیم کنید'],
            ['key' => 'liver', 'label' => 'بیماری کبدی', 'alert_text' => 'احتیاط در تجویز آنتی‌بیوتیک'],
            ['key' => 'thyroid', 'label' => 'بیماری تیروئید', 'alert_text' => null],
            ['key' => 'pregnancy', 'label' => 'بارداری', 'alert_text' => 'از رادیوگرافی و داروهای غیرضروری بپرهیزید'],
            ['key' => 'osteoporosis', 'label' => 'پوکی استخوان', 'alert_text' => 'بیس‌فسفونات → ریسک ONJ (نکروز استخوان فک)'],
            ['key' => 'hiv', 'label' => 'HIV/AIDS', 'alert_text' => 'پروتکل کنترل عفونت'],
            ['key' => 'hepatitis', 'label' => 'هپاتیت', 'alert_text' => 'پروتکل کنترل عفونت'],
        ];

        foreach ($conditions as $condition) {
            MedicalCondition::query()->updateOrCreate(['key' => $condition['key']], $condition);
        }
    }
}
