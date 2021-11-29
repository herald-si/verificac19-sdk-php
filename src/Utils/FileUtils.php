<?php
namespace Herald\GreenPass\Utils;

class FileUtils
{

    const COUNTRY = "it";

    const LOCALE = "it_IT";

    const HOUR_BEFORE_DOWNLOAD_LIST = 24;
	
	const ASSETS_FOLDER_HOP = 2;
	
	const ASSETS_FOLDER_NAME = "assets";

    private static $cache_path_override = null;

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
        self::$cache_path_override = $newPath;
    }

    public static function getCacheFilePath($fileName)
    {
        $cache_uri = self::$cache_path_override;

        if(!$cache_uri)
        {
            $current_dir[] = dirname(__FILE__);
			for($i = 0; $i < self::ASSETS_FOLDER_HOP; $i++)
			{
				$current_dir[] = "..";
			}
			$current_dir[] = self::ASSETS_FOLDER_NAME;
			
            $cache_uri = join(DIRECTORY_SEPARATOR, $current_dir);
        }

        $uri = join(DIRECTORY_SEPARATOR, array(
            $cache_uri,
            $fileName
        ));

        return $uri;
    }
}