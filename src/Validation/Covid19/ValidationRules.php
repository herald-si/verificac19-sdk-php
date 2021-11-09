<?php
namespace Herald\GreenPass\Validation\Covid19;

use GuzzleHttp\Client;

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

    private static function getValidationFromUri($locale)
    {
        $client = new \GuzzleHttp\Client();
        $uri = "";
        switch ($locale) {
            case "it_IT":
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
        $locale = 'it_IT';
        $today = new \DateTime();
        $current_dir = dirname(__FILE__);
        $uri = "$current_dir/../../../assets/greenpass_{$locale}_rules_{$today->format('Ymd')}.json";
        if (! file_exists($uri)) {
            $rules = self::getValidationFromUri($locale);
            $fhandle = fopen($uri, 'w');
            fwrite($fhandle, $rules);
            fclose($fhandle);
        } else {
            $fhandle = fopen($uri, 'r');
            $rules = fread($fhandle, filesize($uri));
            fclose($fhandle);
        }
        return json_decode($rules);
    }
}