<?php

/**
 * Class MockWpCfmReadWrite
 */
class MockWpCfmReadWrite
{
    public $lastPushed = '';
    public $lastPulled = '';

    /**
     * @param $name
     */
    public function push_bundle($name)
    {
        $this->lastPushed = $name;
    }

    /**
     * @param $name
     */
    public function pull_bundle($name)
    {
        $this->lastPulled = $name;
    }
}