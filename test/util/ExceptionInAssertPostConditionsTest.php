<?php

namespace Jodes\PHPUnitDecomposed\Test\Util;

use \Exception;

class ExceptionInAssertPostConditionsTest extends TestCaseUnderTest
{
    public $setUp                = false;
    public $assertPreConditions  = false;
    public $assertPostConditions = false;
    public $tearDown             = false;
    public $testSomething        = false;

    protected function setUp()
    {
        $this->setUp = true;
    }

    protected function assertPreConditions()
    {
        $this->assertPreConditions = true;
    }

    public function testExceptionInAssertPostConditionsTest()
    {
        $this->testSomething = true;
    }

    protected function assertPostConditions()
    {
        $this->assertPostConditions = true;
        throw new Exception;
    }

    protected function tearDown()
    {
        $this->tearDown = true;
    }
}
