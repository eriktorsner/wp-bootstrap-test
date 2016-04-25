<?php

namespace Wpbootstrap;

use \Pimple\Container;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        if (!defined('BASEPATH')) {
            define('BASEPATH', getcwd());
        }
    }

    public function testSimple()
    {
        global $testHelpers;
        $testHelpers->removeSettings();
        $testHelpers->writeDotEnv(['foo' => 'bar']);
        $app = getAppWithMockCli();

        $expected = [
            'path' =>  BASEPATH .'/www/wordpress-test',
            'args' => [],
            'assocArgs' => [],
            'ymlPath' => BASEPATH . '/wp-cli.yml',
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
            'path' => BASEPATH . '/www/wordpress-test',
        ]);

        $app = getAppWithMockCli();
        $this->assertEquals('[notset]', $app['environment']);
    }

    public function testWithYaml2()
    {
        global $testHelpers;
        $testHelpers->removeSettings();
        $testHelpers->writeWpYaml([
            'path' => BASEPATH . '/www/wordpress-test',
            'environment' => 'test',
        ]);

        $app = getAppWithMockCli();
        $this->assertEquals('test', $app['environment']);
    }

    public function testWithSimpleDotEnv()
    {
        global $testHelpers;
        $testHelpers->removeSettings();
        $testHelpers->writeWpYaml([
            'path' => BASEPATH . '/www/wordpress-test',
            'environment' => 'test',
        ]);
        $testHelpers->writeDotEnv(['somekey' => 'somevalue']);

        $app = getAppWithMockCli();
        $this->assertEquals('somevalue', $_ENV['somekey']);
    }

    public function testWithSimpleDotEnvOverload()
    {
        global $testHelpers;
        $testHelpers->removeSettings();
        $testHelpers->writeWpYaml([
            'path' => BASEPATH . '/www/wordpress-test',
            'environment' => 'test',
        ]);
        $testHelpers->writeDotEnv(['somekey' => 'somevalue', 'otherkey' => 'othervalue']);
        $testHelpers->writeDotEnv(['otherkey' => 'overloadedvalue'], '-test');

        $app = getAppWithMockCli();
        $this->assertEquals('somevalue', $_ENV['somekey']);
        $this->assertEquals('overloadedvalue', $_ENV['otherkey']);
    }

    public function testWithNoAppsettings()
    {
        global $testHelpers;
        $testHelpers->removeSettings();

        $app = getAppWithMockCli();
        $this->assertTrue(count($app['settings']) == 0);
    }

    public function testWithYamlAppsettings()
    {
        global $testHelpers;
        $testHelpers->removeSettings();
        $testHelpers->writeAppsettings((object)['title' => 'foobar'], 'yaml');

        $app = getAppWithMockCli();
        $this->assertTrue(isset($app['settings']['title']));
        $this->assertEquals('foobar', $app['settings']['title']);
    }

    public function testWithJsonAppsettings()
    {
        global $testHelpers;
        $testHelpers->removeSettings();
        $testHelpers->writeAppsettings((object)['title' => 'foobar'], 'json');

        $app = getAppWithMockCli();
        $this->assertTrue(isset($app['settings']['title']));
        $this->assertEquals('foobar', $app['settings']['title']);
    }
}