<?php

namespace Wpbootstrap;

class ExportTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        deleteWpInstallation();
        setupWpInstallation('exporttest');
    }

    public function testExportEmpty()
    {
        // in this test, appsettings doesn't comtains any settings for wp-bootstrap
        // wp-cfm is not installed and there are no posts or menus exported
        Bootstrap::recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');
        Export::export();
        $this->assertFalse(file_exists(PROJECTROOT.'/bootstrap'));
    }

    public function testExportAPage()
    {
        Bootstrap::recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');
        copySettingsFiles('exporttest2');
        Export::export();

        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/posts'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/posts/page'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/posts/page/sample-page'));

        $obj = unserialize(file_get_contents(PROJECTROOT.'/bootstrap/posts/page/sample-page'));
        $this->assertTrue($obj->post_name == 'sample-page');
        $this->assertTrue($obj->post_type == 'page');
        $this->assertTrue(substr($obj->guid, 0, 15) == '@@__NEUTRAL__@@');
        $this->assertTrue(isset($obj->post_meta));
    }

    public function testExportAMenu()
    {
        $cmd = "wp --allow-root --path=www/wordpress-test menu create main";
        exec($cmd);
        $cmd = "wp --allow-root --path=www/wordpress-test menu location assign main primary";
        exec($cmd);

        $cmd = "wp --allow-root --path=www/wordpress-test menu item add-post main 2";
        exec($cmd);
        $cmd = "wp --allow-root --path=www/wordpress-test menu item add-term main category 1";
        exec($cmd);

        Bootstrap::recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');
        copySettingsFiles('exporttest3');
        Export::export();

        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/menus'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/menus/main'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/menus/main/3'));

        $obj = unserialize(file_get_contents(PROJECTROOT.'/bootstrap/menus/main/3'));
        $this->assertTrue($obj->post_name == 3);
        $this->assertTrue($obj->post_type == 'nav_menu_item');
        $this->assertTrue(substr($obj->guid, 0, 15) == '@@__NEUTRAL__@@');
        $this->assertTrue(isset($obj->post_meta));

        // as a side effect, the sample-page should also have been exported
        // even if it's not included in the appsettings file.
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/posts'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/posts/page'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/posts/page/sample-page'));

        // and as another side effect, a taxonomy term should also have been exported
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/taxonomies'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/taxonomies/category'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/taxonomies/category/uncategorized'));
    }

    public function testExportAnImage()
    {
        $src = PROJECTROOT.'/tests/fixtures/sampleimage.png';
        $cmd = "wp --allow-root --path=www/wordpress-test media import $src --post_id=2 --featured_image";
        exec($cmd);

        Bootstrap::recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');
        copySettingsFiles('exporttest2');
        Export::export();

        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/media'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/media/5'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/media/5/meta'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/media/5/sampleimage.png'));

        $obj = unserialize(file_get_contents(PROJECTROOT.'/bootstrap/media/5/meta'));
        $this->assertTrue($obj->post_name == '5');
        $this->assertTrue($obj->post_type == 'attachment');
        $this->assertTrue(isset($obj->meta));
    }

    public function testExportTaxonomy()
    {
        Bootstrap::recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');
        copySettingsFiles('exporttest4');
        Export::export();

        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/taxonomies'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/taxonomies/category'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/taxonomies/category/uncategorized'));

        $obj = unserialize(file_get_contents(PROJECTROOT.'/bootstrap/taxonomies/category/uncategorized'));
        $this->assertTrue($obj->name == 'Uncategorized');
        $this->assertTrue($obj->slug == 'uncategorized');
        $this->assertTrue($obj->taxonomy == 'category');
    }

    public function testExportSettings()
    {
        Bootstrap::recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');
        copySettingsFiles('exporttest5');
        Bootstrap::setup();
        Export::export();

        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/config'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/config/wpbootstrap.json'));

        $obj = json_decode(file_get_contents(PROJECTROOT.'/bootstrap/config/wpbootstrap.json'));
        $label = '.label';
        $this->assertTrue(count($obj) > 0);
        $this->assertTrue($obj->$label == 'wpbootstrap');
    }
}
