<?php
namespace Herald\GreenPass\GreenPassEntities;

abstract class DiseaseAgent
{

    abstract public function id();

    abstract public function display();

    abstract public function active();

    abstract public function version();

    abstract public function system();

    /**
     * Resolve the disease class by ID.
     *
     * @param string $id
     */
    public static function resolveById($id)
    {
        switch ($id) {
            case "840539006":
                return new Covid19();
                break;
            default:
                return "";
        }
    }
}
