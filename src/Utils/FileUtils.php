<?php
namespace Herald\GreenPass\Utils;

class FileUtils
{

    const COUNTRY = "it";

    const LOCALE = "it_IT";

    const HOUR_BEFORE_DOWNLOAD_LIST = 24;

    public static function checkFileNotExistOrExpired($file, $time): bool
    {
        if (! file_exists($file)) {
            return true;
        }
        return time() - filemtime($file) > $time;
    }

    public static function readDataFromFile($file)
    {
        $fp = fopen($file, 'r');
        $content = fread($fp, filesize($file));
        fclose($fp);
        return $content;
    }

    public static function saveDataToFile($file, $data): bool
    {
        if (! empty($data)) {
            $fp = fopen($file, 'w');
            fwrite($fp, $data);
            fclose($fp);
            return true;
        }
        return false;
    }
}