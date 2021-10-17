<?php
namespace Herald\GreenPass\GreenPassEntities;

/**
 *
 * @url https://github.com/ehn-dcc-development/ehn-dcc-valuesets/blob/main/disease-agent-targeted.json
 */
class Covid19 extends DiseaseAgent
{

    public $id = "840539006";

    public $name = "COVID-19";

    public $active = true;

    public $version = "http://snomed.info/sct/900000000000207008/version/20210131";

    public $system = "http://snomed.info/sct";

    public function id()
    {
        return $this->id;
    }

    public function display()
    {
        return $this->name;
    }

    public function active()
    {
        return $this->active;
    }

    public function version()
    {
        return $this->version;
    }

    public function system()
    {
        return $this->system;
    }
}
