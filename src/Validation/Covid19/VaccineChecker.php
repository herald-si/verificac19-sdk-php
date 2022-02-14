<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\Country;
use Herald\GreenPass\GreenPassEntities\VaccinationDose;

class VaccineChecker
{
    private const  CERT_RULE_START = 'START_DAY';
    private const  CERT_RULE_END = 'END_DAY';
    private const CERT_BOOSTER = 'BOOSTER';
    private const CERT_COMPLETE = 'COMPLETE';

    public static function verifyVaccinationDose(VaccinationDose $cert, \DateTime $validation_date, string $scanMode)
    {
        $esiste_vaccino = ValidationRules::getValues(ValidationRules::VACCINE_END_DAY_COMPLETE, $cert->product);
        if ($esiste_vaccino == ValidationStatus::NOT_FOUND) {
            return ValidationStatus::NOT_RECOGNIZED;
        }
        // isSputnikNotFromSanMarino ( https://github.com/ministero-salute/it-dgc-verificac19-sdk-android/commit/fee61a8ab86c6f4598afd6bbb48553081933f813 )
        $isSputnikNotFromSanMarino = ($cert->product == MedicinalProduct::SPUTNIK && $cert->country != Country::SAN_MARINO);
        if ($isSputnikNotFromSanMarino) {
            return ValidationStatus::NOT_VALID;
        }

        if ($cert->doseGiven < $cert->totalDoses) {
            if ($scanMode == ValidationScanMode::BOOSTER_DGP || $scanMode == ValidationScanMode::SCHOOL_DGP) {
                return ValidationStatus::NOT_VALID;
            }

            $giorni_min_valido = ValidationRules::getValues(ValidationRules::VACCINE_START_DAY_NOT_COMPLETE, $cert->product);
            $data_inizio_validita = $cert->date->modify("+$giorni_min_valido days");

            $giorni_max_valido = ValidationRules::getValues(ValidationRules::VACCINE_END_DAY_NOT_COMPLETE, $cert->product);
            $data_fine_validita = $cert->date->modify("+$giorni_max_valido days");

            if ($validation_date < $data_inizio_validita) {
                return ValidationStatus::NOT_VALID_YET;
            }
            if ($validation_date > $data_fine_validita) {
                return ValidationStatus::EXPIRED;
            }

            return ValidationStatus::VALID;
        }

        if ($cert->doseGiven >= $cert->totalDoses) {
            // j&j booster
            // https://github.com/ministero-salute/it-dgc-verificac19-sdk-android/commit/6812542889b28343acace7780e536fac9bf637a9
            $check_jj_booster = $cert->product == MedicinalProduct::JOHNSON && (($cert->doseGiven > $cert->totalDoses) || ($cert->doseGiven == $cert->totalDoses && $cert->doseGiven >= 2));
            $check_other_booster = $cert->doseGiven > $cert->totalDoses || ($cert->doseGiven == $cert->totalDoses && $cert->doseGiven > 2);
            $check_booster_dose = $check_jj_booster || $check_other_booster;

            $startDaysToAdd = self::getVaccineCustomDaysFromValidationRules($cert, $scanMode, self::CERT_RULE_START, $check_booster_dose);

            if (ValidationScanMode::SCHOOL_DGP == $scanMode && !$check_booster_dose) {
                $endDaysToAdd = ValidationRules::getEndDaySchool(ValidationRules::VACCINE_END_DAY_SCHOOL, ValidationRules::GENERIC_RULE);
            } else {
                $endDaysToAdd = self::getVaccineCustomDaysFromValidationRules($cert, $scanMode, self::CERT_RULE_END, $check_booster_dose);
            }

            $data_inizio_validita = $cert->date->modify("+$startDaysToAdd days");
            $data_fine_validita = $cert->date->modify("+$endDaysToAdd days");

            if ($validation_date < $data_inizio_validita) {
                return ValidationStatus::NOT_VALID_YET;
            }
            if ($validation_date > $data_fine_validita) {
                return ValidationStatus::EXPIRED;
            }

            if ($scanMode == ValidationScanMode::BOOSTER_DGP) {
                if ($cert->product == MedicinalProduct::JOHNSON) {
                    if ($cert->doseGiven == $cert->totalDoses && $cert->doseGiven < 2) {
                        return ValidationStatus::TEST_NEEDED;
                    }
                } else {
                    if ($cert->doseGiven == $cert->totalDoses && $cert->doseGiven < 3) {
                        return ValidationStatus::TEST_NEEDED;
                    }
                }
            }

            return ValidationStatus::VALID;
        }

        return ValidationStatus::NOT_RECOGNIZED;
    }

    /**
     * Get custom vaccine validation rules for countries.
     *
     * @param VaccinationDose $cert
     *                                   Certificate type
     * @param string          $startEnd
     *                                   const CERT_RULE_START or const CERT_RULE_END
     * @param bool            $isBooster
     *                                   true for booster dose
     *
     * @return int
     *             custom rule value
     */
    private static function getVaccineCustomDaysFromValidationRules(VaccinationDose $cert, string $scanMode, string $startEnd, bool $isBooster): int
    {
        $addDays = 0;
        $ruleType = ValidationRules::GENERIC_RULE;
        if ($isBooster) {
            $customCycle = self::CERT_BOOSTER;
        } else {
            $customCycle = self::CERT_COMPLETE;
        }
        $countryCode = ($scanMode == ValidationScanMode::CLASSIC_DGP) ? $cert->country : Country::ITALY;

        if ($countryCode == Country::ITALY) {
            $customCountry = Country::ITALY;
        } else {
            $customCountry = 'NOT_'.Country::ITALY;
        }
        if ($startEnd == self::CERT_RULE_START && !$isBooster && $cert->product == MedicinalProduct::JOHNSON) {
            $addDays = ValidationRules::DEFAULT_DAYS_START_JJ;
        }

        $ruleToCheck = ValidationRules::convertRuleNameToConstant("VACCINE_{$startEnd}_{$customCycle}_{$customCountry}");

        $result = ValidationRules::getValues($ruleToCheck, $ruleType);

        if ($result == ValidationStatus::NOT_FOUND) {
            $result = ValidationRules::getDefaultValidationDays($startEnd, $countryCode);
        }

        return (int) $result + $addDays;
    }
}