<?php

namespace Wpbootstrap;

use Symfony\Component\Yaml\Yaml;

/**
 * Class MenusManagerTest
 * @package Wpbootstrap
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class MenusManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \WP_Mock::setUp();
    }

    public function tearDown()
    {
        \WP_Mock::tearDown();
    }


    public function testListItems()
    {
        global $testHelpers, $wp_version, $mockTerms;
        $wp_version = '4.5';

        $testHelpers->writeAppsettings(
            [
                'keepDefaultContent' => true,
                'content' => [
                    'menus' => ['main',]
                ]
            ],
            'yaml'
        );

        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $cliutils = $app['cliutils'];
        $cli = $app['cli'];
        $m = new Commands\Menus();
        $cli->launch_self_return = (object)[
            'return_code' => 0,
            'stdout' => json_encode([
                (object)[
                    'term_id' => 1,
                    'name' => 'Main menu',
                    'slug' => 'main',
                    'locations' => ['primary', 'footer'],
                    'count' => 2,
                ],
            ])
        ];

        $m->listItems(array(), array());

        $this->assertEquals('table', $cliutils->format);
        $this->assertEquals(1, count($cliutils->output));
        $this->assertTrue(in_array('Managed', $cliutils->fields));
        $this->assertEquals('Yes', $cliutils->output[0]['Managed']);

        $m->listItems(array(), array('format' => 'json'));
        $this->assertEquals('json', $cliutils->format);
    }


    public function testAdd()
    {
        global $testHelpers, $mockTerms;
        $testHelpers->writeAppsettings(
            [
                'keepDefaultContent' => true,
            ],
            'yaml'
        );
        $yaml = new Yaml();


        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $m = new Commands\Menus();
        $cli = $app['cli'];
        $cli->launch_self_return = (object)[
            'return_code' => 0,
            'stdout' => json_encode([
                (object)[
                    'term_id' => 1,
                    'name' => 'Main menu',
                    'slug' => 'main',
                    'locations' => ['primary', 'footer'],
                    'count' => 2,
                ],
            ])
        ];

        $m->add(array('main'), array());
        $settings = $yaml->parse(WPBOOT_BASEPATH . '/appsettings.yml');

        $this->assertTrue(isset($settings['content']['menus']));
        $this->assertEquals(1, count($settings['content']['menus']));
        $this->assertEquals('main', $settings['content']['menus'][0]);

        file_put_contents(WPBOOT_BASEPATH . '/appsettings.yml', "foobar: true\n");
        $m->add(array(1), array());
        $settings = $yaml->parse(WPBOOT_BASEPATH . '/appsettings.yml');
        $this->assertTrue(isset($settings['content']['menus']));
        $this->assertEquals(1, count($settings['content']['menus']));
        $this->assertEquals('main', $settings['content']['menus'][0]);

        file_put_contents(WPBOOT_BASEPATH . '/appsettings.yml', "foobar: true\n");
        $m->add(array(999), array());
        $settings = $yaml->parse(WPBOOT_BASEPATH . '/appsettings.yml');
        $this->assertFalse(isset($settings['content']['menus']));

    }
}
