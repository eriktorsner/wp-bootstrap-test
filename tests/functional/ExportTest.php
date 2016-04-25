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
        $container = Container::getInstance();
        $container->getHelpers()->recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');

        $container::destroy();
        $container = Container::getInstance();
        $container->getExport()->export();

        $this->assertFalse(file_exists(PROJECTROOT.'/bootstrap'));
    }

    public function testExportAPage()
    {
        $container = Container::getInstance();
        $container->getHelpers()->recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');
        copySettingsFiles('exporttest2');

        $container::destroy();
        $container = Container::getInstance();
        $container->getExport()->export();

        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/posts'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/posts/page'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/posts/page/sample-page'));

        $obj = unserialize(file_get_contents(PROJECTROOT.'/bootstrap/posts/page/sample-page'));
        $this->assertTrue($obj->post_name == 'sample-page');
        $this->assertTrue($obj->post_type == 'page');
        $neutral = Bootstrap::NEUTRALURL;
        $this->assertTrue(substr($obj->guid, 0, strlen($neutral)) == $neutral);
        $this->assertTrue(isset($obj->post_meta));
    }

    public function testExportAMenu()
    {
        $cmd = 'wp --allow-root --path=www/wordpress-test menu create main';
        exec($cmd);
        $cmd = 'wp --allow-root --path=www/wordpress-test menu location assign main primary';
        exec($cmd);

        $cmd = 'wp --allow-root --path=www/wordpress-test menu item add-post main 2';
        exec($cmd);
        $cmd = 'wp --allow-root --path=www/wordpress-test menu item add-term main category 1';
        exec($cmd);

        $container = Container::getInstance();
        $container->getHelpers()->recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');
        copySettingsFiles('exporttest3');

        $container::destroy();
        $container = Container::getInstance();
        $container->getExport()->export();

        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/menus'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/menus/main'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/menus/main/3'));

        $obj = unserialize(file_get_contents(PROJECTROOT.'/bootstrap/menus/main/3'));
        $this->assertTrue($obj->post_name == 3);
        $this->assertTrue($obj->post_type == 'nav_menu_item');
        $neutral = Bootstrap::NEUTRALURL;
        $this->assertTrue(substr($obj->guid, 0, strlen($neutral)) == $neutral);
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

        $container = Container::getInstance();
        $container->getHelpers()->recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');
        copySettingsFiles('exporttest2');

        $container::destroy();
        $container = Container::getInstance();
        $container->getExport()->export();

        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/media'));

        // since WP 4.5 or wp-cli 0.23, the default name for an imported
        // image will be image name minus extension...
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/media/sampleimage'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/media/sampleimage/meta'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/media/sampleimage/sampleimage.png'));

        $obj = unserialize(file_get_contents(PROJECTROOT.'/bootstrap/media/sampleimage/meta'));
        $this->assertTrue($obj->post_name == 'sampleimage');
        $this->assertTrue($obj->post_type == 'attachment');
        $this->assertTrue(isset($obj->post_meta));

    }

    public function testExportTaxonomy()
    {
        $container = Container::getInstance();
        $container->getHelpers()->recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');
        copySettingsFiles('exporttest4');

        $container::destroy();
        $container = Container::getInstance();
        $container->getExport()->export();

        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/taxonomies'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/taxonomies/category'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/taxonomies/category/uncategorized'));

        $obj = unserialize(file_get_contents(PROJECTROOT.'/bootstrap/taxonomies/category/uncategorized'));
        $this->assertTrue($obj->name == 'Uncategorized');
        $this->assertTrue($obj->slug == 'uncategorized');
        $this->assertTrue($obj->taxonomy == 'category');
    }

    public function testExportTaxonomy2()
    {
        $cmd = "wp --allow-root --path=www/wordpress-test term create category Fruit --description='Fruits'";
        exec($cmd);
        $cmd = "wp --allow-root --path=www/wordpress-test term create category Apple --parent=3 --description='Specific fruits'";
        exec($cmd);

        $container = Container::getInstance();
        $container->getHelpers()->recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');
        copySettingsFiles('exporttest5');

        $container::destroy();
        $container = Container::getInstance();
        $container->getExport()->export();

        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/taxonomies'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/taxonomies/category'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/taxonomies/category/fruit'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/taxonomies/category/apple'));

        $obj = unserialize(file_get_contents(PROJECTROOT.'/bootstrap/taxonomies/category/apple'));
        $this->assertTrue($obj->name == 'Apple');
        $this->assertTrue($obj->slug == 'apple');
        $this->assertTrue($obj->description == 'Specific fruits');
        $this->assertTrue($obj->taxonomy == 'category');
    }

    public function testExportSettings()
    {
        $container = Container::getInstance();
        $container->getHelpers()->recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');
        copySettingsFiles('exporttest6');
        $container->getBootstrap()->setup();

        $container::destroy();
        $container = Container::getInstance();
        $container->getExport()->export();

        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/config'));
        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/config/wpbootstrap.json'));

        $obj = json_decode(file_get_contents(PROJECTROOT.'/bootstrap/config/wpbootstrap.json'));
        $label = '.label';
        $this->assertTrue(count($obj) > 0);
        $this->assertTrue($obj->$label == 'wpbootstrap');
    }

    public function testExportSidebar()
    {
        $cmd = "wp --allow-root --path=www/wordpress-test widget add calendar sidebar-1 1 --title='Foobar'";
        exec($cmd);

        $container = Container::getInstance();
        $container->getHelpers()->recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');
        copySettingsFiles('exporttest7');

        $container::destroy();
        $container = Container::getInstance();
        wp_cache_delete('alloptions', 'options');
        $container->getExport()->export();

        $this->assertTrue(file_exists(PROJECTROOT.'/bootstrap/sidebars/sidebar-1/calendar-1'));
        $obj = unserialize(file_get_contents(PROJECTROOT.'/bootstrap/sidebars/sidebar-1/calendar-1'));
        $this->assertEquals('Foobar', $obj['title']);
    }
}
