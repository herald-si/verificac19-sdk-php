<?php
namespace Herald\GreenPass\GreenPassEntities;

class TestResult extends CertificateType
{

    /**
     * The type of the test used, based on the material targeted by the test.
     *
     * @var string|null
     */
    public $type;

    /**
     * The name of the nucleic acid amplification test (NAAT) used.
     * The name should include the name of the test manufacturer and the
     * commercial name of the test, separated by comma.
     *
     * For NAAT: the field is optional.
     * For RAT: the field should not be used, as the name of the test is
     * supplied indirectly through the test device identifier.
     *
     * @var string|null
     */
    public $name;

    /**
     * Rapid antigen test (RAT) device identifier from the JRC database.
     *
     * @var string|null
     */
    public $device;

    /**
     * Date and time of the test sample collection.
     */
    public $date;

    /**
     * The result of the test.
     *
     * @var string|null
     */
    public $result;

    /**
     * Name of the actor (centre/facility) that conducted the test.
     *
     * @var string|null
     */
    public $centre;

    public function __construct($data)
    {
        $this->id = $data["t"][0]["ci"] ?? null;

        $this->diseaseAgent = DiseaseAgent::resolveById($data["t"][0]["tg"]);

        $this->country = $data["t"][0]["co"] ?? null;
        $this->issuer = $data["t"][0]["is"] ?? null;

        $this->type = $data["t"][0]["tt"] ?? null;
        $this->name = $data["t"][0]["tm"] ?? null;
        $this->device = $data["t"][0]["ma"] ?? null;
        $this->date = ! empty($data["t"][0]["sc"]) ? new \DateTimeImmutable($data["t"][0]["sc"]) : null;
        $this->result = $data["t"][0]["tr"] ?? null;
        $this->centre = $data["t"][0]["tc"] ?? null;
    }
}
