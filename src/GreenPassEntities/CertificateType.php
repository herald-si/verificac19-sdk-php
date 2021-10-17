<?php
namespace Herald\GreenPass\GreenPassEntities;

abstract class CertificateType
{

    /**
     * Unique certificate identifier (UVCI).
     *
     * @var string|null
     */
    public $id;

    /**
     * Disease or agent from which the holder has recovered.
     *
     * @var DiseaseAgent
     */
    public $diseaseAgent;

    /**
     * Member State or third country in which the vaccine
     * was administered or the test was carried out.
     *
     * @var string|null
     */
    public $country;

    /**
     * Certificate issuer
     *
     * @var string|null
     */
    public $issuer;
}
