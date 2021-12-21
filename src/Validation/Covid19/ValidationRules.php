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

    private const DRL_CHECK_FILE = FileUtils::COUNTRY . "-gov-dgc-drl-check.json";

    private const DRL_STATUS_FILE = FileUtils::COUNTRY . "-gov-dgc-drl-status.json";

    private const SETTINGS_FILE = FileUtils::COUNTRY . "-gov-dgc-settings.json";

    private static function getValidationFromUri(string $type, array $params = null)
    {
        $client = new \GuzzleHttp\Client();
        $uri = "";
        $querystring = "";
        if (! empty($params)) {
            $querystring = "?" . http_build_query($params);
        }
        switch ($type) {
            case "settings":
                $uri = "https://get.dgc.gov.it/v1/dgc/settings" . $querystring;
                break;
            case "drl-check":
                $uri = "https://get.dgc.gov.it/v1/dgc/drl/check" . $querystring;
                break;
            case "drl-revokes":
                $uri = "https://get.dgc.gov.it/v1/dgc/drl" . $querystring;
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

    private static function getJsonFromFile(string $filename, string $type, $params = null, $force_update = false)
    {
        if (FileUtils::checkFileNotExistOrExpired($filename, FileUtils::HOUR_BEFORE_DOWNLOAD_LIST * 3600) || $force_update) {
            $json = self::getValidationFromUri($type, $params);
            FileUtils::saveDataToFile($filename, $json);
        } else {
            $json = FileUtils::readDataFromFile($filename);
        }
        return json_decode($json);
    }

    private static function getJsonFromUrl(string $type, $params = null)
    {
        $json = self::getValidationFromUri($type, $params);
        return json_decode($json);
    }

    private static function getUvciStatus()
    {
        $uri = FileUtils::getCacheFilePath(self::DRL_STATUS_FILE);
        if (! file_exists($uri)) {
            $json = static::saveUvciStatus(1, 0);
        } else {
            $json = FileUtils::readDataFromFile($uri);
        }
        return json_decode($json);
    }

    private static function saveUvciStatus($chunk, $version)
    {
        $data = <<<JSON
        {"chunk":"$chunk","version":"$version"}
        JSON;

        $uri = FileUtils::getCacheFilePath(self::DRL_STATUS_FILE);
        FileUtils::saveDataToFile($uri, $data);
        return $data;
    }

    public static function getValidationRules()
    {
        $uri = FileUtils::getCacheFilePath(SELF::SETTINGS_FILE);
        return self::getJsonFromFile($uri, "settings");
    }

    private static function getCRLStatus()
    {
        $status = static::getUvciStatus();
        $params = array(
            'version' => $status->version
        );
        $uri = FileUtils::getCacheFilePath(self::DRL_CHECK_FILE);
        return self::getJsonFromFile($uri, "drl-check", $params);
    }

    private static function updateRevokedList(VerificaC19DB $db)
    {
        $status = static::getUvciStatus();
        $params = array(
            'version' => $status->version
        );

        $drl = self::getJsonFromUrl("drl-revokes", $params);

        if (isset($drl->revokedUcvi)) {
            foreach ($drl->revokedUcvi as $revokedUcvi) {
                $db->addRevokedUcviToUcviList($revokedUcvi);
            }
        }
        if (isset($drl->delta->insertions)) {
            foreach ($drl->delta->insertions as $revokedUcvi) {
                $db->addRevokedUcviToUcviList($revokedUcvi);
            }
        }
        if (isset($drl->delta->deletions)) {
            foreach ($drl->delta->deletions as $revokedUcvi) {
                $db->removeRevokedUcviFromUcviList($revokedUcvi);
            }
        }

        static::saveUvciStatus($drl->chunk, $drl->version);

        $uri = FileUtils::getCacheFilePath(self::DRL_CHECK_FILE);

        $params = array(
            'version' => $drl->version
        );
        self::getJsonFromFile($uri, "drl-check", $params, true);
    }

    public static function getRevokeList()
    {
        $check = self::getCRLStatus();

        try {
            $db = new VerificaC19DB();
            $db->initUCVI();
        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Cant connect to DB" . $e);
        }

        if ($check->fromVersion < $check->version) {
            self::updateRevokedList($db);
        }

        return $db->getRevokedUcviList();
    }
}