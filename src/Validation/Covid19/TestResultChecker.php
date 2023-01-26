<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\Holder;
use Herald\GreenPass\GreenPassEntities\TestResult;
use Herald\GreenPass\GreenPassEntities\TestResultType;
use Herald\GreenPass\GreenPassEntities\TestType;

class TestResultChecker
{
    private $validationDate = null;
    private $scanMode = null;
    private $cert = null;
    private $holder = null;

    public function __construct(\DateTime $validationDate, string $scanMode, Holder $holder, TestResult $cert)
    {
        $this->validationDate = $validationDate;
        $this->scanMode = $scanMode;
        $this->holder = $holder;
        $this->cert = $cert;
    }

    public function checkCertificate()
    {
        if ($this->cert->result == TestResultType::DETECTED) {
            return ValidationStatus::NOT_VALID;
        }

        switch ($this->cert->type) {
            case TestType::MOLECULAR:
                $oreMinValido = ValidationRules::getValues(
                    ValidationRules::MOLECULAR_TEST_START_HOUR,
                    ValidationRules::GENERIC_RULE
                );
                $oreMaxValido = ValidationRules::getValues(
                    ValidationRules::MOLECULAR_TEST_END_HOUR,
                    ValidationRules::GENERIC_RULE
                );
                break;
            case TestType::RAPID:
                $oreMinValido = ValidationRules::getValues(
                    ValidationRules::RAPID_TEST_START_HOUR,
                    ValidationRules::GENERIC_RULE
                );
                $oreMaxValido = ValidationRules::getValues(
                    ValidationRules::RAPID_TEST_END_HOUR,
                    ValidationRules::GENERIC_RULE
                );
                break;
            default:
                return ValidationStatus::NOT_VALID;
        }

        $oraInizioValidita = $this->cert->date->modify("+$oreMinValido hours");
        $oraFineValidita = $this->cert->date->modify("+$oreMaxValido hours");

        if ($this->validationDate < $oraInizioValidita) {
            return ValidationStatus::NOT_VALID_YET;
        }
        if ($this->validationDate > $oraFineValidita) {
            return ValidationStatus::EXPIRED;
        }

        return ValidationStatus::VALID;
    }
}
