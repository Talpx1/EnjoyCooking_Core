<?php

namespace Tests\Unit;

use App\Enums\Permissions;
use App\Models\Execution;
use App\Models\Favorite;
use App\Models\Follow;
use App\Models\Ingredient;
use App\Models\IngredientImage;
use App\Models\IngredientVideo;
use App\Models\OauthAccessToken;
use App\Models\OauthRefreshToken;
use App\Models\Rating;
use App\Models\Snack;
use Tests\Seeders\PermissionsAndRolesSeeder;
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
        User::factory()->create(['first_name' => 'test 123', 'company_name'=>null]);
        $this->assertDatabaseHas('users', ['first_name'=>'test 123', 'company_name'=>null]);
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
    public function test_image_path_is_nullable(){
        User::factory()->create(['first_name' => 'test 123', 'image_path'=>null]);
        $this->assertDatabaseHas('users', ['first_name'=>'test 123', 'image_path'=>null]);
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

    /**
     * @test
     */
    public function test_user_has_many_ratings(){
        $user = User::factory()->create();
        $other_user = User::factory()->create();
        $ratings = collect([
            Rating::factory()->create(['rateable_id'=>Recipe::factory()->create()->id, 'rateable_type'=>Recipe::class, 'user_id' => $user->id]),
            Rating::factory()->create(['rateable_id'=>Recipe::factory()->create()->id, 'rateable_type'=>Recipe::class, 'user_id' => $user->id])
        ]);
        $other_ratings = collect([
            Rating::factory()->create(['rateable_id'=>Recipe::factory()->create()->id, 'rateable_type'=>Recipe::class, 'user_id' => $other_user->id]),
            Rating::factory()->create(['rateable_id'=>Recipe::factory()->create()->id, 'rateable_type'=>Recipe::class, 'user_id' => $other_user->id]),
            Rating::factory()->create(['rateable_id'=>Recipe::factory()->create()->id, 'rateable_type'=>Recipe::class, 'user_id' => $other_user->id]),
            Rating::factory()->create(['rateable_id'=>Recipe::factory()->create()->id, 'rateable_type'=>Recipe::class, 'user_id' => $other_user->id])
        ]);

        $this->assertNotNull($user->ratings);
        $this->assertNotNull($other_user->ratings);

        $this->assertInstanceOf(Collection::class, $user->ratings);
        $this->assertInstanceOf(Collection::class, $other_user->ratings);

        $user->ratings->each(fn($rating) => $this->assertInstanceOf(Rating::class, $rating));
        $other_user->ratings->each(fn($rating) => $this->assertInstanceOf(Rating::class, $rating));

        $this->assertCount(2, $user->ratings);
        $this->assertCount(4, $other_user->ratings);

        $ratings->each(fn($rating) => $this->assertTrue($user->ratings->contains($rating)));
        $ratings->each(fn($rating) => $this->assertFalse($other_user->ratings->contains($rating)));

        $other_ratings->each(fn($rating) => $this->assertTrue($other_user->ratings->contains($rating)));
        $other_ratings->each(fn($rating) => $this->assertFalse($user->ratings->contains($rating)));
    }

    /**
     * @test
     */
    public function test_user_morphs_many_follows(){
        $user = User::factory()->create(['first_name' => 'test']);
        $user_followers = collect([
            Follow::factory()->create(['followable_id' => $user->id, 'followable_type' => $user::class, 'user_id' => User::factory()->create()->id]),
            Follow::factory()->create(['followable_id' => $user->id, 'followable_type' => $user::class, 'user_id' => User::factory()->create()->id]),
        ]);
        $other_user_followers = collect([
            Follow::factory()->create(['followable_id'=>User::factory()->create()->id, 'followable_type' => $user::class, 'user_id' => User::factory()->create()->id]),
            Follow::factory()->create(['followable_id'=>User::factory()->create()->id, 'followable_type' => $user::class, 'user_id' => User::factory()->create()->id]),
            Follow::factory()->create(['followable_id'=>User::factory()->create()->id, 'followable_type' => $user::class, 'user_id' => User::factory()->create()->id]),
            Follow::factory()->create(['followable_id'=>User::factory()->create()->id, 'followable_type' => $user::class, 'user_id' => User::factory()->create()->id]),
        ]);

        $this->assertNotNull($user->followers);

        $this->assertInstanceOf(Collection::class, $user->followers);
        $user->followers->each(fn($follow) => $this->assertInstanceOf(Follow::class, $follow));

        $this->assertCount(2, $user->followers);

        $user_followers->each(fn($follow) => $this->assertTrue($user->followers->contains($follow)));
        $other_user_followers->each(fn($follow) => $this->assertFalse($user->followers->contains($follow)));
    }

    /**
     * @test
     */
    public function test_when_user_gets_deleted_its_related_records_in_follows_table_get_deleted(){
        $user = User::factory()->create();
        $follows = collect([
            Follow::factory()->create(['followable_id'=>$user->id, 'followable_type'=>$user::class, 'user_id'=>User::factory()->create()->id]),
            Follow::factory()->create(['followable_id'=>$user->id, 'followable_type'=>$user::class, 'user_id'=>User::factory()->create()->id]),
            Follow::factory()->create(['followable_id'=>$user->id, 'followable_type'=>$user::class, 'user_id'=>User::factory()->create()->id]),
        ]);

        $user->delete();
        $this->assertModelMissing($user);
        $this->assertDatabaseMissing('users', ['email'=>$user->email]);
        $this->assertDatabaseMissing('follows', ['followable_id'=>$user->id, 'followable_type'=>$user::class]);

        $follows->each(fn($follow) => $this->assertModelMissing($follow));
    }

    /**
     * @test
     */
    public function test_user_has_many_follows(){
        $user = User::factory()->create();
        $follows = collect([
            Follow::factory()->create(['followable_id' => User::factory()->create()->id, 'followable_type' => User::class, 'user_id' => $user->id]),
            Follow::factory()->create(['followable_id' => User::factory()->create()->id, 'followable_type' => User::class, 'user_id' => $user->id]),
            Follow::factory()->create(['followable_id' => User::factory()->create()->id, 'followable_type' => User::class, 'user_id' => $user->id]),
            Follow::factory()->create(['followable_id' => User::factory()->create()->id, 'followable_type' => User::class, 'user_id' => $user->id]),
            Follow::factory()->create(['followable_id' => User::factory()->create()->id, 'followable_type' => User::class, 'user_id' => $user->id]),
        ]);
        $other_follows = collect([
            Follow::factory()->create(['followable_id' => User::factory()->create()->id, 'followable_type' => User::class, 'user_id' => User::factory()->create()]),
            Follow::factory()->create(['followable_id' => User::factory()->create()->id, 'followable_type' => User::class, 'user_id' => User::factory()->create()])
        ]);

        $this->assertNotNull($user->follows);
        $this->assertInstanceOf(Collection::class, $user->follows);
        $this->assertCount(5, $user->follows);

        $user->follows->each(fn($follow) => $this->assertInstanceOf(Follow::class, $follow));

        $follows->each(fn($follow) => $this->assertTrue($user->follows->contains($follow)));
        $other_follows->each(fn($follow) => $this->assertFalse($user->follows->contains($follow)));
    }

    /**
     * @test
     */
    public function test_user_has_many_favorites(){
        $user = User::factory()->create();
        $favorites = collect([
            Favorite::factory()->create(['favoritable_id' => User::factory()->create()->id, 'favoritable_type' => User::class, 'user_id' => $user->id]),
            Favorite::factory()->create(['favoritable_id' => User::factory()->create()->id, 'favoritable_type' => User::class, 'user_id' => $user->id]),
            Favorite::factory()->create(['favoritable_id' => User::factory()->create()->id, 'favoritable_type' => User::class, 'user_id' => $user->id]),
            Favorite::factory()->create(['favoritable_id' => User::factory()->create()->id, 'favoritable_type' => User::class, 'user_id' => $user->id]),
            Favorite::factory()->create(['favoritable_id' => User::factory()->create()->id, 'favoritable_type' => User::class, 'user_id' => $user->id]),
        ]);
        $other_favorites = collect([
            Favorite::factory()->create(['favoritable_id' => User::factory()->create()->id, 'favoritable_type' => User::class, 'user_id' => User::factory()->create()]),
            Favorite::factory()->create(['favoritable_id' => User::factory()->create()->id, 'favoritable_type' => User::class, 'user_id' => User::factory()->create()])
        ]);

        $this->assertNotNull($user->favorites);
        $this->assertInstanceOf(Collection::class, $user->favorites);
        $this->assertCount(5, $user->favorites);

        $user->favorites->each(fn($favorite) => $this->assertInstanceOf(Favorite::class, $favorite));

        $favorites->each(fn($favorite) => $this->assertTrue($user->favorites->contains($favorite)));
        $other_favorites->each(fn($favorite) => $this->assertFalse($user->favorites->contains($favorite)));
    }

    /**
     * @test
     */
    public function test_user_has_many_executions(){
        $user = User::factory()->create();
        $user_executions = Execution::factory(3)->create(['recipe_id' => Recipe::factory()->create()->id, 'user_id' => $user->id]);
        $other_user_executions = Execution::factory(4)->create(['recipe_id' => Recipe::factory()->create()->id, 'user_id' => User::factory()->create()->id]);

        $this->assertNotNull($user->executions);
        $this->assertInstanceOf(Collection::class, $user->executions);
        $this->assertCount(3, $user->executions);

        $user->executions->each(fn($execution) => $this->assertInstanceOf(Execution::class, $execution));

        $user_executions->each(fn($execution) => $this->assertTrue($user->executions->contains($execution)));
        $other_user_executions->each(fn($execution) => $this->assertFalse($user->executions->contains($execution)));
    }

    /**
     * @test
     */
    public function test_user_has_many_oauth_access_tokens(){
        $user = User::factory()->create();
        $user_tokens = OauthAccessToken::factory(3)->create(['user_id' => $user->id]);
        $other_user_tokens = OauthAccessToken::factory(4)->create(['user_id' => User::factory()->create()->id]);

        $this->assertNotNull($user->oauthAccessTokens);
        $this->assertInstanceOf(Collection::class, $user->oauthAccessTokens);
        $this->assertCount(3, $user->oauthAccessTokens);

        $user->oauthAccessTokens->each(fn($oauthAccessToken) => $this->assertInstanceOf(OauthAccessToken::class, $oauthAccessToken));

        $user_tokens->each(fn($oauthAccessToken) => $this->assertTrue($user->oauthAccessTokens->contains($oauthAccessToken)));
        $other_user_tokens->each(fn($oauthAccessToken) => $this->assertFalse($user->oauthAccessTokens->contains($oauthAccessToken)));
    }

    /**
     * @test
     */
    public function test_when_user_gets_deleted_its_oauth_access_tokens_get_deleted(){
        $user = User::factory()->create();
        $user_tokens = OauthAccessToken::factory(3)->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertModelMissing($user);
        $this->assertDatabaseMissing('users', ['email'=>$user->email]);
        $this->assertDatabaseMissing('oauth_access_tokens', ['user_id'=>$user->id]);

        $user_tokens->each(fn($token) => $this->assertModelMissing($token));
    }

    /**
     * @test
     */
    public function test_user_has_many_oauth_refresh_tokens_through_access_tokens(){
        $user = User::factory()->create();
        $user_tokens = OauthAccessToken::factory(3)->create(['user_id' => $user->id])
            ->each(fn($access_token) => OauthRefreshToken::factory(3)->create(['access_token_id' => $access_token->id]));

        $this->assertNotNull($user->oauthRefreshTokens);
        $this->assertInstanceOf(Collection::class, $user->oauthRefreshTokens);
        $this->assertCount(9, $user->oauthRefreshTokens);

        $user->oauthRefreshTokens->each(fn($oauthRefreshToken) => $this->assertInstanceOf(OauthRefreshToken::class, $oauthRefreshToken));

        $user_tokens->each(fn($oauthAccessToken) =>
            $oauthAccessToken->refreshTokens->each( fn($refreshToken) => $this->assertTrue($user->oauthRefreshTokens->contains($refreshToken)) )
        );
    }

    /**
     * @test
     */
    public function test_permissions_list_attribute(){
        $this->seed(PermissionsAndRolesSeeder::class);

        $user = $this->actingAsUser();

        $this->assertNotEmpty($user->permissions_list);
        $this->assertContains(Permissions::INDEX_AWARD->value, $user->permissions_list);
        $this->assertNotContains(Permissions::DESTROY_AWARD->value, $user->permissions_list);

        $user = $this->actingAsAdmin();
        $this->assertNotEmpty($user->permissions_list);
        $this->assertContains(Permissions::INDEX_AWARD->value, $user->permissions_list);
        $this->assertContains(Permissions::DESTROY_AWARD->value, $user->permissions_list);

    }
}

