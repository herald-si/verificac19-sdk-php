<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\Holder;
use Herald\GreenPass\GreenPassEntities\TestResult;
use Herald\GreenPass\GreenPassEntities\TestResultType;
use Herald\GreenPass\GreenPassEntities\TestType;

class TestResultChecker
{
    private $validation_date = null;
    private $scanMode = null;
    private $cert = null;
    private $holder = null;

    public function __construct(\DateTime $validation_date, string $scanMode, Holder $holder, TestResult $cert)
    {
        $this->validation_date = $validation_date;
        $this->scanMode = $scanMode;
        $this->holder = $holder;
        $this->cert = $cert;
    }

    public function checkCertificate()
    {
        // if scan mode Super Green Pass, TestResult is non a valid GP
        $isTestNotAllowed = ($this->scanMode == ValidationScanMode::SUPER_DGP || $this->scanMode == ValidationScanMode::BOOSTER_DGP || $this->scanMode == ValidationScanMode::SCHOOL_DGP);

        if ($this->cert->result == TestResultType::DETECTED) {
            return ValidationStatus::NOT_VALID;
        }

        switch ($this->cert->type) {
            case TestType::MOLECULAR:
                $ore_min_valido = ValidationRules::getValues(ValidationRules::MOLECULAR_TEST_START_HOUR, ValidationRules::GENERIC_RULE);
                $ore_max_valido = ValidationRules::getValues(ValidationRules::MOLECULAR_TEST_END_HOUR, ValidationRules::GENERIC_RULE);
            break;
            case TestType::RAPID:
                $ore_min_valido = ValidationRules::getValues(ValidationRules::RAPID_TEST_START_HOUR, ValidationRules::GENERIC_RULE);
                $ore_max_valido = ValidationRules::getValues(ValidationRules::RAPID_TEST_END_HOUR, ValidationRules::GENERIC_RULE);
            break;
            default:
                return ValidationStatus::NOT_VALID;
        }

        $ora_inizio_validita = $this->cert->date->modify("+$ore_min_valido hours");
        $ora_fine_validita = $this->cert->date->modify("+$ore_max_valido hours");

        if ($this->validation_date < $ora_inizio_validita) {
            return ValidationStatus::NOT_VALID_YET;
        }
        if ($this->validation_date > $ora_fine_validita) {
            return ValidationStatus::EXPIRED;
        }

        if ($isTestNotAllowed) {
            return ValidationStatus::NOT_VALID;
        } else {
            return self::checkVaccineMandatoryAge() ? ValidationStatus::NOT_VALID : ValidationStatus::VALID;
        }
    }

    private function checkVaccineMandatoryAge()
    {
        $age = $this->holder->getAgeAtGivenDate($this->validation_date);

        if ($this->scanMode == ValidationScanMode::WORK_DGP && $age >= ValidationRules::VACCINE_MANDATORY_AGE) {
            return true;
        }

        return false;
    }
}