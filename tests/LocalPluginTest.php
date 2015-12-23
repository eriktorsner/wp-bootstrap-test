<?php

namespace Wpbootstrap;

class LocalPluginTest extends \PHPUnit_Framework_TestCase
{
    public function testSetup()
    {
        deleteWpInstallation();
        deleteState();
        copySettingsFiles('localplugins');
        $src = PROJECTROOT.'/tests/fixtures/localplugins/wp-content';
        $trg = PROJECTROOT;
        $cmd = "cp -a $src $trg";
        exec($cmd);

        Container::destroy();

        $container = Container::getInstance();
        $b = $container->getBootstrap();
        $b->install();
        $b->setup();

        $this->assertTrue(file_exists(PROJECTROOT.'/www/wordpress-test/wp-config.php'));
    }

    /*
     * @depends testSetup
     */
    public function testPlugins()
    {
        Container::destroy();
        $container = Container::getInstance();
        $b = $container->getBootstrap();

        $container->getUtils()->includeWordPress();
        wp_cache_delete('plugins', 'plugins');
        require_once $b->localSettings->wppath.'/wp-admin/includes/plugin.php';
        $plugins = get_plugins();

        $this->assertTrue(isset($plugins['foobar/foobar.php']));
        $this->assertTrue(isset($plugins['foobar2/foobar2.php']));
        $this->assertTrue(isset($plugins['wp-bootstrap-ui-master/wp-bootstrap-ui.php']));
    }

    /*
     * @depends testSetup
     */
    public function testThemes()
    {
        Container::destroy();
        $container = Container::getInstance();
        $b = $container->getBootstrap();
        $container->getUtils()->includeWordPress();
        wp_clean_themes_cache(true);
        $themes = wp_get_themes();

        $this->assertTrue(isset($themes['footheme']));
        $this->assertTrue(isset($themes['footheme2']));
        $this->assertTrue(isset($themes['wp-forge']));
    }
}
