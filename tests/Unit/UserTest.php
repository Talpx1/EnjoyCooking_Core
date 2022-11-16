<?php

namespace Tests\Unit;

use App\Models\Ingredient;
use App\Models\IngredientImage;
use App\Models\IngredientVideo;
use App\Models\Snack;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Repost;
use Illuminate\Database\Eloquent\Collection;
use App\Enums\UserTypes;
use Database\Seeders\UserTypeSeeder;
use App\Models\UserType;
use App\Models\Gender;
use App\Models\ProfessionGroup;
use Illuminate\Database\QueryException;
use App\Models\Badge;
use App\Models\BadgeUser;
use App\Models\Recipe;

class UserTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     */
    public function test_user_has_many_reposts(){
        $user = User::factory()->create();
        $reposts = collect([
            Repost::factory()->create(['repostable_id' => Recipe::factory()->create()->id, 'repostable_type' => Recipe::class, 'user_id' => $user->id]),
            Repost::factory()->create(['repostable_id' => Recipe::factory()->create()->id, 'repostable_type' => Recipe::class, 'user_id' => $user->id]),
            Repost::factory()->create(['repostable_id' => Recipe::factory()->create()->id, 'repostable_type' => Recipe::class, 'user_id' => $user->id]),
            Repost::factory()->create(['repostable_id' => Recipe::factory()->create()->id, 'repostable_type' => Recipe::class, 'user_id' => $user->id]),
            Repost::factory()->create(['repostable_id' => Recipe::factory()->create()->id, 'repostable_type' => Recipe::class, 'user_id' => $user->id]),
        ]);
        $other_reposts = collect([
            Repost::factory()->create(['repostable_id' => Recipe::factory()->create()->id, 'repostable_type' => Recipe::class, 'user_id' => User::factory()->create()]),
            Repost::factory()->create(['repostable_id' => Recipe::factory()->create()->id, 'repostable_type' => Recipe::class, 'user_id' => User::factory()->create()])
        ]);

        $this->assertNotNull($user->reposts);
        $this->assertInstanceOf(Collection::class, $user->reposts);
        $this->assertCount(5, $user->reposts);

        $user->reposts->each(fn($repost) => $this->assertInstanceOf(Repost::class, $repost));

        $reposts->each(fn($repost) => $this->assertTrue($user->reposts->contains($repost)));
        $other_reposts->each(fn($repost) => $this->assertFalse($user->reposts->contains($repost)));
    }

    /**
     * @test
     */
    public function test_user_belongs_to_user_type(){
        $this->seed(UserTypeSeeder::class);
        $user = User::factory()->create(['user_type_id' => UserTypes::COMPANY->value]);
        $this->assertNotNull($user->type);
        $this->assertInstanceOf(UserType::class, $user->type);
        $this->assertEquals($user->type->id,UserTypes::COMPANY->value);
    }
    /**
     * @test
     */
    public function test_user_belongs_to_gender(){
        $gender = Gender::factory()->create();
        $user = User::factory()->create(['gender_id' => $gender->id]);
        $this->assertNotNull($user->gender);
        $this->assertInstanceOf(Gender::class, $user->gender);
        $this->assertEquals($user->gender->id, $gender->id);
    }
    /**
     * @test
     */
    public function test_user_belongs_to_profession_group(){
        $profession_group = ProfessionGroup::factory()->create();
        $user = User::factory()->create(['profession_group_id' => $profession_group->id]);
        $this->assertNotNull($user->professionGroup);
        $this->assertInstanceOf(ProfessionGroup::class, $user->professionGroup);
        $this->assertEquals($user->professionGroup->id, $profession_group->id);
    }

    /**
     * @test
     */
    public function test_first_name_is_required(){
        $this->expectException(QueryException::class);
        User::factory()->create(['first_name'=>null]);
    }

    /**
     * @test
     */
    public function test_last_name_id_is_required(){
        $this->expectException(QueryException::class);
        User::factory()->create(['repostable_id'=>null]);
    }

    /**
     * @test
     */
    public function test_email_id_is_required(){
        $this->expectException(QueryException::class);
        User::factory()->create(['last_name'=>null]);
    }

    /**
     * @test
     */
    public function test_email_must_be_unique(){
        User::factory()->create(['email'=>'test@test.test']);
        $this->expectException(QueryException::class);
        User::factory()->create(['email'=>'test@test.test']);
    }

    /**
     * @test
     */
    public function test_company_name_is_nullable(){
        User::factory()->create(['company_name' => 'test 123']);
        $this->assertDatabaseHas('users', ['company_name'=>'test 123']);
    }

    /**
     * @test
     */
    public function test_date_of_bird_is_required(){
        $this->expectException(QueryException::class);
        User::factory()->create(['date_of_bird'=>null]);
    }

    /**
     * @test
     */
    public function test_user_type_id_is_required(){
        $this->expectException(QueryException::class);
        User::factory()->create(['user_type_id'=>null]);
    }

    /**
     * @test
     */
    public function test_profession_group_id_must_exists_in_profession_groups_table(){
        $profession_group = ProfessionGroup::factory()->create();
        User::factory()->create(['first_name' => 'test 456', 'profession_group_id' => $profession_group->id]);
        $this->assertDatabaseHas('users', ['first_name'=>'test 456', 'profession_group_id'=>$profession_group->id]);

        $this->expectException(QueryException::class);
        User::factory()->create(['first_name' => 'test 567', 'profession_group_id' => 111]);
        $this->assertDatabaseMissing('users', ['first_name'=>'test 567', 'profession_group_id'=>111]);
    }

    /**
     * @test
     */
    public function test_gender_id_must_exists_in_genders_table(){
        $gender = Gender::factory()->create();
        User::factory()->create(['first_name' => 'test 456', 'gender_id' => $gender->id]);
        $this->assertDatabaseHas('users', ['first_name'=>'test 456', 'gender_id'=>$gender->id]);

        $this->expectException(QueryException::class);
        User::factory()->create(['first_name' => 'test 567', 'gender_id' => 111]);
        $this->assertDatabaseMissing('users', ['first_name'=>'test 567', 'gender_id'=>111]);
    }

    /**
     * @test
     */
    public function test_user_type_id_must_exists_in_user_types_table(){
        $user_type = UserType::factory()->create();
        User::factory()->create(['first_name' => 'test 456', 'user_type_id' => $user_type->id]);
        $this->assertDatabaseHas('users', ['first_name'=>'test 456', 'user_type_id'=>$user_type->id]);

        $this->expectException(QueryException::class);
        User::factory()->create(['first_name' => 'test 567', 'user_type_id' => 111]);
        $this->assertDatabaseMissing('users', ['first_name'=>'test 567', 'user_type_id'=>111]);
    }
    /**
     * @test
     */
    public function test_gender_id_is_nullable(){
        User::factory()->create(['first_name' => 'test 123', 'gender_id'=>null]);
        $this->assertDatabaseHas('users', ['first_name'=>'test 123', 'gender_id'=>null]);
    }

    /**
     * @test
     */
    public function test_profession_group_id_is_nullable(){
        User::factory()->create(['first_name' => 'test 123', 'profession_group_id'=>null]);
        $this->assertDatabaseHas('users', ['first_name'=>'test 123', 'profession_group_id'=>null]);
    }

    /**
     * @test
     */
    public function test_email_verified_at_is_nullable(){
        User::factory()->create(['first_name' => 'test 123', 'email_verified_at'=>null]);
        $this->assertDatabaseHas('users', ['first_name'=>'test 123', 'email_verified_at'=>null]);
    }

    /**
     * @test
     */
    public function test_password_is_required(){
        $this->expectException(QueryException::class);
        User::factory()->create(['password'=>null]);
    }

    /**
     * @test
     */
    public function test_instagram_url_is_nullable(){
        User::factory()->create(['first_name' => 'test 123', 'instagram_url' => null]);
        $this->assertDatabaseHas('users', ['first_name'=>'test 123', 'instagram_url' => null]);
    }

    /**
     * @test
     */
    public function test_website_url_is_nullable(){
        User::factory()->create(['first_name' => 'test 123', 'website_url'=>null]);
        $this->assertDatabaseHas('users', ['first_name'=>'test 123', 'website_url'=>null]);
    }

    /**
     * @test
     */
    public function test_user_belongs_to_many_badges(){
        $user = User::factory()->create();
        $badges = Badge::factory(3)->create()->each(fn($badge)=>BadgeUser::factory()->create(['user_id'=>$user->id,'badge_id'=>$badge->id]));
        $other_badges = Badge::factory(5)->create()->each(fn($badge)=>BadgeUser::factory()->create(['user_id'=>User::factory()->create()->id,'badge_id'=>$badge->id]));

        $badges->each(fn($badge) => $this->assertDatabaseHas('badge_user', ['user_id'=>$user->id,'badge_id'=>$badge->id]));
        $other_badges->each(fn($badge) => $this->assertDatabaseHas('badge_user', ['user_id'=>$user->id]));
        $other_badges->each(fn($badge) => $this->assertDatabaseMissing('badge_user', ['user_id'=>$user->id, 'badge_id'=>$badge->id]));

        $this->assertNotNull($user->badges);
        $this->assertInstanceOf(Collection::class, $user->badges);
        $this->assertCount(3, $user->badges);
        $user->badges->each(fn($badge)=>$this->assertInstanceOf(Badge::class, $badge));

        $user->badges->each(fn($badge)=>$this->assertTrue($badges->contains($badge)));
        $user->badges->each(fn($badge)=>$this->assertFalse($other_badges->contains($badge)));
    }

    /**
     * @test
     */
    public function test_user_has_many_recipes(){
        $user = User::factory()->create();
        $other_user = User::factory()->create();
        $recipes = Recipe::factory(2)->create(['user_id' => $user->id]);
        $other_recipes = Recipe::factory(4)->create(['user_id' => $other_user->id]);

        $this->assertNotNull($user->recipes);
        $this->assertNotNull($other_user->recipes);

        $this->assertInstanceOf(Collection::class, $user->recipes);
        $this->assertInstanceOf(Collection::class, $other_user->recipes);

        $user->recipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));
        $other_user->recipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));

        $this->assertCount(2, $user->recipes);
        $this->assertCount(4, $other_user->recipes);

        $user->recipes->each(fn($recipe) => $this->assertTrue($recipes->contains($recipe)));
        $user->recipes->each(fn($recipe) => $this->assertFalse($other_recipes->contains($recipe)));

        $other_user->recipes->each(fn($recipe) => $this->assertTrue($other_recipes->contains($recipe)));
        $other_user->recipes->each(fn($recipe) => $this->assertFalse($recipes->contains($recipe)));
    }

    /**
     * @test
     */
    public function test_user_has_many_ingredients(){
        $user = User::factory()->create();
        $other_user = User::factory()->create();
        $ingredients = Ingredient::factory(2)->create(['user_id' => $user->id]);
        $other_ingredients = Ingredient::factory(4)->create(['user_id' => $other_user->id]);

        $this->assertNotNull($user->ingredients);
        $this->assertNotNull($other_user->ingredients);

        $this->assertInstanceOf(Collection::class, $user->ingredients);
        $this->assertInstanceOf(Collection::class, $other_user->ingredients);

        $user->ingredients->each(fn($ingredient) => $this->assertInstanceOf(Ingredient::class, $ingredient));
        $other_user->ingredients->each(fn($ingredient) => $this->assertInstanceOf(Ingredient::class, $ingredient));

        $this->assertCount(2, $user->ingredients);
        $this->assertCount(4, $other_user->ingredients);

        $user->ingredients->each(fn($ingredient) => $this->assertTrue($ingredients->contains($ingredient)));
        $user->ingredients->each(fn($ingredient) => $this->assertFalse($other_ingredients->contains($ingredient)));

        $other_user->ingredients->each(fn($ingredient) => $this->assertTrue($other_ingredients->contains($ingredient)));
        $other_user->ingredients->each(fn($ingredient) => $this->assertFalse($ingredients->contains($ingredient)));
    }

    /**
     * @test
     */
    public function test_user_has_many_ingredient_images(){
        $user = User::factory()->create();
        $other_user = User::factory()->create();
        $ingredient_images = IngredientImage::factory(2)->create(['user_id' => $user->id]);
        $other_ingredient_images = IngredientImage::factory(4)->create(['user_id' => $other_user->id]);

        $this->assertNotNull($user->ingredientImages);
        $this->assertNotNull($other_user->ingredientImages);

        $this->assertInstanceOf(Collection::class, $user->ingredientImages);
        $this->assertInstanceOf(Collection::class, $other_user->ingredientImages);

        $user->ingredientImages->each(fn($ingredient_image) => $this->assertInstanceOf(IngredientImage::class, $ingredient_image));
        $other_user->ingredientImages->each(fn($ingredient_image) => $this->assertInstanceOf(IngredientImage::class, $ingredient_image));

        $this->assertCount(2, $user->ingredientImages);
        $this->assertCount(4, $other_user->ingredientImages);

        $user->ingredientImages->each(fn($ingredient_image) => $this->assertTrue($ingredient_images->contains($ingredient_image)));
        $user->ingredientImages->each(fn($ingredient_image) => $this->assertFalse($other_ingredient_images->contains($ingredient_image)));

        $other_user->ingredientImages->each(fn($ingredient_image) => $this->assertTrue($other_ingredient_images->contains($ingredient_image)));
        $other_user->ingredientImages->each(fn($ingredient_image) => $this->assertFalse($ingredient_images->contains($ingredient_image)));
    }

    /**
     * @test
     */
    public function test_user_has_many_ingredient_videos(){
        $user = User::factory()->create();
        $other_user = User::factory()->create();
        $ingredient_videos = IngredientVideo::factory(2)->create(['user_id' => $user->id]);
        $other_ingredient_videos = IngredientVideo::factory(4)->create(['user_id' => $other_user->id]);

        $this->assertNotNull($user->ingredientVideos);
        $this->assertNotNull($other_user->ingredientVideos);

        $this->assertInstanceOf(Collection::class, $user->ingredientVideos);
        $this->assertInstanceOf(Collection::class, $other_user->ingredientVideos);

        $user->ingredientVideos->each(fn($ingredient_video) => $this->assertInstanceOf(IngredientVideo::class, $ingredient_video));
        $other_user->ingredientVideos->each(fn($ingredient_video) => $this->assertInstanceOf(IngredientVideo::class, $ingredient_video));

        $this->assertCount(2, $user->ingredientVideos);
        $this->assertCount(4, $other_user->ingredientVideos);

        $user->ingredientVideos->each(fn($ingredient_video) => $this->assertTrue($ingredient_videos->contains($ingredient_video)));
        $user->ingredientVideos->each(fn($ingredient_video) => $this->assertFalse($other_ingredient_videos->contains($ingredient_video)));

        $other_user->ingredientVideos->each(fn($ingredient_video) => $this->assertTrue($other_ingredient_videos->contains($ingredient_video)));
        $other_user->ingredientVideos->each(fn($ingredient_video) => $this->assertFalse($ingredient_videos->contains($ingredient_video)));
    }

    /**
     * @test
     */
    public function test_user_has_many_snacks(){
        $user = User::factory()->create();
        $other_user = User::factory()->create();
        $snacks = Snack::factory(2)->create(['user_id' => $user->id]);
        $other_snacks = Snack::factory(4)->create(['user_id' => $other_user->id]);

        $this->assertNotNull($user->snacks);
        $this->assertNotNull($other_user->snacks);

        $this->assertInstanceOf(Collection::class, $user->snacks);
        $this->assertInstanceOf(Collection::class, $other_user->snacks);

        $user->snacks->each(fn($snack) => $this->assertInstanceOf(Snack::class, $snack));
        $other_user->snacks->each(fn($snack) => $this->assertInstanceOf(Snack::class, $snack));

        $this->assertCount(2, $user->snacks);
        $this->assertCount(4, $other_user->snacks);

        $user->snacks->each(fn($snack) => $this->assertTrue($snacks->contains($snack)));
        $user->snacks->each(fn($snack) => $this->assertFalse($other_snacks->contains($snack)));

        $other_user->snacks->each(fn($snack) => $this->assertTrue($other_snacks->contains($snack)));
        $other_user->snacks->each(fn($snack) => $this->assertFalse($snacks->contains($snack)));
    }
}

