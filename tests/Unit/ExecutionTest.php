<?php

namespace Tests\Unit;

use App\Models\Award;
use App\Models\Awardable;
use App\Models\Comment;
use App\Models\Execution;
use App\Models\ExecutionImage;
use App\Models\ExecutionVideo;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Rating;
use App\Models\Recipe;
use App\Models\Repost;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutionTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     */
    public function test_recipe_id_is_nullable(){
        Execution::factory()->create(['recipe_id'=>null]);
        $this->assertDatabaseHas('executions', ['recipe_id'=>null]);
    }

    /**
     * @test
     */
    public function test_recipe_id_must_exists_in_recipes_table(){
        $recipe = Recipe::factory()->create();
        Execution::factory()->create(['recipe_id' => $recipe->id]);
        $this->assertDatabaseHas('executions', ['recipe_id'=>$recipe->id]);

        $this->expectException(QueryException::class);
        Execution::factory()->create(['recipe_id' => 111]);
        $this->assertDatabaseMissing('executions', ['recipe_id'=>111]);
    }

    /**
     * @test
     */
    public function test_execution_belongs_to_user(){
        $user = User::factory()->create();
        $execution = Execution::factory()->create(['user_id' => $user->id]);
        $this->assertNotNull($execution->user);
        $this->assertInstanceOf(User::class, $execution->user);
        $this->assertEquals($execution->user->id, $user->id);
    }

    /**
     * @test
     */
    public function test_execution_belongs_to_recipe(){
        $recipe = Recipe::factory()->create();
        $execution = Execution::factory()->create(['recipe_id' => $recipe->id]);
        $this->assertNotNull($execution->recipe);
        $this->assertInstanceOf(Recipe::class, $execution->recipe);
        $this->assertEquals($execution->recipe->id, $recipe->id);
    }

    /**
     * @test
     */
    public function test_user_id_is_required(){
        $this->expectException(QueryException::class);
        Execution::factory()->create(['user_id'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_must_exists_in_users_table(){
        $user = User::factory()->create();
        Execution::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('executions', ['user_id'=>$user->id]);

        $this->expectException(QueryException::class);
        Execution::factory()->create(['user_id' => 111]);
    }

    /**
     * @test
     */
    public function test_executions_get_deleted_if_related_user_gets_deleted(){
        $user = User::factory()->create(['username' => 'user_test']);
        $other_user = User::factory()->create(['username' => 'other_user_test']);
        $execution = Execution::factory()->create(['user_id' => $user->id]);
        $execution2 = Execution::factory()->create(['user_id' => $user->id]);
        $other_execution = Execution::factory()->create(['user_id' => $other_user->id]);

        $this->assertDatabaseHas('users', ['username'=>'user_test']);
        $this->assertDatabaseHas('users', ['username'=>'other_user_test']);
        $this->assertDatabaseHas('executions', ['user_id' => $user->id]);
        $this->assertDatabaseHas('executions', ['user_id' => $user->id]);
        $this->assertDatabaseHas('executions', ['user_id' => $other_user->id]);

        $user->delete();

        $this->assertDatabaseMissing('users', ['username'=>'user_test']);
        $this->assertDatabaseMissing('executions', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('executions', ['user_id' => $user->id]);

        $this->assertModelMissing($user);
        $this->assertModelMissing($execution);
        $this->assertModelMissing($execution2);

        $this->assertDatabaseHas('users', ['username'=>'other_user_test']);
        $this->assertDatabaseHas('executions', ['user_id' => $other_user->id]);

        $this->assertModelExists($other_execution);
    }

    /**
     * @test
     */
    public function test_recipe_id_gets_set_as_null_if_recipe_gets_deleted(){
        $recipe = Recipe::factory()->create(['title' => 'recipe_test']);
        $execution = Execution::factory()->create(['recipe_id' => $recipe->id]);

        $this->assertDatabaseHas('recipes', ['title'=>'recipe_test']);
        $this->assertDatabaseHas('executions', ['recipe_id'=>$recipe->id]);
        $this->assertEquals($recipe->id, $execution->recipe_id);

        $recipe->delete();

        $this->assertModelMissing($recipe);
        $this->assertModelExists($execution);

        $this->assertDatabaseMissing('recipes', ['title'=>'recipe_test']);
        $this->assertDatabaseMissing('executions', ['recipe_id'=>$recipe->id]);

        $this->assertNull($execution->fresh()->recipe_id);
        $this->assertDatabaseHas('executions', ['recipe_id'=>null]);
    }

    /**
     * @test
     */
    public function test_when_execution_gets_deleted_its_related_records_in_likes_table_get_deleted(){
        $execution = Execution::factory()->create();
        $likes = collect([
            Like::factory()->create(['likeable_id'=>$execution->id, 'likeable_type'=>$execution::class, 'user_id'=>User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>$execution->id, 'likeable_type'=>$execution::class, 'user_id'=>User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>$execution->id, 'likeable_type'=>$execution::class, 'user_id'=>User::factory()->create()->id]),
        ]);

        $execution->delete();
        $this->assertModelMissing($execution);
        $this->assertDatabaseMissing('executions', ['title'=>$execution->id]);
        $this->assertDatabaseMissing('likes', ['likeable_id'=>$execution->id, 'likeable_type'=>$execution::class]);

        $likes->each(fn($like) => $this->assertModelMissing($like));
    }

    /**
     * @test
     */
    public function test_execution_morphs_many_reposts(){
        $execution = Execution::factory()->create();
        $execution_reposts = collect([
            Repost::factory()->create(['repostable_id' => $execution->id, 'repostable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
            Repost::factory()->create(['repostable_id' => $execution->id, 'repostable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
        ]);
        $other_execution_reposts = collect([
            Repost::factory()->create(['repostable_id'=>Execution::factory()->create()->id, 'repostable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
            Repost::factory()->create(['repostable_id'=>Execution::factory()->create()->id, 'repostable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
            Repost::factory()->create(['repostable_id'=>Execution::factory()->create()->id, 'repostable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
            Repost::factory()->create(['repostable_id'=>Execution::factory()->create()->id, 'repostable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
        ]);

        $this->assertNotNull($execution->reposts);

        $this->assertInstanceOf(Collection::class, $execution->reposts);
        $execution->reposts->each(fn($repost) => $this->assertInstanceOf(Repost::class, $repost));

        $this->assertCount(2, $execution->reposts);

        $execution_reposts->each(fn($repost) => $this->assertTrue($execution->reposts->contains($repost)));
        $other_execution_reposts->each(fn($repost) => $this->assertFalse($execution->reposts->contains($repost)));
    }

    /**
     * @test
     */
    public function test_execution_morphs_many_likes(){
        $execution = Execution::factory()->create();
        $execution_likes = collect([
            Like::factory()->create(['likeable_id' => $execution->id, 'likeable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id' => $execution->id, 'likeable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
        ]);
        $other_execution_likes = collect([
            Like::factory()->create(['likeable_id'=>Execution::factory()->create()->id, 'likeable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>Execution::factory()->create()->id, 'likeable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>Execution::factory()->create()->id, 'likeable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>Execution::factory()->create()->id, 'likeable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
        ]);

        $this->assertNotNull($execution->likes);

        $this->assertInstanceOf(Collection::class, $execution->likes);
        $execution->likes->each(fn($like) => $this->assertInstanceOf(Like::class, $like));

        $this->assertCount(2, $execution->likes);

        $execution_likes->each(fn($like) => $this->assertTrue($execution->likes->contains($like)));
        $other_execution_likes->each(fn($like) => $this->assertFalse($execution->likes->contains($like)));
    }

    /**
     * @test
     */
    public function test_execution_morphs_many_favorites(){
        $execution = Execution::factory()->create();
        $execution_favorites = collect([
            Favorite::factory()->create(['favoritable_id' => $execution->id, 'favoritable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
            Favorite::factory()->create(['favoritable_id' => $execution->id, 'favoritable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
        ]);
        $other_execution_favorites = collect([
            Favorite::factory()->create(['favoritable_id'=>Execution::factory()->create()->id, 'favoritable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
            Favorite::factory()->create(['favoritable_id'=>Execution::factory()->create()->id, 'favoritable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
            Favorite::factory()->create(['favoritable_id'=>Execution::factory()->create()->id, 'favoritable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
            Favorite::factory()->create(['favoritable_id'=>Execution::factory()->create()->id, 'favoritable_type' => $execution::class, 'user_id' => User::factory()->create()->id]),
        ]);

        $this->assertNotNull($execution->favorites);

        $this->assertInstanceOf(Collection::class, $execution->favorites);
        $execution->favorites->each(fn($favorite) => $this->assertInstanceOf(Favorite::class, $favorite));

        $this->assertCount(2, $execution->favorites);

        $execution_favorites->each(fn($favorite) => $this->assertTrue($execution->favorites->contains($favorite)));
        $other_execution_favorites->each(fn($favorite) => $this->assertFalse($execution->favorites->contains($favorite)));
    }

    /**
     * @test
     */
    public function test_when_execution_gets_deleted_its_related_records_in_favorites_table_get_deleted(){
        $execution = Execution::factory()->create();
        $favorites = collect([
            Favorite::factory()->create(['favoritable_id'=>$execution->id, 'favoritable_type'=>$execution::class, 'user_id'=>User::factory()->create()->id]),
            Favorite::factory()->create(['favoritable_id'=>$execution->id, 'favoritable_type'=>$execution::class, 'user_id'=>User::factory()->create()->id]),
            Favorite::factory()->create(['favoritable_id'=>$execution->id, 'favoritable_type'=>$execution::class, 'user_id'=>User::factory()->create()->id]),
        ]);

        $execution->delete();
        $this->assertModelMissing($execution);
        $this->assertDatabaseMissing('executions', ['title'=>$execution->id]);
        $this->assertDatabaseMissing('favorites', ['favoritable_id'=>$execution->id, 'favoritable_type'=>$execution::class]);

        $favorites->each(fn($favorite) => $this->assertModelMissing($favorite));
    }

    /**
     * @test
     */
    public function test_execution_morphs_many_ratings(){
        $execution = Execution::factory()->create();
        $execution_ratings = collect([
            Rating::factory()->create(['rateable_id'=>$execution->id, 'rateable_type'=>Execution::class, 'user_id' => User::factory()->create()->id]),
            Rating::factory()->create(['rateable_id'=>$execution->id, 'rateable_type'=>Execution::class, 'user_id' => User::factory()->create()->id])
        ]);
        $other_execution_ratings = collect([
            Rating::factory()->create(['rateable_id'=>Execution::factory()->create()->id, 'rateable_type'=>Execution::class, 'user_id' => User::factory()->create()->id]),
            Rating::factory()->create(['rateable_id'=>Execution::factory()->create()->id, 'rateable_type'=>Execution::class, 'user_id' => User::factory()->create()->id]),
            Rating::factory()->create(['rateable_id'=>Execution::factory()->create()->id, 'rateable_type'=>Execution::class, 'user_id' => User::factory()->create()->id]),
            Rating::factory()->create(['rateable_id'=>Execution::factory()->create()->id, 'rateable_type'=>Execution::class, 'user_id' => User::factory()->create()->id])
        ]);

        $this->assertNotNull($execution->ratings);

        $this->assertInstanceOf(Collection::class, $execution->ratings);
        $execution->ratings->each(fn($rating) => $this->assertInstanceOf(Rating::class, $rating));

        $this->assertCount(2, $execution->ratings);

        $execution_ratings->each(fn($rating) => $this->assertTrue($execution->ratings->contains($rating)));
        $other_execution_ratings->each(fn($rating) => $this->assertFalse($execution->ratings->contains($rating)));
    }

    /**
     * @test
     */
    public function test_execution_morphs_to_many_awards(){
        $execution = Execution::factory()->create();
        $execution_awards = Award::factory(2)->create()->each(fn($award) => Awardable::factory()->create(['awardable_id' => $execution->id, 'award_id' => $award->id, 'awardable_type' => $execution::class]));
        $other_execution_awards = Award::factory(4)->create()->each(fn($award) => Awardable::factory()->create(['awardable_id' => Execution::factory()->create()->id, 'award_id' => $award->id, 'awardable_type' => $execution::class]));

        $this->assertNotNull($execution->awards);

        $this->assertInstanceOf(Collection::class, $execution->awards);
        $execution->awards->each(fn($award) => $this->assertInstanceOf(Award::class, $award));

        $this->assertCount(2, $execution->awards);

        $execution->awards->each(fn($award) => $this->assertTrue($execution_awards->contains($award)));
        $execution->awards->each(fn($award) => $this->assertFalse($other_execution_awards->contains($award)));
    }

    /**
     * @test
     */
    public function test_when_execution_gets_deleted_its_related_records_in_awardables_table_get_deleted(){
        $execution = Execution::factory()->create();
        $awards = Award::factory(3)->create()->each(function($award) use ($execution){
            Awardable::factory()->create(['award_id'=>$award->id, 'awardable_id'=>$execution->id, 'awardable_type'=>$execution::class]);
            $this->assertDatabaseHas('awardables', ['award_id'=>$award->id, 'awardable_id'=>$execution->id, 'awardable_type'=>$execution::class]);
        });

        $execution->delete();
        $this->assertModelMissing($execution);
        $this->assertDatabaseMissing('executions', ['title'=>$execution->id]);

        $awards->each(function($award) use ($execution){
            $this->assertDatabaseMissing('awardables', ['award_id'=>$award->id, 'awardable_id'=>$execution->id, 'awardable_type'=>$execution::class]);
        });
    }

    /**
     * @test
     */
    public function test_when_execution_gets_deleted_its_related_records_in_reposts_table_get_deleted(){
        $execution = Execution::factory()->create();
        $reposts = collect([
            Repost::factory()->create(['repostable_id'=>$execution->id, 'repostable_type'=>$execution::class, 'user_id'=>User::factory()->create()->id]),
            Repost::factory()->create(['repostable_id'=>$execution->id, 'repostable_type'=>$execution::class, 'user_id'=>User::factory()->create()->id]),
            Repost::factory()->create(['repostable_id'=>$execution->id, 'repostable_type'=>$execution::class, 'user_id'=>User::factory()->create()->id]),
        ]);


        $execution->delete();
        $this->assertModelMissing($execution);
        $this->assertDatabaseMissing('executions', ['title'=>$execution->id]);

        $reposts->each(function($repost) use ($execution){
            $this->assertDatabaseMissing('reposts', ['repostable_id'=>$execution->id, 'repostable_type'=>$execution::class]);
        });
    }

    /**
     * @test
     */
    public function test_when_execution_gets_deleted_its_related_records_in_comments_table_get_deleted(){
        $execution = Execution::factory()->create();
        $comments = Comment::factory(3)->create(['commentable_id'=>$execution->id, 'commentable_type'=>$execution::class]);

        $execution->delete();
        $this->assertModelMissing($execution);
        $this->assertDatabaseMissing('executions', ['title'=>$execution->id]);
        $this->assertDatabaseMissing('comments', ['commentable_id'=>$execution->id, 'commentable_type'=>$execution::class]);

        $comments->each(fn($comment) => $this->assertModelMissing($comment));
    }

    /**
     * @test
     */
    public function test_when_execution_gets_deleted_its_related_records_in_ratings_table_get_deleted(){
        $execution = Execution::factory()->create();
        $ratings = collect([
            Rating::factory()->create(['rateable_id'=>$execution->id, 'rateable_type'=>$execution::class, 'user_id'=>User::factory()->create()->id]),
            Rating::factory()->create(['rateable_id'=>$execution->id, 'rateable_type'=>$execution::class, 'user_id'=>User::factory()->create()->id]),
            Rating::factory()->create(['rateable_id'=>$execution->id, 'rateable_type'=>$execution::class, 'user_id'=>User::factory()->create()->id]),
        ]);

        $execution->delete();
        $this->assertModelMissing($execution);
        $this->assertDatabaseMissing('executions', ['title'=>$execution->id]);
        $this->assertDatabaseMissing('ratings', ['rateable_id'=>$execution->id, 'rateable_type'=>$execution::class]);

        $ratings->each(fn($rating) => $this->assertModelMissing($rating));
    }

    /**
     * @test
     */
    public function test_execution_morphs_many_comments(){
        $execution = Execution::factory()->create();
        $execution_comments = Comment::factory(2)->create(['commentable_id' => $execution->id, 'commentable_type' => $execution::class]);
        $other_execution_comments = Comment::factory(4)->create(['commentable_id'=>Execution::factory()->create()->id, 'commentable_type' => $execution::class]);

        $this->assertNotNull($execution->comments);

        $this->assertInstanceOf(Collection::class, $execution->comments);
        $execution->comments->each(fn($comment) => $this->assertInstanceOf(Comment::class, $comment));

        $this->assertCount(2, $execution->comments);

        $execution->comments->each(fn($comment) => $this->assertTrue($execution_comments->contains($comment)));
        $execution->comments->each(fn($comment) => $this->assertFalse($other_execution_comments->contains($comment)));
    }

    /**
     * @test
     */
    public function test_execution_has_many_execution_images(){
        $execution = Execution::factory()->create();
        $execution_images = ExecutionImage::factory(2)->create(['execution_id' => $execution->id]);
        $other_execution_images = ExecutionImage::factory(4)->create(['execution_id'=>Execution::factory()->create()->id]);

        $this->assertNotNull($execution->images);

        $this->assertInstanceOf(Collection::class, $execution->images);
        $execution->images->each(fn($image) => $this->assertInstanceOf(ExecutionImage::class, $image));

        $this->assertCount(2, $execution->images);

        $execution->images->each(fn($image) => $this->assertTrue($execution_images->contains($image)));
        $execution->images->each(fn($image) => $this->assertFalse($other_execution_images->contains($image)));
    }

    /**
     * @test
     */
    public function test_execution_has_many_execution_videos(){
        $execution = Execution::factory()->create();
        $execution_videos = ExecutionVideo::factory(2)->create(['execution_id' => $execution->id]);
        $other_execution_videos = ExecutionVideo::factory(4)->create(['execution_id'=>Execution::factory()->create()->id]);

        $this->assertNotNull($execution->videos);

        $this->assertInstanceOf(Collection::class, $execution->videos);
        $execution->videos->each(fn($video) => $this->assertInstanceOf(ExecutionVideo::class, $video));

        $this->assertCount(2, $execution->videos);

        $execution->videos->each(fn($video) => $this->assertTrue($execution_videos->contains($video)));
        $execution->videos->each(fn($video) => $this->assertFalse($other_execution_videos->contains($video)));
    }
}
