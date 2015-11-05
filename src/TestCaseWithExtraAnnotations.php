<?php

namespace Jodes\PHPUnitDecomposed;

/**
 * Single Responsibility:
 * 
 * @expectedException 
 */
class TestCaseWithExtraAnnotations extends PHPUnitTestCase {
    
    private $causes = array();
    
    protected function checkException(){
        parent::checkException();
        
        if ($this->isCauseExpected()){
            $this->assertExpectedCause();
        }
        
    }
    private function isCauseExpected(){

        var_dump($this->getAnnotations());
    }
    private function assertExpectedCause(){
        // 1. is the exception a subclass of ExceptionWithCauses? If no, fail.
        // 2. does the exception have the expected cause? If no, fail.
        // 3. else, pass.
    }
}
