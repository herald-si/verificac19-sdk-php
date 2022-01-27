<?php

namespace Herald\GreenPass\Model;

class CertificateSimple
{
    public $person;

    public $dateOfBirth;

    public $certificateStatus;

    public $timeStamp;

    public function __construct($simplePerson, $dateOfBirth, $certificateStatus)
    {
        $this->person = $simplePerson;
        $this->dateOfBirth = $dateOfBirth;
        $this->certificateStatus = $certificateStatus;
        $this->timeStamp = new \DateTimeImmutable();
    }
}
