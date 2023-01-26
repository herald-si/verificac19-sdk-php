<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\Exemption;

class ExemptionChecker
{
    private $validation_date = null;
    private $scanMode = null;
    private $cert = null;

    public function __construct(\DateTime $validation_date, string $scanMode, Exemption $cert)
    {
        $this->validation_date = $validation_date;
        $this->scanMode = $scanMode;
        $this->cert = $cert;
    }

    public function checkCertificate()
    {
        $valid_from = $this->cert->validFrom;
        $valid_until = $this->cert->validUntil;

        if ($valid_from > $this->validation_date) {
            return ValidationStatus::NOT_VALID_YET;
        }

        if ($this->validation_date > $valid_until) {
            return ValidationStatus::EXPIRED;
        }

        return ValidationStatus::VALID;
    }
}