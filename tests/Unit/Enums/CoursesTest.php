<?php

namespace Tests\Unit\Enums;

use Tests\TestCase;
use App\Enums\Courses;

/**
 * @coversDefaultClass App\Enums\Courses
 */
class CoursesTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::values
     */
    public function test_can_get_all_values(){
        $this->assertIsArray(Courses::values());
        foreach(Courses::values() as $value) $this->assertIsInt($value);
        $this->assertEquals(Courses::values()[0], Courses::cases()[0]->value);
    }

    /**
     * @test
     *
     * @covers ::names
     */
    public function test_can_get_all_names(){
        $this->assertIsArray(Courses::names());
        foreach(Courses::names() as $value) $this->assertIsString($value);
        $this->assertEquals(Courses::names()[0], Courses::cases()[0]->name);
    }

    /**
     * @test
     *
     * @covers ::normalizedName
     */
    public function test_can_get_cases_normalized_names(){
        $this->assertNotNull(Courses::APPETIZER->normalizedName());
        $this->assertIsString(Courses::APPETIZER->normalizedName());
        $this->assertEquals('Appetizer', Courses::APPETIZER->normalizedName());
    }
}
