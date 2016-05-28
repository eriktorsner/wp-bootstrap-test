<?php

namespace Wpbootstrap;

use \Pimple\Container;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        if (!defined('WPBOOT_BASEPATH')) {
            define('WPBOOT_BASEPATH', getcwd());
        }
    }

    public function testSimple()
    {
        global $testHelpers;
        $testHelpers->removeSettings();
        $testHelpers->writeDotEnv(['foo' => 'bar']);
        $app = $testHelpers->getAppWithMockCli();

        $expected = [
            'path' =>  WPBOOT_BASEPATH .'/www/wordpress-test',
            'args' => [],
            'assocArgs' => [],
            'ymlPath' => WPBOOT_BASEPATH . '/wp-cli.yml',
        ];
        foreach ($expected as $key => $value) {
            $this->assertEquals($value, $app[$key]);
        }
    }

    public function testWithYaml()
    {
        global $testHelpers;
        $testHelpers->removeSettings();
        $testHelpers->writeWpYaml([
            'path' => WPBOOT_BASEPATH . '/www/wordpress-test',
        ]);

        $app = $testHelpers->getAppWithMockCli();
        $this->assertEquals('[notset]', $app['environment']);
    }

    public function testWithYaml2()
    {
        global $testHelpers;
        $testHelpers->removeSettings();
        $testHelpers->writeWpYaml([
            'path' => WPBOOT_BASEPATH . '/www/wordpress-test',
            'environment' => 'test',
        ]);

        $app = $testHelpers->getAppWithMockCli();
        $this->assertEquals('test', $app['environment']);
    }

    public function testWithSimpleDotEnv()
    {
        global $testHelpers;
        $testHelpers->removeSettings();
        $testHelpers->writeWpYaml([
            'path' => WPBOOT_BASEPATH . '/www/wordpress-test',
            'environment' => 'test',
        ]);
        $testHelpers->writeDotEnv(['somekey' => 'somevalue']);

        $app = $testHelpers->getAppWithMockCli();
        $this->assertEquals('somevalue', $_ENV['somekey']);
    }

    public function testWithSimpleDotEnvOverload()
    {
        global $testHelpers;
        $testHelpers->removeSettings();
        $testHelpers->writeWpYaml([
            'path' => WPBOOT_BASEPATH . '/www/wordpress-test',
            'environment' => 'test',
        ]);
        $testHelpers->writeDotEnv(['somekey' => 'somevalue', 'otherkey' => 'othervalue']);
        $testHelpers->writeDotEnv(['otherkey' => 'overloadedvalue'], '-test');

        $app = $testHelpers->getAppWithMockCli();
        $this->assertEquals('somevalue', $_ENV['somekey']);
        $this->assertEquals('overloadedvalue', $_ENV['otherkey']);
    }

    public function testWithNoAppsettings()
    {
        global $testHelpers;
        $testHelpers->removeSettings();

        $app = $testHelpers->getAppWithMockCli();
        $this->assertTrue(count($app['settings']) == 0);
    }

    public function testWithYamlAppsettings()
    {
        global $testHelpers;
        $testHelpers->removeSettings();
        $testHelpers->writeAppsettings((object)['title' => 'foobar'], 'yaml');

        $app = $testHelpers->getAppWithMockCli();
        $this->assertTrue(isset($app['settings']['title']));
        $this->assertEquals('foobar', $app['settings']['title']);
    }

    public function testWithJsonAppsettings()
    {
        global $testHelpers;
        $testHelpers->removeSettings();
        $testHelpers->writeAppsettings((object)['title' => 'foobar'], 'json');

        $app = $testHelpers->getAppWithMockCli();
        $this->assertTrue(isset($app['settings']['title']));
        $this->assertEquals('foobar', $app['settings']['title']);
    }
}