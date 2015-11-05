<?php

namespace Jodes\PHPUnitDecomposed\Test\Util;

class Failure extends TestCaseUnderTest
{
    public function testFailure()
    {
        $this->fail();
    }
}