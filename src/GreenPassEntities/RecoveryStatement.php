<?php

namespace Herald\GreenPass\GreenPassEntities;

class RecoveryStatement extends CertificateType
{
    /**
     * The date of the holder's first positive NAAT test result.
     */
    public $date;

    /**
     * The first date on which the certificate is considered to be valid.
     */
    public $validFrom;

    /**
     * The last date on which the certificate is considered to be valid,
     * assigned by the certificate issuer.
     */
    public $validUntil;

    public function __construct(array $data)
    {
        $this->id = $data["r"][0]["ci"] ?? null;

        $this->diseaseAgent = DiseaseAgent::resolveById($data["r"][0]["tg"]);

        $this->country = $data["r"][0]["co"] ?? null;
        $this->issuer = $data["r"][0]["is"] ?? null;

        $this->date = ! empty($data["r"][0]["fr"]) ? new \DateTimeImmutable($data["r"][0]["fr"]) : null;
        $this->validFrom = ! empty($data["r"][0]["df"]) ? new \DateTimeImmutable($data["r"][0]["df"]) : null;
        $this->validUntil = ! empty($data["r"][0]["du"]) ? new \DateTimeImmutable($data["r"][0]["du"]) : null;
    }
}
