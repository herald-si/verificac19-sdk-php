<?php
namespace Herald\GreenPass\Utils;

use Herald\GreenPass\Decoder\Decoder;
use Herald\GreenPass\Model\SimplePerson;
use Herald\GreenPass\Model\CertificateSimple;

class CertificateValidator
{

    private $greenPass;

    public function __construct($qrCodeText)
    {
        $this->greenPass = Decoder::qrcode($qrCodeText);
    }

    public function getCertificateSimple()
    {
        $person = new SimplePerson($this->greenPass->holder->standardisedSurname, $this->greenPass->holder->surname, $this->greenPass->holder->standardisedForename, $this->greenPass->holder->forename);
        return new CertificateSimple($person, $this->greenPass->holder->dateOfBirth, $this->greenPass->checkValid());
    }
}