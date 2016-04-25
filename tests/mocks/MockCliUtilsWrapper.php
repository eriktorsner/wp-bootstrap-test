<?php

/**
 * Class MockCliWrapper
 */
class MockCliUtilsWrapper
{
    public $format;
    public $output;
    public $fields;

    public function format_items($format, $output, $fields)
    {
        $this->format = $format;
        $this->output = $output;
        $this->fields = $fields;
    }
}