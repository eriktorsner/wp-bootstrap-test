<?php

namespace Wpbootstrap;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ExportTest
 * @package Wpbootstrap
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ExportTest extends \PHPUnit_Framework_TestCase
{
    public function testExportEmpty()
    {
        global $testHelpers, $installHelper;

        // in this test, appsettings doesn't contain any settings for wp-bootstrap
        // wp-cfm is not installed and there are no posts or menus exported
        $testHelpers->deleteWpInstallation();
        $testHelpers->removeSettings();

        $installHelper->createDefaultInstall();

        $this->runExport();

        $this->assertFalse(file_exists(WPBOOT_BASEPATH.'/bootstrap'));
    }

    /**
     * @depends testExportEmpty
     */
    public function testExportAPage()
    {
        global $testHelpers;
        $yaml = new Yaml();

        $testHelpers->writeAppsettings(
            [
                'keepDefaultContent' => true,
                'content' => [
                    'posts' => [
                        'page' => ['sample-page']
                    ]
                ]
            ],
            'yaml'
        );

        $this->runExport();

        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/posts'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/posts/page'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/posts/page/sample-page'));

        $obj = $yaml->parse(file_get_contents(WPBOOT_BASEPATH.'/bootstrap/posts/page/sample-page'));
        $this->assertTrue($obj['post_name'] == 'sample-page');
        $this->assertTrue($obj['post_type'] == 'page');
        $neutral = Bootstrap::NEUTRALURL;
        $this->assertTrue(substr($obj['guid'], 0, strlen($neutral)) == $neutral);
        $this->assertTrue(isset($obj['post_meta']));
    }

    public function testExportAMenu()
    {
        global $testHelpers;
        $yaml = new Yaml();

        exec('wp --path=www/wordpress-test menu create main');
        exec('wp --path=www/wordpress-test menu location assign main primary');
        exec('wp --path=www/wordpress-test menu item add-post main 2');
        exec('wp --path=www/wordpress-test menu item add-term main category 1');

        $testHelpers->writeAppsettings(
            [
                'keepDefaultContent' => true,
                'content' => [
                    'posts' => [
                        'page' => ['sample-page']
                    ],
                    'menus' => ["main"]
                ]
            ],
            'yaml'
        );

        $this->runExport();

        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/menus'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/menus/main'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/menus/main/3'));

        $obj = $yaml->parse(file_get_contents(WPBOOT_BASEPATH.'/bootstrap/menus/main/3'));
        $this->assertTrue($obj['post_name'] == 3);
        $this->assertTrue($obj['post_type'] == 'nav_menu_item');
        $neutral = Bootstrap::NEUTRALURL;
        $this->assertTrue(substr($obj['guid'], 0, strlen($neutral)) == $neutral);
        $this->assertTrue(isset($obj['post_meta']));

        // as a side effect, the sample-page should also have been exported
        // even if it's not included in the appsettings file.
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/posts'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/posts/page'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/posts/page/sample-page'));

        // and as another side effect, a taxonomy term should also have been exported
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/taxonomies'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/taxonomies/category'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/taxonomies/category/uncategorized'));
    }

    /**
     * @depends testExportAMenu
     */
    public function testExportAnImage()
    {
        $yaml = new Yaml();
        $src = __DIR__ . '/fixtures/sampleimage.png';
        exec("wp --path=www/wordpress-test media import $src --post_id=2 --featured_image");

        $this->runExport();

        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/media'));

        // since WP 4.5 or wp-cli 0.23, the default name for an imported
        // image will be image name minus extension...
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/media/sampleimage'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/media/sampleimage/meta'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/media/sampleimage/sampleimage.png'));

        $obj = $yaml->parse(file_get_contents(WPBOOT_BASEPATH.'/bootstrap/media/sampleimage/meta'));
        $this->assertTrue($obj['post_name'] == 'sampleimage');
        $this->assertTrue($obj['post_type'] == 'attachment');
        $this->assertTrue(isset($obj['post_meta']));

    }

    public function testExportTaxonomy()
    {
        global $testHelpers;
        $yaml = new Yaml();

        $testHelpers->writeAppsettings(
            [
                'keepDefaultContent' => true,
                'content' => [
                    'taxonomies' => [
                        'category' => '*'
                    ]
                ]
            ],
            'yaml'
        );

        $this->runExport();

        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/taxonomies'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/taxonomies/category'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/taxonomies/category/uncategorized'));

        $obj = $yaml->parse(file_get_contents(WPBOOT_BASEPATH.'/bootstrap/taxonomies/category/uncategorized'));
        $this->assertTrue($obj['name'] == 'Uncategorized');
        $this->assertTrue($obj['slug'] == 'uncategorized');
        $this->assertTrue($obj['taxonomy'] == 'category');
    }

    public function testExportTaxonomy2()
    {
        global $testHelpers;
        $yaml = new Yaml();

        exec("wp --path=www/wordpress-test term create category Fruit --description='Fruits'");
        exec("wp --path=www/wordpress-test term create category Apple --parent=3 --description='Specific fruits'");

        $testHelpers->writeAppsettings(
            [
                'keepDefaultContent' => true,
                'content' => [
                    'taxonomies' => [
                        'category' => ["apple"]
                    ]
                ]
            ],
            'yaml'
        );

        $this->runExport();

        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/taxonomies'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/taxonomies/category'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/taxonomies/category/fruit'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/taxonomies/category/apple'));

        $obj = $yaml->parse(file_get_contents(WPBOOT_BASEPATH.'/bootstrap/taxonomies/category/apple'));
        $this->assertTrue($obj['name'] == 'Apple');
        $this->assertTrue($obj['slug'] == 'apple');
        $this->assertTrue($obj['description'] == 'Specific fruits');
        $this->assertTrue($obj['taxonomy'] == 'category');
    }

    public function testExportSettings()
    {
        global $testHelpers;

        $testHelpers->writeAppsettings(
            [
                'keepDefaultContent' => true,
                'plugins' => [
                    'standard' => ['wp-cfm']
                ],
                'content' => [
                    'menus' => ['main']
                ]
            ],
            'yaml'
        );

        exec('wp bootstrap setup');

        $this->runExport();

        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/config'));
        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/config/wpbootstrap.json'));

        $obj = json_decode(file_get_contents(WPBOOT_BASEPATH.'/bootstrap/config/wpbootstrap.json'));
        $label = '.label';
        $this->assertTrue(count($obj) > 0);
        $this->assertTrue($obj->$label == 'wpbootstrap');
    }

    public function testExportSidebar()
    {
        global $testHelpers;
        $yaml = new Yaml();

        exec("wp --path=www/wordpress-test widget add calendar sidebar-1 1 --title='Foobar'");

        $testHelpers->writeAppsettings(
            [
                'keepDefaultContent' => true,
                'content' => [
                    'sidebars' => ['sidebar-1']
                ]
            ],
            'yaml'
        );

        $this->runExport();

        $this->assertTrue(file_exists(WPBOOT_BASEPATH.'/bootstrap/sidebars/sidebar-1/calendar-1'));
        $obj = $yaml->parse(file_get_contents(WPBOOT_BASEPATH.'/bootstrap/sidebars/sidebar-1/calendar-1'));
        $this->assertEquals('Foobar', $obj['title']);
    }

    private function runExport()
    {
        global $testHelpers;

        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $helpers = $app['helpers'];
        $helpers->recursiveRemoveDirectory(WPBOOT_BASEPATH . '/bootstrap');

        require_once(WPBOOT_BASEPATH.'/www/wordpress-test/wp-load.php');
        $export = $app['export'];
        $export->run([], []);
    }
}
