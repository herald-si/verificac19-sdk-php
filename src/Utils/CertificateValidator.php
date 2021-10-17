<?php
namespace Herald\GreenPass\Utils;

use Herald\GreenPass\Decoder\Decoder;
use Herald\GreenPass\Model\SimplePerson;
use Herald\GreenPass\Model\CertificateSimple;
use Herald\GreenPass\Validation\Covid19\ValidationStatus;

class CertificateValidator
{

    private $greenPassSimple;

    public function __construct($qrCodeText)
    {
        try{
            $greenPass = Decoder::qrcode($qrCodeText);
            $person = new SimplePerson($greenPass->holder->standardisedSurname, $greenPass->holder->surname, $greenPass->holder->standardisedForename, $greenPass->holder->forename);
            
            $this->greenPassSimple = new CertificateSimple($person, $greenPass->holder->dateOfBirth, $greenPass->checkValid());
        }catch (\Exception $e){
            $this->greenPassSimple = new CertificateSimple(null, null, ValidationStatus::NOT_EU_DCC);
        }
    }

    public function getCertificateSimple(): CertificateSimple
    {
        return $this->greenPassSimple;
    }
}