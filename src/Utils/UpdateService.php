<?php

namespace Herald\GreenPass\Utils;

use Herald\GreenPass\Validation\Covid19\CertificateRevocationList;

class UpdateService
{
    public static function setProxy($proxy)
    {
        EndpointService::setProxy($proxy);
    }

    public static function updateCertificatesStatus($force_update = false)
    {
        EndpointService::getCertificatesStatus($force_update);
    }

    public static function updateCertificateList($force_update = false)
    {
        EndpointService::getCertificates($force_update);
    }

    public static function updateValidationRules($force_update = false)
    {
        EndpointService::getValidationRules($force_update);
    }

    public static function updateRevokeList($force_update = false)
    {
        $crl = new CertificateRevocationList();
        $crl->getUpdatedRevokeList($force_update);
    }

    public static function updateAll($force_update = false)
    {
        self::updateCertificatesStatus($force_update);
        self::updateCertificateList($force_update);
        self::updateValidationRules($force_update);
        self::updateRevokeList($force_update);
    }
}
