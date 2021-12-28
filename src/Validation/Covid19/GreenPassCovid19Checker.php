<?php
namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\Country;
use Herald\GreenPass\GreenPassEntities\VaccinationDose;
use Herald\GreenPass\GreenPassEntities\TestResult;
use Herald\GreenPass\GreenPassEntities\RecoveryStatement;
use Herald\GreenPass\GreenPassEntities\Covid19;
use Herald\GreenPass\GreenPassEntities\TestResultType;
use Herald\GreenPass\GreenPassEntities\TestType;
use Herald\GreenPass\GreenPass;

class GreenPassCovid19Checker
{

    public static function verifyCert(GreenPass $greenPass, String $scanMode = ValidationScanMode::CLASSIC_DGP)
    {
        $cert = $greenPass->certificate;

        if (! self::verifyDiseaseAgent($cert->diseaseAgent)) {
            return ValidationStatus::NOT_COVID_19;
        }

        $data_oggi = new \DateTime();

        $certificateId = self::extractUVCI($greenPass);

        if (empty($certificateId)) {
            return ValidationStatus::NOT_VALID;
        }

        if (self::checkInBlackList($certificateId)) {
            return ValidationStatus::NOT_VALID;
        }

        if (CertificateRevocationList::DRL_SYNC_ACTIVE && self::checkInDrl($certificateId)) {
            return ValidationStatus::REVOKED;
        }

        // vaccino effettuato
        if ($cert instanceof VaccinationDose) {
            return self::verifyVaccinationDose($cert, $data_oggi);
        }

        // tampone effettuato
        if ($cert instanceof TestResult) {
            // if scan mode Super Green Pass, TestResult is non a valid GP
            if ($scanMode == ValidationScanMode::SUPER_DGP) {
                return ValidationStatus::NOT_VALID;
            }
            return self::verifyTestResults($cert, $data_oggi);
        }

        // guarigione avvenuta
        if ($cert instanceof RecoveryStatement) {
            return self::verifyRecoveryStatement($cert, $data_oggi);
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

    private static function verifyVaccinationDose(VaccinationDose $cert, \DateTime $validation_date)
    {
        $esiste_vaccino = self::getValueFromValidationRules(ValidationRules::VACCINE_END_DAY_COMPLETE, $cert->product);
        if ($esiste_vaccino == ValidationStatus::NOT_FOUND) {
            return ValidationStatus::NOT_RECOGNIZED;
        }
        // isSputnikNotFromSanMarino ( https://github.com/ministero-salute/it-dgc-verificac19-sdk-android/commit/fee61a8ab86c6f4598afd6bbb48553081933f813 )
        $isSputnikNotFromSanMarino = ($cert->product == MedicinalProduct::SPUTNIK && $cert->country != Country::SanMarino);
        if ($isSputnikNotFromSanMarino) {
            return ValidationStatus::NOT_VALID;
        }

        if ($cert->doseGiven < $cert->totalDoses) {
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

            return ValidationStatus::PARTIALLY_VALID;
        }

        if ($cert->doseGiven >= $cert->totalDoses) {
            $giorni_min_valido = self::getValueFromValidationRules(ValidationRules::VACCINE_START_DAY_COMPLETE, $cert->product);
            $data_inizio_validita = $cert->date->modify("+$giorni_min_valido days");

            $giorni_max_valido = self::getValueFromValidationRules(ValidationRules::VACCINE_END_DAY_COMPLETE, $cert->product);
            $data_fine_validita = $cert->date->modify("+$giorni_max_valido days");

            if ($validation_date < $data_inizio_validita) {
                return ValidationStatus::NOT_VALID_YET;
            }
            if ($validation_date > $data_fine_validita) {
                return ValidationStatus::EXPIRED;
            }

            return ValidationStatus::VALID;
        }

        return ValidationStatus::NOT_RECOGNIZED;
    }

    private static function verifyTestResults(TestResult $cert, \DateTime $validation_date)
    {
        if ($cert->result == TestResultType::DETECTED) {
            return ValidationStatus::NOT_VALID;
        }

        if ($cert->type == TestType::MOLECULAR) {

            $ore_min_valido = self::getValueFromValidationRules(ValidationRules::MOLECULAR_TEST_START_HOUR, "GENERIC");
            $ora_inizio_validita = $cert->date->modify("+$ore_min_valido hours");

            $ore_max_valido = self::getValueFromValidationRules(ValidationRules::MOLECULAR_TEST_END_HOUR, "GENERIC");
            $ora_fine_validita = $cert->date->modify("+$ore_max_valido hours");

            if ($validation_date < $ora_inizio_validita) {
                return ValidationStatus::NOT_VALID_YET;
            }
            if ($validation_date > $ora_fine_validita) {
                return ValidationStatus::EXPIRED;
            }

            return ValidationStatus::VALID;
        }

        if ($cert->type == TestType::RAPID) {

            $ore_min_valido = self::getValueFromValidationRules(ValidationRules::RAPID_TEST_START_HOUR, "GENERIC");
            $ora_inizio_validita = $cert->date->modify("+$ore_min_valido hours");

            $ore_max_valido = self::getValueFromValidationRules(ValidationRules::RAPID_TEST_END_HOUR, "GENERIC");
            $ora_fine_validita = $cert->date->modify("+$ore_max_valido hours");

            if ($validation_date < $ora_inizio_validita) {
                return ValidationStatus::NOT_VALID_YET;
            }
            if ($validation_date > $ora_fine_validita) {
                return ValidationStatus::EXPIRED;
            }

            return ValidationStatus::VALID;
        }

        return ValidationStatus::NOT_RECOGNIZED;
    }

    private static function verifyRecoveryStatement(RecoveryStatement $cert, \DateTime $validation_date)
    {
        $start_day = self::getValueFromValidationRules(ValidationRules::RECOVERY_CERT_START_DAY, "GENERIC");
        $end_day = self::getValueFromValidationRules(ValidationRules::RECOVERY_CERT_END_DAY, "GENERIC");

        $valid_from = $cert->validFrom;

        $start_date = $valid_from->modify("+$start_day days");
        $end_date = $cert->validUntil;

        if ($start_date > $validation_date) {
            return ValidationStatus::NOT_VALID_YET;
        }

        if ($validation_date > $start_date->modify("+$end_day days")) {
            return ValidationStatus::NOT_VALID;
        }

        if ($validation_date > $end_date) {
            return ValidationStatus::PARTIALLY_VALID;
        }

        return ValidationStatus::VALID;
    }

    private static function checkInBlackList(string $kid): bool
    {
        $list = self::getValueFromValidationRules(ValidationRules::BLACK_LIST_UVCI, ValidationRules::BLACK_LIST_UVCI);
        if ($list != ValidationStatus::NOT_FOUND) {
            $blacklisted = explode(";", $list);
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
        $certificateIdentifier = "";
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
        return $certificateIdentifier;
    }
}