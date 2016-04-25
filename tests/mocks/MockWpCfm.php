<?php

/**
 * Class MockWpCfm
 */
class MockWpCfm
{
    public $readwrite;

    public function __construct()
    {
        $this->readwrite = new MockWpCfmReadWrite();
    }
}

