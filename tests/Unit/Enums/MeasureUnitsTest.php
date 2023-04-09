<?php

namespace Tests\Unit\Enums;

use Tests\TestCase;
use App\Enums\MeasureUnits;

/**
 * @coversDefaultClass App\Enums\MeasureUnitsTest
 */
class MeasureUnitsTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::values
     */
    public function test_can_get_all_values(){
        $this->assertIsArray(MeasureUnits::values());
        foreach(MeasureUnits::values() as $value) $this->assertIsInt($value);
        $this->assertEquals(MeasureUnits::values()[0], MeasureUnits::cases()[0]->value);
    }

    /**
     * @test
     *
     * @covers ::names
     */
    public function test_can_get_all_names(){
        $this->assertIsArray(MeasureUnits::names());
        foreach(MeasureUnits::names() as $value) $this->assertIsString($value);
        $this->assertEquals(MeasureUnits::names()[0], MeasureUnits::cases()[0]->name);
    }

    /**
     * @test
     *
     * @covers ::normalizedName
     */
    public function test_can_get_cases_normalized_names(){
        $this->assertNotNull(MeasureUnits::KILOGRAM->normalizedName());
        $this->assertIsString(MeasureUnits::KILOGRAM->normalizedName());
        $this->assertEquals('Kilogram', MeasureUnits::KILOGRAM->normalizedName());
    }

    /**
     * @test
     *
     * @covers ::abbreviation
     */
    public function test_can_get_abbreviation(){
        $this->assertNotNull(MeasureUnits::KILOGRAM->abbreviation());
        $this->assertIsString(MeasureUnits::KILOGRAM->abbreviation());
        $this->assertEquals('kg', MeasureUnits::KILOGRAM->abbreviation());
    }

    /**
     * @test
     *
     * @covers ::abbreviation
     */
    public function test_abbreviation_defaults_to_null(){
        $this->assertNull(MeasureUnits::TEA_SPOON->abbreviation());
    }

    /**
     * @test
     *
     * @covers ::description
     */
    public function test_can_get_description(){
        $this->assertNotNull(MeasureUnits::TEA_SPOON->description());
        $this->assertIsString(MeasureUnits::TEA_SPOON->description());
        $this->assertEquals('~ 5 ml', MeasureUnits::TEA_SPOON->description());
    }

    /**
     * @test
     *
     * @covers ::description
     */
    public function test_description_defaults_to_null(){
        $this->assertNull(MeasureUnits::KILOGRAM->description());
    }
}
