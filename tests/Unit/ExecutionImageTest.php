<?php

namespace Tests\Unit;

use App\Models\Execution;
use App\Models\ExecutionImage;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutionImageTest extends TestCase{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_path_is_required(){
        $this->expectException(QueryException::class);
        ExecutionImage::factory()->create(['path'=>null]);
    }

    /**
     * @test
     */
    public function test_path_must_be_unique(){
        ExecutionImage::factory()->create(['path'=>'test']);
        $this->expectException(QueryException::class);
        ExecutionImage::factory()->create(['path'=>'test']);
    }

    /**
     * @test
     */
    public function test_thumbnail_path_is_required(){
        $this->expectException(QueryException::class);
        ExecutionImage::factory()->create(['thumbnail_path'=>null]);
    }

    /**
     * @test
     */
    public function test_thumbnail_path_must_be_unique(){
        ExecutionImage::factory()->create(['thumbnail_path'=>'test']);
        $this->expectException(QueryException::class);
        ExecutionImage::factory()->create(['thumbnail_path'=>'test']);
    }

    /**
     * @test
     */
    public function test_execution_id_is_required(){
        $this->expectException(QueryException::class);
        ExecutionImage::factory()->create(['path' => 'test', 'execution_id'=>null]);
    }

    /**
     * @test
     */
    public function test_execution_id_must_exists_in_executions_table(){
        $execution = Execution::factory()->create();
        ExecutionImage::factory()->create(['path' => 'test', 'execution_id' => $execution->id]);
        $this->assertDatabaseHas('execution_images', ['path'=>'test', 'execution_id'=>$execution->id]);

        $this->expectException(QueryException::class);
        ExecutionImage::factory()->create(['path' => 'test 2', 'execution_id' => 111]);
    }

    /**
     * @test
     */
    public function test_execution_image_gets_deleted_if_parent_execution_gets_deleted(){
        $execution = Execution::factory()->create();
        $image = ExecutionImage::factory()->create(['path' => 'test1', 'execution_id' => $execution->id]);

        $this->assertDatabaseHas('execution_images', ['path'=>'test1', 'execution_id'=>$execution->id]);
        $this->assertEquals($execution->id, $image->execution_id);

        $execution->delete();

        $this->assertModelMissing($execution);
        $this->assertModelMissing($image);

        $this->assertDatabaseMissing('execution_images', ['path'=>'test1', 'execution_id'=>$execution->id]);
    }

    /**
     * @test
     */
    public function test_execution_image_belongs_to_execution(){
        $execution = Execution::factory()->create();
        $execution_image = ExecutionImage::factory()->create(['path' => 'test']);
        $this->assertNotNull($execution_image->execution);
        $this->assertInstanceOf(Execution::class, $execution_image->execution);
        $this->assertEquals($execution_image->execution->id, $execution->id);
    }
}
