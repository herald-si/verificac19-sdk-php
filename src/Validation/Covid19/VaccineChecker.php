<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\Country;
use Herald\GreenPass\GreenPassEntities\Holder;
use Herald\GreenPass\GreenPassEntities\VaccinationDose;

class VaccineChecker
{
    private const  CERT_RULE_START = 'START_DAY';
    private const  CERT_RULE_END = 'END_DAY';
    private const CERT_BOOSTER = 'BOOSTER';
    private const CERT_COMPLETE = 'COMPLETE';

    private $validationDate = null;
    private $scanMode = null;
    private $cert = null;
    private $holder = null;

    public function __construct(\DateTime $validationDate, string $scanMode, Holder $holder, VaccinationDose $cert)
    {
        $this->validationDate = $validationDate;
        $this->scanMode = $scanMode;
        $this->holder = $holder;
        $this->cert = $cert;
    }

    public function checkCertificate()
    {
        if ($this->cert->isNotComplete() && !MedicinalProduct::isEma($this->cert->product, $this->cert->country)) {
            return ValidationStatus::NOT_VALID;
        }

        return $this->validate($this->cert);
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
    private function getVaccineCustomDaysFromValidationRules(
        VaccinationDose $cert,
        string $countryCode,
        string $startEnd,
        bool $isBooster
    ): int {
        $ruleType = ValidationRules::GENERIC_RULE;
        if ($isBooster) {
            $customCycle = VaccineChecker::CERT_BOOSTER;
        } else {
            $customCycle = VaccineChecker::CERT_COMPLETE;
        }

        if ($countryCode == Country::ITALY) {
            $customCountry = Country::ITALY;
        } else {
            $customCountry = Country::NOT_ITALY;
        }
        if (
            $startEnd == VaccineChecker::CERT_RULE_START
            && !$isBooster && $cert->product == MedicinalProduct::JOHNSON
        ) {
            $addDays = ValidationRules::getValues(
                ValidationRules::VACCINE_START_DAY_COMPLETE,
                MedicinalProduct::JOHNSON
            );
        } else {
            $addDays = 0;
        }

        $ruleToCheck = ValidationRules::convertRuleNameToConstant(
            "VACCINE_{$startEnd}_{$customCycle}_{$customCountry}"
        );

        $result = ValidationRules::getValues($ruleToCheck, $ruleType);

        if ($result == ValidationStatus::NOT_FOUND) {
            $result = ValidationRules::getDefaultValidationDays($startEnd, $countryCode);
        }

        return (int) $result + (int) $addDays;
    }

    private function validate(VaccinationDose $cert)
    {
        if ($this->scanMode == ValidationScanMode::CLASSIC_DGP) {
            $esito = $this->standardStrategy($cert);
        } else {
            $esito = ValidationStatus::NOT_EU_DCC;
        }
        return $esito;
    }

    private function standardStrategy(VaccinationDose $cert)
    {
        $countryCode = Country::ITALY;

        $vaccineDate = $cert->date;

        $startDaysToAdd = 0;
        if ($cert->isComplete()) {
            $startDaysToAdd = $this->getVaccineCustomDaysFromValidationRules(
                $cert,
                $countryCode,
                VaccineChecker::CERT_RULE_START,
                $cert->isBooster()
            );
        } elseif ($cert->isNotComplete()) {
            $startDaysToAdd = ValidationRules::getValues(
                ValidationRules::VACCINE_START_DAY_NOT_COMPLETE,
                $cert->product
            );
        }

        $endDaysToAdd = 0;
        if ($cert->isComplete()) {
            $endDaysToAdd = $this->getVaccineCustomDaysFromValidationRules(
                $cert,
                $countryCode,
                VaccineChecker::CERT_RULE_END,
                $cert->isBooster()
            );
        } elseif ($cert->isNotComplete()) {
            $endDaysToAdd = ValidationRules::getValues(ValidationRules::VACCINE_END_DAY_NOT_COMPLETE, $cert->product);
        }

        $startDate = $vaccineDate->modify("+$startDaysToAdd days");
        $endDate = $vaccineDate->modify("+$endDaysToAdd days");
        $endDate = $endDate->SetTime(23, 59);

        if ($this->validationDate < $startDate) {
            return ValidationStatus::NOT_VALID_YET;
        }
        if ($this->validationDate > $endDate) {
            return ValidationStatus::EXPIRED;
        }
        if (!MedicinalProduct::isEma($cert->product, $cert->country)) {
            return ValidationStatus::NOT_VALID;
        }

        return ValidationStatus::VALID;
    }
}
