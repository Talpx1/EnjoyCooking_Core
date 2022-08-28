<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\Permissions;

/**
 * @coversDefaultClass App\Enums\Permissions
 */
class PermissionsTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::values
     */
    public function test_can_get_all_values(){
        $this->assertIsArray(Permissions::values());
        foreach(Permissions::values() as $value) $this->assertIsString($value);
        $this->assertEquals(Permissions::values()[0], Permissions::cases()[0]->value);
    }

    /**
     * @test
     *
     * @covers ::names
     */
    public function test_can_get_all_names(){
        $this->assertIsArray(Permissions::names());
        foreach(Permissions::names() as $value) $this->assertIsString($value);
        $this->assertEquals(Permissions::names()[0], Permissions::cases()[0]->name);
    }
}
