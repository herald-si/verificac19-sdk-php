<?php
namespace Herald\GreenPass\Utils;

use Herald\GreenPass\Exceptions\NoCertificateListException;

class EndpointService
{

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

}