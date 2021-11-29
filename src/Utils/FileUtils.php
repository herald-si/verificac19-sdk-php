<?php
namespace Herald\GreenPass\Utils;

class FileUtils
{

    const COUNTRY = "it";

    const LOCALE = "it_IT";

    const HOUR_BEFORE_DOWNLOAD_LIST = 24;

    private $cache_path_override = null;

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

    public static function overrideCacheFilePath($newPath)
    {
        self::$cache_file_path_override = $newPath;
    }

    public static function getCacheFilePath($fileName)
    {
        $cache_uri = self::$cache_path_override;

        if(!$cache_uri)
        {
            $current_dir = dirname(__FILE__);

            $cache_uri = join(DIRECTORY_SEPARATOR, array(
                $current_dir,
                '..',
                '..',
                '..',
                'assets'
            ));
        }

        $uri = join(DIRECTORY_SEPARATOR, array(
            $cache_uri,
            $fileName
        ));

        return $uri;
    }
}