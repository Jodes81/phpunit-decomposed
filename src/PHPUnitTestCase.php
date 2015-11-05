<?php

namespace Jodes\PHPUnitDecomposed;

use \PHPUnit_Util_PHP;
use \PHPUnit_Util_GlobalState;
use \Text_Template;
use \Exception;
use \Throwable;
use \ReflectionClass;
use \PHPUnit_Framework_AssertionFailedError;
use \PHPUnit_Framework_Exception;
use \PHPUnit_Framework_MockObject_Generator;
use \PHPUnit_Framework_MockObject_MockBuilder;
use \PHPUnit_Framework_MockObject_MockObject;
use \PHPUnit_Framework_Constraint_Exception;
use \PHPUnit_Framework_Constraint_ExceptionMessage;
use \PHPUnit_Framework_Constraint_ExceptionMessageRegExp;
use \PHPUnit_Framework_Constraint_ExceptionCode;
use \PHPUnit_Framework_TestCase;
use \PHPUnit_Framework_TestResult;
use \PHPUnit_Util_Test;
use \PHPUnit_Runner_BaseTestRunner;


use SebastianBergmann\GlobalState\Snapshot;
use SebastianBergmann\GlobalState\Restorer;
use SebastianBergmann\GlobalState\Blacklist;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Exporter\Exporter;
use Prophecy\Exception\Prediction\PredictionException;
use Prophecy\Prophet;

abstract class PHPUnitTestCase extends PHPUnit_Framework_TestCase
{

    /** @var bool */
    private $replacement_inIsolation = false;
    /** @var array */
    private $replacement_data = array();
    /** @var string */
    private $replacement_dataName = '';
    /** @var bool */
    private $replacement_useErrorHandler = null;
    /** @var mixed */
    private $replacement_expectedException = null;
    /** @var string */
    private $replacement_expectedExceptionMessage = '';
    /** @var string */
    private $replacement_expectedExceptionMessageRegExp = '';
    /** @var int */
    private $replacement_expectedExceptionCode;
    /** @var string */
    private $replacement_name = null;
    /** @var array*/
    private $replacement_dependencies = array();
    /** @var array */
    private $replacement_dependencyInput = array();
    /** @var array */
    private $replacement_iniSettings = array();
    /** @var array */
    private $replacement_locale = array();
    /** @var array */
    private $replacement_mockObjects = array();
    /** @var array */
    private $replacement_mockObjectGenerator = null;
    /** @var int */
    private $replacement_status;
    /** @var string */
    private $replacement_statusMessage = '';
    /** @var int */
    private $replacement_numAssertions = 0;
    /** @var PHPUnit_Framework_TestResult */
    private $replacement_result;
    /** @var mixed */
    private $replacement_testResult;
    /** @var string */
    private $replacement_output = '';
    /** @var string */
    private $replacement_outputExpectedRegex = null;
    /** @var string */
    private $replacement_outputExpectedString = null;
    /** @var mixed */
    private $replacement_outputCallback = false;
    /** @var bool */
    private $replacement_outputBufferingActive = false;
    /** @var int */
    private $replacement_outputBufferingLevel;
    /** @var SebastianBergmann\GlobalState\Snapshot */
    private $replacement_snapshot;
    /** @var Prophecy\Prophet */
    private $replacement_prophet;
    /** @var bool */
    private $replacement_disallowChangesToGlobalState = false;

    /**
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        if ($name !== null) {
            $this->setName($name);
        }

        $this->replacement_data                = $data;
        $this->replacement_dataName            = $dataName;
    }    

    /**
     * @return array
     */
    public function getAnnotations()
    {
        return PHPUnit_Util_Test::parseTestMethodAnnotations(
            get_class($this),
            $this->replacement_name
        );
    }

    /**
     * @param  bool   $withDataSet
     * @return string
     */
    public function getName($withDataSet = true)
    {
        if ($withDataSet) {
            return $this->replacement_name . $this->getDataSetAsString(false);
        } else {
            return $this->replacement_name;
        }
    }


    /**
     * @return string
     */
    public function getActualOutput()
    {
        if (!$this->replacement_outputBufferingActive) {
            return $this->replacement_output;
        } else {
            return ob_get_contents();
        }
    }

    /**
     * @return bool
     */
    public function hasOutput()
    {
        if (strlen($this->replacement_output) === 0) {
            return false;
        }

        if ($this->hasExpectationOnOutput()) {
            return false;
        }

        return true;
    }

    /**
     * @param string $expectedRegex
     * @throws PHPUnit_Framework_Exception
     */
    public function expectOutputRegex($expectedRegex)
    {
        if ($this->replacement_outputExpectedString !== null) {
            throw new PHPUnit_Framework_Exception;
        }

        if (is_string($expectedRegex) || is_null($expectedRegex)) {
            $this->replacement_outputExpectedRegex = $expectedRegex;
        }
    }

    /**
     * @param string $expectedString
     */
    public function expectOutputString($expectedString)
    {
        if ($this->replacement_outputExpectedRegex !== null) {
            throw new PHPUnit_Framework_Exception;
        }

        if (is_string($expectedString) || is_null($expectedString)) {
            $this->replacement_outputExpectedString = $expectedString;
        }
    }

    /**
     * @return bool
     */
    public function hasExpectationOnOutput()
    {
        return is_string($this->replacement_outputExpectedString) || is_string($this->replacement_outputExpectedRegex);
    }

    /**
     * @return string
     */
    public function getExpectedException()
    {
        return $this->replacement_expectedException;
    }

    /**
     * @param mixed  $exceptionName
     * @param string $exceptionMessage
     * @param int    $exceptionCode
     */
    public function setExpectedException($exceptionName, $exceptionMessage = '', $exceptionCode = null)
    {
        $this->replacement_expectedException        = $exceptionName;
        $this->replacement_expectedExceptionMessage = $exceptionMessage;
        $this->replacement_expectedExceptionCode    = $exceptionCode;
    }

    /**
     * @param mixed  $exceptionName
     * @param string $exceptionMessageRegExp
     * @param int    $exceptionCode
     */
    public function setExpectedExceptionRegExp($exceptionName, $exceptionMessageRegExp = '', $exceptionCode = null)
    {
        $this->replacement_expectedException              = $exceptionName;
        $this->replacement_expectedExceptionMessageRegExp = $exceptionMessageRegExp;
        $this->replacement_expectedExceptionCode          = $exceptionCode;
    }
    
    protected function setExpectedExceptionFromAnnotation()
    {
        try {
            $expectedException = PHPUnit_Util_Test::getExpectedException(
                get_class($this),
                $this->replacement_name
            );
            if ($expectedException !== false) {
                $this->setExpectedException(
                    $expectedException['class'],
                    $expectedException['message'],
                    $expectedException['code']
                );

                if (!empty($expectedException['message_regex'])) {
                    $this->setExpectedExceptionRegExp(
                        $expectedException['class'],
                        $expectedException['message_regex'],
                        $expectedException['code']
                    );
                }
            }
        } catch (ReflectionException $e) {
        }
    }

    /**
     * @param bool $useErrorHandler
     */
    public function setUseErrorHandler($useErrorHandler)
    {
        $this->replacement_useErrorHandler = $useErrorHandler;
    }

    protected function setUseErrorHandlerFromAnnotation()
    {
        try {
            $useErrorHandler = PHPUnit_Util_Test::getErrorHandlerSettings(
                get_class($this),
                $this->replacement_name
            );

            if ($useErrorHandler !== null) {
                $this->setUseErrorHandler($useErrorHandler);
            }
        } catch (ReflectionException $e) {
        }
    }

    protected function checkRequirements()
    {
        if (!$this->replacement_name || !method_exists($this, $this->replacement_name)) {
            return;
        }

        $missingRequirements = PHPUnit_Util_Test::getMissingRequirements(
            get_class($this),
            $this->replacement_name
        );

        if ($missingRequirements) {
            $this->markTestSkipped(implode(PHP_EOL, $missingRequirements));
        }
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->replacement_status;
    }

    /**
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->replacement_statusMessage;
    }

    /**
     * Runs the test case and collects the results in a TestResult object.
     * If no TestResult object is passed a new one will be created.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @return PHPUnit_Framework_TestResult
     * @throws PHPUnit_Framework_Exception
     */
    public function run(PHPUnit_Framework_TestResult $result = null)
    {
        if ($result === null) {
            $result = $this->createResult();
        }

        if (!$this instanceof PHPUnit_Framework_Warning) {
            $this->setTestResultObject($result);
            $this->setUseErrorHandlerFromAnnotation();
        }

        if ($this->replacement_useErrorHandler !== null) {
            $oldErrorHandlerSetting = $result->getConvertErrorsToExceptions();
            $result->convertErrorsToExceptions($this->replacement_useErrorHandler);
        }

        if (!$this instanceof PHPUnit_Framework_Warning && !$this->handleDependencies()) {
            return;
        }
        if ($this->runTestInSeparateProcess === true &&
            $this->replacement_inIsolation !== true &&
            !$this instanceof PHPUnit_Extensions_SeleniumTestCase &&
            !$this instanceof PHPUnit_Extensions_PhptTestCase) {
            $class = new ReflectionClass($this);

            $template = new Text_Template(
                __DIR__ . '/TestCaseMethod.tpl'
            );

            if ($this->preserveGlobalState) {
                $constants     = PHPUnit_Util_GlobalState::getConstantsAsString();
                $globals       = PHPUnit_Util_GlobalState::getGlobalsAsString();
                $includedFiles = PHPUnit_Util_GlobalState::getIncludedFilesAsString();
                $iniSettings   = PHPUnit_Util_GlobalState::getIniSettingsAsString();
            } else {
                $constants     = '';
                if (!empty($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
                    $globals     = '$GLOBALS[\'__PHPUNIT_BOOTSTRAP\'] = ' . var_export($GLOBALS['__PHPUNIT_BOOTSTRAP'], true) . ";\n";
                } else {
                    $globals     = '';
                }
                $includedFiles = '';
                $iniSettings   = '';
            }

            $coverage                                = $result->getCollectCodeCoverageInformation()       ? 'true' : 'false';
            $isStrictAboutTestsThatDoNotTestAnything = $result->isStrictAboutTestsThatDoNotTestAnything() ? 'true' : 'false';
            $isStrictAboutOutputDuringTests          = $result->isStrictAboutOutputDuringTests()          ? 'true' : 'false';
            $isStrictAboutTestSize                   = $result->isStrictAboutTestSize()                   ? 'true' : 'false';
            $isStrictAboutTodoAnnotatedTests         = $result->isStrictAboutTodoAnnotatedTests()         ? 'true' : 'false';

            if (defined('PHPUNIT_COMPOSER_INSTALL')) {
                $composerAutoload = var_export(PHPUNIT_COMPOSER_INSTALL, true);
            } else {
                $composerAutoload = '\'\'';
            }

            if (defined('__PHPUNIT_PHAR__')) {
                $phar = var_export(__PHPUNIT_PHAR__, true);
            } else {
                $phar = '\'\'';
            }

            if ($result->getCodeCoverage()) {
                $codeCoverageFilter = $result->getCodeCoverage()->filter();
            } else {
                $codeCoverageFilter = null;
            }

            $data               = var_export(serialize($this->replacement_data), true);
            $dataName           = var_export($this->replacement_dataName, true);
            $dependencyInput    = var_export(serialize($this->replacement_dependencyInput), true);
            $includePath        = var_export(get_include_path(), true);
            $codeCoverageFilter = var_export(serialize($codeCoverageFilter), true);
            // must do these fixes because TestCaseMethod.tpl has unserialize('{data}') in it, and we can't break BC
            // the lines above used to use addcslashes() rather than var_export(), which breaks null byte escape sequences
            $data               = "'." . $data . ".'";
            $dataName           = "'.(" . $dataName . ").'";
            $dependencyInput    = "'." . $dependencyInput . ".'";
            $includePath        = "'." . $includePath . ".'";
            $codeCoverageFilter = "'." . $codeCoverageFilter . ".'";

            $template->setVar(
                array(
                    'composerAutoload'                        => $composerAutoload,
                    'phar'                                    => $phar,
                    'filename'                                => $class->getFileName(),
                    'className'                               => $class->getName(),
                    'methodName'                              => $this->replacement_name,
                    'collectCodeCoverageInformation'          => $coverage,
                    'data'                                    => $data,
                    'dataName'                                => $dataName,
                    'dependencyInput'                         => $dependencyInput,
                    'constants'                               => $constants,
                    'globals'                                 => $globals,
                    'include_path'                            => $includePath,
                    'included_files'                          => $includedFiles,
                    'iniSettings'                             => $iniSettings,
                    'isStrictAboutTestsThatDoNotTestAnything' => $isStrictAboutTestsThatDoNotTestAnything,
                    'isStrictAboutOutputDuringTests'          => $isStrictAboutOutputDuringTests,
                    'isStrictAboutTestSize'                   => $isStrictAboutTestSize,
                    'isStrictAboutTodoAnnotatedTests'         => $isStrictAboutTodoAnnotatedTests,
                    'codeCoverageFilter'                      => $codeCoverageFilter
                )
            );

            $this->prepareTemplate($template);

            $php = PHPUnit_Util_PHP::factory();
            $php->runTestJob($template->render(), $this, $result);
        } else {
            $result->run($this);
        }

        if ($this->replacement_useErrorHandler !== null) {
            $result->convertErrorsToExceptions($oldErrorHandlerSetting);
        }

        $this->replacement_result = null;

        return $result;
    }
    
    public function runBare()
    {
        if ($this->replacement_name === null) {
            throw new PHPUnit_Framework_Exception(
                'PHPUnit_Framework_TestCase::runTest(): $this->replacement_name must not be null.'
            );
        }
        $this->replacement_numAssertions = 0;
        $this->replacement_snapshotGlobalState();
        $this->replacement_startOutputBuffering();
        clearstatcache();
        $currentWorkingDirectory = getcwd();
        $hookMethods = PHPUnit_Util_Test::getHookMethods(get_class($this));
        
        try {
            $hasMetRequirements = false;
            $this->checkRequirements();
            $hasMetRequirements = true;
            if ($this->replacement_inIsolation) {
                foreach ($hookMethods['beforeClass'] as $method) {
                    $this->$method();
                }
            }
            $this->setExpectedExceptionFromAnnotation();
            foreach ($hookMethods['before'] as $method) {
                $this->$method();
            }
            $this->assertPreConditions();
            $this->replacement_testResult = $this->runTest("This is the one");
            $this->verifyMockObjects();
            $this->assertPostConditions();

            $this->replacement_status = PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
          
      } catch (PHPUnit_Framework_IncompleteTest $e) {
            $this->replacement_status        = PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE;
            $this->replacement_statusMessage = $e->getMessage();
        } catch (PHPUnit_Framework_SkippedTest $e) {
            $this->replacement_status        = PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED;
            $this->replacement_statusMessage = $e->getMessage();
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            $this->replacement_status        = PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
            $this->replacement_statusMessage = $e->getMessage();
        } catch (PredictionException $e) {
            $this->replacement_status        = PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
            $this->replacement_statusMessage = $e->getMessage();
        } catch (Throwable $_e) {
            $e = $_e;
        } catch (Exception $_e) {
            $e = $_e;
        }

        if (isset($e)) {
            $this->replacement_status        = PHPUnit_Runner_BaseTestRunner::STATUS_ERROR;
            $this->replacement_statusMessage = $e->getMessage();
        }

        // Clean up the mock objects.
        $this->replacement_mockObjects = array();
        $this->replacement_prophet     = null;

        // Tear down the fixture. An exception raised in tearDown() will be
        // caught and passed on when no exception was raised before.
        try {
            if ($hasMetRequirements) {
                foreach ($hookMethods['after'] as $method) {
                    $this->$method();
                }

                if ($this->replacement_inIsolation) {
                    foreach ($hookMethods['afterClass'] as $method) {
                        $this->$method();
                    }
                }
            }
        } catch (Throwable $_e) {
            if (!isset($e)) {
                $e = $_e;
            }
        } catch (Exception $_e) {
            if (!isset($e)) {
                $e = $_e;
            }
        }

        try {
            $this->replacement_stopOutputBuffering();
        } catch (PHPUnit_Framework_RiskyTestError $_e) {
            if (!isset($e)) {
                $e = $_e;
            }
        }

        clearstatcache();

        if ($currentWorkingDirectory != getcwd()) {
            chdir($currentWorkingDirectory);
        }

        $this->replacement_restoreGlobalState();

        // Clean up INI settings.
        foreach ($this->replacement_iniSettings as $varName => $oldValue) {
            ini_set($varName, $oldValue);
        }

        $this->replacement_iniSettings = array();

        // Clean up locale settings.
        foreach ($this->replacement_locale as $category => $locale) {
            setlocale($category, $locale);
        }

        // Perform assertion on output.
        if (!isset($e)) {
            try {
                if ($this->replacement_outputExpectedRegex !== null) {
                    $this->assertRegExp($this->replacement_outputExpectedRegex, $this->replacement_output);
                } elseif ($this->replacement_outputExpectedString !== null) {
                    $this->assertEquals($this->replacement_outputExpectedString, $this->replacement_output);
                }
            } catch (Throwable $_e) {
                $e = $_e;
            } catch (Exception $_e) {
                $e = $_e;
            }
        }

        // Workaround for missing "finally".
        if (isset($e)) {
            if ($e instanceof PredictionException) {
                $e = new PHPUnit_Framework_AssertionFailedError($e->getMessage());
            }

            $this->onNotSuccessfulTest($e);
        }
    }


/* ====================================================================================================
 *  START DECOMPOSITION MODIFICATIONS
 ======================================================================================================*/    
    
    
    /**
     *
     * Written to by tryInvokeTestMethod()
     * Read by runTest()
     * Read by handleExceptionThrownByTestMethod()
     * 
     * @var Throwable
     */
    protected $exceptionCaughtInTestMethod;
    
    /**
     * Will return any Exception generated while running the test method 
     * (including PHP_Framework_Exception)
     * 
     * @return Exception
     */
    protected function getExceptionThrownByTestMethod(){
        return $this->exceptionCaughtInTestMethod;
    }
    
    /**
     * A new addition to PHPUnit!!! Flow is given to this method when an 
     * exception has been caught, and it's not a PHPUnit_Framework_Exception.
     * 
     * The behaviour of this method for this base class then asserts that there 
     * was an expected exception, and that it matches the exception actually caught.
     * It then checks it for the message and code, if such expectations are set.
     * 
     * So this can be overridden to ignore exceptions, or to add further checking.
     * 
     * The exception caught can be accessed by calling $this->getExceptionThrownByTestMethod();
     */
    protected function checkException(){
        $this->assertThat(
            $this->exceptionCaughtInTestMethod,
            new PHPUnit_Framework_Constraint_Exception(
                $this->replacement_expectedException
            )
        );
        if (is_string($this->replacement_expectedExceptionMessage) &&
            !empty($this->replacement_expectedExceptionMessage)) {
            $this->assertThat(
                $this->exceptionCaughtInTestMethod,
                new PHPUnit_Framework_Constraint_ExceptionMessage(
                    $this->replacement_expectedExceptionMessage
                )
            );
        }
        if (is_string($this->replacement_expectedExceptionMessageRegExp) &&
            !empty($this->replacement_expectedExceptionMessageRegExp)) {
            $this->assertThat(
                $this->exceptionCaughtInTestMethod,
                new PHPUnit_Framework_Constraint_ExceptionMessageRegExp(
                    $this->replacement_expectedExceptionMessageRegExp
                )
            );
        }
        if ($this->replacement_expectedExceptionCode !== null) {
            $this->assertThat(
                $this->exceptionCaughtInTestMethod,
                new PHPUnit_Framework_Constraint_ExceptionCode(
                    $this->replacement_expectedExceptionCode
                )
            );
        }
    }
/* ====================================================================================================
 *  END DECOMPOSITION MODIFICATIONS
 ======================================================================================================*/    
/* ====================================================================================================
 *  START DEBUG ADDITIONS
 ======================================================================================================*/    
//        $this->replacement_data                = $data;
//        $this->replacement_dataName            = $dataName;
        public function getData(){
            return $this->replacement_data;
        }
        public function getDataName(){
            return $this->replacement_dataName;
        }
/* ====================================================================================================
  *  START DEBUG ADDITIONS
 ======================================================================================================*/    
    
    
    /**
     * The result of: 
     * 
     *  $class  = new ReflectionClass($this);
     *  $method = $class->getMethod($this->name);
     * 
     * Written to by attainTestMethodReflected()
     * Used by tryInvokeTestMethod()
     * 
     * @var type 
     */
    private $testMethodReflected;
    
    /**
     * Written to by tryInvokeTestMethod()
     * Read by runTest()
     * 
     * @var mixed
     */
    private $runTestResult;
    private function couldTheExceptionThrownByTestMethodHaveBeenExpected(){
            $checkException = false;

            if (is_string($this->replacement_expectedException)) {
                $checkException = true;

                if ($this->exceptionCaughtInTestMethod instanceof PHPUnit_Framework_Exception) {
                    $checkException = false;
                }

                $reflector = new ReflectionClass($this->replacement_expectedException);

                if ($this->replacement_expectedException == 'PHPUnit_Framework_Exception' ||
                    $reflector->isSubclassOf('PHPUnit_Framework_Exception')) {
                    $checkException = true;
                }
            }
            return $checkException;
    }
    private function attainTestMethodReflected(){
        try {
            $class  = new ReflectionClass($this);
            $this->testMethodReflected = $class->getMethod($this->replacement_name);
        } catch (ReflectionException $e) {
            $this->fail($e->getMessage());
        }
    }
    
    private function makeSureNameIsNotNull(){
        if ($this->replacement_name === null) {
            throw new PHPUnit_Framework_Exception(
                'PHPUnit_Framework_TestCase::runTest(): $this->replacement_name must not be null.'
            );
        }
    }
    
    private function tryInvokeTestMethod(){
        try {
            $this->runTestResult = $this->testMethodReflected->invokeArgs(
                $this,
                array_merge($this->replacement_data, $this->replacement_dependencyInput)
            );
        } catch (Throwable $_e) {
            $this->exceptionCaughtInTestMethod = $_e;
        } catch (Exception $_e) {
            $this->exceptionCaughtInTestMethod = $_e;
        }
    }

    private function isAnExceptionExpected(){
        return is_string($this->replacement_expectedException);
    }
    
    private function handleException(){
            if (
                $this->couldTheExceptionThrownByTestMethodHaveBeenExpected()
            ) {
                $this->checkException();
            } else {
                throw $this->exceptionCaughtInTestMethod;
            }
    }
    private function assertThatTheExpectedExceptionOccurred(){
        // (if there is an expected exception, and)...
        // if no exception was caught, the test should fail
        $this->assertThat(
            null,
            new PHPUnit_Framework_Constraint_Exception(
                $this->replacement_expectedException
            )
        );
    }
/* ====================================================================================================
 *  END REFACTORINGS THAT AID UNDERSTANDING
 ======================================================================================================*/    
    
    /**
     * Override to run the test and assert its state.
     *
     * @return mixed
     * @throws Exception|PHPUnit_Framework_Exception
     * @throws PHPUnit_Framework_Exception
     */
    protected function runTest()
    {
        $this->makeSureNameIsNotNull();
        
        $this->attainTestMethodReflected();
        
        $this->tryInvokeTestMethod();
        
        if (isset($this->exceptionCaughtInTestMethod)) {
            
            $this->handleException();
            
            return;
        }
        
        if ($this->isAnExceptionExpected()){
            
            $this->assertThatTheExpectedExceptionOccurred();
            
        }
        
        return $this->runTestResult;
    }
    /**
     * Verifies the mock object expectations.
     */
    protected function verifyMockObjects()
    {
        foreach ($this->replacement_mockObjects as $mockObject) {
            if ($mockObject->__phpunit_hasMatchers()) {
                $this->replacement_numAssertions++;
            }

            $mockObject->__phpunit_verify();
        }

        if ($this->replacement_prophet !== null) {
            try {
                $this->replacement_prophet->checkPredictions();
            } catch (Throwable $t) {
                /* Intentionally left empty */
            } catch (Exception $e) {
                /* Intentionally left empty */
            }

            foreach ($this->replacement_prophet->getProphecies() as $objectProphecy) {
                foreach ($objectProphecy->getMethodProphecies() as $methodProphecies) {
                    foreach ($methodProphecies as $methodProphecy) {
                        $this->replacement_numAssertions += count($methodProphecy->getCheckedPredictions());
                    }
                }
            }

            if (isset($e)) {
                throw $e;
            }
        }
    }

    /**
     * @param  string
     */
    public function setName($name)
    {
        $this->replacement_name = $name;
    }

    /**
     * @param array $dependencies
     */
    public function setDependencies(array $dependencies)
    {
        $this->replacement_dependencies = $dependencies;
    }

    /**
     * @return bool
     */
    public function hasDependencies()
    {
        return count($this->replacement_dependencies) > 0;
    }

    /**
     * @param array $dependencyInput
     */
    public function setDependencyInput(array $dependencyInput)
    {
        $this->replacement_dependencyInput = $dependencyInput;
    }

    /**
     * @param bool $disallowChangesToGlobalState
     */
    public function setDisallowChangesToGlobalState($disallowChangesToGlobalState)
    {
        $this->replacement_disallowChangesToGlobalState = $disallowChangesToGlobalState;
    }


    /**
     * @param  bool                        $inIsolation
     * @throws PHPUnit_Framework_Exception
     */
    public function setInIsolation($inIsolation)
    {
        if (is_bool($inIsolation)) {
            $this->replacement_inIsolation = $inIsolation;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * @return bool
     */
    public function isInIsolation()
    {
        return $this->replacement_inIsolation;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->replacement_testResult;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->replacement_testResult = $result;
    }

    /**
     * @param  callable $callback
     * @throws PHPUnit_Framework_Exception
     */
    public function setOutputCallback($callback)
    {
        if (!is_callable($callback)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'callback');
        }

        $this->replacement_outputCallback = $callback;
    }

    /**
     * @return PHPUnit_Framework_TestResult
     */
    public function getTestResultObject()
    {
        return $this->replacement_result;
    }

    /**
     * @param PHPUnit_Framework_TestResult $result
     */
    public function setTestResultObject(PHPUnit_Framework_TestResult $result)
    {
        $this->replacement_result = $result;
    }

    /**
     * This method is a wrapper for the ini_set() function that automatically
     * resets the modified php.ini setting to its original value after the
     * test is run.
     *
     * @param  string                      $varName
     * @param  string                      $newValue
     * @throws PHPUnit_Framework_Exception
     */
    protected function iniSet($varName, $newValue)
    {
        if (!is_string($varName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $currentValue = ini_set($varName, $newValue);

        if ($currentValue !== false) {
            $this->replacement_iniSettings[$varName] = $currentValue;
        } else {
            throw new PHPUnit_Framework_Exception(
                sprintf(
                    'INI setting "%s" could not be set to "%s".',
                    $varName,
                    $newValue
                )
            );
        }
    }

    /**
     * This method is a wrapper for the setlocale() function that automatically
     * resets the locale to its original value after the test is run.
     *
     * @param  int                         $category
     * @param  string                      $locale
     * @throws PHPUnit_Framework_Exception
     */
    protected function setLocale()
    {
        $args = func_get_args();

        if (count($args) < 2) {
            throw new PHPUnit_Framework_Exception;
        }

        $category = $args[0];
        $locale   = $args[1];

        $categories = array(
          LC_ALL, LC_COLLATE, LC_CTYPE, LC_MONETARY, LC_NUMERIC, LC_TIME
        );

        if (defined('LC_MESSAGES')) {
            $categories[] = LC_MESSAGES;
        }

        if (!in_array($category, $categories)) {
            throw new PHPUnit_Framework_Exception;
        }

        if (!is_array($locale) && !is_string($locale)) {
            throw new PHPUnit_Framework_Exception;
        }

        $this->replacement_locale[$category] = setlocale($category, null);

        $result = call_user_func_array('setlocale', $args);

        if ($result === false) {
            throw new PHPUnit_Framework_Exception(
                'The locale functionality is not implemented on your platform, ' .
                'the specified locale does not exist or the category name is ' .
                'invalid.'
            );
        }
    }

    /**
     * Returns a mock object for the specified class.
     *
     * @param  string           $originalClassName       Name of the class to mock.
     * @param  array|null       $methods                 When provided, only methods whose names are in the array
     *                                                                          are replaced with a configurable test double. The behavior
     *                                                                          of the other methods is not changed.
     *                                                                          Providing null means that no methods will be replaced.
     * @param  array            $arguments               Parameters to pass to the original class' constructor.
     * @param  string           $mockClassName           Class name for the generated test double class.
     * @param  bool             $callOriginalConstructor Can be used to disable the call to the original class' constructor.
     * @param  bool             $callOriginalClone       Can be used to disable the call to the original class' clone constructor.
     * @param  bool             $callAutoload            Can be used to disable __autoload() during the generation of the test double class.
     * @param  bool             $cloneArguments
     * @param  bool             $callOriginalMethods
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws PHPUnit_Framework_Exception
     */
    public function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false, $callOriginalMethods = false)
    {
        $mockObject = $this->getMockObjectGenerator()->getMock(
            $originalClassName,
            $methods,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $cloneArguments,
            $callOriginalMethods
        );

        $this->replacement_mockObjects[] = $mockObject;

        return $mockObject;
    }

    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @param  string           $className
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function getMockBuilder($className)
    {
        return new PHPUnit_Framework_MockObject_MockBuilder($this, $className);
    }

    /**
     * Returns a mock object for the specified abstract class with all abstract
     * methods of the class mocked. Concrete methods are not mocked by default.
     * To mock concrete methods, use the 7th parameter ($mockedMethods).
     *
     * @param  string                                  $originalClassName
     * @param  array                                   $arguments
     * @param  string                                  $mockClassName
     * @param  bool                                    $callOriginalConstructor
     * @param  bool                                    $callOriginalClone
     * @param  bool                                    $callAutoload
     * @param  array                                   $mockedMethods
     * @param  bool                                    $cloneArguments
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws PHPUnit_Framework_Exception
     */
    public function getMockForAbstractClass($originalClassName, array $arguments = array(), $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = array(), $cloneArguments = false)
    {
        $mockObject = $this->getMockObjectGenerator()->getMockForAbstractClass(
            $originalClassName,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $mockedMethods,
            $cloneArguments
        );

        $this->replacement_mockObjects[] = $mockObject;

        return $mockObject;
    }
    /**
     * Returns a mock object for the specified trait with all abstract methods
     * of the trait mocked. Concrete methods to mock can be specified with the
     * `$mockedMethods` parameter.
     *
     * @param  string                                  $traitName
     * @param  array                                   $arguments
     * @param  string                                  $mockClassName
     * @param  bool                                    $callOriginalConstructor
     * @param  bool                                    $callOriginalClone
     * @param  bool                                    $callAutoload
     * @param  array                                   $mockedMethods
     * @param  bool                                    $cloneArguments
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws PHPUnit_Framework_Exception
     */
    public function getMockForTrait($traitName, array $arguments = array(), $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = array(), $cloneArguments = false)
    {
        $mockObject = $this->getMockObjectGenerator()->getMockForTrait(
            $traitName,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $mockedMethods,
            $cloneArguments
        );

        $this->replacement_mockObjects[] = $mockObject;

        return $mockObject;
    }

    /**
     * @param  string|null                       $classOrInterface
     * @return \Prophecy\Prophecy\ObjectProphecy
     * @throws \LogicException
     */
    protected function prophesize($classOrInterface = null)
    {
        return $this->replacement_getProphet()->prophesize($classOrInterface);
    }

    /**
     * @param int $count
     */
    public function addToAssertionCount($count)
    {
        $this->replacement_numAssertions += $count;
    }

    /**
     * Returns the number of assertions performed by this test.
     *
     * @return int
     */
    public function getNumAssertions()
    {
        return $this->replacement_numAssertions;
    }
    /**
     * Gets the data set description of a TestCase.
     *
     * @param  bool   $includeData
     * @return string
     */
    protected function getDataSetAsString($includeData = true)
    {
        $buffer = '';

        if (!empty($this->replacement_data)) {
            if (is_int($this->replacement_dataName)) {
                $buffer .= sprintf(' with data set #%d', $this->replacement_dataName);
            } else {
                $buffer .= sprintf(' with data set "%s"', $this->replacement_dataName);
            }

            $exporter = new Exporter;

            if ($includeData) {
                $buffer .= sprintf(' (%s)', $exporter->shortenedRecursiveExport($this->replacement_data));
            }
        }

        return $buffer;
    }

    protected function handleDependencies()
    {
        if (!empty($this->replacement_dependencies) && !$this->replacement_inIsolation) {
            $className  = get_class($this);
            $passed     = $this->replacement_result->passed();
            $passedKeys = array_keys($passed);
            $numKeys    = count($passedKeys);

            for ($i = 0; $i < $numKeys; $i++) {
                $pos = strpos($passedKeys[$i], ' with data set');

                if ($pos !== false) {
                    $passedKeys[$i] = substr($passedKeys[$i], 0, $pos);
                }
            }

            $passedKeys = array_flip(array_unique($passedKeys));

            foreach ($this->replacement_dependencies as $dependency) {
                if (strpos($dependency, '::') === false) {
                    $dependency = $className . '::' . $dependency;
                }

                if (!isset($passedKeys[$dependency])) {
                    $this->replacement_result->addError(
                        $this,
                        new PHPUnit_Framework_SkippedTestError(
                            sprintf(
                                'This test depends on "%s" to pass.',
                                $dependency
                            )
                        ),
                        0
                    );

                    return false;
                }

                if (isset($passed[$dependency])) {
                    if ($passed[$dependency]['size'] != PHPUnit_Util_Test::UNKNOWN &&
                        $this->getSize() != PHPUnit_Util_Test::UNKNOWN &&
                        $passed[$dependency]['size'] > $this->getSize()) {
                        $this->replacement_result->addError(
                            $this,
                            new PHPUnit_Framework_SkippedTestError(
                                'This test depends on a test that is larger than itself.'
                            ),
                            0
                        );

                        return false;
                    }

                    $this->replacement_dependencyInput[$dependency] = $passed[$dependency]['result'];
                } else {
                    $this->replacement_dependencyInput[$dependency] = null;
                }
            }
        }

        return true;
    }

    /**
     * Get the mock object generator, creating it if it doesn't exist.
     *
     * @return PHPUnit_Framework_MockObject_Generator
     */
    protected function getMockObjectGenerator()
    {
        if (null === $this->replacement_mockObjectGenerator) {
            $this->replacement_mockObjectGenerator = new PHPUnit_Framework_MockObject_Generator;
        }

        return $this->replacement_mockObjectGenerator;
    }

    private function replacement_startOutputBuffering()
    {
        $this->outputBufferingStartLevel = ob_get_level();
        
        ob_start();

        $this->replacement_outputBufferingActive = true;
        $this->replacement_outputBufferingLevel  = $this->outputBufferingStartLevel + 1;
    }
    
    private function replacement_stopOutputBuffering()
    {
        if (ob_get_level() != $this->replacement_outputBufferingLevel) {
            while (ob_get_level() > $this->outputBufferingStartLevel) {
                ob_end_clean();
            }

            throw new PHPUnit_Framework_RiskyTestError(
                'Test code or tested code did not (only) close its own output buffers'
            );
        }

        $output = ob_get_contents();

        if ($this->replacement_outputCallback === false) {
            $this->replacement_output = $output;
        } else {
            $this->replacement_output = call_user_func_array(
                $this->replacement_outputCallback,
                array($output)
            );
        }

        ob_end_clean();

        $this->replacement_outputBufferingActive = false;
        $this->replacement_outputBufferingLevel  = ob_get_level();
    }

    private function replacement_snapshotGlobalState()
    {
        $backupGlobals = $this->backupGlobals === null || $this->backupGlobals === true;

        if ($this->runTestInSeparateProcess || $this->replacement_inIsolation ||
            (!$backupGlobals && !$this->backupStaticAttributes)) {
            return;
        }

        $this->replacement_snapshot = $this->replacement_createGlobalStateSnapshot($backupGlobals);
    }

    private function replacement_restoreGlobalState()
    {
        if (!$this->replacement_snapshot instanceof Snapshot) {
            return;
        }

        $backupGlobals = $this->backupGlobals === null || $this->backupGlobals === true;

        if ($this->replacement_disallowChangesToGlobalState) {
            $this->replacement_compareGlobalStateSnapshots(
                $this->replacement_snapshot,
                $this->replacement_createGlobalStateSnapshot($backupGlobals)
            );
        }

        $restorer = new Restorer;

        if ($backupGlobals) {
            $restorer->restoreGlobalVariables($this->replacement_snapshot);
        }

        if ($this->backupStaticAttributes) {
            $restorer->restoreStaticAttributes($this->replacement_snapshot);
        }

        $this->replacement_snapshot = null;
    }

    /**
     * @param  bool     $backupGlobals
     * @return Snapshot
     */
    private function replacement_createGlobalStateSnapshot($backupGlobals)
    {
        $blacklist = new Blacklist;

        foreach ($this->backupGlobalsBlacklist as $globalVariable) {
            $blacklist->addGlobalVariable($globalVariable);
        }

        if (!defined('PHPUNIT_TESTSUITE')) {
            $blacklist->addClassNamePrefix('PHPUnit');
            $blacklist->addClassNamePrefix('File_Iterator');
            $blacklist->addClassNamePrefix('PHP_CodeCoverage');
            $blacklist->addClassNamePrefix('PHP_Invoker');
            $blacklist->addClassNamePrefix('PHP_Timer');
            $blacklist->addClassNamePrefix('PHP_Token');
            $blacklist->addClassNamePrefix('Symfony');
            $blacklist->addClassNamePrefix('Text_Template');
            $blacklist->addClassNamePrefix('Doctrine\Instantiator');

            foreach ($this->backupStaticAttributesBlacklist as $class => $attributes) {
                foreach ($attributes as $attribute) {
                    $blacklist->addStaticAttribute($class, $attribute);
                }
            }
        }

        return new Snapshot(
            $blacklist,
            $backupGlobals,
            $this->backupStaticAttributes,
            false,
            false,
            false,
            false,
            false,
            false,
            false
        );
    }

    /**
     * @param  Snapshot                         $before
     * @param  Snapshot                         $after
     * @throws PHPUnit_Framework_RiskyTestError
     */
    private function replacement_compareGlobalStateSnapshots(Snapshot $before, Snapshot $after)
    {
        $backupGlobals = $this->backupGlobals === null || $this->backupGlobals === true;

        if ($backupGlobals) {
            $this->replacement_compareGlobalStateSnapshotPart(
                $before->globalVariables(),
                $after->globalVariables(),
                "--- Global variables before the test\n+++ Global variables after the test\n"
            );

            $this->replacement_compareGlobalStateSnapshotPart(
                $before->superGlobalVariables(),
                $after->superGlobalVariables(),
                "--- Super-global variables before the test\n+++ Super-global variables after the test\n"
            );
        }

        if ($this->backupStaticAttributes) {
            $this->replacement_compareGlobalStateSnapshotPart(
                $before->staticAttributes(),
                $after->staticAttributes(),
                "--- Static attributes before the test\n+++ Static attributes after the test\n"
            );
        }
    }

    /**
     * @param  array                            $before
     * @param  array                            $after
     * @param  string                           $header
     * @throws PHPUnit_Framework_RiskyTestError
     */
    private function replacement_compareGlobalStateSnapshotPart(array $before, array $after, $header)
    {
        if ($before != $after) {
            $differ   = new Differ($header);
            $exporter = new Exporter;

            $diff = $differ->diff(
                $exporter->export($before),
                $exporter->export($after)
            );

            throw new PHPUnit_Framework_RiskyTestError(
                $diff
            );
        }
    }

    /**
     * @return Prophecy\Prophet
     */
    private function replacement_getProphet()
    {
        if ($this->replacement_prophet === null) {
            $this->replacement_prophet = new Prophet;
        }

        return $this->replacement_prophet;
    }
}
