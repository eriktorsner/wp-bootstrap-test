<?php

namespace Wpbootstrap;

use \Pimple\Container;
use Wpbootstrap\Commands\OptionSnap;

class CmdOptionsnapTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \WP_Mock::setUp();
    }

    public function tearDown()
    {
        \WP_Mock::tearDown();
    }

    public function testSnap()
    {
        global $wpdb, $testHelpers;

        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $helpers = $app['helpers'];
        $helpers->recursiveRemoveDirectory(BASEPATH . '/bootstrap/snapshots');

        \WP_Mock::wpfunction('wp_cache_delete', ['times' => 1]);
        $wpdb = $this->getMock('wpdb', ['get_col']);
        $wpdb->expects($this->any())->method('get_col')->will($this->returnValue(['opta', 'optb']));
        $wpdb->options = 'wp_options';
        \WP_Mock::wpfunction('get_option', ['return_in_order' => ['vala', ['valb', 'foo']],]);

        $snapper = new OptionSnap();
        $snapper->snap(['snap1'], []);

        $file = BASEPATH . '/bootstrap/snapshots/snap1.snapshot';
        $this->assertTrue(file_exists($file));
        $content = unserialize(file_get_contents($file));
        foreach (['name', 'created', 'environment', 'host', 'options', 'comment'] as $member) {
            $this->assertTrue(isset($content->$member));
        }
        $this->assertEquals('snap1', $content->name);
        $this->assertEquals(2, count($content->options));

    }

    /**
     * @depends testSnap
     */
    public function testSnapDuplicateName()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);

        $snapper = new OptionSnap();
        $snapper->snap(['snap1'], []);

        $this->assertEquals(1, count($app['cli']->error));

    }

    /**
     * @depends testSnap
     */
    public function testListSnapshots()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);

        $snapper = new OptionSnap();
        $snapper->listSnapshots([], []);


        $utils = $app['cliutils'];
        $this->assertEquals('table', $utils->format);
        $this->assertTrue(count($utils->output) == 1);
        foreach (['name', 'created', 'environment', 'host', 'comment'] as $field) {
            $this->assertTrue(isset($utils->output[0][$field]));
        }
        $this->assertTrue(count($utils->fields) == 5);
    }

    /**
     * @depends testSnap
     */
    public function testDiffSnapshotsToCurrent()
    {
        global $wpdb, $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);

        \WP_Mock::wpfunction('wp_cache_delete', ['times' => 1]);
        $wpdb = $this->getMock('wpdb', ['get_col']);
        $wpdb->expects($this->any())->method('get_col')->will($this->returnValue(['opta', 'optb', 'optc']));
        $wpdb->options = 'wp_options';
        \WP_Mock::wpfunction('get_option', ['return_in_order' => ['valaa', ['valbb', 'foo'], 'valcc'],]);

        $snapper = new OptionSnap();
        $snapper->diff(['snap1'], []);

        $utils = $app['cliutils'];

        $this->assertEquals('table', $utils->format);
        $this->assertTrue(count($utils->output) == 3);

        $this->assertEquals('NEW', $utils->output[0]['state']);
        $this->assertEquals('optc', $utils->output[0]['name']);
        $this->assertEquals('valcc', $utils->output[0]['[current state]']);
        $this->assertEquals('', $utils->output[0]['snap1']);
        $this->assertEquals('No', $utils->output[0]['managed']);

        $this->assertEquals('MOD', $utils->output[1]['state']);
        $this->assertEquals('opta', $utils->output[1]['name']);
        $this->assertEquals('valaa', $utils->output[1]['[current state]']);
        $this->assertEquals('vala', $utils->output[1]['snap1']);
        $this->assertEquals('No', $utils->output[1]['managed']);
    }

    /**
     * @depends testSnap
     */
    public function testDiffTwoSnapshots()
    {
        global $wpdb, $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);

        \WP_Mock::wpfunction('wp_cache_delete', ['times' => 1]);
        $wpdb = $this->getMock('wpdb', ['get_col']);
        $wpdb->expects($this->any())->method('get_col')->will($this->returnValue(['opta', 'optb', 'optc']));
        $wpdb->options = 'wp_options';
        \WP_Mock::wpfunction('get_option', ['return_in_order' => ['valaa', ['valbb', 'foo'], 'valcc'],]);

        $snapper = new OptionSnap();
        $snapper->snap(['snap2'], []);
        $snapper->diff(['snap1', 'snap2'], []);

        $utils = $app['cliutils'];
        $this->assertEquals('table', $utils->format);
        $this->assertTrue(count($utils->output) == 3);

        $this->assertEquals('NEW', $utils->output[0]['state']);
        $this->assertEquals('optc', $utils->output[0]['name']);
        $this->assertEquals('valcc', $utils->output[0]['snap2']);
        $this->assertEquals('', $utils->output[0]['snap1']);
        $this->assertEquals('No', $utils->output[0]['managed']);

        $this->assertEquals('MOD', $utils->output[1]['state']);
        $this->assertEquals('opta', $utils->output[1]['name']);
        $this->assertEquals('valaa', $utils->output[1]['snap2']);
        $this->assertEquals('vala', $utils->output[1]['snap1']);
        $this->assertEquals('No', $utils->output[1]['managed']);
    }

    /**
     * @depends testDiffTwoSnapshots
     */
    public function testDiffWrongName()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);

        $snapper = new OptionSnap();
        $snapper->diff(['snapWrong'], []);

        $cli = $app['cli'];
        $this->assertTrue(count($cli->error) == 1);

        $snapper->diff(['snap1', 'snapWrong'], []);
        $this->assertTrue(count($cli->error) == 2);
    }

    public function testShow()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);

        $snapper = new OptionSnap();
        $snapper->show(['snapWrong'], []);
        $cli = $app['cli'];
        $this->assertTrue(count($cli->error) == 1);

        $snapper->show(['snap1'], []);
        $utils = $app['cliutils'];


        $this->assertTrue(count($utils->output) == 2);
        $this->assertTrue($utils->output[0]['name'] == 'opta');

        $snapper->show(['snap1', 'optb'], []);
        $snapper->show(['snap1', 'optb'], []);
        $cli = $app['cli'];
        $this->assertTrue(count($cli->line) == 2);


    }

}