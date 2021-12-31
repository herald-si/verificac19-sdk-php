<?php
namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\Utils\FileUtils;
use Herald\GreenPass\Utils\VerificaC19DB;
use Herald\GreenPass\Utils\EndpointService;
use Herald\GreenPass\Exceptions\DownloadFailedException;

// https://github.com/ministero-salute/it-dgc-documentation/blob/master/DRL.md
class CertificateRevocationList
{

    const DRL_SYNC_ACTIVE = TRUE;

    const MAX_RETRY = 3;

    private const MAX_WAIT_SECONDS = 60;

    private const DRL_STATUS_FILE = FileUtils::COUNTRY . "-gov-dgc-drl-status.json";

    private const DRL_STATUS_VALID = 'VALID';

    private const DRL_STATUS_NEED_VALIDATION = 'NEED_VALIDATION';

    private const DRL_STATUS_UPDATING = 'UPDATE_IN_PROGRESS';

    private const DRL_STATUS_PENDING = 'PENDING_DOWNLOAD';

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

    private function saveCurrentStatus($chunk, $version, $validity, $additional_info = null)
    {
        $config = array(
            "chunk" => $chunk,
            "version" => $version,
            "validity" => $validity,
            "info" => $additional_info
        );
        $data = json_encode($config, JSON_FORCE_OBJECT);

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

    private function updateRevokedList($chunk, $version)
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
    }

    public function getRevokeList()
    {
        // error counter >= MAX_ALLOWED_RETRY
        if ($this->error_counter >= self::MAX_RETRY) {
            $this->saveCurrentStatus(1, 0, self::DRL_STATUS_NEED_VALIDATION);
            throw new DownloadFailedException();
        }

        // CRL Status
        $status = $this->getCurrentCRLStatus();
        $check = $this->getCRLStatus($status->version);

        // outdated version
        if ($status->version < $check->version) {

            $download_pending = ($status->validity == self::DRL_STATUS_PENDING);
            // if no pending download OR pending download with same version requested / same chunk size
            if (! $download_pending || ($download_pending && $status->info->version == $check->version && $status->info->totalChunk == $check->totalChunk)) {
                // get first chunk from status
                $initChunk = $status->chunk;
                $endChunk = $check->totalChunk;
                for ($chunk = $initChunk; $chunk <= $endChunk; $chunk ++) {
                    try {
                        $this->updateRevokedList($chunk, $check->fromVersion);

                        if ($endChunk > $chunk) {
                            // not last chunk -> set next chunk to download
                            $this->saveCurrentStatus($chunk + 1, $check->fromVersion, self::DRL_STATUS_PENDING, $check);
                        } else {
                            // is last chunk -> update currentVersion
                            $this->saveCurrentStatus(1, $check->version, self::DRL_STATUS_NEED_VALIDATION);
                            // restart validation for this version
                            return $this->getRevokeList();
                        }
                    } catch (\Exception $e) {
                        // inconsistent download, reset drl
                        break;
                    }
                }
            }
        } else {

            $list = $this->db->getRevokedUcviList();
            $totalNumberUCVI = $check->totalNumberUCVI;

            // same remote-local db size
            if (count($list) == $totalNumberUCVI) {
                // set drl valid
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
        $this->saveCurrentStatus(1, 0, self::DRL_STATUS_NEED_VALIDATION);
    }

    private function resetCRLWithError()
    {
        $this->cleanCRL();
        $this->error_counter ++;
    }

    public function isUVCIRevoked($kid)
    {
        $hashedKid = $this->kidHash($kid);
        $revoked = $this->getUpdatedRevokeList();
        foreach ($revoked as $bl_item) {
            if ($hashedKid == $bl_item['revokedUcvi']) {
                return true;
            }
        }
        return false;
    }

    public function getUpdatedRevokeList()
    {
        // DRL validation flow: https://github.com/ministero-salute/it-dgc-documentation/blob/master/DRL.md#flusso-applicativo
        // Timer 24h or VALIDATION/RESUME DOWNLOAD NEEDED
        $revoked = "";
        if (FileUtils::checkFileNotExistOrExpired(FileUtils::getCacheFilePath(self::DRL_STATUS_FILE), FileUtils::HOUR_BEFORE_DOWNLOAD_LIST * 3600) || $this->getCurrentCRLStatus()->validity == self::DRL_STATUS_NEED_VALIDATION || $this->getCurrentCRLStatus()->validity == self::DRL_STATUS_PENDING) {
            $revoked = $this->getRevokeList();
        } else {
            $revoked = $this->db->getRevokedUcviList();
        }
        return $revoked;
    }

    private function kidHash($kid)
    {
        // Hash docs: https://github.com/ministero-salute/it-dgc-documentation/blob/master/DRL.md#panoramica
        $hash = hash('sha256', $kid, true);
        return base64_encode($hash);
    }
}