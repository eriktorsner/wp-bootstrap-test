<?php

namespace Wpbootstrap;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function testFieldSearchReplace()
    {
        $container = Container::getInstance();
        $h = $container->getHelpers();

        // scalar
        $fld = 'fuuSEARCHbar';
        $h->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals('fuuREPLACEbar', $fld);

        // Arrays
        $fld = array('fuu' => 'fuuSEARCHbar', 'foo' => 'fooSEARCHbar');
        $h->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals('fuuREPLACEbar', $fld['fuu']);
        $this->assertEquals('fooREPLACEbar', $fld['foo']);

        // Object with array
        $fld = new \stdClass();
        $fld->fuu = 'fuuSEARCHbar';
        $fld->foo = array('foo' => 'fooSEARCHbar');
        $h->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals('fuuREPLACEbar', $fld->fuu);
        $this->assertEquals('fooREPLACEbar', $fld->foo['foo']);
    }

    public function testFieldSearchReplaceB64()
    {
        $container = Container::getInstance();
        $h = $container->getHelpers();

        // scalar
        $fld = base64_encode('fuuSEARCHbar');
        $h->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals(base64_encode('fuuREPLACEbar'), $fld);

        // Arrays
        $fld = array('fuu' => base64_encode('fuuSEARCHbar'), 'foo' => base64_encode('fooSEARCHbar'));
        $h->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals(base64_encode('fuuREPLACEbar'), $fld['fuu']);
        $this->assertEquals(base64_encode('fooREPLACEbar'), $fld['foo']);

        // Object with array
        $fld = new \stdClass();
        $fld->fuu = base64_encode('fuuSEARCHbar');
        $fld->foo = array('foo' => base64_encode('fooSEARCHbar'));
        $h->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals(base64_encode('fuuREPLACEbar'), $fld->fuu);
        $this->assertEquals(base64_encode('fooREPLACEbar'), $fld->foo['foo']);
    }

    public function testFieldSearchReplaceSer()
    {
        $container = Container::getInstance();
        $h = $container->getHelpers();

        // scalar
        $fld = serialize('fuuSEARCHbar');
        $h->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals(serialize('fuuREPLACEbar'), $fld);

        // Arrays
        $fld = array('fuu' => serialize('fuuSEARCHbar'), 'foo' => serialize('fooSEARCHbar'));
        $h->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals(serialize('fuuREPLACEbar'), $fld['fuu']);
        $this->assertEquals(serialize('fooREPLACEbar'), $fld['foo']);

        // Object with array
        $fld = new \stdClass();
        $fld->fuu = serialize('fuuSEARCHbar');
        $fld->foo = array('foo' => serialize('fooSEARCHbar'));
        $h->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals(serialize('fuuREPLACEbar'), $fld->fuu);
        $this->assertEquals(serialize('fooREPLACEbar'), $fld->foo['foo']);
    }

    public function _testExtractMedia()
    {
        $container = Container::getInstance();
        $e = $container->getExportMedia();
        //$b = new Boostrap();

        $string = sprintf(
            'some string <img src="%s/2015/01/foobar.png> sdfom other  <img src="%s/2015/01/ither.png">',
            $uploadDir['baseurl'],
            $uploadDir['baseurl']
        );
        $ret = $e->getReferencedMedia($string);
        print_r($ret);

        $obj = array($string, 'foobar', $string);
        $ret = $e->getReferencedMedia($obj);
        print_r($ret);
    }

    public function testIsImageUrl()
    {
        $container = Container::getInstance();
        $e = $container->getExtractMedia();

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
        $container = Container::getInstance();
        $e = $container->getExtractMedia();

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
