<?php

namespace Tests\Unit;

use App\Models\MeasureUnit;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeasureUnitTest extends TestCase{

    use RefreshDatabase;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        MeasureUnit::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        MeasureUnit::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        MeasureUnit::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_abbreviation_is_nullable(){
        MeasureUnit::factory()->create(['name'=>'test', 'abbreviation' => null]);
        $this->assertDatabaseHas(MeasureUnit::class, ['name'=>'test', 'abbreviation' => null]);
    }

    /**
     * @test
     */
    public function test_abbreviation_must_be_unique(){
        MeasureUnit::factory()->create(['abbreviation'=>'test']);
        $this->expectException(QueryException::class);
        MeasureUnit::factory()->create(['abbreviation'=>'test']);
    }

    /**
     * @test
     */
    public function test_description_is_nullable(){
        MeasureUnit::factory()->create(['name'=>'test', 'description' => null]);
        $this->assertDatabaseHas(MeasureUnit::class, ['name'=>'test', 'description' => null]);
    }
}
