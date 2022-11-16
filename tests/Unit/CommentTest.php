<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\DifficultyLevel;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
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
        //TODO: replace difficulty level with other commentables
        $recipe = Recipe::factory()->create();
        $difficulty_level = DifficultyLevel::factory()->create();

        $comment1 = Comment::factory()->create(['commentable_id' => $recipe->id,'commentable_type' => $recipe::class]);
        $comment2 = Comment::factory()->create(['commentable_id' => $difficulty_level->id,'commentable_type' => $difficulty_level::class]);

        $this->assertNotNull($comment1->commentable);
        $this->assertInstanceOf($recipe::class, $comment1->commentable);
        $this->assertEquals($recipe->id, $comment1->commentable->id);

        $this->assertNotNull($comment2->commentable);
        $this->assertInstanceOf($difficulty_level::class, $comment2->commentable);
        $this->assertEquals($difficulty_level->id, $comment2->commentable->id);
    }

    /**
     * @test
     */
    public function test_combination_of_commentable_id_commentable_type_user_id_and_body_must_be_unique(){
        $recipe = Recipe::factory()->create();
        $recipe2 = Recipe::factory()->create();
        //TODO: replace Ingredient with Execution or other commentable
        $ingredient = Ingredient::factory()->create();
        $ingredient2 = Ingredient::factory()->create();
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $body = 'test';
        $body2 = 'test2';


        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class, 'body' => $body]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class]);

        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class, 'body' => $body2]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class]);

        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $ingredient->id, 'commentable_type' => $ingredient::class, 'body' => $body]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $ingredient->id, 'commentable_type' => $ingredient::class]);

        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $ingredient->id, 'commentable_type' => $ingredient::class, 'body' => $body2]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $ingredient->id, 'commentable_type' => $ingredient::class]);

        Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class, 'body' => $body]);
        $this->assertDatabaseHas('comments', ['user_id' => $user2->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class]);

        Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class, 'body' => $body2]);
        $this->assertDatabaseHas('comments', ['user_id' => $user2->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class]);

        Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $ingredient->id, 'commentable_type' => $ingredient::class, 'body' => $body]);
        $this->assertDatabaseHas('comments', ['user_id' => $user2->id, 'commentable_id' => $ingredient->id, 'commentable_type' => $ingredient::class]);

        Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $ingredient->id, 'commentable_type' => $ingredient::class, 'body' => $body2]);
        $this->assertDatabaseHas('comments', ['user_id' => $user2->id, 'commentable_id' => $ingredient->id, 'commentable_type' => $ingredient::class]);

        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe2->id, 'commentable_type' => $recipe2::class, 'body' => $body]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $recipe2->id, 'commentable_type' => $recipe2::class]);

        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe2->id, 'commentable_type' => $recipe2::class, 'body' => $body2]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $recipe2->id, 'commentable_type' => $recipe2::class]);

        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $ingredient2->id, 'commentable_type' => $ingredient2::class, 'body' => $body]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $ingredient2->id, 'commentable_type' => $ingredient2::class]);

        Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $ingredient2->id, 'commentable_type' => $ingredient2::class, 'body' => $body2]);
        $this->assertDatabaseHas('comments', ['user_id' => $user->id, 'commentable_id' => $ingredient2->id, 'commentable_type' => $ingredient2::class]);


        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class, 'body' => $body2]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $ingredient->id, 'commentable_type' => $ingredient::class, 'body' => $body]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $ingredient->id, 'commentable_type' => $ingredient::class, 'body' => $body2]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class, 'body' => $body]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $recipe->id, 'commentable_type' => $recipe::class, 'body' => $body2]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $ingredient->id, 'commentable_type' => $ingredient::class, 'body' => $body]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user2->id, 'commentable_id' => $ingredient->id, 'commentable_type' => $ingredient::class, 'body' => $body2]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe2->id, 'commentable_type' => $recipe2::class, 'body' => $body]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $recipe2->id, 'commentable_type' => $recipe2::class, 'body' => $body2]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $ingredient2->id, 'commentable_type' => $ingredient2::class, 'body' => $body]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Comment::factory()->create(['user_id' => $user->id, 'commentable_id' => $ingredient2->id, 'commentable_type' => $ingredient2::class, 'body' => $body2]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }
    }
}
