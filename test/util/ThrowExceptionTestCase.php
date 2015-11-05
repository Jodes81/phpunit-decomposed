<?php

namespace Jodes\PHPUnitDecomposed\Test\Util;

use \RuntimeException;
class ThrowExceptionTestCase extends TestCaseUnderTest
{
    public function testThrowExceptionTestCase()
    {
        throw new RuntimeException('A runtime error occurred');
    }
}