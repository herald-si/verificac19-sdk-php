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

    private $db = null;

    public function __construct()
    {
        try {
            $this->db = new VerificaC19DB();
            $this->db->initUCVI();
        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Cant connect to DB" . $e);
        }
    }

    private function getUvciStatus()
    {
        $uri = FileUtils::getCacheFilePath(self::DRL_STATUS_FILE);
        if (! file_exists($uri)) {
            $json = $this->saveUvciStatus(1, 0);
        } else {
            $json = FileUtils::readDataFromFile($uri);
        }
        return json_decode($json);
    }

    private function saveUvciStatus($chunk, $version)
    {
        $data = <<<JSON
        {"chunk":"$chunk","version":"$version"}
        JSON;

        $uri = FileUtils::getCacheFilePath(self::DRL_STATUS_FILE);
        FileUtils::saveDataToFile($uri, $data);
        return $data;
    }

    private function getCRLStatus()
    {
        $status = static::getUvciStatus();
        $params = array(
            'version' => $status->version
        );
        $uri = FileUtils::getCacheFilePath(self::DRL_CHECK_FILE);
        return EndpointService::getJsonFromFile($uri, "drl-check", $params);
    }

    private function updateRevokedList()
    {
        $status = $this->getUvciStatus();
        $params = array(
            'version' => $status->version
        );

        $drl = EndpointService::getJsonFromUrl("drl-revokes", $params);

        if (isset($drl->revokedUcvi)) {
            foreach ($drl->revokedUcvi as $revokedUcvi) {
                $this->db->addRevokedUcviToUcviList($revokedUcvi);
            }
        }
        if (isset($drl->delta->insertions)) {
            foreach ($drl->delta->insertions as $revokedUcvi) {
                $this->db->addRevokedUcviToUcviList($revokedUcvi);
            }
        }
        if (isset($drl->delta->deletions)) {
            foreach ($drl->delta->deletions as $revokedUcvi) {
                $this->db->removeRevokedUcviFromUcviList($revokedUcvi);
            }
        }

        $this->saveUvciStatus($drl->chunk, $drl->version);

        $uri = FileUtils::getCacheFilePath(self::DRL_CHECK_FILE);

        $params = array(
            'version' => $drl->version
        );
        EndpointService::getJsonFromFile($uri, "drl-check", $params, true);
    }

    public function getRevokeList()
    {
        $check = $this->getCRLStatus();

        if ($check->fromVersion < $check->version) {
            $this->updateRevokedList($this->db);
        }

        return $this->db->getRevokedUcviList();
    }

    public function cleanCRL()
    {
        $this->db->emptyList();
        $this->saveUvciStatus(1, 0);
    }

    public function isUVCIRevoked($kid)
    {
        
        $revoked = $this->getRevokeList();
        /*
         * TODO
         * Implementare le logiche di check
         */
        if(in_array($kid, $revoked)){
            return true;
        }
        return false;
        
    }
}