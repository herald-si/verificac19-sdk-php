<?php
namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\VaccinationDose;
use Herald\GreenPass\GreenPassEntities\TestResult;
use Herald\GreenPass\GreenPassEntities\RecoveryStatement;
use Herald\GreenPass\GreenPassEntities\Covid19;
use Herald\GreenPass\GreenPassEntities\TestResultType;
use Herald\GreenPass\GreenPassEntities\TestType;

class GreenPassCovid19Checker
{

    public static function verifyCert($greenPass)
    {
        $cert = $greenPass->certificate;

        if (! self::verifyDiseaseAgent($cert->diseaseAgent)) {
            return ValidationStatus::NOT_COVID_19;
        }

        $data_oggi = new \DateTime();

        // vaccino effettuato
        if ($cert instanceof VaccinationDose) {
            $esiste_vaccino = self::getValueFromValidationRules(ValidationRules::VACCINE_END_DAY_COMPLETE, $cert->product);
            if ($esiste_vaccino == ValidationStatus::NOT_FOUND) {
                return ValidationStatus::NOT_RECOGNIZED;
            }

            if ($cert->doseGiven < $cert->totalDoses) {
                $giorni_min_valido = self::getValueFromValidationRules(ValidationRules::VACCINE_START_DAY_NOT_COMPLETE, $cert->product);
                $data_inizio_validita = $cert->date->modify("+$giorni_min_valido days");

                $giorni_max_valido = self::getValueFromValidationRules(ValidationRules::VACCINE_END_DAY_NOT_COMPLETE, $cert->product);
                $data_fine_validita = $cert->date->modify("+$giorni_max_valido days");

                if ($data_oggi < $data_inizio_validita)
                    return ValidationStatus::NOT_VALID_YET;
                if ($data_oggi > $data_fine_validita)
                    return ValidationStatus::EXPIRED;

                return ValidationStatus::PARTIALLY_VALID;
            }

            if ($cert->doseGiven >= $cert->totalDoses) {
                $giorni_min_valido = self::getValueFromValidationRules(ValidationRules::VACCINE_START_DAY_COMPLETE, $cert->product);
                $data_inizio_validita = $cert->date->modify("+$giorni_min_valido days");

                $giorni_max_valido = self::getValueFromValidationRules(ValidationRules::VACCINE_END_DAY_COMPLETE, $cert->product);
                $data_fine_validita = $cert->date->modify("+$giorni_max_valido days");

                if ($data_oggi < $data_inizio_validita)
                    return ValidationStatus::NOT_VALID_YET;
                if ($data_oggi > $data_fine_validita)
                    return ValidationStatus::EXPIRED;

                return ValidationStatus::VALID;
            }

            return ValidationStatus::NOT_RECOGNIZED;
        }

        // tampone effettuato
        if ($cert instanceof TestResult) {
            if ($cert->result == TestResultType::DETECTED)
                return ValidationStatus::NOT_VALID;

            if ($cert->type == TestType::MOLECULAR) {

                $ore_min_valido = self::getValueFromValidationRules(ValidationRules::MOLECULAR_TEST_START_HOUR, "GENERIC");
                $ora_inizio_validita = $cert->date->modify("+$ore_min_valido hours");

                $ore_max_valido = self::getValueFromValidationRules(ValidationRules::MOLECULAR_TEST_END_HOUR, "GENERIC");
                $ora_fine_validita = $cert->date->modify("+$ore_max_valido hours");

                if ($data_oggi < $ora_inizio_validita)
                    return ValidationStatus::NOT_VALID_YET;
                if ($data_oggi > $ora_fine_validita)
                    return ValidationStatus::EXPIRED;

                return ValidationStatus::VALID;
            }

            if ($cert->type == TestType::RAPID) {

                $ore_min_valido = self::getValueFromValidationRules(ValidationRules::RAPID_TEST_START_HOUR, "GENERIC");
                $ora_inizio_validita = $cert->date->modify("+$ore_min_valido hours");

                $ore_max_valido = self::getValueFromValidationRules(ValidationRules::RAPID_TEST_END_HOUR, "GENERIC");
                $ora_fine_validita = $cert->date->modify("+$ore_max_valido hours");

                if ($data_oggi < $ora_inizio_validita)
                    return ValidationStatus::NOT_VALID_YET;
                if ($data_oggi > $ora_fine_validita)
                    return ValidationStatus::EXPIRED;

                return ValidationStatus::VALID;
            }

            return ValidationStatus::NOT_RECOGNIZED;
        }

        // guarigione avvenuta
        if ($cert instanceof RecoveryStatement) {

            $data_inizio_validita = $cert->validFrom;
            $data_fine_validita = $cert->validUntil;

            if ($data_oggi < $data_inizio_validita)
                return ValidationStatus::NOT_VALID_YET;
            if ($data_oggi > $data_fine_validita)
                return ValidationStatus::EXPIRED;

            return ValidationStatus::VALID;
        }

        return ValidationStatus::NOT_RECOGNIZED;
    }

    private static function getValueFromValidationRules($rule, $type)
    {
        $validity_rules = ValidationRules::getValidationRules();
        $value = ValidationStatus::NOT_FOUND;
        foreach ($validity_rules as $item) {
            if (($item->name == $rule) && ($item->type == $type)) {
                $value = $item->value;
                break;
            }
        }
        return $value;
    }

    private static function verifyDiseaseAgent($agent)
    {
        return ($agent instanceof Covid19);
    }
}