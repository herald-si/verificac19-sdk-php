<?php

namespace Herald\GreenPass\Utils;

use Herald\GreenPass\Decoder\Decoder;
use Herald\GreenPass\Exceptions\InvalidPeriodException;
use Herald\GreenPass\GreenPass;
use Herald\GreenPass\Model\CertificateSimple;
use Herald\GreenPass\Model\SimplePerson;
use Herald\GreenPass\Validation\Covid19\ValidationScanMode;
use Herald\GreenPass\Validation\Covid19\ValidationStatus;

class CertificateValidator
{
    private $greenPassSimple;

    private $scanMode;

    private static function getSimplePersonFromGreenPass(GreenPass $greenPass): SimplePerson
    {
        $debug = EnvConfig::isDebugEnabled();
        $debugDisclaimer = 'DISABLE-DEBUG-MODE-IN-PRODUCTION';

        $standardizedFamilyName = $debug ? $debugDisclaimer : $greenPass->holder->standardisedSurname;
        $familyName = $debug ? $debugDisclaimer : $greenPass->holder->surname;
        $standardizedGivenName = $debug ? $debugDisclaimer : $greenPass->holder->standardisedForename;
        $givenName = $debug ? $debugDisclaimer : $greenPass->holder->forename;

        return new SimplePerson($standardizedFamilyName, $familyName, $standardizedGivenName, $givenName);
    }

    public function __construct(string $qrCodeText, string $scanMode = ValidationScanMode::CLASSIC_DGP)
    {
        $this->scanMode = $scanMode;
        try {
            $greenPass = Decoder::qrcode($qrCodeText);
            $person = self::getSimplePersonFromGreenPass($greenPass);
            $this->greenPassSimple = new CertificateSimple($person, $greenPass->holder->dateOfBirth, $greenPass->checkValid($this->scanMode));
        } catch (InvalidPeriodException $e) {
            $greenPass = $e->getGreenPass();
            $person = self::getSimplePersonFromGreenPass($greenPass);
            $this->greenPassSimple = new CertificateSimple($person, $greenPass->holder->dateOfBirth, ValidationStatus::NOT_VALID);
        } catch (\Exception $e) {
            $this->greenPassSimple = new CertificateSimple(null, null, (EnvConfig::isDebugEnabled() ? $e : ValidationStatus::NOT_EU_DCC));
        }
    }

    public function getCertificateSimple(): CertificateSimple
    {
        return $this->greenPassSimple;
    }
}