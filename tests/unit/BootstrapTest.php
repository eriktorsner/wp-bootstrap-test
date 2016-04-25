<?php

namespace Wpbootstrap;

use \Pimple\Container;

class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \WP_Mock::setUp();
    }

    public function tearDown()
    {
        \WP_Mock::tearDown();
    }

    public function testCreate()
    {
        global $testHelpers;
        $testHelpers->removeSettings();

        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $app = Bootstrap::getApplication();
        $bootstrap = new Bootstrap();
    }

    /**
     * @depends testCreate
     */
    public function testCommands()
    {
        global $testHelpers;

        \WP_Mock::wpFunction('get_option', [
            'args' => ['siteurl'],
            'times' => '2+',
            'return' => 'http://www.example.com'
        ]);
        \WP_Mock::wpFunction('get_taxonomies', [
            'times' => '1+',
            'return' => ['category' => 'category']
        ]);
        \WP_Mock::wpFunction('get_option', [
            'args' => ['sidebars_widgets', []],
            'times' => '1+',
            'return' => []
        ]);
        \WP_Mock::wpFunction('get_option', [
            'args' => ['page_on_front', 0],
            'times' => '1+',
            'return' => []
        ]);
        \WP_Mock::wpFunction('wp_upload_dir', [
            'times' => '1+',
            'return' => ['basedir' => '/tmp/import',]
        ]);
        \WP_Mock::wpFunction('wp_cache_flush', [
            'times' => '1+',
        ]);
        //\WP_Mock::wpFunction('remove_all_actions', ['times' => 1,]);

        $app = $testHelpers->getAppWithMockCli();
        $helpers = $app['helpers'];
        $helpers->recursiveRemoveDirectory(BASEPATH . '/bootstrap');

        $bootstrap = new Bootstrap();

        $bootstrap->install(array(), array());
        $bootstrap->reset(array(), array());
        $bootstrap->setup(array(), array());
        $bootstrap->import(array(), array());
        $bootstrap->export(array(), array());
    }

}