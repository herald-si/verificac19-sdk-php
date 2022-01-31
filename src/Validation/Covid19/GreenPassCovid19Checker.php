<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPass;
use Herald\GreenPass\GreenPassEntities\CertCode;
use Herald\GreenPass\GreenPassEntities\CertificateType;
use Herald\GreenPass\GreenPassEntities\Country;
use Herald\GreenPass\GreenPassEntities\Covid19;
use Herald\GreenPass\GreenPassEntities\Exemption;
use Herald\GreenPass\GreenPassEntities\RecoveryStatement;
use Herald\GreenPass\GreenPassEntities\TestResult;
use Herald\GreenPass\GreenPassEntities\TestResultType;
use Herald\GreenPass\GreenPassEntities\TestType;
use Herald\GreenPass\GreenPassEntities\VaccinationDose;
use Herald\GreenPass\Utils\EndpointService;
use Herald\GreenPass\Utils\EnvConfig;

class GreenPassCovid19Checker
{
    private const  CERT_RULE_START = 'START_DAY';
    private const  CERT_RULE_END = 'END_DAY';
    private const CERT_BOOSTER = 'BOOSTER';
    private const CERT_COMPLETE = 'COMPLETE';

    /**
     * Verify GreenPass due date and blacklist.
     *
     * @param GreenPass $greenPass
     *                             GreenPass to check
     * @param string    $scanMode
     *                             ValidationScanMode
     *
     * @return string
     *                ValidationStatus
     */
    public static function verifyCert(GreenPass $greenPass, string $scanMode = ValidationScanMode::CLASSIC_DGP)
    {
        if (!EnvConfig::isDebugEnabled() && ($scanMode == ValidationScanMode::SCHOOL_DGP || $scanMode == ValidationScanMode::WORK_DGP)) {
            throw new  \InvalidArgumentException('Unrelased scan mode, dont use in production');
        }
        $cert = $greenPass->certificate;

        if (!self::verifyDiseaseAgent($cert->diseaseAgent)) {
            return ValidationStatus::NOT_COVID_19;
        }

        $data_oggi = new \DateTime();

        $certificateId = self::extractUVCI($greenPass);

        if (empty($certificateId)) {
            return ValidationStatus::NOT_EU_DCC;
        }

        if (self::checkInBlackList($certificateId)) {
            return ValidationStatus::NOT_VALID;
        }

        if (CertificateRevocationList::DRL_SYNC_ACTIVE && self::checkInDrl($certificateId)) {
            return ValidationStatus::REVOKED;
        }

        // vaccino effettuato
        if ($cert instanceof VaccinationDose) {
            return self::verifyVaccinationDose($cert, $data_oggi, $scanMode);
        }

        // tampone effettuato
        if ($cert instanceof TestResult) {
            // if scan mode Super Green Pass, TestResult is non a valid GP
            if ($scanMode == ValidationScanMode::SUPER_DGP || $scanMode == ValidationScanMode::BOOSTER_DGP || $scanMode == ValidationScanMode::SCHOOL_DGP) {
                return ValidationStatus::NOT_VALID;
            }

            return self::verifyTestResults($cert, $data_oggi, $scanMode, $greenPass->holder->dateOfBirth);
        }

        // guarigione avvenuta
        if ($cert instanceof RecoveryStatement) {
            return self::verifyRecoveryStatement($cert, $data_oggi, $scanMode, $greenPass->signingCertInfo);
        }

        // esenzione
        if ($cert instanceof Exemption) {
            return self::verifyExemption($cert, $data_oggi, $scanMode);
        }

        return ValidationStatus::NOT_RECOGNIZED;
    }

    /**
     * Get validation rules from rule name and type.
     *
     * @param string $rule
     *                     rule name
     * @param string $type
     *                     rule type
     *
     * @return string
     *                rule value if set, ValidationStatus::NOT_FOUND otherwise
     */
    private static function getValueFromValidationRules(string $rule, string $type)
    {
        $validity_rules = EndpointService::getValidationRules();
        $value = ValidationStatus::NOT_FOUND;
        foreach ($validity_rules as $item) {
            if (($item->name == $rule) && ($item->type == $type)) {
                $value = $item->value;
                break;
            }
        }

        return $value;
    }

    /**
     * Get default days check.
     */
    private static function getDefaultValidationDays(string $startEnd, string $country): int
    {
        $default = ValidationRules::DEFAULT_DAYS_START;

        if ($startEnd == self::CERT_RULE_END) {
            $default = ($country == Country::ITALY) ? ValidationRules::DEFAULT_DAYS_END_IT : ValidationRules::DEFAULT_DAYS_END_NOT_IT;
        }

        return $default;
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
        $ruleType = $cert->product;
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

        $ruleToCheck = ValidationRules::convertRuleNameToConstant("VACCINE_{$startEnd}_{$customCycle}");

        $result = self::getValueFromValidationRules($ruleToCheck, $ruleType);

        if ($result == ValidationStatus::NOT_FOUND) {
            $result = self::getDefaultValidationDays($startEnd, $countryCode);
        }

        return (int) $result + $addDays;
    }

    /**
     * Get custom recovery validation rules for countries.
     *
     * @param RecoveryStatement $cert
     *                                    Certificate type
     * @param string            $startEnd
     *                                    const CERT_RULE_START or CERT_RULE_END
     *
     * @return string
     *                custom rule value
     */
    private static function getRecoveryCustomRulesFromValidationRules(RecoveryStatement $cert, string $scanMode, string $startEnd): int
    {
        $ruleType = ValidationRules::GENERIC_RULE;

        $countryCode = ($scanMode == ValidationScanMode::CLASSIC_DGP) ? $cert->country : Country::ITALY;

        if ($countryCode == Country::ITALY) {
            $customCountry = Country::ITALY;
        } else {
            $customCountry = 'NOT_'.Country::ITALY;
        }

        $ruleToCheck = ValidationRules::convertRuleNameToConstant("RECOVERY_CERT_{$startEnd}_{$customCountry}");

        $result = self::getValueFromValidationRules($ruleToCheck, $ruleType);

        return ($result != ValidationStatus::NOT_FOUND) ? (int) $result : self::getDefaultValidationDays($startEnd, $countryCode);
    }

    /**
     * Check if GreenPass is for Covid19.
     *
     * @return bool
     */
    private static function verifyDiseaseAgent($agent)
    {
        return $agent instanceof Covid19;
    }

    private static function verifyVaccinationDose(VaccinationDose $cert, \DateTime $validation_date, string $scanMode)
    {
        $esiste_vaccino = self::getValueFromValidationRules(ValidationRules::VACCINE_END_DAY_COMPLETE, $cert->product);
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

            $giorni_min_valido = self::getValueFromValidationRules(ValidationRules::VACCINE_START_DAY_NOT_COMPLETE, $cert->product);
            $data_inizio_validita = $cert->date->modify("+$giorni_min_valido days");

            $giorni_max_valido = self::getValueFromValidationRules(ValidationRules::VACCINE_END_DAY_NOT_COMPLETE, $cert->product);
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
                $endDaysToAdd = self::getEndDaySchool(ValidationRules::VACCINE_END_DAY_SCHOOL, ValidationRules::GENERIC_RULE);
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

    private static function verifyTestResults(TestResult $cert, \DateTime $validation_date, string $scanMode, \DateTimeImmutable $dob)
    {
        if ($cert->result == TestResultType::DETECTED) {
            return ValidationStatus::NOT_VALID;
        }

        if ($cert->type == TestType::MOLECULAR) {
            $ore_min_valido = self::getValueFromValidationRules(ValidationRules::MOLECULAR_TEST_START_HOUR, ValidationRules::GENERIC_RULE);
            $ora_inizio_validita = $cert->date->modify("+$ore_min_valido hours");

            $ore_max_valido = self::getValueFromValidationRules(ValidationRules::MOLECULAR_TEST_END_HOUR, ValidationRules::GENERIC_RULE);
            $ora_fine_validita = $cert->date->modify("+$ore_max_valido hours");

            if ($validation_date < $ora_inizio_validita) {
                return ValidationStatus::NOT_VALID_YET;
            }
            if ($validation_date > $ora_fine_validita) {
                return ValidationStatus::EXPIRED;
            }

            return self::checkVaccineMandatoryAge($validation_date, $scanMode, $dob) ? ValidationStatus::NOT_VALID : ValidationStatus::VALID;
        }

        if ($cert->type == TestType::RAPID) {
            $ore_min_valido = self::getValueFromValidationRules(ValidationRules::RAPID_TEST_START_HOUR, ValidationRules::GENERIC_RULE);
            $ora_inizio_validita = $cert->date->modify("+$ore_min_valido hours");

            $ore_max_valido = self::getValueFromValidationRules(ValidationRules::RAPID_TEST_END_HOUR, ValidationRules::GENERIC_RULE);
            $ora_fine_validita = $cert->date->modify("+$ore_max_valido hours");

            if ($validation_date < $ora_inizio_validita) {
                return ValidationStatus::NOT_VALID_YET;
            }
            if ($validation_date > $ora_fine_validita) {
                return ValidationStatus::EXPIRED;
            }

            return self::checkVaccineMandatoryAge($validation_date, $scanMode, $dob) ? ValidationStatus::NOT_VALID : ValidationStatus::VALID;
        }

        return ValidationStatus::NOT_RECOGNIZED;
    }

    private static function verifyRecoveryStatement(RecoveryStatement $cert, \DateTime $validation_date, string $scanMode, $certificate)
    {
        $isRecoveryBis = self::isRecoveryBis($cert, $certificate);
        $start_day = $isRecoveryBis ? self::getValueFromValidationRules(ValidationRules::RECOVERY_CERT_PV_START_DAY, ValidationRules::GENERIC_RULE) : self::getRecoveryCustomRulesFromValidationRules($cert, $scanMode, self::CERT_RULE_START);

        if ($scanMode == ValidationScanMode::SCHOOL_DGP) {
            $end_day = self::getEndDaySchool(ValidationRules::RECOVERY_CERT_END_DAY_SCHOOL, ValidationRules::GENERIC_RULE);
        } else {
            $end_day = $isRecoveryBis ? self::getValueFromValidationRules(ValidationRules::RECOVERY_CERT_PV_END_DAY, ValidationRules::GENERIC_RULE) : self::getRecoveryCustomRulesFromValidationRules($cert, $scanMode, self::CERT_RULE_END);
        }

        $valid_from = $cert->validFrom;

        $start_date = $valid_from->modify("+$start_day days");

        if ($start_date > $validation_date) {
            return ValidationStatus::NOT_VALID_YET;
        }

        if ($validation_date > $start_date->modify("+$end_day days")) {
            return ValidationStatus::NOT_VALID;
        }

        if ($scanMode == ValidationScanMode::BOOSTER_DGP) {
            return ValidationStatus::TEST_NEEDED;
        }

        return ValidationStatus::VALID;
    }

    private static function verifyExemption(Exemption $cert, \DateTime $validation_date, string $scanMode)
    {
        if ($scanMode == ValidationScanMode::SCHOOL_DGP) {
            return ValidationStatus::NOT_VALID;
        }

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

    private static function checkInBlackList(string $kid): bool
    {
        $list = self::getValueFromValidationRules(ValidationRules::BLACK_LIST_UVCI, ValidationRules::BLACK_LIST_UVCI);
        if ($list != ValidationStatus::NOT_FOUND) {
            $blacklisted = explode(';', $list);
            foreach ($blacklisted as $bl_item) {
                if ($kid == $bl_item) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function checkInDrl(string $kid): bool
    {
        $crl = new CertificateRevocationList();

        return $crl->isUVCIRevoked($kid);
    }

    private static function extractUVCI(GreenPass $greenPass): string
    {
        $certificateIdentifier = '';
        $cert = $greenPass->certificate;

        if ($cert instanceof VaccinationDose) {
            $certificateIdentifier = $cert->id;
        }
        if ($cert instanceof TestResult) {
            $certificateIdentifier = $cert->id;
        }
        if ($cert instanceof RecoveryStatement) {
            $certificateIdentifier = $cert->id;
        }
        if ($cert instanceof Exemption) {
            $certificateIdentifier = $cert->id;
        }

        return $certificateIdentifier;
    }

    private static function isRecoveryBis(CertificateType $cert, $signingCertificate)
    {
        if ($cert->country == Country::ITALY) {
            $eku = isset($signingCertificate['extensions']['extendedKeyUsage']) ? $signingCertificate['extensions']['extendedKeyUsage'] : '';
            foreach (explode(', ', $eku) as $keyUsage) {
                if (CertCode::OID_RECOVERY == $keyUsage || CertCode::OID_ALT_RECOVERY == $keyUsage) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function checkVaccineMandatoryAge(\DateTime $validation_date, string $scanMode, \DateTimeImmutable $dob)
    {
        $age = $dob->diff($validation_date)->y;

        if ($scanMode == ValidationScanMode::WORK_DGP && $age >= ValidationRules::VACCINE_MANDATORY_AGE) {
            return true;
        }

        return false;
    }

    private static function getEndDaySchool(string $rule, string $type)
    {
        $days = self::getValueFromValidationRules($rule, $type);
        if ($days == ValidationStatus::NOT_FOUND) {
            $days = ValidationRules::DEFAULT_DAYS_SCHOOL;
        }

        return $days;
    }
}