<?php
namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\Utils\FileUtils;
use Herald\GreenPass\Utils\VerificaC19DB;
use Herald\GreenPass\Utils\EndpointService;

class CertificateRevocationList
{

    const DRL_SYNC_ACTIVE = "DRL_SYNC_ACTIVE";

    const MAX_RETRY = "MAX_RETRY";

    private const DRL_CHECK_FILE = FileUtils::COUNTRY . "-gov-dgc-drl-check.json";

    private const DRL_STATUS_FILE = FileUtils::COUNTRY . "-gov-dgc-drl-status.json";

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

    private static function getCRLStatus()
    {
        $status = static::getUvciStatus();
        $params = array(
            'version' => $status->version
        );
        $uri = FileUtils::getCacheFilePath(self::DRL_CHECK_FILE);
        return EndpointService::getJsonFromFile($uri, "drl-check", $params);
    }

    private static function updateRevokedList(VerificaC19DB $db)
    {
        $status = static::getUvciStatus();
        $params = array(
            'version' => $status->version
        );

        $drl = EndpointService::getJsonFromUrl("drl-revokes", $params);

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
        EndpointService::getJsonFromFile($uri, "drl-check", $params, true);
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

    public static function cleanCRL()
    {
        try {
            $db = new VerificaC19DB();
            $db->initUCVI();
        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Cant connect to DB" . $e);
        }
        $db->emptyList();
        static::saveUvciStatus(1, 0);
    }
}