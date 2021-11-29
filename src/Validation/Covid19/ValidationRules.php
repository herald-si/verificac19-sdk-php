<?php
namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\Exceptions\NoCertificateListException;
use Herald\GreenPass\Utils\FileUtils;

class ValidationRules
{

    const RECOVERY_CERT_START_DAY = "recovery_cert_start_day";

    const RECOVERY_CERT_END_DAY = "recovery_cert_end_day";

    const MOLECULAR_TEST_START_HOUR = "molecular_test_start_hours";

    const MOLECULAR_TEST_END_HOUR = "molecular_test_end_hours";

    const RAPID_TEST_START_HOUR = "rapid_test_start_hours";

    const RAPID_TEST_END_HOUR = "rapid_test_end_hours";

    const VACCINE_START_DAY_NOT_COMPLETE = "vaccine_start_day_not_complete";

    const VACCINE_END_DAY_NOT_COMPLETE = "vaccine_end_day_not_complete";

    const VACCINE_START_DAY_COMPLETE = "vaccine_start_day_complete";

    const VACCINE_END_DAY_COMPLETE = "vaccine_end_day_complete";

    const BLACK_LIST_UVCI = "black_list_uvci";

    private $it_uri = "https://get.dgc.gov.it/v1/dgc/settings";
    private $cache_file_path_override = null;

    public static function overrideItDgcSettingsUri($newUri)
    {
        self::$it_uri = $newUri;
    }

    public static function overrideCacheFilePath($newPath)
    {
        self::$cache_file_path_override = $newPath;
    }

    private static function getValidationFromUri($country)
    {
        $client = new \GuzzleHttp\Client();
        $uri = "";
        switch ($country) {
            case "it":
                $uri = self::$it_uri;
                break;
            case "other_country":
                $uri = "set_country_uri_there";
                break;
            default:
                throw new \InvalidArgumentException("No country selected");
        }
        $res = $client->request('GET', $uri);

        if(empty($res) || empty ($res->getBody()) ){
            throw new NoCertificateListException("rules");
        }
        
        return $res->getBody();
    }

    private static function _getRulesCacheFilePath()
    {
        $uri = self::$cache_file_path_override;

        if(!$uri)
        {
            $current_dir = dirname(__FILE__);

            $uri = join(DIRECTORY_SEPARATOR, array(
                $current_dir,
                '..',
                '..',
                '..',
                'assets',
                "{$country}-gov-dgc-settings.json"
            ));
        }

        return $uri;
    }

    public static function getValidationRules()
    {
        $country = FileUtils::COUNTRY;

        $uri = self::_getRulesCacheFilePath();

        $rules = "";

        if (FileUtils::checkFileNotExistOrExpired($uri, FileUtils::HOUR_BEFORE_DOWNLOAD_LIST * 3600)) {
            $rules = self::getValidationFromUri($country);
            FileUtils::saveDataToFile($uri, $rules);
        } else {
            $rules = FileUtils::readDataFromFile($uri);
        }

        return json_decode($rules);
    }


}