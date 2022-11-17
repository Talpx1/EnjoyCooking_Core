<?php

namespace Database\Seeders;

use App\Enums\MeasureUnits;
use App\Models\MeasureUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MeasureUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (MeasureUnits::cases() as $measure_unit) MeasureUnit::create([
            "name" => $measure_unit->normalizedName(),
            "abbreviation" => $measure_unit->abbreviation(),
            "description" => $measure_unit->description(),
        ]);
    }
}
