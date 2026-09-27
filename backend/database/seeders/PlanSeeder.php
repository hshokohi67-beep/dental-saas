<?php

namespace Database\Seeders;

use App\Domain\Platform\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'key' => 'practice',
                'name' => 'Practice',
                'limits' => ['max_branches' => 1, 'max_users' => 5, 'max_patients' => 500, 'storage_mb' => 1024],
                'features' => [],
            ],
            [
                'key' => 'clinic',
                'name' => 'Clinic',
                'limits' => ['max_branches' => 3, 'max_users' => 20, 'max_patients' => 5000, 'storage_mb' => 10240],
                'features' => ['advanced_analytics'],
            ],
            [
                'key' => 'group',
                'name' => 'Group',
                'limits' => ['max_branches' => null, 'max_users' => null, 'max_patients' => null, 'storage_mb' => 51200],
                'features' => ['advanced_analytics', 'ai', 'api'],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(['key' => $plan['key']], $plan);
        }
    }
}
