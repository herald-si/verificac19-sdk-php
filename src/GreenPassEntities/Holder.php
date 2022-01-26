<?php

namespace Herald\GreenPass\GreenPassEntities;

class Holder
{
    public $surname;

    public $standardisedSurname;

    public $forename;

    public $standardisedForename;

    public $dateOfBirth;

    public function __construct($data)
    {
        $this->dateOfBirth = ! empty($data["dob"] ?? null) ? new \DateTimeImmutable($data["dob"]) : null;

        $this->surname = $data["nam"]["fn"] ?? null;
        $this->standardisedSurname = $data["nam"]["fnt"] ?? null;
        $this->forename = $data["nam"]["gn"] ?? null;
        $this->standardisedForename = $data["nam"]["gnt"] ?? null;
    }
}
