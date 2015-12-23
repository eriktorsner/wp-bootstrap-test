<?php

namespace Wpbootstrap;

class InitTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        if (!defined('BASEPATH')) {
            define('BASEPATH', getcwd());
        }
    }

    public function testInit()
    {
        // ensure no previous files exists
        $local = './localsettings.json';
        $app = './appsettings.json';
        @unlink($local);
        @unlink($app);

        $this->assertFalse(file_exists($local));
        $this->assertFalse(file_exists($app));

        $container = Container::getInstance();
        $container->getInitbootstrap()->init();

        $this->assertTrue(file_exists($local));
        $this->assertTrue(file_exists($app));
        $this->assertJson(file_get_contents($local));
        $this->assertJson(file_get_contents($app));

        $localSettings = new Settings('local');
        $this->assertTrue($localSettings->isValid());
        $this->assertTrue(isset($localSettings->environment));
        $this->assertTrue(isset($localSettings->url));
        $this->assertTrue(isset($localSettings->dbhost));
        $this->assertTrue(isset($localSettings->dbname));
        $this->assertTrue(isset($localSettings->dbuser));
        $this->assertTrue(isset($localSettings->dbpass));
        $this->assertTrue(isset($localSettings->wpuser));
        $this->assertTrue(isset($localSettings->wppass));
        $this->assertTrue(isset($localSettings->wppath));

        $appSettings = new Settings('app');
        $this->assertTrue($appSettings->isValid());
        $this->assertTrue(isset($appSettings->title));

        @unlink($local);
        @unlink($app);
    }

    public function testComposerInit()
    {
        $composerFile = './composer.json';
        $this->assertJson(file_get_contents($composerFile));

        $container = Container::getInstance();
        $container->getInitbootstrap()->initComposer();
        $composer = json_decode(file_get_contents($composerFile));

        $names = array('wp-bootstrap', 'wp-install', 'wp-setup', 'wp-import', 'wp-export', 'wp-init');
        foreach ($names as $name) {
            $this->assertTrue(isset($composer->scripts->$name));
        }
    }
}
