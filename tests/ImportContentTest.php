<?php

namespace Wpbootstrap;

class ImportContentTest extends \PHPUnit_Framework_TestCase
{
    public function testImport()
    {
        deleteWpInstallation();
        deleteState();
        copyState('importtest1');
        Bootstrap::destroy();

        $b = Bootstrap::getInstance();
        $this->assertEquals(1, count($b->getFiles(PROJECTROOT.'/bootstrap/posts/page')));
        $b->install();
        $b->setup();
        Import::getInstance()->import();

        // is the page there?
        $pages = get_posts(array('name' => 'sample-page', 'post_type' => 'page'));
        $this->assertTrue(count($pages) == 1);
        $page = $pages[0];
        $this->assertEquals('Sample Page_IMPORTED', $page->post_title);

        // is the blog name correct?
        $this->assertEquals('ImportTests', get_bloginfo());

        // make sure we can ask WP for list of plugins
        $wppath = $b->localSettings->wppath;
        require_once $wppath.'/wp-admin/includes/plugin.php';

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
        $this->assertEquals('1.0.6', $themes['griffin']->Version);
    }

    /**
     * @depends testImport
     */
    public function testMediaImport()
    {
        // is the media file there?
        $attachments = get_posts(array('name' => 'selection_287', 'post_type' => 'attachment'));
        $this->assertTrue(count($attachments) == 1);
        $attachment = $attachments[0];
        $this->assertEquals('ImgTitle', $attachment->post_title);

        $meta = get_post_meta($attachment->ID);
        $this->assertEquals('2014/11/Selection_287.png', $meta['_wp_attached_file'][0]);
    }

    /**
     * @depends testImport
     */
    public function testUpdatePlugin()
    {
        global $argv;
        $orgArgv = $argv;

        wp_cache_delete('plugins', 'plugins');

        // emulate command line
        $argv = [];
        $argv[] = 'wpbootstrap';
        $argv[] = 'update';
        $argv[] = 'plugins';

        Bootstrap::getInstance()->update();

        wp_cache_delete('plugins', 'plugins');
        $plugins = get_plugins();
        $this->assertEquals('1.3.2', $plugins['disable-comments/disable-comments.php']['Version']);

        $argv = $orgArgv;
    }

    /**
     * @depends testUpdatePlugin
     */
    public function testUpdateTheme()
    {
        global $argv;
        $orgArgv = $argv;

        // emulate command line
        $argv = [];
        $argv[] = 'wpbootstrap';
        $argv[] = 'update';
        $argv[] = 'themes';

        Bootstrap::getInstance()->update();

        wp_clean_themes_cache();
        $theme = wp_get_theme('griffin');
        $this->assertEquals('1.0.9', $theme->Version);

        $argv = $orgArgv;
    }

    /**
     * @depends testUpdateTheme
     */
    public function testUpdateAll()
    {
        global $argv;
        $orgArgv = $argv;

        wp_cache_delete('plugins', 'plugins');

        // emulate command line
        $argv = [];
        $argv[] = 'wpbootstrap';
        $argv[] = 'update';

        Bootstrap::getInstance()->update();

        $argv = $orgArgv;
    }

    private function parentcmp($a, $b)
    {
        return $a->parent > $b->parent;
    }
}
