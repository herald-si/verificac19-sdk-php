<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPass;
use Herald\GreenPass\GreenPassEntities\Covid19;
use Herald\GreenPass\GreenPassEntities\Exemption;
use Herald\GreenPass\GreenPassEntities\RecoveryStatement;
use Herald\GreenPass\GreenPassEntities\TestResult;
use Herald\GreenPass\GreenPassEntities\VaccinationDose;
use Herald\GreenPass\Utils\EnvConfig;

class GreenPassCovid19Checker
{
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
            throw new  \InvalidArgumentException('Restricted scan mode, dont use in production');
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
            $vaccineValidator = new VaccineChecker($data_oggi, $scanMode, $greenPass->holder, $cert);

            return $vaccineValidator->checkCertificate();
        }

        // tampone effettuato
        if ($cert instanceof TestResult) {
            $testResultValidator = new TestResultChecker($data_oggi, $scanMode, $greenPass->holder, $cert);

            return $testResultValidator->checkCertificate();
        }

        // guarigione avvenuta
        if ($cert instanceof RecoveryStatement) {
            $recoveryValidator = new RecoveryChecker($cert, $data_oggi, $scanMode, $greenPass->holder, $greenPass->signingCertInfo);

            return $recoveryValidator->checkCertificate();
        }

        // esenzione
        if ($cert instanceof Exemption) {
            $excemptionValidator = new ExemptionChecker($data_oggi, $scanMode, $cert);

            return $excemptionValidator->checkCertificate();
        }

        return ValidationStatus::NOT_RECOGNIZED;
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

    private static function checkInBlackList(string $kid): bool
    {
        $list = ValidationRules::getValues(ValidationRules::BLACK_LIST_UVCI, ValidationRules::BLACK_LIST_UVCI);
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
}