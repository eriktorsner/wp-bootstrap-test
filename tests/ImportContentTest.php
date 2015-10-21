<?php

namespace Wpbootstrap;

class ImportContentTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        //deleteWpInstallation();
        //setupWpInstallation('none');
    }

    public static function testImport()
    {
        deleteState();
        copyState('importtest1');
        Import::import();
    }
}
