<?php

namespace Jodes\PHPUnitDecomposed\Test\Util;

use \Exception;

class ExpectedException extends TestCaseUnderTest
{
    
    /**
     * @expectedException \Exception
     */
    public function testExpectedException(){
        throw new Exception();
    }
    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /ooba/
     */
    public function testExpectedExceptionMessageRegExp(){
        throw new Exception("foobar");
    }
}
