<?php

namespace Jodes\PHPUnitDecomposed\Test\Util;

class ChangeCurrentWorkingDirectoryTest extends TestCaseUnderTest
{
    public function testChangeCurrentWorkingDirectoryTest()
    {
        chdir('../');
        $this->assertTrue(true);
    }
}
