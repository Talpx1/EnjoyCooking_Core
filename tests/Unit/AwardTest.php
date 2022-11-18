<?php

namespace Tests\Unit;

use App\Models\Award;
use App\Models\Awardable;
use App\Models\Comment;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AwardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        Award::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        Award::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        Award::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_icon_path_is_required(){
        $this->expectException(QueryException::class);
        Award::factory()->create(['icon_path'=>null]);
    }

    /**
     * @test
     */
    public function test_icon_path_must_be_unique(){
        Award::factory()->create(['icon_path'=>'test']);
        $this->expectException(QueryException::class);
        Award::factory()->create(['icon_path'=>'test']);
    }

    /**
     * @test
     */
    public function test_price_is_nullable(){
        Award::factory()->create(['name'=>'test', 'price'=>null]);
        $this->assertDatabaseHas('awards', ['name'=>'test', 'price'=>null]);
    }

    /**
     * @test
     */
    public function test_award_is_morphed_by_many_recipes(){
        $award = Award::factory()->create(['name' => 'test']);
        $recipes = Recipe::factory(3)->create()->each(fn($recipe) => Awardable::factory()->create(['award_id'=>$award->id, 'awardable_id' => $recipe->id, 'awardable_type' => $recipe::class]));
        $other_recipes = Recipe::factory(5)->create();

        $this->assertNotNull($award->recipes);
        $this->assertCount(3, $award->recipes);
        $this->assertInstanceOf(Collection::class, $award->recipes);

        $award->recipes->each(function($recipe) use ($recipes, $other_recipes){
            $this->assertTrue($recipes->contains($recipe));
            $this->assertFalse($other_recipes->contains($recipe));
        });
    }

    /**
     * @test
     */
    public function test_award_is_morphed_by_many_comments(){
        $award = Award::factory()->create(['name' => 'test']);
        $comments = Comment::factory(3)->create()->each(fn($comment) => Awardable::factory()->create(['award_id'=>$award->id, 'awardable_id' => $comment->id, 'awardable_type' => $comment::class]));
        $other_comments = Comment::factory(5)->create();

        $this->assertNotNull($award->comments);
        $this->assertCount(3, $award->comments);
        $this->assertInstanceOf(Collection::class, $award->comments);

        $award->comments->each(function($comment) use ($comments, $other_comments){
            $this->assertTrue($comments->contains($comment));
            $this->assertFalse($other_comments->contains($comment));
        });
    }

}
