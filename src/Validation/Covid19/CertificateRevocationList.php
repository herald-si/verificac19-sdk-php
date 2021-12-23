<?php
namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\Utils\FileUtils;
use Herald\GreenPass\Utils\VerificaC19DB;
use Herald\GreenPass\Utils\EndpointService;
use Herald\GreenPass\Exceptions\DownloadFailedException;
use Herald\GreenPass\Exceptions\NoCertificateListException;

class CertificateRevocationList
{

    const DRL_SYNC_ACTIVE = TRUE;

    const MAX_RETRY = 3;

    private const MAX_WAIT_SECONDS = 60;

    private const DRL_STATUS_FILE = FileUtils::COUNTRY . "-gov-dgc-drl-status.json";

    private const DRL_STATUS_VALID = 'valid';

    private const DRL_STATUS_INCONSISTENT = 'inconsistent';

    private const DRL_STATUS_UPDATING = 'download_in_progress';

    private $db = null;

    private $error_counter = 0;

    public function __construct()
    {
        try {
            $this->db = new VerificaC19DB();
            $this->db->initUCVI();
        } catch (\PDOException $e) {
            throw new \InvalidArgumentException("Cant connect to DB" . $e);
        }
    }

    private function getCurrentCRLStatus()
    {
        $uri = FileUtils::getCacheFilePath(self::DRL_STATUS_FILE);
        if (! file_exists($uri)) {
            $json = $this->saveCurrentStatus(1, 0, self::DRL_STATUS_VALID);
        } else {
            $json = FileUtils::readDataFromFile($uri);
        }
        return json_decode($json);
    }

    private function saveCurrentStatus($chunk, $version, $validity)
    {
        $data = <<<JSON
        {"chunk":$chunk,"version":$version,"validity":"$validity"}
        JSON;

        $uri = FileUtils::getCacheFilePath(self::DRL_STATUS_FILE);
        FileUtils::saveDataToFile($uri, $data);
        return $data;
    }

    private function getCRLStatus($version)
    {
        $params = array(
            'version' => $version
        );
        return EndpointService::getJsonFromUrl("drl-check", $params);
    }

    private function updateRevokedList($version, $chunk)
    {
        $params = array(
            'version' => $version,
            'chunk' => $chunk
        );

        $drl = EndpointService::getJsonFromUrl("drl-revokes", $params);
        if (isset($drl->revokedUcvi)) {
            $this->db->addAllRevokedUcviToUcviList($drl->revokedUcvi);
        }
        if (isset($drl->delta->deletions)) {
            foreach ($drl->delta->deletions as $revokedUcvi) {
                $this->db->removeRevokedUcviFromUcviList($revokedUcvi);
            }
        }
        if (isset($drl->delta->insertions)) {
            $this->db->addAllRevokedUcviToUcviList($drl->delta->insertions);
        }

        $this->saveCurrentStatus($chunk, $version, self::DRL_STATUS_UPDATING);
    }

    public function getRevokeList()
    {
        // error counter >= MAX_ALLOWED_RETRY
        if ($this->error_counter >= self::MAX_RETRY) {
            $this->saveCurrentStatus(1, 0, self::DRL_STATUS_INCONSISTENT);
            throw new DownloadFailedException();
        }

        // CRL Status
        $status = $this->getCurrentCRLStatus();
        $check = $this->getCRLStatus($status->version);

        $incosistent_download = false;

        // outdated version
        if ($status->version < $check->version) {

            $initChunk = $check->chunk;
            $endChunk = $check->totalChunk;
            for ($chunk = $initChunk; $chunk <= $endChunk; $chunk ++) {
                try {
                    $this->saveCurrentStatus($status->chunk, $status->version, self::DRL_STATUS_UPDATING);
                    $this->updateRevokedList($check->fromVersion, $chunk);
                } catch (\Exception $e) {
                    // inconsistent download
                    $incosistent_download = true;
                    break;
                }
            }
            // all chunk downloaded
            if (! $incosistent_download) {
                // update currentVersion
                $this->saveCurrentStatus(1, $check->version, self::DRL_STATUS_UPDATING);

                // Restart CRL status check
                return $this->getRevokeList();
            }
        } else {

            // same remote-local db size
            $list = $this->db->getRevokedUcviList();
            $totalNumberUCVI = $check->totalNumberUCVI;

            if (count($list) == $totalNumberUCVI) {
                // update latest check date
                $this->saveCurrentStatus(1, $check->version, self::DRL_STATUS_VALID);
                $this->error_counter = 0;
                // return revokedUcvi list
                return $list;
            }
        }

        // Clean DB + reset progress + error counter++
        $this->resetCRLWithError();

        // Restart CRL status check from scratch
        return $this->getRevokeList();
    }

    private function cleanCRL()
    {
        $this->db->emptyList();
        $this->saveCurrentStatus(1, 0, self::DRL_STATUS_INCONSISTENT);
    }

    private function resetCRLWithError()
    {
        $this->cleanCRL();
        $this->error_counter ++;
    }

    public function isUVCIRevoked($kid)
    {
        // Timer 24h
        if (FileUtils::checkFileNotExistOrExpired(FileUtils::getCacheFilePath(self::DRL_STATUS_FILE), FileUtils::HOUR_BEFORE_DOWNLOAD_LIST * 3600) || $this->getCurrentCRLStatus()->validity == self::DRL_STATUS_INCONSISTENT) {
            $revoked = $this->getRevokeList();
        } else {
            $retry = 0;
            while ($this->getCurrentCRLStatus()->validity == self::DRL_STATUS_UPDATING && $retry < self::MAX_WAIT_SECONDS) {
                $retry ++;
                sleep(1);
            }
            if ($retry >= self::MAX_WAIT_SECONDS) {
                throw new DownloadFailedException("Server busy, give up");
            }
            $revoked = $this->db->getRevokedUcviList();
        }

        $hashedKid = $this->kidHash($kid);

        foreach ($revoked as $bl_item) {
            if ($hashedKid == $bl_item['revokedUcvi']) {
                return true;
            }
        }
        return false;
    }

    private function kidHash($kid)
    {
        $hash = hash('sha256', $kid, true);
        return base64_encode($hash);
    }
}