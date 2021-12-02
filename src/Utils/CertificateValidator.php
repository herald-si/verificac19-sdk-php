<?php
namespace Herald\GreenPass\Utils;

use Herald\GreenPass\Decoder\Decoder;
use Herald\GreenPass\Model\SimplePerson;
use Herald\GreenPass\Model\CertificateSimple;
use Herald\GreenPass\Validation\Covid19\ValidationStatus;
use Herald\GreenPass\Validation\Covid19\ValidationScanMode;

class CertificateValidator
{

    private $greenPassSimple;
    
    private String $scanMode;

    public function __construct(String $qrCodeText, String $scanMode = ValidationScanMode::CLASSIC_DGP)
    {
        $this->scanMode = $scanMode;
        try{
            $greenPass = Decoder::qrcode($qrCodeText);
            $person = new SimplePerson($greenPass->holder->standardisedSurname, $greenPass->holder->surname, $greenPass->holder->standardisedForename, $greenPass->holder->forename);
            
            $this->greenPassSimple = new CertificateSimple($person, $greenPass->holder->dateOfBirth, $greenPass->checkValid($this->scanMode));
        }catch (\Exception $e){
            $this->greenPassSimple = new CertificateSimple(null, null, ValidationStatus::NOT_EU_DCC);
        }
    }

    public function getCertificateSimple(): CertificateSimple
    {
        return $this->greenPassSimple;
    }
}