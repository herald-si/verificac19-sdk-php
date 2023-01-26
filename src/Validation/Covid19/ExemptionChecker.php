<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\Exemption;

class ExemptionChecker
{
    private $validationDate = null;
    private $scanMode = null;
    private $cert = null;

    public function __construct(\DateTime $validationDate, string $scanMode, Exemption $cert)
    {
        $this->validationDate = $validationDate;
        $this->scanMode = $scanMode;
        $this->cert = $cert;
    }

    public function checkCertificate()
    {
        $validFrom = $this->cert->validFrom;
        $validUntil = $this->cert->validUntil;

        if ($validFrom > $this->validationDate) {
            return ValidationStatus::NOT_VALID_YET;
        }

        if ($this->validationDate > $validUntil) {
            return ValidationStatus::EXPIRED;
        }

        return ValidationStatus::VALID;
    }
}
