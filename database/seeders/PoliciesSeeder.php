<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Policies;
use Illuminate\Database\Seeder;

class PoliciesSeeder extends Seeder
{
    public function run(): void
    {
        Policies::factory()->count(10)
            ->hasAttached(
                Department::factory()->count(10)
            )
            ->create();
    }
}
