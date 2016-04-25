<?php

namespace Wpbootstrap;

class ImportTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once(__DIR__ . '/fixtures/posts.php');
        require_once __DIR__ . '/fixtures/terms.php';

        \WP_Mock::setUp();
    }

    public function tearDown()
    {
        \WP_Mock::tearDown();
    }

    protected function mockForImport()
    {
        global $wpdb;

        \WP_Mock::wpFunction('wp_cache_flush');
        \WP_Mock::wpFunction('get_option', [
            'args' => ['siteurl'],
            'times' => '2+',
            'return' => 'http://www.example.com'
        ]);
        \WP_Mock::wpFunction('get_option', [
            'args' => ['sidebars_widgets', []],
            'times' => '1+',
            'return' => []
        ]);
        \WP_Mock::wpFunction('get_option', [
            'args' => ['page_on_front', 0],
            'times' => '1+',
            'return' => 12
        ]);
        \WP_Mock::wpFunction('wp_upload_dir', [
            'times' => '0+',
            'return' => ['basedir' => '/tmp/import',]
        ]);
        \WP_Mock::wpFunction('get_taxonomies', [
            'times' => '1+',
            'return' => ['category' => 'category']
        ]);
        \WP_Mock::wpFunction('update_option', [
            'times' => '0+',
        ]);

        \WP_Mock::expectAction('wp-bootstrap_before_import');
        \WP_Mock::expectAction('wp-bootstrap_after_import_settings');
        \WP_Mock::expectAction('wp-bootstrap_after_import_content');
        \WP_Mock::expectAction('wp-bootstrap_after_import');

        $wpdb = $this->getMock('wpdb', ['get_var', 'prepare']);
        $wpdb->expects($this->any())->method('prepare')->will($this->returnValue(''));
        $wpdb->posts = 'wp_posts';

    }

    public function testRunCompletelyEmpty()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $helpers = $app['helpers'];
        $helpers->recursiveRemoveDirectory(BASEPATH . '/bootstrap');

        // Mocking for this test
        $this->mockForImport();

        // run
        $import = $app['import'];
        $import->run([], []);
    }

    public function testRunJustConfig()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $helpers = $app['helpers'];
        $helpers->recursiveRemoveDirectory(BASEPATH . '/bootstrap');

        // Set up some files
        $this->saveWpCfmSettings((object)['.label' => 'wpbootstrap']);

        // Mocking for this test
        $this->mockForImport();
        \WP_Mock::wpFunction('WPCFM', ['times' => 1, 'return' => new \MockWpCfm()]);

        // run
        $import = $app['import'];
        $import->run([], []);
    }

    public function testImportObjects()
    {
        global $wpdb, $mockPosts, $mockTerms, $testHelpers;

        require_once __DIR__.'/../mocks/MockWpObjects.php';

        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $helpers = $app['helpers'];
        $helpers->recursiveRemoveDirectory(BASEPATH . '/bootstrap');

        // Set up some files
        $this->saveWpCfmSettings((object)['.label' => 'wpbootstrap']);
        $this->savePost('posts', $mockPosts['testpost1']);
        $this->savePost('posts', $mockPosts['testpost2']);
        $this->saveTerm($mockTerms['catTerm1']);
        $this->saveTerm($mockTerms['catTerm2']);
        $this->saveMedia($mockPosts['testimage1']);
        $this->saveMedia($mockPosts['testimage2']);


        // emulate that the posts doesn't exist in WP
        unset($mockPosts['testpost1']);
        unset($mockPosts['testimage1']);

        // Mocking
        $this->mockForImport();

        // Mocking for posts import
        \WP_Mock::wpFunction('WPCFM', ['times' => 1, 'return' => new \MockWpCfm()]);
        \WP_Mock::wpFunction('remove_all_actions', ['times' => 1,]);
        $wpdb->expects($this->any())->method('get_var')->will($this->onConsecutiveCalls(0, 112, 0, 142));
        \WP_Mock::wpFunction('wp_insert_post', ['times' => 2, 'return_in_order' => [110, 141]]);
        \WP_Mock::wpFunction('wp_update_post', ['times' => 1,]);
        \WP_Mock::wpFunction('get_post_meta', ['times' => 2, 'return' => ['othermeta' => ['imported']],]);
        \WP_Mock::wpFunction('update_post_meta', ['times' => 7,]);
        \WP_Mock::wpFunction('set_post_thumbnail', ['args' => [110, 141], 'times' => 1,]);

        // Mocking for importing taxonomies
        \WP_Mock::wpFunction('get_terms', [
            'times' => '1+',
            'return' => [(object)['slug' => 'catTerm2', 'term_id' => 27]]
        ]);
        \WP_Mock::wpFunction('wp_insert_term', [
            'times' => '1+',
            'return_in_order' => [['term_id' => 121], ['term_id' => 122],]
        ]);
        \WP_Mock::wpFunction('wp_update_term', ['times' => 1,]);


        // run
        $import = $app['import'];
        $import->run([], []);
    }

    public function testFindTargetObjectId()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);

        $app = Bootstrap::getApplication();
        $import = $app['import'];
        $testHelpers->makePublic($import, 'posts');
        $testHelpers->makePublic($import, 'taxonomies');

        $import->taxonomies = [
            'category' => (object)[
                'terms' => [
                    (object)['id' => 20, 'term' => (object)['term_id' => 98]],
                ]
            ]
        ];
        $this->assertEquals(20, $import->findTargetObjectId(98, 'term'));
        $this->assertEquals(0, $import->findTargetObjectId(999, 'term'));

        $import->posts = [
            (object)['id' => 10, 'post' => (object)['ID' => 12]],
        ];
        $this->assertEquals(10, $import->findTargetObjectId(12, 'post'));
        $this->assertEquals(0, $import->findTargetObjectId(999, 'post'));

    }

    private function savePost($type, $obj)
    {
        $fName = $obj->post_name;
        $subtype = $obj->post_type;
        $folder = BASEPATH . "/bootstrap/$type/$subtype";
        @mkdir($folder, 0777, true);
        file_put_contents("$folder/$fName", serialize($obj));
    }

    private function saveTerm($obj)
    {
        $fName = $obj->slug;
        $taxName = $obj->taxonomy;
        $folder = BASEPATH . "/bootstrap/taxonomies/$taxName";
        @mkdir($folder, 0777, true);
        file_put_contents("$folder/$fName", serialize($obj));
    }

    private function saveMedia($obj)
    {
        $folder = BASEPATH . "/bootstrap/media/{$obj->post_name}";
        @mkdir($folder, 0777, true);
        file_put_contents("$folder/meta", serialize($obj));
        file_put_contents("$folder/{$obj->post_name}", "nothinghere");
    }

    private function saveWpCfmSettings($obj)
    {
        $folder = BASEPATH . "/bootstrap/config";
        @mkdir($folder, 0777, true);
        file_put_contents("$folder/wpbootstrap.json", json_encode($obj));
    }
}