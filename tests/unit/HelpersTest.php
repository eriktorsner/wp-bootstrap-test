<?php

namespace Wpbootstrap;

class HelpersTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \WP_Mock::setUp();

        /*\WP_Mock::wpFunction('wp_upload_dir', [
                'return' => ['baseurl' => 'http://www.example.com']
            ]
        );*/
    }

    public function tearDown()
    {
        \WP_Mock::tearDown();
    }


    public function testFieldSearchReplace()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $h = $app['helpers'];

        // scalar
        $fld = 'fuuSEARCHbar';
        $h->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals('fuuREPLACEbar', $fld);

        // Arrays
        $fld = array('fuu' => 'fuuSEARCHbar', 'foo' => 'fooSEARCHbar');
        $h->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals('fuuREPLACEbar', $fld['fuu']);
        $this->assertEquals('fooREPLACEbar', $fld['foo']);

        // Object with array, bools, numerics etc.
        $fld = new \stdClass();
        $fld->fuu = 'fuuSEARCHbar';
        $fld->foo = array('foo' => 'fooSEARCHbar');
        $fld->bool = true;
        $fld->numeric = 123;
        $fld->nothing = null;
        $fld->dblSerialized = serialize(['first' => serialize('fooSEARCHbar')]);
        $h->fieldSearchReplace($fld, 'SEARCH', 'REPLACE');
        $this->assertEquals('fuuREPLACEbar', $fld->fuu);
        $this->assertEquals('fooREPLACEbar', $fld->foo['foo']);

        $arr = unserialize($fld->dblSerialized);
        $this->assertEquals('fooREPLACEbar', unserialize($arr['first']));

    }

    public function testFieldSearchReplaceB64()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $h = $app['helpers'];

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
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $h = $app['helpers'];

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

    public function testIsUrl()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $h = $app['helpers'];

        $this->assertEquals(true, $h->isUrl('http://www.foobar.com/'));
        $this->assertEquals(true, $h->isUrl('https://www.foobar.com/'));
        $this->assertEquals(true, $h->isUrl('unknown://www.foobar.com/'));

        $this->assertEquals(false, $h->isUrl('www.foobar.com/'));
        $this->assertEquals(false, $h->isUrl('www.foobar.com/foo/bar.html'));
    }

    public function testJsonPrettyPrint()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $h = $app['helpers'];

        $obj = new \stdClass();
        $obj->foo = 'bar';
        $obj->arr = ['aa' => 'bb', 'cc' => 10];

        $jsonStr = json_encode($obj);
        $prettyJsonStr = $h->prettyPrint($jsonStr);
        $rows = explode("\n", $prettyJsonStr);
        $this->assertEquals("{", $rows[0]);
        $this->assertEquals("\t" .'"foo": "bar",', $rows[1]);
        $this->assertEquals("\t\t".'"aa": "bb",', $rows[3]);

        // switch to Windows line endings:
        $jsonStr = str_replace("\n", "\r\n", $prettyJsonStr);
        $prettyJsonStr = $h->prettyPrint($jsonStr);
        $rows = explode("\n", $prettyJsonStr);
        $this->assertEquals("{", $rows[0]);
        $this->assertEquals("\t" .'"foo": "bar",', $rows[1]);
        $this->assertEquals("\t\t".'"aa": "bb",', $rows[3]);

        // escape
        $jsonStr = '{"foo": "bar\""}';
        $prettyJsonStr = $h->prettyPrint($jsonStr);
        $rows = explode("\n", $prettyJsonStr);
        $this->assertEquals("{", $rows[0]);
        $this->assertEquals("\t" .'"foo": "bar\""', $rows[1]);

    }

    public function testGetFiles()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $h = $app['helpers'];

        $cmd = "rm -rf /tmp/testGetFiles";
        exec($cmd);

        @mkdir('/tmp/testGetFiles', 0777, true);
        file_put_contents('/tmp/testGetFiles/file1', 'random');
        file_put_contents('/tmp/testGetFiles/file2', 'random');
        @mkdir('/tmp/testGetFiles/folder1', 0777, true);

        $files = $h->getFiles('/tmp/testGetFiles');

        $this->assertTrue(in_array('file1', $files));
        $this->assertTrue(in_array('file2', $files));
        $this->assertTrue(in_array('folder1', $files));

        $files = $h->getFiles('/tmp/ImNotHere');
        $this->assertEquals(0, count($files));

    }

    public function testRecursiveRemoveDirectory()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $h = $app['helpers'];

        $cmd = "rm -rf /tmp/testGetFiles";
        exec($cmd);

        @mkdir('/tmp/testGetFiles', 0777, true);
        file_put_contents('/tmp/testGetFiles/file1', 'random');
        file_put_contents('/tmp/testGetFiles/file2', 'random');
        @mkdir('/tmp/testGetFiles/folder1', 0777, true);

        $h->recursiveRemoveDirectory('/tmp/testGetFiles');

        $this->assertFalse(file_exists('/tmp/testGetFiles'));
        $this->assertFalse(file_exists('/tmp/testGetFiles/file1'));
        $this->assertFalse(file_exists('/tmp/testGetFiles/file2'));
        $this->assertFalse(file_exists('/tmp/testGetFiles/folder1'));

    }

    public function testUniqueObjectArray()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $h = $app['helpers'];

        $testArray = [
            'first' => (object)['id' => 1, 'value' => 'foo'],
            'second' => (object)['id' => 2, 'value' => 'foo2'],
            'duplicate' => (object)['id' => 1, 'value' => 'duplicate'],
        ];

        $cleanArray = $h->uniqueObjectArray($testArray, 'id');

        $this->assertEquals(2, count($cleanArray));
        $this->assertEquals(1, $cleanArray[0]->id);
        $this->assertEquals(2, $cleanArray[1]->id);

    }

    public function testIsBase64()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $h = $app['helpers'];

        $this->assertTrue($h->isBase64(base64_encode('abc')));
        $this->assertTrue($h->isBase64(base64_encode('räksmörgås')));
        $this->assertFalse($h->isBase64('räksmörgås'));
        $this->assertFalse($h->isBase64('abc123'));
        $this->assertFalse($h->isBase64([1,2,3]));
    }

    public function testIsSerialized()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $h = $app['helpers'];

        $this->assertTrue($h->isSerialized(serialize('räksmörgås')));
        $this->assertTrue($h->isSerialized(serialize([1,2,3,4])));
        $this->assertFalse($h->isSerialized('rämsmörgås'));
        $this->assertFalse($h->isSerialized([1,2,3]));
    }

    public function testGetWPCFMSettings()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $h = $app['helpers'];

        @mkdir(WPBOOT_BASEPATH . '/bootstrap/config', 0777, true);
        file_put_contents(
            WPBOOT_BASEPATH . '/bootstrap/config/wpbootstrap.json',
            json_encode((object)[".label" => "wpbootstrap"])
        );

        $ret = $h->getWPCFMSettings();
        $this->assertTrue(is_object($ret));
        $this->assertTrue(isset($ret->{'.label'}));
    }

    public function testEnsureDefineInFile()
    {
        global $testHelpers;
        $app = $testHelpers->getAppWithMockCli();
        Bootstrap::setApplication($app);
        $h = $app['helpers'];

        $file = WPBOOT_BASEPATH . '/test-config.php';

        file_put_contents(
            $file,
            "<?php\n\n"
        );

        $h->ensureDefineInFile($file, 'foobar', 'foovalue');
        $lines = file($file);
        $patterns = [
            preg_quote("/Added by WP Bootstrap/"),
            preg_quote("/if (!defined('foobar'))/"),
            preg_quote("/define('foobar', 'foovalue');/"),
        ];

        foreach ($patterns as $pattern) {
            $this->assertTrue(count(preg_grep($pattern, $lines)) > 0);
        }

        $h->ensureDefineInFile($file, 'foobar', 'foovalue2');
        $lines = file($file);
        $patterns = [
            preg_quote("/Added by WP Bootstrap/"),
            preg_quote("/if (!defined('foobar'))/"),
            preg_quote("/define('foobar', 'foovalue2');/"),
        ];

        foreach ($patterns as $pattern) {
            $this->assertTrue(count(preg_grep($pattern, $lines)) > 0);
        }

        $h->ensureDefineInFile($file . 'jada', 'foobar', 'foovalue2');
        foreach ($patterns as $pattern) {
            $this->assertTrue(count(preg_grep($pattern, $lines)) > 0);
        }

    }

}