<?php

namespace Wpbootstrap;

/**
 * Class LocalPluginTest
 * @package Wpbootstrap
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LocalPluginTest extends \PHPUnit_Framework_TestCase
{
    public function testSetupLocalPlugins()
    {
        global $testHelpers, $installHelper;

        exec('rm -rf '.BASEPATH.'/wp-content');
        $testHelpers->deleteWpInstallation();
        $testHelpers->deleteState();
        $testHelpers->removeSettings();

        $installHelper->createDefaultInstall('localplugins');
        $testHelpers->copyState(__DIR__ . '/fixtures/localplugins');

        // this will generate code coverage for the setup class
        // however we're not fully able to mock wp-cli launch_self
        $this->runSetup();

        $this->assertTrue(file_exists('www/wordpress-test/wp-content/plugins/foobar'));
        $this->assertTrue(file_exists('www/wordpress-test/wp-content/plugins/foobar2'));
        $this->assertTrue(file_exists('www/wordpress-test/wp-content/themes/footheme'));
        $this->assertTrue(file_exists('www/wordpress-test/wp-content/themes/footheme2'));

        $out = [];
        exec('wp plugin list --format=json', $out);
        $plugins = json_decode($out[0]);
        $this->assertEquals(4, count($plugins));

    }

    /*
     * @depends testSetup
     */
    public function testPlugins()
    {

        exec('wp bootstrap setup');
        /*require_once(BASEPATH.'/www/wordpress-test/wp-load.php');
        require_once(BASEPATH.'/www/wordpress-test/wp-admin/includes/plugin.php');
        wp_cache_delete('plugins', 'plugins');
        $plugins = get_plugins();*/

        $out = [];
        exec('wp plugin list --format=json', $out);
        $plugins = json_decode($out[0]);

        foreach ($plugins as $key => $plugin) {
            $plugins[$plugin->name] = $plugin;
            unset($plugins[$key]);
        }

        $this->assertTrue(isset($plugins['akismet']));
        $this->assertEquals('inactive', $plugins['akismet']->status);
        $this->assertTrue(isset($plugins['foobar']));
        $this->assertEquals('active', $plugins['foobar']->status);
        $this->assertTrue(isset($plugins['foobar2']));
        $this->assertEquals('active', $plugins['foobar2']->status);
        $this->assertTrue(isset($plugins['hello']));
        $this->assertEquals('inactive', $plugins['hello']->status);
        $this->assertTrue(isset($plugins['wp-cfm']));
        $this->assertEquals('active', $plugins['wp-cfm']->status);
    }

    /*
     * @depends testPlugins
     */
    public function testThemes()
    {
        $out = [];
        exec('wp theme list --format=json', $out);
        $themes = json_decode($out[0]);

        foreach ($themes as $key => $theme) {
            $themes[$theme->name] = $theme;
            unset($themes[$key]);
        }

        $this->assertTrue(isset($themes['footheme']));
        $this->assertTrue(isset($themes['footheme2']));
        $this->assertEquals('active', $themes['footheme2']->status);
        $this->assertTrue(isset($themes['wp-forge']));
    }

    private function runSetup()
    {
        global $testHelpers;

        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);

        require_once(BASEPATH.'/www/wordpress-test/wp-load.php');
        $setup = $app['setup'];
        $setup->run([], []);
    }
}
