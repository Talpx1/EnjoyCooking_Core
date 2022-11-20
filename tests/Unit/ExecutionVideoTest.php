<?php

namespace Tests\Unit;

use App\Models\Execution;
use App\Models\ExecutionVideo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutionVideoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_path_is_required(){
        $this->expectException(QueryException::class);
        ExecutionVideo::factory()->create(['path'=>null]);
    }

    /**
     * @test
     */
    public function test_path_must_be_unique(){
        ExecutionVideo::factory()->create(['path'=>'test']);
        $this->expectException(QueryException::class);
        ExecutionVideo::factory()->create(['path'=>'test']);
    }

    /**
     * @test
     */
    public function test_execution_id_is_required(){
        $this->expectException(QueryException::class);
        ExecutionVideo::factory()->create(['path' => 'test', 'execution_id'=>null]);
    }

    /**
     * @test
     */
    public function test_execution_id_must_exists_in_executions_table(){
        $execution = Execution::factory()->create();
        ExecutionVideo::factory()->create(['path' => 'test', 'execution_id' => $execution->id]);
        $this->assertDatabaseHas('execution_videos', ['path'=>'test', 'execution_id'=>$execution->id]);

        $this->expectException(QueryException::class);
        ExecutionVideo::factory()->create(['path' => 'test 2', 'execution_id' => 111]);
    }

    /**
     * @test
     */
    public function test_execution_video_gets_deleted_if_parent_execution_gets_deleted(){
        $execution = Execution::factory()->create();
        $video = ExecutionVideo::factory()->create(['path' => 'test1', 'execution_id' => $execution->id]);

        $this->assertDatabaseHas('execution_videos', ['path'=>'test1', 'execution_id'=>$execution->id]);
        $this->assertEquals($execution->id, $video->execution_id);

        $execution->delete();

        $this->assertModelMissing($execution);
        $this->assertModelMissing($video);

        $this->assertDatabaseMissing('execution_videos', ['path'=>'test1', 'execution_id'=>$execution->id]);
    }

    /**
     * @test
     */
    public function test_execution_video_belongs_to_execution(){
        $execution = Execution::factory()->create();
        $execution_video = ExecutionVideo::factory()->create(['path' => 'test']);
        $this->assertNotNull($execution_video->execution);
        $this->assertInstanceOf(Execution::class, $execution_video->execution);
        $this->assertEquals($execution_video->execution->id, $execution->id);
    }
}
