<?php
namespace Herald\GreenPass\Utils;

use Herald\GreenPass\Exceptions\NoCertificateListException;
use Herald\GreenPass\Decoder\Decoder;

class EndpointService
{
    private const STATUS_FILE = FileUtils::COUNTRY . "-gov-dgc-status.json";
    
    private const CERTS_FILE = FileUtils::COUNTRY . "-gov-dgc-certs.json";
    
    private const SETTINGS_FILE = FileUtils::COUNTRY . "-gov-dgc-settings.json";
    
    private static function getValidationFromUri(string $type, array $params = null)
    {
        $uri = "";
        $querystring = "";
        if (! empty($params)) {
            $querystring = "?" . http_build_query($params);
        }
        $return = "";
        switch ($type) {
            case "settings":
                $uri = "https://get.dgc.gov.it/v1/dgc/settings" . $querystring;
                $return = self::enpointRequest($uri, $type);
                break;
            case "drl-check":
                $uri = "https://get.dgc.gov.it/v1/dgc/drl/check" . $querystring;
                $return = self::enpointRequest($uri, $type);
                break;
            case "drl-revokes":
                $uri = "https://get.dgc.gov.it/v1/dgc/drl" . $querystring;
                $return = self::enpointRequest($uri, $type);
                break;
            case "certificate-status":
                $uri = "https://get.dgc.gov.it/v1/dgc/signercertificate/status" . $querystring;
                $return = self::enpointRequest($uri, $type);
                break;
            case "certificate-list":
                $uri = 'https://get.dgc.gov.it/v1/dgc/signercertificate/update' . $querystring;
                $certificates = array();
                $list = static::retrieveCertificateFromList($uri, $certificates);
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

        $res = $client->request('GET', $uri);

        if (empty($res) || empty($res->getBody())) {
            throw new NoCertificateListException($type);
        }

        return $res->getBody();
    }

    // Retrieve from RESUME-TOKEN KID
    private static function retrieveCertificateFromList(string $url, array $list, string $resume_token = "")
    {
        // We retrieve the public keys
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (! empty($resume_token)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "X-RESUME-TOKEN: $resume_token"
            ));
        }

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        // Then, after your curl_exec call:
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        // Convert the $headers string to an indexed array
        $headers_indexed_arr = explode("\r\n", $headers);

        // Define as array before using in loop
        $headers_arr = array();

        // Create an associative array containing the response headers
        foreach ($headers_indexed_arr as $value) {
            if (false !== ($matches = array_pad(explode(':', $value), 2, null))) {
                $headers_arr["{$matches[0]}"] = trim($matches[1]);
            }
        }

        if (empty($info['http_code'])) {
            throw new \InvalidArgumentException("No HTTP code was returned");
        }

        if ($info['http_code'] == 200) {
            $list[$headers_arr['X-KID']] = $body;
            return static::retrieveCertificateFromList($url, $list, $headers_arr['X-RESUME-TOKEN']);
        } else {
            return $list;
        }
    }

    public static function getJsonFromFile(string $filename, string $type, $params = null, $force_update = false)
    {
        if (FileUtils::checkFileNotExistOrExpired($filename, FileUtils::HOUR_BEFORE_DOWNLOAD_LIST * 3600) || $force_update) {
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
    
    public static function getCertificatesStatus()
    {
        $uri = FileUtils::getCacheFilePath(self::STATUS_FILE);
        return EndpointService::getJsonFromFile($uri, "certificate-status");
    }
    
    public static function getCertificates()
    {
        $uri = FileUtils::getCacheFilePath(self::CERTS_FILE);
        return EndpointService::getJsonFromFile($uri, "certificate-list");
    }
        
    public static function getValidationRules()
    {
        $uri = FileUtils::getCacheFilePath(static::SETTINGS_FILE);
        return EndpointService::getJsonFromFile($uri, "settings");
    }
        
}