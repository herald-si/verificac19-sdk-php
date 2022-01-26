<?php

namespace Herald\GreenPass\Model;

class SimplePerson
{
    public $standardizedFamilyName;

    public $familyName;

    public $standardisedGivenName;

    public $givenName;

    public function __construct($standardizedFamilyName, $familyName, $standardisedGivenName, $givenName)
    {
        $this->standardizedFamilyName = $standardizedFamilyName;
        $this->familyName = $familyName;
        $this->standardisedGivenName = $standardisedGivenName;
        $this->givenName = $givenName;
    }
}
