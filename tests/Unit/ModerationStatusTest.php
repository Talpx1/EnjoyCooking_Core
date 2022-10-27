<?php

namespace Tests\Unit;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ModerationStatus;
use Illuminate\Database\QueryException;

class ModerationStatusTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        ModerationStatus::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        ModerationStatus::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        ModerationStatus::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_moderation_status_has_many_recipes(){
        $moderation_status = ModerationStatus::factory()->create();
        $other_moderation_status = ModerationStatus::factory()->create();
        $recipes = Recipe::factory(2)->create(['moderation_status_id' => $moderation_status->id]);
        $other_recipes = Recipe::factory(4)->create(['moderation_status_id' => $other_moderation_status->id]);

        $this->assertNotNull($moderation_status->recipes);
        $this->assertNotNull($other_moderation_status->recipes);

        $this->assertInstanceOf(Collection::class, $moderation_status->recipes);
        $this->assertInstanceOf(Collection::class, $other_moderation_status->recipes);

        $moderation_status->recipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));
        $other_moderation_status->recipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));

        $this->assertCount(2, $moderation_status->recipes);
        $this->assertCount(4, $other_moderation_status->recipes);

        $moderation_status->recipes->each(fn($recipe) => $this->assertTrue($recipes->contains($recipe)));
        $moderation_status->recipes->each(fn($recipe) => $this->assertFalse($other_recipes->contains($recipe)));

        $other_moderation_status->recipes->each(fn($recipe) => $this->assertTrue($other_recipes->contains($recipe)));
        $other_moderation_status->recipes->each(fn($recipe) => $this->assertFalse($recipes->contains($recipe)));
    }
}
