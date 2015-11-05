<?php

namespace Jodes\PHPUnitDecomposed\Test\Util;

class IsolationTest extends TestCaseUnderTest
{
    public function testIsInIsolationReturnsFalse()
    {
        $this->assertFalse($this->isInIsolation());
    }

    public function testIsInIsolationReturnsTrue()
    {
        $this->assertTrue($this->isInIsolation());
    }
}
