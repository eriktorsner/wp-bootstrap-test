<?php

namespace Wpbootstrap;

use Symfony\Component\Yaml\Yaml;

/**
 * Class PostManagerTest
 * @package Wpbootstrap
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PostManagerTest extends \PHPUnit_Framework_TestCase
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


    public function testListItems()
    {
        global $testHelpers;
        $testHelpers->writeAppsettings(
            [
                'keepDefaultContent' => true,
                'content' => [
                    'posts' => [
                        'post' => ["hello-world"]
                    ]
                ]
            ],
            'yaml'
        );

        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $cli = $app['cli'];
        $cliutils = $app['cliutils'];
        $p = new Commands\Posts();

        $cli->launch_self_return = (object)[
            'return_code' => 0,
            'stdout' => json_encode([
                (object)[
                    'ID' => 1,
                    'post_title' => 'hello world',
                    'post_date' => '2016-01-01 12:12:10',
                    'post_status' => 'publish',
                    'post_type' => 'post',
                    'post_name' => 'hello-world',
                ],
                (object)[
                    'ID' => 2,
                    'post_title' => 'hello world',
                    'post_date' => '2016-01-01 12:12:10',
                    'post_status' => 'publish',
                    'post_type' => 'post',
                    'post_name' => 'hello-world_unmmanaged',
                ]
            ])
        ];

        \WP_Mock::wpFunction('get_post_types', [
            'times' => '1+',
            'return' => ['post' => 'post', 'page' => 'page'],
        ]);
        $p->listItems(array(), array());

        $this->assertEquals('table', $cliutils->format);
        $this->assertEquals(2, count($cliutils->output));
        $this->assertTrue(in_array('Managed', $cliutils->fields));
        $this->assertEquals('Yes', $cliutils->output[0]['Managed']);
        $this->assertEquals('No', $cliutils->output[1]['Managed']);

        $p->listItems(array(), array('format' => 'json'));
        $this->assertEquals('json', $cliutils->format);

    }

    public function testListItems2()
    {
        global $testHelpers;
        $testHelpers->writeAppsettings(
            [
                'keepDefaultContent' => true,
                'content' => [
                    'posts' => [
                        'post' => "*"
                    ]
                ]
            ],
            'yaml'
        );

        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $cli = $app['cli'];
        $cliutils = $app['cliutils'];
        $p = new Commands\Posts();

        $cli->launch_self_return = (object)[
            'return_code' => 0,
            'stdout' => json_encode([
                (object)[
                    'ID' => 1,
                    'post_title' => 'hello world',
                    'post_date' => '2016-01-01 12:12:10',
                    'post_status' => 'publish',
                    'post_type' => 'post',
                    'post_name' => 'hello-world',
                ],
                (object)[
                    'ID' => 2,
                    'post_title' => 'hello world',
                    'post_date' => '2016-01-01 12:12:10',
                    'post_status' => 'publish',
                    'post_type' => 'post',
                    'post_name' => 'hello-world_unmmanaged',
                ]
            ])
        ];

        \WP_Mock::wpFunction('get_post_types', [
            'times' => '1+',
            'return' => ['post' => 'post', 'page' => 'page'],
        ]);

        $p->listItems(array(), array());

        $this->assertEquals('table', $cliutils->format);
        $this->assertEquals(2, count($cliutils->output));
        $this->assertTrue(in_array('Managed', $cliutils->fields));
        $this->assertEquals('Yes', $cliutils->output[0]['Managed']);
        $this->assertEquals('Yes', $cliutils->output[1]['Managed']);

        $p->listItems(array(), array('format' => 'json'));
        $this->assertEquals('json', $cliutils->format);

    }

    public function testAdd()
    {
        global $testHelpers, $mockPosts;
        $testHelpers->writeAppsettings(
            [
                'keepDefaultContent' => true,
            ],
            'yaml'
        );
        $yaml = new Yaml();

        \WP_Mock::wpFunction('get_post', [
            'times' => '1',
            'return' => (object)$mockPosts['testpost1']
        ]);

        \WP_Mock::wpFunction('get_posts', [
            'times' => '1',
            'return' => [(object)$mockPosts['testpost1']]
        ]);

        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $cli = $app['cli'];
        $p = new Commands\Posts();
        $p->add(array(10), array());
        $settings = $yaml->parse(WPBOOT_BASEPATH . '/appsettings.yml');

        $this->assertTrue(isset($settings['content']['posts']['post']));
        $this->assertEquals(1, count($settings['content']['posts']['post']));
        $this->assertEquals('testpost1', $settings['content']['posts']['post'][0]);

        file_put_contents(WPBOOT_BASEPATH . '/appsettings.yml', "foobar: true\n");
        $p->add(array('testpost1'), array());
        $settings = $yaml->parse(WPBOOT_BASEPATH . '/appsettings.yml');
        $this->assertTrue(isset($settings['content']['posts']['post']));
        $this->assertEquals(1, count($settings['content']['posts']['post']));
        $this->assertEquals('testpost1', $settings['content']['posts']['post'][0]);


        \WP_Mock::wpFunction('get_posts', [
            'times' => '1',
            'return' => false
        ]);

        file_put_contents(WPBOOT_BASEPATH . '/appsettings.yml', "foobar: true\n");
        $p->add(array('testpost1'), array());
        $settings = $yaml->parse(WPBOOT_BASEPATH . '/appsettings.yml');
        $this->assertFalse(isset($settings['content']['posts']['post']));
    }
}
