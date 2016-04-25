<?php

namespace Wpbootstrap;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \WP_Mock::setUp();

        \WP_Mock::wpFunction('wp_upload_dir', [
            'return' => ['baseurl' => 'http://www.example.com']
            ]
        );

    }

    public function tearDown()
    {
        \WP_Mock::tearDown();
    }



    public function testIsImageUrl()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $e = $app['extractmedia'];

        $strings = array(
            'http://foo.com/a.b.d-1x1-150x150.png',
            'http://foo.com/a.b.d-1x1-150x150.PNG',
            'http://foo.com/a.b.d-1x1-150x150.jpg',
            'http://foo.com/a.b.d-1x1-150x150.jpeg',
            'http://foo.com/a.b.d-1x1-150x150.GIF',
            'http://foo.com/a.b.d-1x1-150x150.gif',
            'a.b.d-1x1-150x150.png',
            'http://foo.com/a.b.d.image.jpg?size=test',
        );

        foreach ($strings as $string) {
            $ret = $e->isImageUrl($string);
            $this->assertTrue($ret);
        }

        $strings = array(
            'http://foo.com/a.b.d-1x1-150x150.pn',
            'http://foo.com/a.b.d.image.jpg.foobar',
        );

        foreach ($strings as $string) {
            $ret = $e->isImageUrl($string);
            $this->assertFalse($ret);
        }
    }

    public function testRemoveLastSizeIndicator()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $e = $app['extractmedia'];

        $testStrings = array(
            'http://foo.com/a.b.d-1x1-150x150.png',
            'http://foo.com/a.b.d-1123x19999-150x150.png',
            'http://foo.com/a.b.d-1123x19999-1123x19999.png',
            'a.b.d-150x150.png',
        );

        $expectedStrings = array(
            'http://foo.com/a.b.d-1x1.png',
            'http://foo.com/a.b.d-1123x19999.png',
            'http://foo.com/a.b.d-1123x19999.png',
            'a.b.d.png',
        );

        for ($i = 0; $i < count($testStrings); ++$i) {
            $test = $testStrings[$i];
            $expected = $expectedStrings[$i];
            $result = $e->removeLastSizeIndicator($test);
            $this->assertEquals($expected, $result);
        }
    }
}
