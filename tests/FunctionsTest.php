<?php

namespace Wpbootstrap;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function testFieldSearchReplace()
    {
        $r = new Resolver();

        // scalar
        $fld = 'fuuSEARCHbar';
        $r->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals('fuuREPLACEbar', $fld);

        // Arrays
        $fld = array('fuu' => 'fuuSEARCHbar', 'foo' => 'fooSEARCHbar');
        $r->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals('fuuREPLACEbar', $fld['fuu']);
        $this->assertEquals('fooREPLACEbar', $fld['foo']);

        // Object with array
        $fld = new \stdClass();
        $fld->fuu = 'fuuSEARCHbar';
        $fld->foo = array('foo' => 'fooSEARCHbar');
        $r->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals('fuuREPLACEbar', $fld->fuu);
        $this->assertEquals('fooREPLACEbar', $fld->foo['foo']);
    }

    public function testFieldSearchReplaceB64()
    {
        $r = new Resolver();

        // scalar
        $fld = base64_encode('fuuSEARCHbar');
        $r->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals(base64_encode('fuuREPLACEbar'), $fld);

        // Arrays
        $fld = array('fuu' => base64_encode('fuuSEARCHbar'), 'foo' => base64_encode('fooSEARCHbar'));
        $r->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals(base64_encode('fuuREPLACEbar'), $fld['fuu']);
        $this->assertEquals(base64_encode('fooREPLACEbar'), $fld['foo']);

        // Object with array
        $fld = new \stdClass();
        $fld->fuu = base64_encode('fuuSEARCHbar');
        $fld->foo = array('foo' => base64_encode('fooSEARCHbar'));
        $r->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals(base64_encode('fuuREPLACEbar'), $fld->fuu);
        $this->assertEquals(base64_encode('fooREPLACEbar'), $fld->foo['foo']);
    }

    public function testFieldSearchReplaceSer()
    {
        $r = new Resolver();

        // scalar
        $fld = serialize('fuuSEARCHbar');
        $r->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals(serialize('fuuREPLACEbar'), $fld);

        // Arrays
        $fld = array('fuu' => serialize('fuuSEARCHbar'), 'foo' => serialize('fooSEARCHbar'));
        $r->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals(serialize('fuuREPLACEbar'), $fld['fuu']);
        $this->assertEquals(serialize('fooREPLACEbar'), $fld['foo']);

        // Object with array
        $fld = new \stdClass();
        $fld->fuu = serialize('fuuSEARCHbar');
        $fld->foo = array('foo' => serialize('fooSEARCHbar'));
        $r->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals(serialize('fuuREPLACEbar'), $fld->fuu);
        $this->assertEquals(serialize('fooREPLACEbar'), $fld->foo['foo']);
    }
}
