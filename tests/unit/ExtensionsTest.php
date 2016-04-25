<?php

namespace Wpbootstrap;

class ExtensionsTEst extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        deleteState();
        copyState('extensions');
        Container::destroy();

        require_once(__DIR__ . '/fixtures/extensions/extension.php');
    }

    public function testLoad()
    {
        $container = Container::getInstance();
        $container->getExtensions()->init();
        print_r($container->getExtensions());
    }

}
