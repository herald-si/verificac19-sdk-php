<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\Exemption;

class ExemptionChecker
{
    public static function verifyExemption(Exemption $cert, \DateTime $validation_date, string $scanMode)
    {
        $valid_from = $cert->validFrom;
        $valid_until = $cert->validUntil;

        if ($valid_from > $validation_date) {
            return ValidationStatus::NOT_VALID_YET;
        }

        if ($validation_date > $valid_until) {
            return ValidationStatus::NOT_VALID;
        }

        if ($scanMode == ValidationScanMode::BOOSTER_DGP) {
            return ValidationStatus::TEST_NEEDED;
        }

        return ValidationStatus::VALID;
    }
}