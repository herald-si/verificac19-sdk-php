<?php

namespace Herald\GreenPass\Utils;

use Herald\GreenPass\Exceptions\DownloadFailedException;
use Herald\GreenPass\Exceptions\NoCertificateListException;

class EndpointService
{
    private const STATUS_FILE = FileUtils::COUNTRY.'-gov-dgc-status.json';

    private const CERTS_FILE = FileUtils::COUNTRY.'-gov-dgc-certs.json';

    private const SETTINGS_FILE = FileUtils::COUNTRY.'-gov-dgc-settings.json';

    private static $proxy;

    public static function setProxy($proxy)
    {
        self::$proxy = $proxy;
    }

    private static function getValidationFromUri(string $type, array $params = null)
    {
        $uri = '';
        $querystring = '';
        if (!empty($params)) {
            $querystring = '?'.http_build_query($params);
        }
        $return = '';
        switch ($type) {
            case 'settings':
                $uri = 'https://get.dgc.gov.it/v1/dgc/settings'.$querystring;
                $return = self::enpointRequest($uri, $type);
                break;
            case 'drl-check':
                $uri = 'https://get.dgc.gov.it/v1/dgc/drl/check'.$querystring;
                $return = self::enpointRequest($uri, $type);
                break;
            case 'drl-revokes':
                $uri = 'https://get.dgc.gov.it/v1/dgc/drl'.$querystring;
                $return = self::enpointRequest($uri, $type);
                break;
            case 'certificate-status':
                $uri = 'https://get.dgc.gov.it/v1/dgc/signercertificate/status'.$querystring;
                $return = self::enpointRequest($uri, $type);
                break;
            case 'certificate-list':
                $uri = 'https://get.dgc.gov.it/v1/dgc/signercertificate/update'.$querystring;
                $list = static::retrieveCertificateFromList($uri);
                // the list signer certificate can't is empty
                if (empty($list)) {
                    throw new DownloadFailedException(DownloadFailedException::NO_DATA_RESPONSE.' '.$uri);
                }
                $return = json_encode($list);
                break;
            default:
                throw new NoCertificateListException($type);
        }

        return $return;
    }

    private static function enpointRequest($uri, $type)
    {
        $client = new \GuzzleHttp\Client();

        try {
            if (empty(self::$proxy)) {
                $res = $client->request('GET', $uri);
            } else {
                $res = $client->request('GET', $uri, [
                    'proxy' => self::$proxy,
                ]);
            }
        } catch (\Exception $e) {
            throw new DownloadFailedException(DownloadFailedException::NO_WEBSITE_RESPONSE.' '.$uri);
        }

        if (empty($res) || empty($res->getBody())) {
            throw new NoCertificateListException($type);
        }

        return $res->getBody();
    }

    // Retrieve from RESUME-TOKEN KID
    private static function retrieveCertificateFromList(string $uri)
    {
        $client = new \GuzzleHttp\Client();
        $resume_token = '';
        $list = [];
        try {
            do {
                $params = [];
                $params['headers'] = ['X-RESUME-TOKEN' => $resume_token];

                if (!empty(self::$proxy)) {
                    $params['proxy'] = self::$proxy;
                }

                $res = $client->request('GET', $uri, $params);

                if ($res->getStatusCode() == 200) {
                    if (empty($res->getBody())) {
                        throw new DownloadFailedException(DownloadFailedException::NO_WEBSITE_RESPONSE);
                    }
                    $response = $res->getBody()->getContents();
                    $headers = self::getHeadersArray($res->getHeaders());
                    $list[$headers['X-KID']] = $response;
                    $resume_token = $headers['X-RESUME-TOKEN'];
                    // Create an associative array containing the response headers
                }
            } while ($res->getStatusCode() == 200);
        } catch (\Exception $e) {
            throw new DownloadFailedException(DownloadFailedException::NO_WEBSITE_RESPONSE.' '.$uri);
        }

        return $list;
    }

    private static function getHeadersArray($guzzleHeaders)
    {
        $list = [];
        foreach ($guzzleHeaders as $header => $value) {
            $list["$header"] = $value[0];
        }

        return $list;
    }

    public static function getJsonFromFile(string $filename, string $type, $params = null, $force_update = false)
    {
        if (FileUtils::checkFileNotExistOrExpired($filename, FileUtils::HOUR_BEFORE_DOWNLOAD_LIST * 3600) || ($force_update && EnvConfig::isDebugEnabled())) {
            $json = self::getValidationFromUri($type, $params);
            FileUtils::saveDataToFile($filename, $json);
        } else {
            $json = FileUtils::readDataFromFile($filename);
        }

        return json_decode($json);
    }

    public static function getJsonFromUrl(string $type, $params = null)
    {
        $json = self::getValidationFromUri($type, $params);

        return json_decode($json);
    }

    public static function getCertificatesStatus($force_update = false)
    {
        $uri = FileUtils::getCacheFilePath(self::STATUS_FILE);

        return EndpointService::getJsonFromFile($uri, 'certificate-status', null, $force_update);
    }

    public static function getCertificates($force_update = false)
    {
        $uri = FileUtils::getCacheFilePath(self::CERTS_FILE);

        return EndpointService::getJsonFromFile($uri, 'certificate-list', null, $force_update);
    }

    public static function getValidationRules($force_update = false)
    {
        $uri = FileUtils::getCacheFilePath(self::SETTINGS_FILE);

        return EndpointService::getJsonFromFile($uri, 'settings', null, $force_update);
    }
}
