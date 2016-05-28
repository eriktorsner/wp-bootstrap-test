<?php

/**
 * Class MockCliWrapper
 */
class MockCliWrapper
{
    private $runner;
    public $line = [];
    public $log = [];
    public $debug = [];
    public $warning = [];
    public $error = [];

    public $launch_self_return = null;

    public function __construct($runner = null)
    {
        $this->runner = $runner;

        if (!$runner) {
            $this->runner = new \stdClass();
            $this->runner->config = ['path' => WPBOOT_BASEPATH . '/www/wordpress-test'];
            $this->runner->arguments = [];
            $this->runner->assoc_args = [];
            $this->runner->project_config_path = WPBOOT_BASEPATH . '/wp-cli.yml';
        }
    }

    /**
     * @return \WP_CLI\Runner
     */
    public function get_runner()
    {
        return $this->runner;
    }

    /**
     * @param string $msg
     */
    public function log($msg)
    {
        $this->log[] = $msg;
    }

    public function line($message = '')
    {
        $this->line[] = $message;
    }

    /**
     * @param string $message
     */
    public function debug($message)
    {
        $this->debug[] = $message;
    }

    /**
     * @param string $message
     */
    public function warning($message)
    {
        $this->warning[] = $message;
    }

    /**
     * @param string    $message
     * @param bool|true $exit
     */
    public function error($message, $exit = true)
    {
        $this->error[] = $message;
    }

    /**
     * @param string $question
     * @param array  $assoc_args
     */
    public function confirm($question, $assoc_args = array())
    {

    }

    /**
     * @param string  $message
     */
    public function success($message)
    {

    }

    /**
     * @param       $args
     * @param array $assoc_args
     */
    public function run_command($args, $assoc_args = array())
    {

    }

    /**
     * @param string     $command
     * @param bool|true  $exit_on_error
     * @param bool|false $return_detailed
     *
     * @return int|\ProcessRun
     */
    public function launch($command, $exit_on_error = true, $return_detailed = false)
    {
        exec($command);

    }

    /**
     * @param string     $command
     * @param array      $args
     * @param array      $assoc_args
     * @param bool|true  $exit_on_error
     * @param bool|false $return_detailed
     * @param array      $runtime_args
     *
     * @return int|\ProcessRun
     */
    public function launch_self(
        $command,
        $args = array(),
        $assoc_args = array(),
        $exit_on_error = true,
        $return_detailed = false,
        $runtime_args = array()
    ) {

        if ($this->launch_self_return) {
            return $this->launch_self_return;
        }

        return (object)[
            'return_code' => 0,
            'stdout' => json_encode([]),
        ];
    }
}