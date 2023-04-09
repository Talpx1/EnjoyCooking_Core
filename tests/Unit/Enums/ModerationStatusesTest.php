<?php

namespace Tests\Unit\Enums;

use Tests\TestCase;
use App\Enums\ModerationStatuses;

/**
 * @coversDefaultClass App\Enums\ModerationStatuses
 */
class ModerationStatusesTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::values
     */
    public function test_can_get_all_values(){
        $this->assertIsArray(ModerationStatuses::values());
        foreach(ModerationStatuses::values() as $value) $this->assertIsInt($value);
        $this->assertEquals(ModerationStatuses::values()[0], ModerationStatuses::cases()[0]->value);
    }

    /**
     * @test
     *
     * @covers ::names
     */
    public function test_can_get_all_names(){
        $this->assertIsArray(ModerationStatuses::names());
        foreach(ModerationStatuses::names() as $value) $this->assertIsString($value);
        $this->assertEquals(ModerationStatuses::names()[0], ModerationStatuses::cases()[0]->name);
    }

    /**
     * @test
     *
     * @covers ::normalizedName
     */
    public function test_can_get_cases_normalized_names(){
        $this->assertNotNull(ModerationStatuses::APPROVED->normalizedName());
        $this->assertIsString(ModerationStatuses::APPROVED->normalizedName());
        $this->assertEquals('Approved', ModerationStatuses::APPROVED->normalizedName());
    }
}
