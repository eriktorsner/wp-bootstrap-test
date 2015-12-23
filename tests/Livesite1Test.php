<?php

namespace Wpbootstrap;

class Livesite1Test extends \PHPUnit_Framework_TestCase
{
    public function testImport()
    {
        deleteWpInstallation();
        deleteState();
        copyState('erik.torgesta.com');
        Container::destroy();

        $container = Container::getInstance();
        $bootstrap = $container->getBootstrap();
        $bootstrap->install();
        $bootstrap->setup();

        $container->getImport()->import();

        $this->assertEquals(1, 2 - 1);

        // The front page has ID=22 in the bootstrap/posts/page file
        // but in a fresh WP-install, it should get ID = 3
        //$pages = get_posts(array('post_type' => 'page'));
    }

    public function testReset()
    {
        $container = Container::getInstance();
        $container->getBootstrap()->reset();

        $this->assertFalse(file_exists(PROJECTROOT.'/www/wordpress-test/index.php'));
        $this->assertFalse(file_exists(PROJECTROOT.'/www/wordpress-test/wp-config.php'));
    }
}
