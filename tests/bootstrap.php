<?php

define('PROJECTROOT', dirname(dirname(__FILE__)));
define('BASEPATH', PROJECTROOT);
define('TESTMODE', true);

require_once __DIR__.'/../vendor/autoload.php';
require_once(__DIR__ . '/helpers/TestHelpers.php');
require_once(__DIR__ . '/helpers/InstallHelper.php');
require_once __DIR__.'/mocks/MockCliWrapper.php';
require_once __DIR__.'/mocks/MockCliUtilsWrapper.php';
require_once __DIR__.'/mocks/MockWpCfm.php';
require_once __DIR__.'/mocks/MockWpCfmReadWrite.php';


global $testHelpers;
$testHelpers = new \TestHelpers();
$installHelper = new \InstallHelper();


function prompt($msg = 'Press any key')
{
    echo $msg."\n";
    //ob_flush();
    $in = trim(fgets(STDIN));

    return $in;
}
