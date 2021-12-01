<?php
namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\Exceptions\NoCertificateListException;
use Herald\GreenPass\Utils\FileUtils;
use Herald\GreenPass\Utils\VerificaC19DB;

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
                $uri = "https://raw.githubusercontent.com/italia/verificac19-sdk/feature/crl/test/data/responses/CRL-check-v3.json";
                break;
            /**
             * TODO
             * cambiare con endpoint ufficiale, ora inaccessibile:
             * https://get.dgc.gov.it/v1/dgc/drl
             */
            case "drl-revokes":
                $uri = "https://raw.githubusercontent.com/italia/verificac19-sdk/feature/crl/test/data/responses/CRL-v3-c1.json";
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

    private static function updateRevokedList(VerificaC19DB $db)
    {  
        $country = FileUtils::COUNTRY;
        
        $uri = FileUtils::getCacheFilePath("{$country}-gov-dgc-drl-revokes.json");
        $chunk = self::getJsonFromFile($uri, "drl-revokes");

        foreach($chunk->revokedUcvi as $revokedUcvi){
            $db->addRevokedUcviToUcviList($revokedUcvi);
        }
    }

    public static function getRevokeList()
    {
        try {
            $db = new VerificaC19DB();
            $db->initUCVI();
        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Cant connect to DB" . $e);
        }
        
        self::updateRevokedList($db);
        return $db->getRevokedUcviList();
        
    }
}