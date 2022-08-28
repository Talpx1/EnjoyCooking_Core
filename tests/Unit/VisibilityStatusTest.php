<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\VisibilityStatus;
use Illuminate\Database\QueryException;

class VisibilityStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        VisibilityStatus::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        VisibilityStatus::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        VisibilityStatus::factory()->create(['name'=>'test']);
    }
}
