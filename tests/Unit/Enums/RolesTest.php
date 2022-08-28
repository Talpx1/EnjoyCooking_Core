<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\Roles;

/**
 * @coversDefaultClass App\Enums\Roles
 */
class RolesTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::values
     */
    public function test_can_get_all_values(){
        $this->assertIsArray(Roles::values());
        foreach(Roles::values() as $value) $this->assertIsString($value);
        $this->assertEquals(Roles::values()[0], Roles::cases()[0]->value);
    }

    /**
     * @test
     *
     * @covers ::names
     */
    public function test_can_get_all_names(){
        $this->assertIsArray(Roles::names());
        foreach(Roles::names() as $value) $this->assertIsString($value);
        $this->assertEquals(Roles::names()[0], Roles::cases()[0]->name);
    }
}
