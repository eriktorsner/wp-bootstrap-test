<?php

namespace Wpbootstrap;

class ToddTimber1 extends \PHPUnit_Framework_TestCase
{
    public function testImport()
    {
        deleteWpInstallation();
        deleteState();
        copyState('toddtimber1');
        Container::destroy();

        $container = Container::getInstance();
        $bootstrap = $container->getBootstrap();
        $bootstrap->install();
        $bootstrap->setup();

        $container->getImport()->import();
    }
}
