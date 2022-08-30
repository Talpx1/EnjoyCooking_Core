<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\VisibilityStatuses;
use App\Models\VisibilityStatus;

class VisibilityStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (VisibilityStatuses::cases() as $visibility_status) VisibilityStatus::create(["name" => $visibility_status->normalizedName()]);
    }
}
