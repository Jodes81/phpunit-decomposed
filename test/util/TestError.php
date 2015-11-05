<?php

namespace Jodes\PHPUnitDecomposed\Test\Util;

class TestError extends TestCaseUnderTest
{
    protected function testTestError()
    {
        throw new Exception;
    }
    /**
     * @errorHandler enabled
     * Warning!! THIS IS THE DEFAULT BEHAVIOUR! 
     * This test is pretty much useless.
     */
    public function testUserErrorWithHandler(){
        //trigger_error("Some Error", E_USER_ERROR);
        // for some reason, the above code PREVENTS the annotation
        // from resulting in a setErrorHandler() call to $this (as well as the code that
        // calls it in setUseErrorHandlerFromAnnotation()
    }
}
