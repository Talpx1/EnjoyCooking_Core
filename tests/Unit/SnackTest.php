<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Recipe;
use App\Models\Snack;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SnackTest extends TestCase{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_title_is_required(){
        $this->expectException(QueryException::class);
        Snack::factory()->create(['title'=>null]);
    }

    /**
     * @test
     */
    public function test_description_is_nullable(){
        Snack::factory()->create(['title' => 'test', 'description'=>null]);
        $this->assertDatabaseHas('snacks', ['title'=>'test', 'description'=>null]);
    }

    /**
     * @test
     */
    public function test_video_path_is_required(){
        $this->expectException(QueryException::class);
        Snack::factory()->create(['video_path'=>null]);
    }

    /**
     * @test
     */
    public function test_video_path_must_be_unique(){
        Snack::factory()->create(['video_path'=>'test']);
        $this->expectException(QueryException::class);
        Snack::factory()->create(['video_path'=>'test']);
    }

    /**
     * @test
     */
    public function test_user_id_is_required(){
        $this->expectException(QueryException::class);
        Snack::factory()->create(['user_id'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_must_exists_in_users_table(){
        $user = User::factory()->create();
        Snack::factory()->create(['title' => 'test', 'user_id' => $user->id]);
        $this->assertDatabaseHas('snacks', ['title'=>'test', 'user_id'=>$user->id]);

        $this->expectException(QueryException::class);
        Snack::factory()->create(['title' => 'test 2', 'user_id' => 111]);
    }

    /**
     * @test
     */
    public function test_if_user_gets_deleted_its_snacks_get_deleted(){
        $user = User::factory()->create(['first_name'=>'test']);
        $snacks = Snack::factory(3)->create(['user_id' => $user->id]);
        $other_snacks = Snack::factory(3)->create(['user_id' => User::factory()->create()->id]);

        $this->assertCount(3, $user->snacks);

        $user->delete();
        $this->assertModelMissing($user);
        $this->assertDatabaseMissing('users', ['first_name'=>'test']);
        $this->assertDatabaseMissing('snacks', ['user_id'=>$user->id]);
        $this->assertDatabaseCount(Snack::class, 3);

        $snacks->each(fn($snack) => $this->assertModelMissing($snack));
        $other_snacks->each(fn($snack) => $this->assertModelExists($snack));
    }

    /**
     * @test
     */
    public function test_recipe_id_is_nullable(){
        Snack::factory()->create(['title' => 'test', 'recipe_id'=>null]);
        $this->assertDatabaseHas('snacks', ['title'=>'test', 'recipe_id'=>null]);
    }

    /**
     * @test
     */
    public function test_recipe_id_must_exists_in_recipes_table(){
        $recipe = Recipe::factory()->create();
        Snack::factory()->create(['title' => 'test', 'recipe_id' => $recipe->id]);
        $this->assertDatabaseHas('snacks', ['title'=>'test', 'recipe_id'=>$recipe->id]);

        $this->expectException(QueryException::class);
        Snack::factory()->create(['title' => 'test 2', 'recipe_id' => 111]);
    }

    /**
     * @test
     */
    public function test_recipe_id_gets_set_as_null_if_recipe_gets_deleted(){
        $recipe = Recipe::factory()->create(['title' => 'recipe_test']);
        $snack = Snack::factory()->create(['title' => 'snack_test', 'recipe_id' => $recipe->id]);

        $this->assertDatabaseHas('recipes', ['title'=>'recipe_test']);
        $this->assertDatabaseHas('snacks', ['title'=>'snack_test', 'recipe_id'=>$recipe->id]);
        $this->assertEquals($recipe->id, $snack->recipe_id);

        $recipe->delete();

        $this->assertModelMissing($recipe);
        $this->assertModelExists($snack);

        $this->assertDatabaseMissing('recipes', ['title'=>'recipe_test']);
        $this->assertDatabaseMissing('snacks', ['title'=>'snack_test', 'recipe_id'=>$recipe->id]);

        $this->assertNull($snack->fresh()->recipe_id);
        $this->assertDatabaseHas('snacks', ['title'=>'snack_test', 'recipe_id'=>null]);
    }

    /**
     * @test
     */
    public function test_when_snack_gets_deleted_its_related_records_in_taggables_table_get_deleted(){
        $snack = Snack::factory()->create();
        $tags = Tag::factory(3)->create()->each(function($tag) use ($snack){
            Taggable::factory()->create(['tag_id'=>$tag->id, 'taggable_id'=>$snack->id, 'taggable_type'=>$snack::class]);
            $this->assertDatabaseHas('taggables', ['tag_id'=>$tag->id, 'taggable_id'=>$snack->id, 'taggable_type'=>$snack::class]);
        });

        $snack->delete();
        $this->assertModelMissing($snack);
        $this->assertDatabaseMissing('snacks', ['title'=>$snack->id]);

        $tags->each(function($tag) use ($snack){
            $this->assertDatabaseMissing('taggables', ['tag_id'=>$tag->id, 'taggable_id'=>$snack->id, 'taggable_type'=>$snack::class]);
        });
    }

    /**
     * @test
     */
    public function test_snack_morphs_to_many_tags(){
        $snack = Snack::factory()->create(['title' => 'test']);
        $snack_tags = Tag::factory(2)->create()->each(fn($tag) => Taggable::factory()->create(['taggable_id' => $snack->id, 'tag_id' => $tag->id, 'taggable_type' => $snack::class]));
        $other_snack_tags = Tag::factory(4)->create()->each(fn($tag) => Taggable::factory()->create(['taggable_id' => Snack::factory()->create()->id, 'tag_id' => $tag->id, 'taggable_type' => $snack::class]));

        $this->assertNotNull($snack->tags);

        $this->assertInstanceOf(Collection::class, $snack->tags);
        $snack->tags->each(fn($tag) => $this->assertInstanceOf(Tag::class, $tag));

        $this->assertCount(2, $snack->tags);

        $snack->tags->each(fn($tag) => $this->assertTrue($snack_tags->contains($tag)));
        $snack->tags->each(fn($tag) => $this->assertFalse($other_snack_tags->contains($tag)));
    }

    /**
     * @test
     */
    public function test_when_snack_gets_deleted_its_related_records_in_comments_table_get_deleted(){
        $snack = Snack::factory()->create();
        $comments = Comment::factory(3)->create(['commentable_id'=>$snack->id, 'commentable_type'=>$snack::class]);

        $snack->delete();
        $this->assertModelMissing($snack);
        $this->assertDatabaseMissing('snacks', ['title'=>$snack->id]);

        $comments->each(function($tag) use ($snack){
            $this->assertDatabaseMissing('comments', ['tag_id'=>$tag->id, 'commentable_id'=>$snack->id, 'commentable_type'=>$snack::class]);
        });
    }

    /**
     * @test
     */
    public function test_snack_morphs_many_comments(){
        $snack = Snack::factory()->create(['title' => 'test']);
        $snack_comments = Comment::factory(2)->create(['commentable_id' => $snack->id, 'commentable_type' => $snack::class]);
        $other_snack_comments = Comment::factory(4)->create(['commentable_id'=>Snack::factory()->create()->id, 'commentable_type' => $snack::class]);

        $this->assertNotNull($snack->comments);

        $this->assertInstanceOf(Collection::class, $snack->comments);
        $snack->comments->each(fn($comment) => $this->assertInstanceOf(Comment::class, $comment));

        $this->assertCount(2, $snack->comments);

        $snack->comments->each(fn($comment) => $this->assertTrue($snack_comments->contains($comment)));
        $snack->comments->each(fn($comment) => $this->assertFalse($other_snack_comments->contains($comment)));
    }

    /**
     * @test
     */
    public function test_snack_belongs_to_user(){
        $user = User::factory()->create();
        $snack = Snack::factory()->create(['user_id' => $user->id]);
        $this->assertNotNull($snack->user);
        $this->assertInstanceOf(User::class, $snack->user);
        $this->assertEquals($user->id, $snack->user->id);
    }

    /**
     * @test
     */
    public function test_when_snack_gets_deleted_its_related_records_in_likes_table_get_deleted(){
        $snack = Recipe::factory()->create();
        $likes = collect([
            Like::factory()->create(['likeable_id'=>$snack->id, 'likeable_type'=>$snack::class, 'user_id'=>User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>$snack->id, 'likeable_type'=>$snack::class, 'user_id'=>User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>$snack->id, 'likeable_type'=>$snack::class, 'user_id'=>User::factory()->create()->id]),
        ]);

        $snack->delete();
        $this->assertModelMissing($snack);
        $this->assertDatabaseMissing('snacks', ['title'=>$snack->id]);
        $this->assertDatabaseMissing('likes', ['likeable_id'=>$snack->id, 'likeable_type'=>$snack::class]);

        $likes->each(fn($like) => $this->assertModelMissing($like));
    }

    /**
     * @test
     */
    public function test_snack_morphs_many_likes(){
        $snack = Recipe::factory()->create(['title' => 'test']);
        $snack_likes = collect([
            Like::factory()->create(['likeable_id' => $snack->id, 'likeable_type' => $snack::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id' => $snack->id, 'likeable_type' => $snack::class, 'user_id' => User::factory()->create()->id]),
        ]);
        $other_snack_likes = collect([
            Like::factory()->create(['likeable_id'=>Recipe::factory()->create()->id, 'likeable_type' => $snack::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>Recipe::factory()->create()->id, 'likeable_type' => $snack::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>Recipe::factory()->create()->id, 'likeable_type' => $snack::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>Recipe::factory()->create()->id, 'likeable_type' => $snack::class, 'user_id' => User::factory()->create()->id]),
        ]);

        $this->assertNotNull($snack->likes);

        $this->assertInstanceOf(Collection::class, $snack->likes);
        $snack->likes->each(fn($like) => $this->assertInstanceOf(Like::class, $like));

        $this->assertCount(2, $snack->likes);

        $snack_likes->each(fn($like) => $this->assertTrue($snack->likes->contains($like)));
        $other_snack_likes->each(fn($like) => $this->assertFalse($snack->likes->contains($like)));
    }

    /**
     * @test
     */
    public function test_snack_morphs_many_favorites(){
        $snack = Snack::factory()->create(['title' => 'test']);
        $snack_favorites = collect([
            Favorite::factory()->create(['favoritable_id' => $snack->id, 'favoritable_type' => $snack::class, 'user_id' => User::factory()->create()->id]),
            Favorite::factory()->create(['favoritable_id' => $snack->id, 'favoritable_type' => $snack::class, 'user_id' => User::factory()->create()->id]),
        ]);
        $other_snack_favorites = collect([
            Favorite::factory()->create(['favoritable_id'=>Snack::factory()->create()->id, 'favoritable_type' => $snack::class, 'user_id' => User::factory()->create()->id]),
            Favorite::factory()->create(['favoritable_id'=>Snack::factory()->create()->id, 'favoritable_type' => $snack::class, 'user_id' => User::factory()->create()->id]),
            Favorite::factory()->create(['favoritable_id'=>Snack::factory()->create()->id, 'favoritable_type' => $snack::class, 'user_id' => User::factory()->create()->id]),
            Favorite::factory()->create(['favoritable_id'=>Snack::factory()->create()->id, 'favoritable_type' => $snack::class, 'user_id' => User::factory()->create()->id]),
        ]);

        $this->assertNotNull($snack->favorites);

        $this->assertInstanceOf(Collection::class, $snack->favorites);
        $snack->favorites->each(fn($favorite) => $this->assertInstanceOf(Favorite::class, $favorite));

        $this->assertCount(2, $snack->favorites);

        $snack_favorites->each(fn($favorite) => $this->assertTrue($snack->favorites->contains($favorite)));
        $other_snack_favorites->each(fn($favorite) => $this->assertFalse($snack->favorites->contains($favorite)));
    }

    /**
     * @test
     */
    public function test_when_snack_gets_deleted_its_related_records_in_favorites_table_get_deleted(){
        $snack = Snack::factory()->create();
        $favorites = collect([
            Favorite::factory()->create(['favoritable_id'=>$snack->id, 'favoritable_type'=>$snack::class, 'user_id'=>User::factory()->create()->id]),
            Favorite::factory()->create(['favoritable_id'=>$snack->id, 'favoritable_type'=>$snack::class, 'user_id'=>User::factory()->create()->id]),
            Favorite::factory()->create(['favoritable_id'=>$snack->id, 'favoritable_type'=>$snack::class, 'user_id'=>User::factory()->create()->id]),
        ]);

        $snack->delete();
        $this->assertModelMissing($snack);
        $this->assertDatabaseMissing('snacks', ['title'=>$snack->id]);
        $this->assertDatabaseMissing('favorites', ['favoritable_id'=>$snack->id, 'favoritable_type'=>$snack::class]);

        $favorites->each(fn($favorite) => $this->assertModelMissing($favorite));
    }

}
