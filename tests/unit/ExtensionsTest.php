<?php

namespace Wpbootstrap;

class ExtensionsTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        global $testHelpers;
        $testHelpers->deleteState();
        $testHelpers->copyState('extensions');

        require_once(__DIR__ . '/../fixtures/extensions/extension.php');
    }

    public function testLoad()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);

        $extensions = $app['extensions'];
        $extensions->init();
    }
}
