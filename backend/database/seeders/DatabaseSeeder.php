<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PlanSeeder::class);
        $this->call(MedicalConditionSeeder::class);

        User::factory()->create([
            'name' => 'مدیر پلتفرم',
            'email' => 'admin@platform.local',
            'is_super_admin' => true,
        ]);
    }
}
