<?php

namespace Tests\Unit;

use App\Models\Execution;
use App\Models\Recipe;
use App\Models\User;
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
}
