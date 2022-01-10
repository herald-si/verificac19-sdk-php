<?php
namespace Herald\GreenPass\Utils;

class EnvConfig
{

    const PRODUCTION = "PRODUCTION";

    const DEBUG = "DEBUG";

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
        return (self::$envMode == self::DEBUG);
    }
}