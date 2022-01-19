<?php
namespace Herald\GreenPass\Utils;

use Herald\GreenPass\Validation\Covid19\CertificateRevocationList;

class UpdateService
{

    public static function setProxy($proxy) 
    { 
        EndpointService::setProxy($proxy);
    }

    public static function updateCertificatesStatus()
    {
        EndpointService::getCertificatesStatus();
    }

    public static function updateCertificateList()
    {
        EndpointService::getCertificates();
    }

    public static function updateValidationRules()
    {
        EndpointService::getValidationRules();
    }

    public static function updateRevokeList()
    {
        $crl = new CertificateRevocationList();
        $crl->getUpdatedRevokeList();
    }

    public static function updateAll()
    {
        self::updateCertificatesStatus();
        self::updateCertificateList();
        self::updateValidationRules();
        self::updateRevokeList();
    }
}