<?php

namespace Tests\Unit;

use App\Models\Award;
use App\Models\Awardable;
use App\Models\User;
use Database\Seeders\ModerationStatusSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AwardableTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;
    protected $seeder = ModerationStatusSeeder::class;

    /**
     * @test
     */
    public function test_awardable_id_is_required(){
        $this->expectException(QueryException::class);
        Awardable::factory()->create(['awardable_id'=>null]);
    }

    public function test_awardable_type_is_required(){
        $this->expectException(QueryException::class);
        Awardable::factory()->create(['awardable_type'=>null]);
    }

    /**
     * @test
     */
    public function test_award_id_must_exists_in_awards_table(){

        $award = Award::factory()->create();
        Awardable::factory()->create(['award_id' => $award->id]);
        $this->assertDatabaseHas('awardables', ['award_id'=>$award->id]);

        $this->expectException(QueryException::class);
        Awardable::factory()->create(['award_id' => 111]);
        $this->assertDatabaseMissing('awardables', ['award_id'=>111]);
    }

    /**
     * @test
     */
    public function test_awardable_gets_deleted_if_award_gets_deleted(){

        $award = Award::factory()->create();
        $awardable = Awardable::factory()->create(['award_id' => $award->id]);
        $this->assertDatabaseHas('awardables', ['award_id'=>$award->id]);

        $award->delete();
        $this->assertModelMissing($award);

        $this->assertDatabaseMissing('awardables', ['award_id'=>$award->id]);

        $this->assertModelMissing($awardable);
    }

    /**
     * @test
     */
    public function test_awardable_gets_deleted_if_user_gets_deleted(){

        $user = User::factory()->create();
        $awardable = Awardable::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('awardables', ['user_id' => $user->id]);

        $user->delete();
        $this->assertModelMissing($user);

        $this->assertDatabaseMissing('awardables', ['user_id' => $user->id]);

        $this->assertModelMissing($awardable);
    }

}
