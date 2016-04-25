<?php

class WpMirrorHelperFiles
{
    public function getFileIndex($root)
    {
        $fileName = str_replace('.php', '.tmp', __FILE__);
        $ignore[] = '/'.basename(__FILE__);
        $ignore[] = '/'.basename($fileName);

        $fileHandle = fopen($fileName, 'w');
        recScandir(ABSPATH, $fileHandle);
        fclose($fileHandle);

        echo file_get_contents($fileName);
        unlink($fileName);
    }

    private function recScandir($dir, $f)
    {
        global $ignore;
        $dir = rtrim($dir, '/');
        $root = scandir($dir);
        foreach ($root as $value) {
            if ($value === '.' || $value === '..') {
                continue;
            }
            if (fnInArray("$dir/$value", $ignore)) {
                continue;
            }
            if (is_file("$dir/$value")) {
                fileInfo2File($f, "$dir/$value");
                continue;
            }
            fileInfo2File($f, "$dir/$value");
            recScandir("$dir/$value", $f);
        }
    }

    function fileInfo2File($f, $file)
    {
        $stat = stat($file);
        $sum = sha1($stat['size'] . $stat['mtime']);
        $relfile = substr($file, strlen(ABSPATH));
        $row =  array(
            $relfile,
            is_dir($file) ? 0 : $stat['mtime'],
            is_dir($file) ? 0 : $stat['size'],
            is_dir($file) ? 0 : $sum,
            (int) is_dir($file),
            (int) is_file($file),
            (int) is_link($file),
        );
        fwrite($f, join("\t", $row) . "\n");
    }

    function fnInArray($needle, $haystack)
    {
        # this function allows wildcards in the array to be searched
        $needle = substr($needle, strlen(ABSPATH));#
        foreach ($haystack as $value) {
            if (true === fnmatch($value, $needle)) {
                return true;
            }
        }

        return false;
    }


}

