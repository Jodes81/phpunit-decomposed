<?php
if (!defined('STDOUT')) {
    // php://stdout does not obey output buffering. Any output would break
    // unserialization of child process results in the parent process.
    define('STDOUT', fopen('php://temp', 'w+b'));
    define('STDERR', fopen('php://stderr', 'wb'));
}

{iniSettings}
ini_set('display_errors', 'stderr');
set_include_path('{include_path}');

$composerAutoload = {composerAutoload};
$phar             = {phar};

ob_start();

if ($composerAutoload) {
    require_once $composerAutoload;
    define('PHPUNIT_COMPOSER_INSTALL', $composerAutoload);
} else if ($phar) {
    require $phar;
}

function __phpunit_run_isolated_test()
{
    if (!class_exists('{className}')) {
        require_once '{filename}';
    }

    $result = new PHPUnit_Framework_TestResult;

    if ({collectCodeCoverageInformation}) {
        $result->setCodeCoverage(
            new PHP_CodeCoverage(
                null,
                unserialize('{codeCoverageFilter}')
            )
        );
    }

    $result->beStrictAboutTestsThatDoNotTestAnything({isStrictAboutTestsThatDoNotTestAnything});
    $result->beStrictAboutOutputDuringTests({isStrictAboutOutputDuringTests});
    $result->beStrictAboutTestSize({isStrictAboutTestSize});
    $result->beStrictAboutTodoAnnotatedTests({isStrictAboutTodoAnnotatedTests});

    $test = new {className}('{methodName}', unserialize('{data}'), '{dataName}');
    $test->setDependencyInput(unserialize('{dependencyInput}'));
    $test->setInIsolation(TRUE);

    ob_end_clean();
    $test->run($result);
    $output = '';
    if (!$test->hasExpectationOnOutput()) {
        $output = $test->getActualOutput();
    }

    rewind(STDOUT);
    if ($stdout = stream_get_contents(STDOUT)) {
        $output = $stdout . $output;
    }

    print serialize(
      array(
        'testResult'    => $test->getResult(),
        'numAssertions' => $test->getNumAssertions(),
        'result'        => $result,
        'output'        => $output
      )
    );
}

{constants}
{included_files}
{globals}

if (isset($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
    require_once $GLOBALS['__PHPUNIT_BOOTSTRAP'];
    unset($GLOBALS['__PHPUNIT_BOOTSTRAP']);
}

__phpunit_run_isolated_test();
