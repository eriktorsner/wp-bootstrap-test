<?php

use \Symfony\Component\Yaml;
use Symfony\Component\Yaml\Dumper;

/**
 * Class TestHelpers
 */
class TestHelpers
{
    /**
     * @param        $arr
     * @param string $suffix
     */
    public function writeDotEnv($arr, $suffix = '')
    {
        $out = '';
        foreach ($arr as $key => $value) {
            $out .= "$key=$value\n";
        }
        file_put_contents(WPBOOT_BASEPATH."/.env$suffix", $out);
    }

    /**
     * @param $config
     */
    public function writeWpYaml($config)
    {
        $dumper = new Dumper();
        file_put_contents(WPBOOT_BASEPATH . '/wp-cli.yml', $dumper->dump($config, 2));
    }

    /**
     * @param $settings
     * @param $format
     */
    public function writeAppsettings($settings, $format)
    {
        if ($format == 'yaml') {
            $dumper = new Dumper();
            $settings = json_decode(json_encode($settings), true);
            file_put_contents(WPBOOT_BASEPATH . '/appsettings.yml', $dumper->dump($settings, 2));
            return;
        }
        if ($format == 'json') {
            file_put_contents(WPBOOT_BASEPATH . '/appsettings.json', json_encode($settings, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Delete all known settings files
     */
    public function removeSettings()
    {
        @unlink(WPBOOT_BASEPATH . '/appsettings.yml');
        @unlink(WPBOOT_BASEPATH . '/appsettings.json');
        @unlink(WPBOOT_BASEPATH . '/wp-cli.yml');
        @unlink(WPBOOT_BASEPATH . '/.env');
        @unlink(WPBOOT_BASEPATH . '/.env-test');
        @unlink(WPBOOT_BASEPATH . '/.env-development');

        // during testing, we need to drag bootstrap into
        // wp-cli
        file_put_contents(
            WPBOOT_BASEPATH . '/wp-cli.yml',
            "require:\n    - vendor/autoload.php\n"
        );
    }

    public function makePublic($obj, $property)
    {
        $reflection = new ReflectionClass($obj);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
    }

    public function getAppWithMockCli($mockContent = null)
    {
        $app = new Pimple\Container();
        $app->register(new Wpbootstrap\Providers\DefaultObjectProvider());
        $app['cli'] = new MockCliWrapper($mockContent);
        $app['cliutils'] = new MockCliUtilsWrapper();
        $app->register(new Wpbootstrap\Providers\ApplicationParametersProvider());

        return $app;
    }

    public function deleteWpInstallation()
    {
        $mysql = sprintf(
            'mysql -u %s -p%s < %s/tests/helpers/resetdatabase.sql',
            'wordpress',
            'wordpress',
            WPBOOT_BASEPATH
        );
        exec($mysql);

        $cmd = sprintf('rm -rf %s/www/wordpress-test', PROJECTROOT);
        exec($cmd);
    }

    public function copySettingsFiles($path)
    {
        $src = "$path/appsettings.yml";
        $trg = WPBOOT_BASEPATH.'/appsettings.yml';
        if (file_exists($src)) {
            copy($src, $trg);
        }
    }

    public function clearSettingsFiles()
    {
        @unlink(WPBOOT_BASEPATH.'/localsettings.json');
        @unlink(WPBOOT_BASEPATH.'/appsettings.json');
        @unlink(WPBOOT_BASEPATH.'/wp-cli.yml');
    }

    public function deleteState()
    {
        $cmd = 'rm -rf '.WPBOOT_BASEPATH.'/bootstrap/*';
        exec($cmd);
    }

    public function copyState($path)
    {
        @mkdir(WPBOOT_BASEPATH.'/bootstrap/', 0777, true);
        exec(sprintf("cp -fa $path/* %s/bootstrap/", WPBOOT_BASEPATH));
        @exec(sprintf('mv %s/bootstrap/appsettings.yml %s/', WPBOOT_BASEPATH, WPBOOT_BASEPATH));

        exec('rm -rf '.WPBOOT_BASEPATH.'/wp-content');
        if (file_exists(WPBOOT_BASEPATH.'/bootstrap/wp-content')) {
            $cmd = sprintf('mv %s/bootstrap/wp-content %s/', WPBOOT_BASEPATH, WPBOOT_BASEPATH);
            exec($cmd);
        }
    }

    public function setupWpInstallation($prefix)
    {
        copySettingsFiles($prefix);

        $cmd = WPBOOT_BASEPATH.'/wp-bootstrap/bin/wpbootstrap wp-install';
        exec($cmd);

        $cmd = WPBOOT_BASEPATH.'/wp-bootstrap/bin/wpbootstrap wp-setup';
        exec($cmd);
    }

}