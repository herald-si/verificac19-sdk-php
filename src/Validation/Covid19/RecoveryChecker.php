<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\CertCode;
use Herald\GreenPass\GreenPassEntities\Country;
use Herald\GreenPass\GreenPassEntities\Holder;
use Herald\GreenPass\GreenPassEntities\RecoveryStatement;

class RecoveryChecker
{
    private $validation_date = null;
    private $scanMode = null;
    private $cert = null;
    private $holder = null;
    private $signingCertificate = null;

    public function __construct(RecoveryStatement $cert, \DateTime $validation_date, string $scanMode, Holder $holder, $signingCertificate)
    {
        $this->validation_date = $validation_date;
        $this->scanMode = $scanMode;
        $this->holder = $holder;
        $this->cert = $cert;
        $this->signingCertificate = $signingCertificate;
    }

    public function checkCertificate()
    {
        if ($this->scanMode == ValidationScanMode::ENTRY_IT_DGP) {
            $countryCode = $this->cert->country;
        } else {
            $countryCode = Country::ITALY;
        }
        $isRecoveryBis = $this->isRecoveryBis();
        $startDaysToAdd = $isRecoveryBis ? ValidationRules::getValues(ValidationRules::RECOVERY_CERT_PV_START_DAY, ValidationRules::GENERIC_RULE) : $this->getRecoveryCustomRulesFromValidationRules($this->cert, $countryCode, ValidationRules::CERT_RULE_START);

        if ($this->scanMode == ValidationScanMode::SCHOOL_DGP) {
            $endDaysToAdd = ValidationRules::getEndDaySchool(ValidationRules::RECOVERY_CERT_END_DAY_SCHOOL, ValidationRules::GENERIC_RULE);
        } else {
            $endDaysToAdd = $isRecoveryBis ? ValidationRules::getValues(ValidationRules::RECOVERY_CERT_PV_END_DAY, ValidationRules::GENERIC_RULE) : $this->getRecoveryCustomRulesFromValidationRules($this->cert, $countryCode, ValidationRules::CERT_RULE_END);
        }

        $certificateValidUntil = $this->cert->validUntil;
        $certificateValidFrom = $this->cert->validFrom;

        $startDate = $certificateValidFrom->modify("+$startDaysToAdd days");
        $endFromDateOfFirstPositiveTest = $this->cert->date->modify("+ $endDaysToAdd days");

        if ($this->scanMode == ValidationScanMode::SCHOOL_DGP) {
            $endDate = ($certificateValidUntil < $endFromDateOfFirstPositiveTest) ? $certificateValidUntil : $endFromDateOfFirstPositiveTest;
        } else {
            $endDate = $certificateValidFrom->modify("+$endDaysToAdd days");
        }

        if ($startDate > $this->validation_date) {
            return ValidationStatus::NOT_VALID_YET;
        }

        if ($this->validation_date > $endDate) {
            return ValidationStatus::NOT_VALID;
        }

        if ($this->scanMode == ValidationScanMode::BOOSTER_DGP) {
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
    private function getRecoveryCustomRulesFromValidationRules(RecoveryStatement $cert, string $countryCode, string $startEnd): int
    {
        $ruleType = ValidationRules::GENERIC_RULE;

        if ($countryCode == Country::ITALY) {
            $customCountry = Country::ITALY;
        } else {
            $customCountry = Country::NOT_ITALY;
        }

        $ruleToCheck = ValidationRules::convertRuleNameToConstant("RECOVERY_CERT_{$startEnd}_{$customCountry}");

        $result = ValidationRules::getValues($ruleToCheck, $ruleType);

        return ($result != ValidationStatus::NOT_FOUND) ? (int) $result : ValidationRules::getDefaultValidationDays($startEnd, $countryCode);
    }

    private function isRecoveryBis()
    {
        if ($this->cert->country == Country::ITALY) {
            $eku = isset($this->signingCertificate['extensions']['extendedKeyUsage']) ? $this->signingCertificate['extensions']['extendedKeyUsage'] : '';
            foreach (explode(', ', $eku) as $keyUsage) {
                if (CertCode::OID_RECOVERY == $keyUsage || CertCode::OID_ALT_RECOVERY == $keyUsage) {
                    return true;
                }
            }
        }

        return false;
    }
}