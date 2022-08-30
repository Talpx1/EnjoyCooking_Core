<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\ProfessionGroups;
use App\Models\ProfessionGroup;

class ProfessionGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (ProfessionGroups::cases() as $profession_group) ProfessionGroup::create(["name" => $profession_group->normalizedName()]);
    }
}
