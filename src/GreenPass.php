<?php

namespace Herald\GreenPass;

use Herald\GreenPass\GreenPassEntities\CertificateType;
use Herald\GreenPass\GreenPassEntities\Exemption;
use Herald\GreenPass\GreenPassEntities\Holder;
use Herald\GreenPass\GreenPassEntities\RecoveryStatement;
use Herald\GreenPass\GreenPassEntities\TestResult;
use Herald\GreenPass\GreenPassEntities\VaccinationDose;
use Herald\GreenPass\Validation\Covid19\GreenPassCovid19Checker;
use Herald\GreenPass\Validation\Covid19\ValidationStatus;

class GreenPass
{
    /**
     * Schema version.
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

    /**
     * Health Certificate
     *
     * @var array
     */
    public $hcert;

    /**
     * Issued At
     *
     * @var DateTime
     */
    public $iat;
    
    /**
     * Expiration Time
     *
     * @var DateTime
     */
    public $exp;
    
    public function __construct($data, string $signingCert = '')
    {
        $this->hcert = $data[- 260];

        $this->version = $this->hcert[1]['ver'] ?? null;

        $this->holder = new Holder($this->hcert[1]);

        if (array_key_exists('v', $this->hcert[1])) {
            $this->certificate = new VaccinationDose($this->hcert[1]);
        }

        if (array_key_exists('t', $this->hcert[1])) {
            $this->certificate = new TestResult($this->hcert[1]);
        }

        if (array_key_exists('r', $this->hcert[1])) {
            $this->certificate = new RecoveryStatement($this->hcert[1]);
        }

        if (array_key_exists('e', $this->hcert[1])) {
            $this->certificate = new Exemption($this->hcert[1]);
        }

        $this->iat = \DateTime::createFromFormat("Ymd",date("Ymd", $data[6])); 

        $this->exp = \DateTime::createFromFormat("Ymd",date("Ymd", $data[4]));
        
        if (!empty($signingCert)) {
            $this->signingCertInfo = openssl_x509_parse($signingCert);
        }
    }
    
    public function checkValid(string $scanMode)
    {
        $today = new \DateTime();
        if($today < $this->iat || $today > $this->exp){
            echo("entro\n");
            return ValidationStatus::NOT_VALID;
        }
        return ValidationStatus::greenpassStatusAnonymizer(GreenPassCovid19Checker::verifyCert($this, $scanMode));
    }
}
