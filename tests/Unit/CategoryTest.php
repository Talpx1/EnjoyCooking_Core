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
    public function test_children_categories_get_deleted_if_parent_category_gets_deleted(){
        $category = Category::factory()->create(['name' => 'test']);
        $child_category_1 = Category::factory()->create(['name' => 'test 1', 'parent_category_id' => $category->id]);
        $child_category_2 = Category::factory()->create(['name' => 'test 2', 'parent_category_id' => $category->id]);
        $child_category_3 = Category::factory()->create(['name' => 'test 3', 'parent_category_id' => $category->id]);
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
        $this->assertModelMissing($child_category_1);
        $this->assertModelMissing($child_category_2);
        $this->assertModelMissing($child_category_3);
    }

    /**
     * @test
     */
    public function test_category_has_children_categories(){
        $category = Category::factory()->create();
        $children = Category::factory(3)->create(['parent_category_id' => $category->id]);
        $this->assertCount(3, $category->children);
        $children->each(fn($child) => $this->assertInstanceOf(Category::class, $child));
        $children->each(fn($child) => $this->assertTrue($category->children->contains($child)));
        $children->each(fn($child) => $this->assertEmpty($child->children));
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
}
