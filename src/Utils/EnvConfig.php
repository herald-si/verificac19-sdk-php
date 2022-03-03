<?php

namespace Herald\GreenPass\Utils;

class EnvConfig
{
    public const PRODUCTION = 'PRODUCTION';

    public const DEBUG = 'DEBUG';

    private const COMPOSER_FOLDER_HOP = 2;

    private const COMPOSER_FILENAME = 'composer.json';

    private static $envMode = self::PRODUCTION;

    public static function enableDebugMode()
    {
        self::$envMode = self::DEBUG;
    }

    public static function disableDebugMode()
    {
        self::$envMode = self::PRODUCTION;
    }

    public static function getCurrentMode()
    {
        return self::$envMode;
    }

    public static function isDebugEnabled()
    {
        return self::$envMode == self::DEBUG;
    }

    private static function getComposerInfo()
    {
        $current_dir[] = dirname(__FILE__);
        for ($i = 0; $i < self::COMPOSER_FOLDER_HOP; ++$i) {
            $current_dir[] = '..';
        }
        $current_dir[] = self::COMPOSER_FILENAME;

        $composer_uri = join(DIRECTORY_SEPARATOR, $current_dir);
        $content = file_get_contents($composer_uri);
        $content = json_decode($content, true);

        return $content;
    }

    public static function getSdkName()
    {
        $info = self::getComposerInfo();
        $name = explode('/', $info['name']);

        return $name[1];
    }

    public static function getSdkVersion()
    {
        $info = self::getComposerInfo();

        return $info['version'];
    }
}