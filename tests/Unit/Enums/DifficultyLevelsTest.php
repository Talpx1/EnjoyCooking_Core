<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\DifficultyLevels;

/**
 * @coversDefaultClass App\Enums\DifficultyLevels
 */
class DifficultyLevelsTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::values
     */
    public function test_can_get_all_values(){
        $this->assertIsArray(DifficultyLevels::values());
        foreach(DifficultyLevels::values() as $value) $this->assertIsInt($value);
        $this->assertEquals(DifficultyLevels::values()[0], DifficultyLevels::cases()[0]->value);
    }

    /**
     * @test
     *
     * @covers ::names
     */
    public function test_can_get_all_names(){
        $this->assertIsArray(DifficultyLevels::names());
        foreach(DifficultyLevels::names() as $value) $this->assertIsString($value);
        $this->assertEquals(DifficultyLevels::names()[0], DifficultyLevels::cases()[0]->name);
    }

    /**
     * @test
     *
     * @covers ::normalizedName
     */
    public function test_can_get_cases_normalized_names(){
        $this->assertNotNull(DifficultyLevels::DIFFICULT->normalizedName());
        $this->assertIsString(DifficultyLevels::DIFFICULT->normalizedName());
        $this->assertEquals('Difficult', DifficultyLevels::DIFFICULT->normalizedName());
    }
}
