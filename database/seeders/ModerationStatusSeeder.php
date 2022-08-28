<?php

namespace Database\Seeders;

use App\Enums\ModerationStatuses;
use App\Models\ModerationStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModerationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         foreach (ModerationStatuses::cases() as $role) ModerationStatus::create(["name" => $role->value]);
    }
}
