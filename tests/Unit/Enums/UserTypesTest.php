<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\UserTypes;

/**
 * @coversDefaultClass App\Enums\UserTypes
 */
class UserTypesTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::values
     */
    public function test_can_get_all_values(){
        $this->assertIsArray(UserTypes::values());
        foreach(UserTypes::values() as $value) $this->assertIsInt($value);
        $this->assertEquals(UserTypes::values()[0], UserTypes::cases()[0]->value);
    }

    /**
     * @test
     *
     * @covers ::names
     */
    public function test_can_get_all_names(){
        $this->assertIsArray(UserTypes::names());
        foreach(UserTypes::names() as $value) $this->assertIsString($value);
        $this->assertEquals(UserTypes::names()[0], UserTypes::cases()[0]->name);
    }

    /**
     * @test
     *
     * @covers ::normalizedName
     */
    public function test_can_get_cases_normalized_names(){
        $this->assertNotNull(UserTypes::STANDARD->normalizedName());
        $this->assertIsString(UserTypes::STANDARD->normalizedName());
        $this->assertEquals('Standard', UserTypes::STANDARD->normalizedName());
    }
}
