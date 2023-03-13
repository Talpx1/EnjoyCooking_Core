<?php

namespace Tests\Unit;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Category;
use Illuminate\Database\QueryException;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        Category::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_slug_is_generated_from_name(){
        $category = Category::factory()->create(['name'=>'test 123']);
        $this->assertModelExists($category);
        $this->assertNotNull($category->slug);
        $this->assertEquals('test-123', $category->slug);
        $this->assertDatabaseHas('categories', ['slug'=>'test-123', 'name'=>'test 123']);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        Category::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        Category::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_slug_must_be_unique(){
        Category::factory()->create(['slug'=>'test']);
        $this->expectException(QueryException::class);
        Category::factory()->create(['slug'=>'test']);
    }

    /**
     * @test
     */
    public function test_unique_slug_is_generated(){
        Category::factory()->create(['name'=>'test 123']);
        $this->assertDatabaseHas('categories', ['slug'=>'test-123', 'name'=>'test 123']);
        Category::factory()->create(['name'=>'test 123 ']);
        $this->assertDatabaseHas('categories', ['slug'=>'test-123-2', 'name'=>'test 123 ']);
    }

    /**
     * @test
     */
    public function test_parent_category_id_is_nullable(){
        Category::factory()->create(['name' => 'test 123', 'parent_category_id'=>null]);
        $this->assertDatabaseHas('categories', ['name'=>'test 123', 'parent_category_id'=>null]);
    }

    /**
     * @test
     */
    public function test_parent_category_id_must_exists(){
        $category = Category::factory()->create(['name' => 'test 123']);
        Category::factory()->create(['name' => 'test 456', 'parent_category_id' => $category->id]);
        $this->assertDatabaseHas('categories', ['name'=>'test 456', 'parent_category_id'=>$category->id]);

        $this->expectException(QueryException::class);
        Category::factory()->create(['name' => 'test 567', 'parent_category_id' => 111]);
        $this->assertDatabaseMissing('categories', ['name'=>'test 567', 'parent_category_id'=>111]);
    }

    /**
     * @test
     */
    public function test_subcategories_get_deleted_if_parent_category_gets_deleted(){
        $category = Category::factory()->create(['name' => 'test']);
        $subcategory_1 = Category::factory()->create(['name' => 'test 1', 'parent_category_id' => $category->id]);
        $subcategory_2 = Category::factory()->create(['name' => 'test 2', 'parent_category_id' => $category->id]);
        $subcategory_3 = Category::factory()->create(['name' => 'test 3', 'parent_category_id' => $category->id]);
        $this->assertDatabaseHas('categories', ['name'=>'test', 'parent_category_id'=>null]);
        $this->assertDatabaseHas('categories', ['name'=>'test 1', 'parent_category_id'=>$category->id]);
        $this->assertDatabaseHas('categories', ['name'=>'test 2', 'parent_category_id'=>$category->id]);
        $this->assertDatabaseHas('categories', ['name'=>'test 3', 'parent_category_id'=>$category->id]);

        $category->delete();

        $this->assertDatabaseMissing('categories', ['name'=>'test', 'parent_category_id'=>null]);
        $this->assertDatabaseMissing('categories', ['name'=>'test 1', 'parent_category_id'=>$category->id]);
        $this->assertDatabaseMissing('categories', ['name'=>'test 2', 'parent_category_id'=>$category->id]);
        $this->assertDatabaseMissing('categories', ['name'=>'test 3', 'parent_category_id'=>$category->id]);

        $this->assertModelMissing($category);
        $this->assertModelMissing($subcategory_1);
        $this->assertModelMissing($subcategory_2);
        $this->assertModelMissing($subcategory_3);
    }

    /**
     * @test
     */
    public function test_category_has_subcategories(){
        $category = Category::factory()->create();
        $subcategories = Category::factory(3)->create(['parent_category_id' => $category->id]);
        $this->assertCount(3, $category->subcategories);
        $subcategories->each(fn($child) => $this->assertInstanceOf(Category::class, $child));
        $subcategories->each(fn($child) => $this->assertTrue($category->subcategories->contains($child)));
        $subcategories->each(fn($child) => $this->assertEmpty($child->subcategories));
    }

    /**
     * @test
     */
    public function test_category_belongs_to_parent_category(){
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_category_id' => $parent->id]);
        $this->assertNotNull($child->parent);
        $this->assertNull($parent->parent);
        $this->assertInstanceOf(Category::class, $child->parent);
        $this->assertEquals($parent->id, $child->parent->id);
    }

    /**
     * @test
     */
    public function test_is_parent_category_attribute(){
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_category_id' => $parent->id]);
        $this->assertFalse($child->is_parent_category);
        $this->assertTrue($parent->is_parent_category);
    }

    /**
     * @test
     */
    public function test_category_has_many_recipes(){
        $category = Category::factory()->create();
        $other_category = Category::factory()->create();
        $recipes = Recipe::factory(2)->create(['category_id' => $category->id]);
        $other_recipes = Recipe::factory(4)->create(['category_id' => $other_category->id]);

        $this->assertNotNull($category->recipes);
        $this->assertNotNull($other_category->recipes);

        $this->assertInstanceOf(Collection::class, $category->recipes);
        $this->assertInstanceOf(Collection::class, $other_category->recipes);

        $category->recipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));
        $other_category->recipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));

        $this->assertCount(2, $category->recipes);
        $this->assertCount(4, $other_category->recipes);

        $category->recipes->each(fn($recipe) => $this->assertTrue($recipes->contains($recipe)));
        $category->recipes->each(fn($recipe) => $this->assertFalse($other_recipes->contains($recipe)));

        $other_category->recipes->each(fn($recipe) => $this->assertTrue($other_recipes->contains($recipe)));
        $other_category->recipes->each(fn($recipe) => $this->assertFalse($recipes->contains($recipe)));
    }

    public function test_where_name_like_scope(){
        Category::factory()->create(['name' => 'test']);
        Category::factory()->create(['name' => 'test1']);
        Category::factory()->create(['name' => 'test2']);
        Category::factory()->create(['name' => 'other']);
        Category::factory()->create(['name' => 'other1']);
        Category::factory()->create(['name' => 'name_example']);

        Category::factory(30)->create();

        $this->assertCount(3, Category::whereNameLike('test')->get());
        $this->assertCount(2, Category::whereNameLike('other')->get());
        $this->assertCount(1, Category::whereNameLike('name_example')->get());
        $this->assertCount(2, Category::whereNameLike('1')->get());

        $this->assertCount(36, Category::whereNameLike(null)->get());
    }
}
