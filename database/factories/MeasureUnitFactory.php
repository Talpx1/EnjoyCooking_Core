<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeasureUnit>
 */
class MeasureUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->words(rand(1,5), true),
            'abbreviation' => $this->faker->unique()->word,
            'description' => $this->faker->paragraph,
        ];
    }
}
