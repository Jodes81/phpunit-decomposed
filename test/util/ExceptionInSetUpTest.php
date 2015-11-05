<?php

namespace Jodes\PHPUnitDecomposed\Test\Util;

use \Exception;

class ExceptionInSetUpTest extends TestCaseUnderTest
{
    public $setUp                = false;
    public $assertPreConditions  = false;
    public $assertPostConditions = false;
    public $tearDown             = false;
    public $testSomething        = false;

    protected function setUp()
    {
        $this->setUp = true;
        throw new Exception;
    }

    protected function assertPreConditions()
    {
        $this->assertPreConditions = true;
    }

    public function testExceptionInSetUpTest()
    {
        $this->testSomething = true;
    }

    protected function assertPostConditions()
    {
        $this->assertPostConditions = true;
    }

    protected function tearDown()
    {
        $this->tearDown = true;
    }
}
