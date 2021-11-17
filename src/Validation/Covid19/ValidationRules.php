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

    private static function getValidationFromUri($country)
    {
        $client = new \GuzzleHttp\Client();
        $uri = "";
        switch ($country) {
            case "it":
                $uri = "https://get.dgc.gov.it/v1/dgc/settings";
                break;
            case "other_country":
                $uri = "set_country_uri_there";
                break;
            default:
                throw new \InvalidArgumentException("No country selected");
        }
        $res = $client->request('GET', $uri);

        return $res->getBody();
    }
    
    public static function getValidationRules()
    {
        $country = FileUtils::COUNTRY;
        $current_dir = dirname(__FILE__);
        
        $uri = join(DIRECTORY_SEPARATOR, array(
            $current_dir,
            '..',
            '..',
            '..',
            'assets',
            "{$country}-gov-dgc-settings.json"
        ));
        $rules = "";
        
        if (FileUtils::checkFileNotExistOrExpired($uri, FileUtils::HOUR_BEFORE_DOWNLOAD_LIST * 3600)) {
            $rules = self::getValidationFromUri($country);
            if (! empty($rules)) {
                $fhandle = fopen($uri, 'w');
                fwrite($fhandle, $rules);
                fclose($fhandle);
            } else {
                throw new NoCertificateListException("rules");
            }
        } else {
            $fhandle = fopen($uri, 'r');
            $rules = fread($fhandle, filesize($uri));
            fclose($fhandle);
        }
        
        return json_decode($rules);
    }
    
    
}