<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\CertCode;
use Herald\GreenPass\GreenPassEntities\CertificateType;
use Herald\GreenPass\GreenPassEntities\Country;
use Herald\GreenPass\GreenPassEntities\RecoveryStatement;

class RecoveryChecker
{
    public static function verifyRecoveryStatement(RecoveryStatement $cert, \DateTime $validation_date, string $scanMode, $certificate)
    {
        $isRecoveryBis = self::isRecoveryBis($cert, $certificate);
        $startDaysToAdd = $isRecoveryBis ? ValidationRules::getValues(ValidationRules::RECOVERY_CERT_PV_START_DAY, ValidationRules::GENERIC_RULE) : self::getRecoveryCustomRulesFromValidationRules($cert, $scanMode, ValidationRules::CERT_RULE_START);

        if ($scanMode == ValidationScanMode::SCHOOL_DGP) {
            $endDaysToAdd = ValidationRules::getEndDaySchool(ValidationRules::RECOVERY_CERT_END_DAY_SCHOOL, ValidationRules::GENERIC_RULE);
        } else {
            $endDaysToAdd = $isRecoveryBis ? ValidationRules::getValues(ValidationRules::RECOVERY_CERT_PV_END_DAY, ValidationRules::GENERIC_RULE) : self::getRecoveryCustomRulesFromValidationRules($cert, $scanMode, ValidationRules::CERT_RULE_END);
        }

        $certificateValidUntil = $cert->validUntil;
        $certificateValidFrom = $cert->validFrom;

        $startDate = $certificateValidFrom->modify("+$startDaysToAdd days");
        $endFromDateOfFirstPositiveTest = $cert->date->modify("+ $endDaysToAdd days");

        if ($scanMode == ValidationScanMode::SCHOOL_DGP) {
            $endDate = ($certificateValidUntil < $endFromDateOfFirstPositiveTest) ? $certificateValidUntil : $endFromDateOfFirstPositiveTest;
        } else {
            $endDate = $certificateValidFrom->modify("+$endDaysToAdd days");
        }

        if ($startDate > $validation_date) {
            return ValidationStatus::NOT_VALID_YET;
        }

        if ($validation_date > $endDate) {
            return ValidationStatus::NOT_VALID;
        }

        if ($scanMode == ValidationScanMode::BOOSTER_DGP) {
            return ValidationStatus::TEST_NEEDED;
        }

        return ValidationStatus::VALID;
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

        $result = ValidationRules::getValues($ruleToCheck, $ruleType);

        return ($result != ValidationStatus::NOT_FOUND) ? (int) $result : ValidationRules::getDefaultValidationDays($startEnd, $countryCode);
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
}