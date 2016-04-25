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
        file_put_contents(BASEPATH."/.env$suffix", $out);
    }

    /**
     * @param $config
     */
    public function writeWpYaml($config)
    {
        $dumper = new Dumper();
        file_put_contents(BASEPATH . '/wp-cli.yml', $dumper->dump($config, 2));
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
            file_put_contents(BASEPATH . '/appsettings.yml', $dumper->dump($settings, 2));
            return;
        }
        if ($format == 'json') {
            file_put_contents(BASEPATH . '/appsettings.json', json_encode($settings, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Delete all known settings files
     */
    public function removeSettings()
    {
        @unlink(BASEPATH . '/appsettings.yml');
        @unlink(BASEPATH . '/appsettings.json');
        @unlink(BASEPATH . '/wp-cli.yml');
        @unlink(BASEPATH . '/.env');
        @unlink(BASEPATH . '/.env-test');
        @unlink(BASEPATH . '/.env-development');

        // during testing, we need to drag bootstrap into
        // wp-cli
        file_put_contents(
            BASEPATH . '/wp-cli.yml',
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
            BASEPATH
        );
        exec($mysql);

        $cmd = sprintf('rm -rf %s/www/wordpress-test', PROJECTROOT);
        exec($cmd);
    }

    public function copySettingsFiles($path)
    {
        $src = "$path/appsettings.yml";
        $trg = BASEPATH.'/appsettings.yml';
        if (file_exists($src)) {
            copy($src, $trg);
        }
    }

    public function clearSettingsFiles()
    {
        @unlink(BASEPATH.'/localsettings.json');
        @unlink(BASEPATH.'/appsettings.json');
        @unlink(BASEPATH.'/wp-cli.yml');
    }

    public function deleteState()
    {
        $cmd = 'rm -rf '.BASEPATH.'/bootstrap/*';
        exec($cmd);
    }

    public function copyState($path)
    {
        @mkdir(BASEPATH.'/bootstrap/', 0777, true);
        exec(sprintf("cp -fa $path/* %s/bootstrap/", BASEPATH));
        @exec(sprintf('mv %s/bootstrap/appsettings.yml %s/', BASEPATH, BASEPATH));

        exec('rm -rf '.BASEPATH.'/wp-content');
        if (file_exists(BASEPATH.'/bootstrap/wp-content')) {
            $cmd = sprintf('mv %s/bootstrap/wp-content %s/', BASEPATH, BASEPATH);
            exec($cmd);
        }
    }

    public function setupWpInstallation($prefix)
    {
        copySettingsFiles($prefix);

        $cmd = BASEPATH.'/wp-bootstrap/bin/wpbootstrap wp-install';
        exec($cmd);

        $cmd = BASEPATH.'/wp-bootstrap/bin/wpbootstrap wp-setup';
        exec($cmd);
    }

}