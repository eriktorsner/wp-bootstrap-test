<?php

namespace Wpbootstrap;

class VersionsTest extends \PHPUnit_Framework_TestCase
{
    private $tPlugins = array(
        'google-sitemap-generator/sitemap.php' => array('4.0.3','4.0.8'),
        'wp-pagenavi/wp-pagenavi.php' => array('2.82','2.90'),
        'wordpress-importer/wordpress-importer.php' => array('0.5.2','0.6.1'),
    );

    private $tThemes = array(
        'swallow' => array('1.5','1.8'),
        'radix' => array('1.0.4','1.1.1'),
    );

    public static function setUpBeforeClass()
    {
        deleteWpInstallation();
        copySettingsFiles('versionstest');
        Container::destroy();
    }

    public function testDoWPInstall()
    {
        $container = Container::getInstance();
        $b = $container->getBootstrap();
        $b->install();
        $b->setup();
    }

    /**
     * @depends testDoWPInstall
     */
    public function testCorrectWPInstall()
    {
        global $wp_version;

        Container::destroy();
        $container = Container::getInstance();

        $container->getUtils()->includeWordPress();
        $ls = $container->getLocalSettings();
        require $ls->wppath.'/wp-includes/version.php';

        $this->assertEquals('4.1.8', $wp_version);
    }

    /**
     * @depends testCorrectWPInstall
     */
    public function testCorrectPluginVersions()
    {
        Container::destroy();
        $container = Container::getInstance();

        $container->getUtils()->includeWordPress();
        $ls = $container->getLocalSettings();
        wp_cache_delete('plugins', 'plugins');
        require_once $ls->wppath.'/wp-admin/includes/plugin.php';
        $plugins = get_plugins();

        foreach ($this->tPlugins as $key => $versions) {
            $this->assertTrue(isset($plugins[$key]));
            $this->assertEquals($versions[0], $plugins[$key]['Version']);
        }
    }

    /**
     * @depends testCorrectWPInstall
     */
    public function testCorrectThemeVersions()
    {
        Container::destroy();
        $container = Container::getInstance();
        $container->getUtils()->includeWordPress();
        wp_clean_themes_cache(true);
        $themes = wp_get_themes();

        foreach ($this->tThemes as $key => $versions) {
            $this->assertTrue(isset($themes[$key]));
            $this->assertEquals($versions[0], $themes[$key]['Version']);
        }
    }

    /**
     * @depends testCorrectPluginVersions
     */
    public function testUpgradeOnePlugin()
    {
        global $argv;
        $argv = array();
        $argv[] = 'composer';
        $argv[] = 'wp-update';
        $argv[] = 'plugins';
        $argv[] = 'wp-pagenavi';

        Container::destroy();
        $container = Container::getInstance();
        $ls = $container->getLocalSettings();
        $b = $container->getBootstrap();
        $b->update();
        $container->getUtils()->includeWordPress();
        wp_cache_delete('plugins', 'plugins');
        require_once $ls->wppath.'/wp-admin/includes/plugin.php';
        $plugins = get_plugins();

        $key = 'wp-pagenavi/wp-pagenavi.php';
        $this->assertTrue(isset($plugins[$key]));
        $this->assertEquals($this->tPlugins[$key][1], $plugins[$key]['Version']);

        $key = 'google-sitemap-generator/sitemap.php';
        $this->assertTrue(isset($plugins[$key]));
        $this->assertEquals($this->tPlugins[$key][0], $plugins[$key]['Version']);
    }

    /**
     * @depends testUpgradeOnePlugin
     */
    public function testUpgradeAllPlugins()
    {
        global $argv;
        $argv = array();
        $argv[] = 'composer';
        $argv[] = 'wp-update';
        $argv[] = 'plugins';

        Container::destroy();
        $container = Container::getInstance();
        $ls = $container->getLocalSettings();
        $b = $container->getBootstrap();
        $b->update();
        $container->getUtils()->includeWordPress();
        wp_cache_delete('plugins', 'plugins');
        require_once $ls->wppath.'/wp-admin/includes/plugin.php';
        $plugins = get_plugins();

        foreach ($this->tPlugins as $key => $versions) {
            $this->assertTrue(isset($plugins[$key]));
            $this->assertEquals($versions[1], $plugins[$key]['Version']);
        }
    }

    /**
     * @depends testCorrectThemeVersions
     */
    public function testUpgradeOneTheme()
    {
        global $argv;
        $argv = array();
        $argv[] = 'composer';
        $argv[] = 'wp-update';
        $argv[] = 'themes';
        $argv[] = 'swallow';

        Container::destroy();
        $container = Container::getInstance();
        $b = $container->getBootstrap();

        $b->update();
        $container->getUtils()->includeWordPress();
        wp_clean_themes_cache(true);
        $themes = wp_get_themes();

        $key = 'swallow';
        $this->assertTrue(isset($themes[$key]));
        $this->assertEquals($this->tThemes[$key][1], $themes[$key]['Version']);

        $key = 'radix';
        $this->assertTrue(isset($themes[$key]));
        $this->assertEquals($this->tThemes[$key][0], $themes[$key]['Version']);
    }

    /**
     * @depends testUpgradeOneTheme
     */
    public function testUpgradeAllThemes()
    {
        global $argv;
        $argv = array();
        $argv[] = 'composer';
        $argv[] = 'wp-update';
        $argv[] = 'themes';

        Container::destroy();
        $container = Container::getInstance();
        $b = $container->getBootstrap();
        $b->update();
        $container->getUtils()->includeWordPress();
        wp_clean_themes_cache(true);
        $themes = wp_get_themes();

        foreach ($this->tThemes as $key => $versions) {
            $this->assertTrue(isset($themes[$key]));
            $this->assertEquals($versions[1], $themes[$key]['Version']);
        }
    }
}
