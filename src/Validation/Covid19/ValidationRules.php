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

    const DRL_SYNC_ACTIVE = "DRL_SYNC_ACTIVE";

    const MAX_RETRY = "MAX_RETRY";

    private static function getValidationFromUri(string $type)
    {
        $client = new \GuzzleHttp\Client();
        $uri = "";
        switch ($type) {
            case "settings":
                $uri = "https://get.dgc.gov.it/v1/dgc/settings";
                break;
            /**
             * TODO
             * cambiare con endpoint ufficiale, ora inaccessibile: 
             * https://get.dgc.gov.it/v1/dgc/drl/check
             */
            case "drl-check":
                $uri = "https://gist.githubusercontent.com/rawmain/85ea0786ded9e4634ae13f467ef343ac/raw/42ad731d33d12442a98c0af1721673ae4df1f6e1/rvktest02.json";
                break;
            /**
             * TODO
             * cambiare con endpoint ufficiale, ora inaccessibile:
             * https://get.dgc.gov.it/v1/dgc/drl
             */
            case "drl-revokes":
                $uri = "https://gist.githubusercontent.com/rawmain/85ea0786ded9e4634ae13f467ef343ac/raw/42ad731d33d12442a98c0af1721673ae4df1f6e1/rvktest02.json";
                break;
            default:
                throw new NoCertificateListException($type);
        }
        $res = $client->request('GET', $uri);

        if (empty($res) || empty($res->getBody())) {
            throw new NoCertificateListException($type);
        }

        return $res->getBody();
    }

    private static function getJsonFromFile(string $filename, string $type)
    {
        if (FileUtils::checkFileNotExistOrExpired($filename, FileUtils::HOUR_BEFORE_DOWNLOAD_LIST * 3600)) {
            $json = self::getValidationFromUri($type);
            FileUtils::saveDataToFile($filename, $json);
        } else {
            $json = FileUtils::readDataFromFile($filename);
        }
        return json_decode($json);
    }

    public static function getValidationRules()
    {
        $country = FileUtils::COUNTRY;

        $uri = FileUtils::getCacheFilePath("{$country}-gov-dgc-settings.json");
        return self::getJsonFromFile($uri, "settings");
    }

    public static function getCRLStatus()
    {
        $country = FileUtils::COUNTRY;

        $uri = FileUtils::getCacheFilePath("{$country}-gov-dgc-drl-check.json");
        return self::getJsonFromFile($uri, "drl-check");
    }

    public static function getRevokeList()
    {
        $country = FileUtils::COUNTRY;

        $uri = FileUtils::getCacheFilePath("{$country}-gov-dgc-drl-revokes.json");
        return self::getJsonFromFile($uri, "drl-revokes");
    }
}