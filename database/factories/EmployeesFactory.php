<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class EmployeesFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => Carbon::now(),
            'password' => bcrypt($this->faker->password()),
            'remember_token' => Str::random(10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'job_title' => $this->faker->word(),
            'job_description' => $this->faker->text(),
            'employee_id' => $this->faker->word(),
            'department_id' => $this->faker->randomNumber(1,3),
            'status' => $this->faker->randomElement(User::STATUS),
            'extension_number' => $this->faker->word(),
            'role' => $this->faker->randomElement(array_keys(User::ROLES)),

        ];
    }
}
