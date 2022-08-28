<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ModerationStatus;
use Illuminate\Database\QueryException;

class ModerationStatusTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        ModerationStatus::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        ModerationStatus::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        ModerationStatus::factory()->create(['name'=>'test']);
    }
}
