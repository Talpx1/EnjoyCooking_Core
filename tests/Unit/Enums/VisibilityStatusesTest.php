<?php

namespace Tests\Unit\Enums;

use Tests\TestCase;
use App\Enums\Courses;
use App\Enums\VisibilityStatuses;

/**
 * @coversDefaultClass App\Enums\VisibilityStatuses
 */
class VisibilityStatusesTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::values
     */
    public function test_can_get_all_values(){
        $this->assertIsArray(VisibilityStatuses::values());
        foreach(VisibilityStatuses::values() as $value) $this->assertIsInt($value);
        $this->assertEquals(VisibilityStatuses::values()[0], VisibilityStatuses::cases()[0]->value);
    }

    /**
     * @test
     *
     * @covers ::names
     */
    public function test_can_get_all_names(){
        $this->assertIsArray(VisibilityStatuses::names());
        foreach(VisibilityStatuses::names() as $value) $this->assertIsString($value);
        $this->assertEquals(VisibilityStatuses::names()[0], VisibilityStatuses::cases()[0]->name);
    }

    /**
     * @test
     *
     * @covers ::normalizedName
     */
    public function test_can_get_cases_normalized_names(){
        $this->assertNotNull(VisibilityStatuses::DRAFT->normalizedName());
        $this->assertIsString(VisibilityStatuses::DRAFT->normalizedName());
        $this->assertEquals('Draft', VisibilityStatuses::DRAFT->normalizedName());
    }
}
