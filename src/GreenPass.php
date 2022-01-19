<?php
namespace Herald\GreenPass;

use Herald\GreenPass\GreenPassEntities\CertificateType;
use Herald\GreenPass\GreenPassEntities\RecoveryStatement;
use Herald\GreenPass\GreenPassEntities\TestResult;
use Herald\GreenPass\GreenPassEntities\VaccinationDose;
use Herald\GreenPass\GreenPassEntities\Holder;
use Herald\GreenPass\Validation\Covid19\GreenPassCovid19Checker;
use Herald\GreenPass\Validation\Covid19\ValidationStatus;

class GreenPass
{

    /**
     * Schema version
     *
     * @var string|mixed|null
     */
    public $version;

    /**
     * The person who holds the certificate.
     *
     * @var Holder
     */
    public $holder;

    /**
     * Certificate issued.
     *
     * @var CertificateType
     */
    public $certificate;

    public $signingCertInfo;

    public function __construct($data, string $signingCert = "")
    {
        $this->version = $data["ver"] ?? null;

        $this->holder = new Holder($data);

        if (array_key_exists('v', $data)) {
            $this->certificate = new VaccinationDose($data);
        }

        if (array_key_exists('t', $data)) {
            $this->certificate = new TestResult($data);
        }

        if (array_key_exists('r', $data)) {
            $this->certificate = new RecoveryStatement($data);
        }

        if(!empty($signingCert)){
            $this->signingCertInfo = openssl_x509_parse($signingCert);
        }
    }

    public function checkValid(String $scanMode)
    {
        $statusCert = GreenPassCovid19Checker::verifyCert($this, $scanMode);
        return ValidationStatus::greenpassStatusAnonymizer($statusCert, $scanMode);
    }
}
