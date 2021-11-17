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
}