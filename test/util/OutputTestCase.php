<?php

namespace Jodes\PHPUnitDecomposed\Test\Util;

class OutputTestCase extends TestCaseUnderTest
{
    public function testExpectOutputStringFooActualFoo()
    {
        $this->expectOutputString('foo');
        print 'foo';
    }

    public function testExpectOutputStringFooActualBar()
    {
        $this->expectOutputString('foo');
        print 'bar';
    }

    public function testExpectOutputRegexFooActualFoo()
    {
        $this->expectOutputRegex('/foo/');
        print 'foo';
    }

    public function testExpectOutputRegexFooActualBar()
    {
        $this->expectOutputRegex('/foo/');
        print 'bar';
    }
    public function testOutputFoo(){
        print 'foo';
    }
    public function testOutputNothing(){
    }
    public function testGetActualOutputDuringTest(){
        print 'foo';
        $this->assertEquals(
                'foo',
                $this->getActualOutput()
            );
    }
}
