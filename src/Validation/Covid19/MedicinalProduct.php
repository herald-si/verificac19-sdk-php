<?php

namespace Herald\GreenPass\Validation\Covid19;

class MedicinalProduct
{
    public const JOHNSON = 'EU/1/20/1525';
    public const SPUTNIK = 'Sputnik-V';
    public const  MODERNA = 'EU/1/20/1507';
    public const  PFIZER = 'EU/1/20/1528';
    public const  ASTRAZENECA = 'EU/1/21/1529';
    public const  COVISHIELD = 'Covishield';
    public const  R_COVI = 'R-COVI';
    public const  COVID19_RECOMBINANT = 'Covid-19-recombinant';

    public function isEma(string $medicinalProduct)
    {
        $list = self::getEmaList();
        $ema = explode(';', $list);
        foreach ($ema as $emaProduct) {
            if ($medicinalProduct == $emaProduct) {
                return true;
            }
        }

        return false;
    }

    private static function getEmaList(): bool
    {
        return  'EU/1/20/1525;EU/1/20/1507;EU/1/20/1528;EU/1/21/1529;Covishield;R-COVI;Covid-19-recombinant';
    }
}