<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\TestResult;
use Herald\GreenPass\GreenPassEntities\TestResultType;
use Herald\GreenPass\GreenPassEntities\TestType;

class TestResultChecker
{
    public static function verifyTestResults(TestResult $cert, \DateTime $validation_date, string $scanMode, \DateTimeImmutable $dob)
    {
        // if scan mode Super Green Pass, TestResult is non a valid GP
        if ($scanMode == ValidationScanMode::SUPER_DGP || $scanMode == ValidationScanMode::BOOSTER_DGP || $scanMode == ValidationScanMode::SCHOOL_DGP) {
            return ValidationStatus::NOT_VALID;
        }

        if ($cert->result == TestResultType::DETECTED) {
            return ValidationStatus::NOT_VALID;
        }

        switch ($cert->type) {
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

        $ora_inizio_validita = $cert->date->modify("+$ore_min_valido hours");
        $ora_fine_validita = $cert->date->modify("+$ore_max_valido hours");

        if ($validation_date < $ora_inizio_validita) {
            return ValidationStatus::NOT_VALID_YET;
        }
        if ($validation_date > $ora_fine_validita) {
            return ValidationStatus::EXPIRED;
        }

        return self::checkVaccineMandatoryAge($validation_date, $scanMode, $dob) ? ValidationStatus::NOT_VALID : ValidationStatus::VALID;
    }

    private static function checkVaccineMandatoryAge(\DateTime $validation_date, string $scanMode, \DateTimeImmutable $dob)
    {
        $age = $dob->diff($validation_date)->y;

        if ($scanMode == ValidationScanMode::WORK_DGP && $age >= ValidationRules::VACCINE_MANDATORY_AGE) {
            return true;
        }

        return false;
    }
}