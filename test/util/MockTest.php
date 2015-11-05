<?php

namespace Jodes\PHPUnitDecomposed\Test\Util;

use \PHPUnit_Framework_MockObject_Generator;
use \PHPUnit_Framework_MockObject_MockBuilder;
use \PHPUnit_Framework_MockObject_MockObject;

class MockTest extends TestCaseUnderTest
{
    
    public function testGetMockObjectGenerator(){
        
        $generator = $this->getMockObjectGenerator();
        $this->assertInstanceOf('PHPUnit_Framework_MockObject_Generator', $generator);
    }
    
    public function testGetMock(){
        $mock = $this->getMock('Object');
        $this->assertInstanceOf('PHPUnit_Framework_MockObject_MockObject', $mock);
    }
    
    public function testGetMockBuilder(){
        $builder = $this->getMockBuilder('Object');
        $this->assertInstanceOf('PHPUnit_Framework_MockObject_MockBuilder', $builder);
        // note: does not check that is instantiated with correct args:
        // PHPUnit_Framework_MockObject_MockBuilder($this, $className);
    }
    public function testVerifyMockObjectsWithMatchers(){
        $mock = $this->getMock('\Object', ['someMethod']);
        $mock->expects($this->once())
                ->method('someMethod');
        $mock->someMethod();
    }
    public function testVerifyMockObjectsWithProphecyThatPasses(){
        $prophecy = $this->prophesize('Jodes\PHPUnitDecomposed\Test\Util\Hello');
        $prophecy->hello()->shouldBeCalled();
        $hello = $prophecy->reveal();
        $hello->hello();
    }
    public function testVerifyMockObjectsWithProphecyThatFails(){
        $prophecy = $this->prophesize('Jodes\PHPUnitDecomposed\Test\Util\Hello');
        $prophecy->hello()->shouldBeCalled();
        $hello = $prophecy->reveal();
    }
}
