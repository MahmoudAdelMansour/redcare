<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Factories\EmployeesFactory;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
       EmployeesFactory::new()->count(10)->create();
    }
}
