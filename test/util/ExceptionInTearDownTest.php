<?php

namespace Jodes\PHPUnitDecomposed\Test\Util;

use \Exception;

class ExceptionInTearDownTest extends TestCaseUnderTest
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

    public function testExceptionInTearDownTest()
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
        throw new Exception;
    }
}
