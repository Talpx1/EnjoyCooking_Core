<?php

namespace Tests\Unit;

use App\Models\DifficultyLevel;
use App\Models\Execution;
use App\Models\Ingredient;
use App\Models\Rating;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_rating_id_is_required(){
        $this->expectException(QueryException::class);
        Rating::factory()->create(['rating'=>null]);
    }

    /**
     * @test
     */
    public function test_rateable_id_is_required(){
        $this->expectException(QueryException::class);
        Rating::factory()->create(['rateable_id'=>null]);
    }

    /**
     * @test
     */
    public function test_rateable_type_is_required(){
        $this->expectException(QueryException::class);
        Rating::factory()->create(['rateable_type'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_is_required(){
        $this->expectException(QueryException::class);
        Rating::factory()->create(['user_id'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_must_exists_in_users_table(){
        $user = User::factory()->create();
        Rating::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('ratings', ['user_id'=>$user->id]);

        $this->expectException(QueryException::class);
        Rating::factory()->create(['user_id' => 111]);
        $this->assertDatabaseMissing('ratings', ['user_id'=>111]);
    }

    /**
     * @test
     */
    public function test_rating_gets_deleted_if_user_gets_deleted(){
        $user = User::factory()->create();
        $rating = Rating::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('ratings', ['user_id'=>$user->id]);

        $user->delete();
        $this->assertModelMissing($user);

        $this->assertDatabaseMissing('ratings', ['user_id'=>$user->id]);

        $this->assertModelMissing($rating);
    }

    /**
     * @test
     */
    public function test_rating_belongs_to_user(){
        $user = User::factory()->create();
        $rating = Rating::factory()->create(['user_id' => $user->id]);
        $this->assertNotNull($rating->user);
        $this->assertInstanceOf(User::class, $rating->user);
        $this->assertEquals($user->id, $rating->user->id);
    }

    /**
     * @test
     */
    public function test_morphs_to_rateable(){
        $recipe = Recipe::factory()->create();
        $execution = Execution::factory()->create();

        $rating1 = Rating::factory()->create(['rateable_id' => $recipe->id,'rateable_type' => $recipe::class]);
        $rating2 = Rating::factory()->create(['rateable_id' => $execution->id,'rateable_type' => $execution::class]);

        $this->assertNotNull($rating1->rateable);
        $this->assertInstanceOf($recipe::class, $rating1->rateable);
        $this->assertEquals($recipe->id, $rating1->rateable->id);

        $this->assertNotNull($rating2->rateable);
        $this->assertInstanceOf($execution::class, $rating2->rateable);
        $this->assertEquals($execution->id, $rating2->rateable->id);
    }

    /**
     * @test
     */
    public function test_combination_of_rateable_id_rateable_type_user_id_must_be_unique(){
        $recipe = Recipe::factory()->create();
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        Rating::factory()->create(['rateable_id' => $recipe->id,'rateable_type' => $recipe::class, 'user_id' => $user->id]);
        $this->assertDatabaseHas('ratings', ['rateable_id' => $recipe->id,'rateable_type' => $recipe::class, 'user_id' => $user->id]);

        Rating::factory()->create(['rateable_id' => $recipe->id,'rateable_type' => $recipe::class, 'user_id' => $user2->id]);
        $this->assertDatabaseHas('ratings', ['rateable_id' => $recipe->id,'rateable_type' => $recipe::class, 'user_id' => $user2->id]);

        Rating::factory()->create(['rateable_id' => Execution::factory()->create()->id,'rateable_type' => Execution::class, 'user_id' => $user->id]);

        $this->expectException(QueryException::class);
        Rating::factory()->create(['rateable_id' => $recipe->id,'rateable_type' => $recipe::class, 'user_id' => $user->id]);
    }
}
