<?php

namespace Tests\Unit;

use App\Models\Tag;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase{

    use RefreshDatabase;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        Tag::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        Tag::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        Tag::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_slug_is_generated_from_name(){
        $tag = Tag::factory()->create(['name'=>'test 123']);
        $this->assertModelExists($tag);
        $this->assertNotNull($tag->slug);
        $this->assertEquals('test-123', $tag->slug);
        $this->assertDatabaseHas('tags', ['slug'=>'test-123', 'name'=>'test 123']);
    }

    /**
     * @test
     */
    public function test_slug_must_be_unique(){
        Tag::factory()->create(['slug'=>'test']);
        $this->expectException(QueryException::class);
        Tag::factory()->create(['slug'=>'test']);
    }

    /**
     * @test
     */
    public function test_unique_slug_is_generated(){
        Tag::factory()->create(['name'=>'test 123']);
        $this->assertDatabaseHas('tags', ['slug'=>'test-123', 'name'=>'test 123']);
        Tag::factory()->create(['name'=>'test 123 ']);
        $this->assertDatabaseHas('tags', ['slug'=>'test-123-2', 'name'=>'test 123 ']);
    }

    /**
     * @test
     */
    public function test_description_is_nullable(){
        Tag::factory()->create(['name' => 'test', 'description'=>null]);
        $this->assertDatabaseHas('tags', ['name'=>'test', 'description'=>null]);
    }

}
