<?php

namespace Wpbootstrap;

class SnapshotTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        deleteWpInstallation();
        setupWpInstallation('snapshottest');
        Container::destroy();
    }

    public function testGetSnapshot()
    {
        global $argv;
        $this->destroyState();

        $argv = array(
            'dummy',
            'dummy',
            'snapshot',
        );

        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $helpers = $container->getHelpers();
        $snapshot = $container->getSnapshots();

        $snapshot->manage();

        $files = $helpers->getFiles(PROJECTROOT.'/bootstrap/config/snapshots');
        $this->assertEquals(1, count($files));
        $file = $files[0];
        $file = str_replace('.snapshot', '', $file);
        $this->assertTrue(is_numeric($file));
    }

    public function testGetSnapshot2()
    {
        global $argv;
        $this->destroyState();

        $argv = array(
            'dummy',
            'dummy',
        );

        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $helpers = $container->getHelpers();
        $snapshot = $container->getSnapshots();

        $snapshot->manage();

        $files = $helpers->getFiles(PROJECTROOT.'/bootstrap/config/snapshots');
        $this->assertEquals(0, count($files));
        $this->assertEquals('wp-snapshots expects at least one subcommand', $this->getOutput($container->getClimate()));
    }

    public function testGetSnapshot3()
    {
        global $argv;
        $this->destroyState();

        $argv = array(
            'dummy',
            'dummy',
            'snapshot',
            'foobar',
        );

        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $helpers = $container->getHelpers();
        $snapshot = $container->getSnapshots();

        $snapshot->manage();

        $files = $helpers->getFiles(PROJECTROOT.'/bootstrap/config/snapshots');
        $this->assertEquals(1, count($files));
        $this->assertEquals('foobar.snapshot', $files[0]);
    }

    public function testGetSnapshot4()
    {
        global $argv;
        $this->destroyState();

        $argv = array(
            'dummy',
            'dummy',
            'snapshot',
            'foobar',
            'comment',
        );

        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $helpers = $container->getHelpers();
        $snapshot = $container->getSnapshots();

        $snapshot->manage();

        $files = $helpers->getFiles(PROJECTROOT.'/bootstrap/config/snapshots');
        $this->assertEquals(1, count($files));
        $this->assertEquals('foobar.snapshot', $files[0]);
        $file = $files[0];
        $obj = unserialize(file_get_contents(PROJECTROOT.'/bootstrap/config/snapshots/foobar.snapshot'));
        $this->assertEquals('comment', $obj->comment);
    }

    public function testGetSnapshot5()
    {
        global $argv;
        $this->destroyState();

        $argv = array(
            'dummy',
            'dummy',
            'snapshot',
            'foobar',
            'comment',
        );

        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $helpers = $container->getHelpers();
        $snapshot = $container->getSnapshots();
        $snapshot->manage();

        $content = file_get_contents(PROJECTROOT.'/bootstrap/config/snapshots/foobar.snapshot');
        $snapshot->manage();

        $this->assertEquals('Snapshot foobar already exists', $this->getOutput($container->getClimate()));
    }

    public function testListSnapshot()
    {
        global $argv;
        $this->destroyState();

        $argv = array(
            'dummy',
            'dummy',
            'snapshot',
            'foobar',
            'comment',
        );

        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $snapshot = $container->getSnapshots();
        $snapshot->manage();

        $argv = array(
            'dummy',
            'dummy',
            'list',
        );

        Container::destroy();
        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $snapshot = $container->getSnapshots();
        $snapshot->manage();

        $out = $this->getOutput($container->getClimate());
        $this->assertTrue(strlen($out) > 20);
    }

    public function testDiffSnapshots()
    {
        global $argv;
        $this->destroyState();

        $argv = array(
            'dummy',
            'dummy',
            'diff',
        );

        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $snapshot = $container->getSnapshots();
        $snapshot->manage();

        $out = $this->getOutput($container->getClimate());
        $this->assertEquals('wp-state diff requires at least', substr($out, 0, 31));
    }

    public function testDiffSnapshots2()
    {
        global $argv;
        $this->destroyState();

        $argv = array(
            'dummy',
            'dummy',
            'snapshot',
            'foobar',
            'comment',
        );

        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $snapshot = $container->getSnapshots();
        $snapshot->manage();

        $argv = array(
            'dummy',
            'dummy',
            'diff',
            'foobar',
        );

        Container::destroy();
        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $snapshot = $container->getSnapshots();
        $snapshot->manage();

        $out = $this->getOutput($container->getClimate());
        $this->assertTrue(strpos($out, 'No new, removed or changed options') !== false);
    }

    public function testDiffSnapshots3()
    {
        global $argv;
        $this->destroyState();

        $argv = array(
            'dummy',
            'dummy',
            'diff',
            'foobar',
        );

        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $snapshot = $container->getSnapshots();
        $snapshot->manage();

        $out = $this->getOutput($container->getClimate());
        $this->assertTrue(strpos($out, 'no snapshot file for') !== false);
    }

    public function testDiffSnapshots4()
    {
        global $argv;
        $this->destroyState();

        $cmd = 'wp --allow-root --path=www/wordpress-test option update users_can_register 0';
        exec($cmd);

        $argv = array(
            'dummy',
            'dummy',
            'snapshot',
            'foobar',
            'comment',
        );
        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $snapshot = $container->getSnapshots();
        $snapshot->manage();

        $cmd = 'wp --allow-root --path=www/wordpress-test option update users_can_register 99';
        exec($cmd);

        $argv = array(
            'dummy',
            'dummy',
            'diff',
            'foobar',
        );

        Container::destroy();
        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $snapshot = $container->getSnapshots();
        $snapshot->manage();

        $out = $this->getOutput($container->getClimate());
        $this->assertTrue(strpos($out, 'users_can_register') !== false);
    }

    /**
     * @depends testDiffSnapshots4
     */
    public function testDiffSnapshots5()
    {
        global $argv;

        $unique = time();
        $cmd = "wp --allow-root --path=www/wordpress-test option update dummy_option_$unique 100";
        exec($cmd);

        $argv = array(
            'dummy',
            'dummy',
            'snapshot',
            'foobar2',
        );

        Container::destroy();
        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $snapshot = $container->getSnapshots();
        $snapshot->manage();

        $argv = array(
            'dummy',
            'dummy',
            'diff',
            'foobar',
            'foobar2',
        );

        Container::destroy();
        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $snapshot = $container->getSnapshots();
        $snapshot->manage();

        $out = $this->getOutput($container->getClimate());
        $this->assertTrue(strpos($out, 'users_can_register') !== false);
        $this->assertTrue(strpos($out, 'NEW') !== false);
        $this->assertTrue(strpos($out, 'dummy_option') !== false);
    }

    public function testShowSnapshots()
    {
        global $argv;

        $argv = array(
            'dummy',
            'dummy',
            'show',
            'foobar',
        );
        Container::destroy();
        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $snapshot = $container->getSnapshots();
        $snapshot->manage();

        $out = $this->getOutput($container->getClimate());
        $this->assertTrue(strpos($out, 'active_plugins') !== false);
        $this->assertTrue(strpos($out, 'avatar_default') !== false);
        $this->assertTrue(strpos($out, 'blog_public') !== false);
    }

    public function testShowSnapshots2()
    {
        global $argv;

        $argv = array(
            'dummy',
            'dummy',
            'show',
            'foobar',
            'show_on_front',
        );
        Container::destroy();
        $container = Container::getInstance();
        $container->getClImate()->output->defaultTo('buffer');
        $snapshot = $container->getSnapshots();
        $snapshot->manage();

        $out = $this->getOutput($container->getClimate());
        $this->assertTrue(strpos($out, 'posts') !== false);
    }

    private function getOutput($climate)
    {
        $out = $climate->output->get('buffer')->get();
        $out = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $out);
        $out = ltrim($out, '[m');
        $out = rtrim($out, '[0m');

        return $out;
    }

    private function destroyState()
    {
        $container = Container::getInstance();
        $container->getHelpers()->recursiveRemoveDirectory(PROJECTROOT.'/bootstrap');
        Container::destroy();
    }
}
