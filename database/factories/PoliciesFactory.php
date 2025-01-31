<?php

namespace Database\Factories;

use App\Models\Policies;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PoliciesFactory extends Factory
{
    protected $model = Policies::class;

    public function definition(): array
    {
        return [
            'policy_name' => $this->faker->word,
            'policy_number' => $this->faker->word,
            'description' => $this->faker->sentence,
            'purpose' => $this->faker->sentence,
            'version' => $this->faker->word,
            'details' => $this->faker->sentence,
            'link' => $this->faker->url,
            'attachment' => $this->faker->word,
            'status' => $this->faker->boolean,
            'approval' => $this->faker->boolean,
            'compliance' => $this->faker->boolean,
            'notes' => $this->faker->sentence,
            'user_id' => User::factory(1)->create()->first()->id,

        ];
    }
}
