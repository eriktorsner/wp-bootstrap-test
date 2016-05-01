<?php

namespace Wpbootstrap;

/**
 * Class ImportContentTest
 * @package Wpbootstrap
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ImportContentTest extends \PHPUnit_Framework_TestCase
{
    public function testImport()
    {
        global $testHelpers, $installHelper;

        // in this test, appsettings doesn't contain any settings for wp-bootstrap
        // wp-cfm is not installed and there are no posts or menus exported
        $testHelpers->deleteWpInstallation();
        $testHelpers->removeSettings();
        $testHelpers->deleteState();
        $installHelper->createDefaultInstall('ImportTests');

        $testHelpers->copyState(__DIR__ . '/fixtures/importtest1');
        exec('wp bootstrap setup');

        $this->runImport();

        // is the page there?
        $pages = get_posts(array('name' => 'sample-page', 'post_type' => 'page'));
        $this->assertTrue(count($pages) == 1);
        $page = $pages[0];
        $this->assertEquals('Sample Page_IMPORTED', $page->post_title);

        // is the blog name correct?
        $this->assertEquals('ImportTests', get_bloginfo());

        // make sure we can ask WP for list of plugins
        require_once(ABSPATH.'/wp-admin/includes/plugin.php');

        // is the wp-cfm plugin installed as expected?
        $plugins = get_plugins();
        $this->assertTrue(isset($plugins['wp-cfm/wp-cfm.php']));
        $this->assertTrue(isset($plugins['disable-comments/disable-comments.php']));
        $this->assertEquals('1.3', $plugins['disable-comments/disable-comments.php']['Version']);

        // did the taxonomies work?
        $currentTerms = get_terms('category', array('hide_empty' => false));
        usort($currentTerms, array($this, 'parentcmp'));
        $term = $currentTerms[0];
        $this->assertEquals('Uncategorize2', $term->name);
        $this->assertEquals('uncategorized', $term->slug);
        $this->assertEquals('category', $term->taxonomy);
        $this->assertEquals(0, $term->parent);
        $term = $currentTerms[1];
        $this->assertEquals('child', $term->name);
        $this->assertEquals('child', $term->slug);
        $this->assertEquals('category', $term->taxonomy);
        $this->assertEquals(1, $term->parent);

        // do we have the menus?
        $menuItems = wp_get_nav_menu_items('main');
        $this->assertEquals(2, count($menuItems));
        $item = $menuItems[0];
        $this->assertEquals('post_type', $item->type);
        $this->assertEquals(1, $item->menu_order);
        $this->assertEquals('Page', $item->type_label);
        $this->assertEquals('Sample Page_IMPORTED', $item->title);

        $item = $menuItems[1];
        $this->assertEquals('taxonomy', $item->type);
        $this->assertEquals(2, $item->menu_order);
        $this->assertEquals('Category', $item->type_label);
        $this->assertEquals('Uncategorize2', $item->title);

        $themes = wp_get_themes();
        $this->assertEquals('1.0.7', $themes['griffin']->Version);
    }

    /**
     * @depends testImport
     */
    public function testMediaImport()
    {
        require_once(BASEPATH.'/www/wordpress-test/wp-load.php');

        // is the media file there?
        $attachments = get_posts(array('name' => 'selection_287', 'post_type' => 'attachment'));
        $this->assertTrue(count($attachments) == 1);
        $attachment = $attachments[0];
        $this->assertEquals('Selection_287', $attachment->post_title);

        $meta = get_post_meta($attachment->ID);
        $this->assertEquals('2014/11/Selection_287.png', $meta['_wp_attached_file'][0]);
    }

    private function parentcmp($a, $b)
    {
        return $a->parent > $b->parent;
    }

    private function runImport()
    {
        global $testHelpers;

        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);

        require_once(BASEPATH.'/www/wordpress-test/wp-load.php');
        $import = $app['import'];
        $import->run([], []);
    }
}
