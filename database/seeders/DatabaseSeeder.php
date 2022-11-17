<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VisibilityStatus;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(PermissionRoleSeeder::class);

        $this->call(GenderSeeder::class);
        $this->call(ProfessionGroupSeeder::class);
        $this->call(UserTypeSeeder::class);
        $this->call(UserSeeder::class);

        $this->call(ModerationStatusSeeder::class);
        $this->call(VisibilityStatusSeeder::class);

        $this->call(CourseSeeder::class);
        $this->call(DifficultyLevelSeeder::class);
        $this->call(MeasureUnitSeeder::class);
    }
}
