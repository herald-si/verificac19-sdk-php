<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\CertCode;
use Herald\GreenPass\GreenPassEntities\Country;
use Herald\GreenPass\GreenPassEntities\Holder;
use Herald\GreenPass\GreenPassEntities\RecoveryStatement;

class RecoveryChecker
{
    private $validationDate = null;
    private $scanMode = null;
    private $cert = null;
    private $holder = null;
    private $signingCertificate = null;

    public function __construct(
        RecoveryStatement $cert,
        \DateTime $validationDate,
        string $scanMode,
        Holder $holder,
        $signingCertificate
    ) {
        $this->validationDate = $validationDate;
        $this->scanMode = $scanMode;
        $this->holder = $holder;
        $this->cert = $cert;
        $this->signingCertificate = $signingCertificate;
    }

    public function checkCertificate()
    {

        $countryCode = Country::ITALY;

        $isRecoveryBis = $this->isRecoveryBis();
        $startDaysToAdd = $isRecoveryBis ?
            ValidationRules::getValues(ValidationRules::RECOVERY_CERT_PV_START_DAY, ValidationRules::GENERIC_RULE)
            :
            $this->getRecoveryCustomRulesFromValidationRules($countryCode, ValidationRules::CERT_RULE_START);

        $endDaysToAdd = $isRecoveryBis ?
            ValidationRules::getValues(ValidationRules::RECOVERY_CERT_PV_END_DAY, ValidationRules::GENERIC_RULE)
            :
            $this->getRecoveryCustomRulesFromValidationRules($countryCode, ValidationRules::CERT_RULE_END);

        $certificateValidFrom = $this->cert->validFrom;

        $startDate = $certificateValidFrom->modify("+$startDaysToAdd days");
        $endDate = $certificateValidFrom->modify("+$endDaysToAdd days");
        $endDate = $endDate->SetTime(23, 59);

        if ($startDate > $this->validationDate) {
            return ValidationStatus::NOT_VALID_YET;
        }

        if ($this->validationDate > $endDate) {
            return ValidationStatus::EXPIRED;
        }

        return ValidationStatus::VALID;
    }

    /**
     * Get custom recovery validation rules for countries.
     *
     * @param string $startEnd
     *                         const CERT_RULE_START or CERT_RULE_END
     *
     * @return string
     *                custom rule value
     */
    private function getRecoveryCustomRulesFromValidationRules(string $countryCode, string $startEnd): int
    {
        $ruleType = ValidationRules::GENERIC_RULE;

        if ($countryCode == Country::ITALY) {
            $customCountry = Country::ITALY;
        } else {
            $customCountry = Country::NOT_ITALY;
        }

        $ruleToCheck = ValidationRules::convertRuleNameToConstant("RECOVERY_CERT_{$startEnd}_{$customCountry}");

        $result = ValidationRules::getValues($ruleToCheck, $ruleType);

        return ($result != ValidationStatus::NOT_FOUND) ?
            (int) $result
            :
            ValidationRules::getDefaultValidationDays($startEnd, $countryCode);
    }

    private function isRecoveryBis()
    {
        if ($this->cert->country == Country::ITALY) {
            $eku = isset($this->signingCertificate['extensions']['extendedKeyUsage']) ?
                $this->signingCertificate['extensions']['extendedKeyUsage'] : '';
            foreach (explode(', ', $eku) as $keyUsage) {
                if (CertCode::OID_RECOVERY == $keyUsage || CertCode::OID_ALT_RECOVERY == $keyUsage) {
                    return true;
                }
            }
        }

        return false;
    }
}
