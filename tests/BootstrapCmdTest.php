<?php

namespace Wpbootstrap;

class BootstrapCmdTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        deleteWpInstallation();
    }

    public static function testBootstrapCommand()
    {
        deleteState();
        copyState('importtest1');
        $boostrap = Bootstrap::getInstance();
        $boostrap->bootstrap();
    }
}
