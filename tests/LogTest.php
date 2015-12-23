<?php

namespace Wpbootstrap;

class LogTest extends \PHPUnit_Framework_TestCase
{
    public function testFileLog1()
    {
        // ensure no previous files exists
        $logfile = 'log/testlog.log';
        @unlink($logfile);
        copySettingsFiles('logtest');
        $container = Container::getInstance();

        $this->addMessages($container->getLog());
        $this->assertEquals(0, $this->getLines($logfile));
    }

    public function testFileLog2()
    {
        // ensure no previous files exists
        $logfile = 'log/testlog.log';
        @unlink($logfile);
        copySettingsFiles('logtest');

        $ls = json_decode(file_get_contents('localsettings.json'));
        $ls->logfile = $logfile;
        file_put_contents('localsettings.json', json_encode($ls));
        Container::destroy();

        $container = Container::getInstance();
        $this->addMessages($container->getLog());
        $this->assertEquals(5, $this->getLines($logfile));
    }

    public function testFileLog3()
    {
        // ensure no previous files exists
        $logfile = 'log/testlog.log';
        @unlink($logfile);
        copySettingsFiles('logtest');

        $ls = json_decode(file_get_contents('localsettings.json'));
        $ls->logfile = $logfile;
        $ls->loglevel = 'CRITICAL';
        file_put_contents('localsettings.json', json_encode($ls));
        Container::destroy();

        $container = Container::getInstance();
        $this->addMessages($container->getLog());
        $this->assertEquals(3, $this->getLines($logfile));
    }

    /*
     * @outputBuffering enabled
     * NOTE: this test is disabled, can't get the expectOutput.. call 
     * to work as intended. Perhaps a Monolog issue?
     */
    public function testConsoleLog()
    {
        copySettingsFiles('logtest');

        $ls = json_decode(file_get_contents('localsettings.json'));
        $ls->consoleloglevel = 'DEBUG';
        file_put_contents('localsettings.json', json_encode($ls));

        Container::destroy();
        $container = Container::getInstance();
        $log = $container->getLog();
        /*$this->expectOutputRegex('~\[\d\d\d\d\-\d\d\-\d\d \d\d:\d\d:\d\d\] test *.~');
        $log->addDebug('test');*/
    }

    private function addMessages($log)
    {
        $log->addDebug('Test');
        $log->addInfo('Test');
        $log->addNotice('Test');
        $log->addWarning('Test');
        $log->addError('Test');
        $log->addCritical('Test');
        $log->addAlert('Test');
        $log->addEmergency('Test');
    }

    private function getLines($file)
    {
        if (!file_exists($file)) {
            return 0;
        }
        $f = fopen($file, 'rb');
        $lines = 0;

        while (!feof($f)) {
            $lines += substr_count(fread($f, 8192), "\n");
        }

        fclose($f);

        return $lines;
    }
}
