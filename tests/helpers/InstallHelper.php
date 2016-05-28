<?php

class InstallHelper
{
    public function createDefaultInstall($title = '[title]')
    {
        global $testHelpers;

        $testHelpers->writeDotEnv([
            'wppath' => WPBOOT_BASEPATH . '/www/wordpress-test',
            'wpurl' => 'test.wpbootstraptest.local',
            'dbhost' => 'localhost',
            'dbname' => 'wordpress-test',
            'dbuser' => 'wordpress',
            'dbpass' => 'wordpress',
            'wpuser' => 'admin',
            'wppass' => 'admin',
        ]);
        $testHelpers->writeWpYaml([
            'require' => ['vendor/autoload.php'],
            'path' => WPBOOT_BASEPATH . '/www/wordpress-test',
        ]);
        $testHelpers->writeAppsettings(
            [
                'title' => $title,
                'keepDefaultContent' => true,
            ],
            'yaml'
        );
        exec('wp bootstrap install');
    }
}