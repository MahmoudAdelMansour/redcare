<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'avatar' => $this->faker->word(),
            'code' => $this->faker->word(),
            'goals' => $this->faker->word(),
            'main_responsibilities' => $this->faker->word(),
            'user_id' => User::factory(),
        ];
    }
}
