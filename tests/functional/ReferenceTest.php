<?php

namespace Wpbootstrap;

class ReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testImport()
    {
        deleteWpInstallation();
        deleteState();
        copyState('referencetest');
        Container::destroy();

        $container = Container::getInstance();
        $b = $container->getBootstrap();
        $helpers = $container->getHelpers();
        $this->assertEquals(2, count($helpers->getFiles(PROJECTROOT.'/bootstrap/posts/page')));
        $b->install();
        $b->setup();
        $container->getImport()->import();

        // The front page has ID=22 in the bootstrap/posts/page file
        // but in a fresh WP-install, it should get ID = 3
        $pages = get_posts(array('name' => 'front-page', 'post_type' => 'page'));
        $this->assertEquals(1, count($pages));
        $this->assertEquals(3, $pages[0]->ID);

        // The sample page has ID=20 in the bootstrap/posts/page file
        // but in a fresh WP-install it already exists and should have
        // ID = 2
        $pages = get_posts(array('name' => 'sample-page', 'post_type' => 'page'));
        $this->assertEquals(1, count($pages));
        $this->assertEquals(2, $pages[0]->ID);
    }

    /*
     * * @depends testImport
     *
     */
    public function testOptionPageReferences()
    {

        // Also, the "page_on_front" setting in the import data is set to point to page 22
        // But since the actual page now has ID=3, we want to check that it's also correct.
        $pageOnFront = get_option('page_on_front', 0);
        $this->assertEquals(3, $pageOnFront);

        // Our own test settings
        $value = get_option('bootstrap_post_ref', 0);
        $this->assertEquals(2, $value);

        $value = get_option('bootstrap_post_ref2', 0);
        $this->assertTrue(is_object($value));
        $this->assertEquals(2, $value->page_id);

        $value = get_option('bootstrap_post_ref3', 0);
        $this->assertTrue(is_array($value));
        $this->assertEquals(2, $value[2]);

        $value = get_option('bootstrap_post_ref4', 0);
        $this->assertTrue(is_object($value));
        $this->assertEquals(2, $value->page_id);
        $this->assertEquals(3, $value->other_page_id);
    }

    /*
     * * @depends testImport
     *
     */

    public function testOptionTermReference()
    {
        // Our own test settings
        $value = get_option('bootstrap_term_ref', 0);
        $this->assertEquals(4, $value);

        $value = get_option('bootstrap_term_ref2', 0);
        $this->assertTrue(is_object($value));
        $this->assertEquals(3, $value->term_id);

        $value = get_option('bootstrap_term_ref3', 0);
        $this->assertTrue(is_array($value));
        $this->assertEquals(4, $value[2]);

        $value = get_option('bootstrap_term_ref4', 0);
        $this->assertTrue(is_object($value));
        $this->assertEquals(3, $value->term_id);
        $this->assertEquals(4, $value->other_term_id);
    }
}
