<?php

namespace Database\Factories;

use App\Models\Execution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExecutionVideo>
 */
class ExecutionVideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'path' => $this->faker->unique()->url,
            'execution_id' => Execution::getRandomOrCreate()->id
        ];
    }
}
