<?php

require_once 'bootstrap.php';

use Jodes\PHPUnitDecomposed\PHPUnitTestCase;
//use Jodes\DabbleLabs\Test\PHPUnit\Success;

class UseTestCaseTest extends PHPUnitTestCase {
    
    public function testItActuallyWorks(){
//        $success = new Success("testSuccess");
//        $success->testSuccess();
        $this->assertTrue(true);
    }
    
    /**
     * @dataProvider addProvider
     */
    public function testAdd($a, $b, $c){
        
        /*
        // test to see what is passed to this test
        die(
                "\n"
                ."Data: ".print_r($this->getData(), true)."\n"
                ."DataName: ".$this->getDataName()."\n"
            );
        */
        
        $this->assertEquals($c, $a + $b);
    }
    public function addProvider(){
        return array(
            array (0, 1, 1),
            array (1, 2, 3),
            array (7, 13, 20),
        );
    }
}