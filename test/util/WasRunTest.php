<?php

namespace Jodes\PHPUnitDecomposed\Test\Util;

class WasRunTest extends TestCaseUnderTest
{
    public $wasRun = false;

    public function testWasRunTest()
    {
        $this->wasRun = true;
    }
}
