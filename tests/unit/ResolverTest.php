<?php

namespace Wpbootstrap;

class ResolverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \WP_Mock::setUp();
        \WP_Mock::wpFunction('wp_cache_flush');

    }

    public function tearDown()
    {
        \WP_Mock::tearDown();
    }

    public function testResolveOptionReferences()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $r = $app['resolver'];
        $i = $app['import'];
        $testHelpers->makePublic($i, 'posts');

        $i->posts = [(object)['id' => 10, 'post' => ['ID' => 12]]];

        /*******************************************************
         * simple
         *
         */
        \WP_Mock::wpFunction('get_option', [
            'args' => ['site_url', 0],
            'times' => 1,
            'return' => 12
        ]);
        \WP_Mock::wpFunction('update_option', [
            'args' => ['site_url', 10],
            'times' => 1
        ]);
        $references = ['site_url'];
        $r->resolveOptionReferences($references, 'post');

        /*******************************************************
         * expression
         *
         */
        \WP_Mock::wpFunction('get_option', [
            'args' => ['complex', 0],
            'times' => 1,
            'return' => [0,12,100]
        ]);
        \WP_Mock::wpFunction('update_option', [
            'args' => ['complex', [0,10,100]],
            'times' => 1
        ]);
        $references = ['complex' => '[1]'];
        $r->resolveOptionReferences($references, 'post');


        \WP_Mock::wpFunction('get_option', [
            'args' => ['complex', 0],
            'times' => 1,
            'return' => ['a' => 0,'b' => 12, 'c' => 100]
        ]);
        \WP_Mock::wpFunction('update_option', [
            'args' => ['complex', ['a' => 0,'b' => 10, 'c' => 100]],
            'times' => 1
        ]);
        $references = ['complex' => "['b']"];
        $r->resolveOptionReferences($references, 'post');


        \WP_Mock::wpFunction('get_option', [
            'args' => ['complex', 0],
            'times' => 1,
            'return' => [0,12,100]
        ]);
        \WP_Mock::wpFunction('update_option', [
            'args' => ['complex', [0,10,100]],
            'times' => 0
        ]);
        $references = ['complex' => '[19]'];
        $r->resolveOptionReferences($references, 'post');

        /*******************************************************
         * array of expressions
         *
         */
        \WP_Mock::wpFunction('get_option', [
            'args' => ['complex', 0],
            'times' => 1,
            'return' => ['a' => 0,'b' => 12, 'c' => 12]
        ]);
        \WP_Mock::wpFunction('update_option', [
            'args' => ['complex', ['a' => 0,'b' => 10, 'c' => 10]],
            'times' => 1
        ]);
        $references = ['complex' => ["['b']", "['c']"]];
        $r->resolveOptionReferences($references, 'post');

    }

    public function testResolvePostMetaReferences()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $r = $app['resolver'];
        $i = $app['import'];
        $testHelpers->makePublic($i, 'posts');

        $i->posts = [
            (object)['id' => 10,
            'post' => [
                'ID' => 12,
                'post_meta' => ['foobar' => [12, 'x:12']]
            ]]
        ];

        \WP_Mock::wpFunction('update_post_meta', [
            'args' => [10,'foobar', 10, 12],
            'times' => 1
        ]);
        \WP_Mock::wpFunction('update_post_meta', [
            'args' => [10,'foobar', 'x:10', 'x:12'],
            'times' => 1
        ]);

        $references = ['foobar'];
        $r->resolvePostMetaReferences($references, 'post');

    }
}