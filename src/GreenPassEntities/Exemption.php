<?php

namespace Herald\GreenPass\GreenPassEntities;

class Exemption extends CertificateType
{
    /**
     * The first date on which the exemption is considered to be valid.
     */
    public $validFrom;

    /**
     * The last date on which the exemption is considered to be valid,
     * assigned by the certificate issuer.
     */
    public $validUntil;

    public function __construct(array $data)
    {
        $this->id = $data['e'][0]['ci'] ?? null;

        $this->diseaseAgent = DiseaseAgent::resolveById($data['e'][0]['tg']);

        $this->country = $data['e'][0]['co'] ?? null;
        $this->issuer = $data['e'][0]['is'] ?? null;

        $this->validFrom = !empty($data['e'][0]['df']) ? new \DateTimeImmutable($data['e'][0]['df']) : null;
        $this->validUntil = !empty($data['e'][0]['du']) ? new \DateTimeImmutable($data['e'][0]['du']) : null;
    }
}
