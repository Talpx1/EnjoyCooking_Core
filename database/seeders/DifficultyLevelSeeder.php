<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\DifficultyLevels;
use App\Models\DifficultyLevel;

class DifficultyLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (DifficultyLevels::cases() as $difficulty_level) DifficultyLevel::create(["name" => $difficulty_level->normalizedName()]);
    }
}
