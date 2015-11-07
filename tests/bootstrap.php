<?php

define('PROJECTROOT', dirname(dirname(__FILE__)));
define('TESTMODE', true);

require_once __DIR__.'/../vendor/autoload.php';

function deleteWpInstallation()
{
    $mysql = sprintf(
        'mysql -u %s -p%s < %s/tests/fixtures/resetdatabase.sql',
        'wordpress',
        'wordpress',
        PROJECTROOT
    );
    exec($mysql);

    $cmd = sprintf("rm -rf %s/www/wordpress-test", PROJECTROOT);
    exec($cmd);
}

function copySettingsFiles($prefix)
{
    $src = PROJECTROOT."/tests/fixtures/$prefix.localsettings.json";
    $trg = PROJECTROOT."/localsettings.json";
    if (file_exists($src)) {
        copy($src, $trg);
    }

    $src = PROJECTROOT."/tests/fixtures/$prefix.appsettings.json";
    $trg = PROJECTROOT."/appsettings.json";
    if (file_exists($src)) {
        copy($src, $trg);
    }
}

function clearSettingsFiles()
{
    @unlink(PROJECTROOT."/localsettings.json");
    @unlink(PROJECTROOT."/appsettings.json");
}

function deleteState()
{
    $cmd = "rm -rf ".PROJECTROOT."/bootstrap/*";
    exec($cmd);
}

function copyState($name)
{
    $cmd = sprintf("cp -fa %s/tests/fixtures/%s/* %s/bootstrap/", PROJECTROOT, $name, PROJECTROOT);
    exec($cmd);

    $cmd = sprintf("mv %s/bootstrap/*settings.json %s/", PROJECTROOT, PROJECTROOT);
    exec($cmd);
}

function setupWpInstallation($prefix)
{
    copySettingsFiles($prefix);

    $cmd = PROJECTROOT.'/wp-bootstrap/bin/wpbootstrap wp-install';
    exec($cmd);

    $cmd = PROJECTROOT.'/wp-bootstrap/bin/wpbootstrap wp-setup';
    exec($cmd);
}

function prompt($msg = "Press any key")
{
    echo $msg."\n";
    ob_flush();
    $in = trim(fgets(STDIN));

    return $in;
}
