<?php

namespace Tests\Unit;

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
        foreach(ModerationStatuses::values() as $value) $this->assertIsString($value);
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
}
