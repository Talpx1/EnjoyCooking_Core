<?php

namespace Tests\Unit;

use App\Models\Award;
use App\Models\Awardable;
use App\Models\Comment;
use App\Models\DifficultyLevel;
use App\Models\Execution;
use App\Models\Ingredient;
use App\Models\Like;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_commentable_id_is_required(){
        $this->expectException(QueryException::class);
        Comment::factory()->create(['commentable_id'=>null]);
    }

    /**
     * @test
     */
    public function test_commentable_type_is_required(){
        $this->expectException(QueryException::class);
        Comment::factory()->create(['commentable_type'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_is_required(){
        $this->expectException(QueryException::class);
        Comment::factory()->create(['user_id'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_must_exists_in_users_table(){
        $user = User::factory()->create();
        Comment::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('comments', ['user_id'=>$user->id]);

        $this->expectException(QueryException::class);
        Comment::factory()->create(['user_id' => 111]);
        $this->assertDatabaseMissing('comments', ['user_id'=>111]);
    }

    /**
     * @test
     */
    public function test_comment_gets_deleted_if_user_gets_deleted(){
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('comments', ['user_id'=>$user->id]);

        $user->delete();
        $this->assertModelMissing($user);

        $this->assertDatabaseMissing('comments', ['user_id'=>$user->id]);

        $this->assertModelMissing($comment);
    }

    /**
     * @test
     */
    public function test_comment_belongs_to_user(){
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);
        $this->assertNotNull($comment->user);
        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertEquals($user->id, $comment->user->id);
    }

    /**
     * @test
     */
    public function test_morphs_to_commentable(){
        $recipe = Recipe::factory()->create();
        $execution = Execution::factory()->create();

        $comment1 = Comment::factory()->create(['commentable_id' => $recipe->id,'commentable_type' => $recipe::class]);
        $comment2 = Comment::factory()->create(['commentable_id' => $execution->id,'commentable_type' => $execution::class]);

        $this->assertNotNull($comment1->commentable);
        $this->assertInstanceOf($recipe::class, $comment1->commentable);
        $this->assertEquals($recipe->id, $comment1->commentable->id);

        $this->assertNotNull($comment2->commentable);
        $this->assertInstanceOf($execution::class, $comment2->commentable);
        $this->assertEquals($execution->id, $comment2->commentable->id);
    }

    /**
     * @test
     */
    public function test_combination_of_commentable_id_commentable_type_user_id_and_body_must_be_unique(){
        $recipe = Recipe::factory()->create();
        $recipe2 = Recipe::factory()->create();
        $execution = Execution::factory()->create();
        $execution2 = Execution::factory()->create();
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $body = 'test';
        $body2 = 'test2';


        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class, 'body' => $body]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class]);

        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class, 'body' => $body2]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class]);

        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $execution->id, 'commentable_type' => $execution::class, 'body' => $body]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $execution->id, 'commentable_type' => $execution::class]);

        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $execution->id, 'commentable_type' => $execution::class, 'body' => $body2]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $execution->id, 'commentable_type' => $execution::class]);

        Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class, 'body' => $body]);
        $this->assertDatabaseHas('comments', ['user_id' => $user2->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class]);

        Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class, 'body' => $body2]);
        $this->assertDatabaseHas('comments', ['user_id' => $user2->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class]);

        Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $execution->id, 'commentable_type' => $execution::class, 'body' => $body]);
        $this->assertDatabaseHas('comments', ['user_id' => $user2->id, 'commentable_id' => $execution->id, 'commentable_type' => $execution::class]);

        Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $execution->id, 'commentable_type' => $execution::class, 'body' => $body2]);
        $this->assertDatabaseHas('comments', ['user_id' => $user2->id, 'commentable_id' => $execution->id, 'commentable_type' => $execution::class]);

        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe2->id, 'commentable_type' => $recipe2::class, 'body' => $body]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $recipe2->id, 'commentable_type' => $recipe2::class]);

        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe2->id, 'commentable_type' => $recipe2::class, 'body' => $body2]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $recipe2->id, 'commentable_type' => $recipe2::class]);

        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $execution2->id, 'commentable_type' => $execution2::class, 'body' => $body]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $execution2->id, 'commentable_type' => $execution2::class]);

        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $execution2->id, 'commentable_type' => $execution2::class, 'body' => $body2]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $execution2->id, 'commentable_type' => $execution2::class]);


        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class, 'body' => $body2]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $execution->id, 'commentable_type' => $execution::class, 'body' => $body]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $execution->id, 'commentable_type' => $execution::class, 'body' => $body2]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class, 'body' => $body]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class, 'body' => $body2]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $execution->id, 'commentable_type' => $execution::class, 'body' => $body]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $execution->id, 'commentable_type' => $execution::class, 'body' => $body2]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe2->id, 'commentable_type' => $recipe2::class, 'body' => $body]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe2->id, 'commentable_type' => $recipe2::class, 'body' => $body2]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $execution2->id, 'commentable_type' => $execution2::class, 'body' => $body]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $execution2->id, 'commentable_type' => $execution2::class, 'body' => $body2]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }
    }

    /**
     * @test
     */
    public function test_comment_morphs_to_many_awards(){
        $comment = Comment::factory()->create(['body' => 'test']);
        $comment_awards = Award::factory(2)->create()->each(fn($award) => Awardable::factory()->create(['awardable_id' => $comment->id, 'award_id' => $award->id, 'awardable_type' => $comment::class]));
        $other_comment_awards = Award::factory(4)->create()->each(fn($award) => Awardable::factory()->create(['awardable_id' => Comment::factory()->create()->id, 'award_id' => $award->id, 'awardable_type' => $comment::class]));

        $this->assertNotNull($comment->awards);

        $this->assertInstanceOf(Collection::class, $comment->awards);
        $comment->awards->each(fn($award) => $this->assertInstanceOf(Award::class, $award));

        $this->assertCount(2, $comment->awards);

        $comment->awards->each(fn($award) => $this->assertTrue($comment_awards->contains($award)));
        $comment->awards->each(fn($award) => $this->assertFalse($other_comment_awards->contains($award)));
    }

    /**
     * @test
     */
    public function test_when_comment_gets_deleted_its_related_records_in_awardables_table_get_deleted(){
        $comment = Comment::factory()->create();
        $awards = Award::factory(3)->create()->each(function($award) use ($comment){
            Awardable::factory()->create(['award_id'=>$award->id, 'awardable_id'=>$comment->id, 'awardable_type'=>$comment::class]);
            $this->assertDatabaseHas('awardables', ['award_id'=>$award->id, 'awardable_id'=>$comment->id, 'awardable_type'=>$comment::class]);
        });

        $comment->delete();
        $this->assertModelMissing($comment);
        $this->assertDatabaseMissing('comments', ['body'=>$comment->id]);

        $awards->each(function($award) use ($comment){
            $this->assertDatabaseMissing('awardables', ['award_id'=>$award->id, 'awardable_id'=>$comment->id, 'taggable_type'=>$comment::class]);
        });
    }

    /**
     * @test
     */
    public function test_comment_morphs_many_likes(){
        $comment = Comment::factory()->create(['body' => 'test']);
        $comment_likes = collect([
            Like::factory()->create(['likeable_id' => $comment->id, 'likeable_type' => $comment::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id' => $comment->id, 'likeable_type' => $comment::class, 'user_id' => User::factory()->create()->id]),
        ]);
        $other_comment_likes = collect([
            Like::factory()->create(['likeable_id'=>Comment::factory()->create()->id, 'likeable_type' => $comment::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>Comment::factory()->create()->id, 'likeable_type' => $comment::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>Comment::factory()->create()->id, 'likeable_type' => $comment::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>Comment::factory()->create()->id, 'likeable_type' => $comment::class, 'user_id' => User::factory()->create()->id]),
        ]);

        $this->assertNotNull($comment->likes);

        $this->assertInstanceOf(Collection::class, $comment->likes);
        $comment->likes->each(fn($like) => $this->assertInstanceOf(Like::class, $like));

        $this->assertCount(2, $comment->likes);

        $comment_likes->each(fn($like) => $this->assertTrue($comment->likes->contains($like)));
        $other_comment_likes->each(fn($like) => $this->assertFalse($comment->likes->contains($like)));
    }

    /**
     * @test
     */
    public function test_when_comment_gets_deleted_its_related_records_in_likes_table_get_deleted(){
        $comment = Comment::factory()->create();
        $likes = collect([
            Like::factory()->create(['likeable_id'=>$comment->id, 'likeable_type'=>$comment::class, 'user_id'=>User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>$comment->id, 'likeable_type'=>$comment::class, 'user_id'=>User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>$comment->id, 'likeable_type'=>$comment::class, 'user_id'=>User::factory()->create()->id]),
        ]);

        $comment->delete();
        $this->assertModelMissing($comment);
        $this->assertDatabaseMissing('comments', ['title'=>$comment->id]);
        $this->assertDatabaseMissing('likes', ['likeable_id'=>$comment->id, 'likeable_type'=>$comment::class]);

        $likes->each(fn($like) => $this->assertModelMissing($like));
    }
}
