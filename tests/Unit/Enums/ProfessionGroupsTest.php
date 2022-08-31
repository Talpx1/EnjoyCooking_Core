<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\ProfessionGroups;

/**
 * @coversDefaultClass App\Enums\ProfessionGroups
 */
class ProfessionGroupsTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::values
     */
    public function test_can_get_all_values(){
        $this->assertIsArray(ProfessionGroups::values());
        foreach(ProfessionGroups::values() as $value) $this->assertIsInt($value);
        $this->assertEquals(ProfessionGroups::values()[0], ProfessionGroups::cases()[0]->value);
    }

    /**
     * @test
     *
     * @covers ::names
     */
    public function test_can_get_all_names(){
        $this->assertIsArray(ProfessionGroups::names());
        foreach(ProfessionGroups::names() as $value) $this->assertIsString($value);
        $this->assertEquals(ProfessionGroups::names()[0], ProfessionGroups::cases()[0]->name);
    }

    /**
     * @test
     *
     * @covers ::normalizedName
     */
    public function test_can_get_cases_normalized_names(){
        $this->assertNotNull(ProfessionGroups::ADVERTISING->normalizedName());
        $this->assertIsString(ProfessionGroups::ADVERTISING->normalizedName());
        $this->assertEquals('Advertising', ProfessionGroups::ADVERTISING->normalizedName());
    }
}
