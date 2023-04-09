<?php

namespace Tests\Unit;

use App\Models\Recipe;
use Database\Seeders\ModerationStatusSeeder;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\VisibilityStatus;
use Illuminate\Database\QueryException;

class VisibilityStatusTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;
    protected $seeder = ModerationStatusSeeder::class;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        VisibilityStatus::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        VisibilityStatus::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        VisibilityStatus::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_visibility_status_has_many_recipes(){
        $visibility_status = VisibilityStatus::factory()->create();
        $other_visibility_status = VisibilityStatus::factory()->create();
        $recipes = Recipe::factory(2)->create(['visibility_status_id' => $visibility_status->id]);
        $other_recipes = Recipe::factory(4)->create(['visibility_status_id' => $other_visibility_status->id]);

        $this->assertNotNull($visibility_status->recipes);
        $this->assertNotNull($other_visibility_status->recipes);

        $this->assertInstanceOf(Collection::class, $visibility_status->recipes);
        $this->assertInstanceOf(Collection::class, $other_visibility_status->recipes);

        $visibility_status->recipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));
        $other_visibility_status->recipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));

        $this->assertCount(2, $visibility_status->recipes);
        $this->assertCount(4, $other_visibility_status->recipes);

        $visibility_status->recipes->each(fn($recipe) => $this->assertTrue($recipes->contains($recipe)));
        $visibility_status->recipes->each(fn($recipe) => $this->assertFalse($other_recipes->contains($recipe)));

        $other_visibility_status->recipes->each(fn($recipe) => $this->assertTrue($other_recipes->contains($recipe)));
        $other_visibility_status->recipes->each(fn($recipe) => $this->assertFalse($recipes->contains($recipe)));
    }
}
