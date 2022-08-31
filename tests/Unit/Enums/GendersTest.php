<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\Genders;

/**
 * @coversDefaultClass App\Enums\Genders
 */
class GendersTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::values
     */
    public function test_can_get_all_values(){
        $this->assertIsArray(Genders::values());
        foreach(Genders::values() as $value) $this->assertIsInt($value);
        $this->assertEquals(Genders::values()[0], Genders::cases()[0]->value);
    }

    /**
     * @test
     *
     * @covers ::names
     */
    public function test_can_get_all_names(){
        $this->assertIsArray(Genders::names());
        foreach(Genders::names() as $value) $this->assertIsString($value);
        $this->assertEquals(Genders::names()[0], Genders::cases()[0]->name);
    }

    /**
     * @test
     *
     * @covers ::normalizedName
     */
    public function test_can_get_cases_normalized_names(){
        $this->assertNotNull(Genders::MALE->normalizedName());
        $this->assertIsString(Genders::MALE->normalizedName());
        $this->assertEquals('Male', Genders::MALE->normalizedName());
    }
}
