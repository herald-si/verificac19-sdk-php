<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\Country;

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

    public static function isEma(string $medicinalProduct, string $countryCode)
    {
        // isSputnikNotFromSanMarino ( https://github.com/ministero-salute/it-dgc-verificac19-sdk-android/commit/fee61a8ab86c6f4598afd6bbb48553081933f813 )
        if ($medicinalProduct == MedicinalProduct::SPUTNIK && $countryCode == Country::SAN_MARINO) {
            return true;
        }
        $list = ValidationRules::getValues(ValidationRules::EMA_VACCINES, ValidationRules::GENERIC_RULE);

        $ema = explode(';', $list);
        foreach ($ema as $emaProduct) {
            if ($medicinalProduct == $emaProduct) {
                return true;
            }
        }

        return false;
    }
}