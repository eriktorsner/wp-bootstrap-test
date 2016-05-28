<?php

namespace Wpbootstrap;

use Symfony\Component\Yaml\Yaml;

/**
 * Class TaxonomiesManagerTest
 * @package Wpbootstrap
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TaxonomiesManagerTest extends \PHPUnit_Framework_TestCase
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
        global $testHelpers, $wp_version, $mockTerms;
        $wp_version = '4.5';

        $testHelpers->writeAppsettings(
            [
                'keepDefaultContent' => true,
                'content' => [
                    'taxonomies' => [
                        'category' => ["catTerm1"]
                    ]
                ]
            ],
            'yaml'
        );

        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $cliutils = $app['cliutils'];
        $t = new Commands\Taxonomies();

        \WP_Mock::wpFunction('get_terms', [
            'times' => '1+',
            'return' => [(object)$mockTerms['catTerm1'], (object)$mockTerms['catTerm2']],
        ]);
        $t->listItems(array(), array());

        $this->assertEquals('table', $cliutils->format);
        $this->assertEquals(2, count($cliutils->output));
        $this->assertTrue(in_array('Managed', $cliutils->fields));
        $this->assertEquals('Yes', $cliutils->output[0]['Managed']);
        $this->assertEquals('No', $cliutils->output[1]['Managed']);

        $t->listItems(array(), array('format' => 'json'));
        $this->assertEquals('json', $cliutils->format);
    }

    public function testListItems2()
    {
        global $testHelpers, $wp_version, $mockTerms;
        $wp_version = '4.5';

        $testHelpers->writeAppsettings(
            [
                'keepDefaultContent' => true,
                'content' => [
                    'taxonomies' => [
                        'category' => "*"
                    ]
                ]
            ],
            'yaml'
        );

        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $cliutils = $app['cliutils'];
        $t = new Commands\Taxonomies();

        \WP_Mock::wpFunction('get_terms', [
            'times' => '1+',
            'return' => [(object)$mockTerms['catTerm1'], (object)$mockTerms['catTerm2']],
        ]);
        $t->listItems(array(), array());

        $this->assertEquals('table', $cliutils->format);
        $this->assertEquals(2, count($cliutils->output));
        $this->assertTrue(in_array('Managed', $cliutils->fields));
        $this->assertEquals('Yes', $cliutils->output[0]['Managed']);
        $this->assertEquals('Yes', $cliutils->output[1]['Managed']);

        $t->listItems(array(), array('format' => 'json'));
        $this->assertEquals('json', $cliutils->format);

    }

    public function testAdd()
    {
        global $testHelpers, $mockTerms;
        $testHelpers->writeAppsettings(
            [
                'keepDefaultContent' => true,
            ],
            'yaml'
        );
        $yaml = new Yaml();

        \WP_Mock::wpFunction('get_term_by', [
            'times' => '2',
            'return' => (object)$mockTerms['catTerm1']
        ]);

        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $t = new Commands\Taxonomies();
        $t->add(array('category', 21), array());
        $settings = $yaml->parse(WPBOOT_BASEPATH . '/appsettings.yml');

        $this->assertTrue(isset($settings['content']['taxonomies']['category']));
        $this->assertEquals(1, count($settings['content']['taxonomies']['category']));
        $this->assertEquals('catTerm1', $settings['content']['taxonomies']['category'][0]);

        file_put_contents(WPBOOT_BASEPATH . '/appsettings.yml', "foobar: true\n");
        $t->add(array('category', 'catTerm1'), array());
        $settings = $yaml->parse(WPBOOT_BASEPATH . '/appsettings.yml');
        $this->assertTrue(isset($settings['content']['taxonomies']['category']));
        $this->assertEquals(1, count($settings['content']['taxonomies']['category']));
        $this->assertEquals('catTerm1', $settings['content']['taxonomies']['category'][0]);

        file_put_contents(WPBOOT_BASEPATH . '/appsettings.yml', "foobar: true\n");
        $t->add(array('category', '*'), array());
        $settings = $yaml->parse(WPBOOT_BASEPATH . '/appsettings.yml');
        $this->assertTrue(isset($settings['content']['taxonomies']['category']));
        $this->assertEquals(1, count($settings['content']['taxonomies']['category']));
        $this->assertEquals('*', $settings['content']['taxonomies']['category'][0]);

        \WP_Mock::wpFunction('get_term_by', [
            'times' => '1',
            'return' => false
        ]);
        file_put_contents(WPBOOT_BASEPATH . '/appsettings.yml', "foobar: true\n");
        $t->add(array('category', 'yadayada'), array());
        $settings = $yaml->parse(WPBOOT_BASEPATH . '/appsettings.yml');
        $this->assertFalse(isset($settings['content']['taxonomies']['category']));
    }
}
