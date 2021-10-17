<?php
namespace Herald\GreenPass\Validation\Covid19;

/*
 * https://ministero-salute.github.io/it-dgc-verificac19-sdk-android/documentation/-verifica-c19%20-s-d-k/it.ministerodellasalute.verificaC19sdk.model/-certificate-status/index.html
 */
class ValidationStatus
{

    const VALID = "VALID";

    const PARTIALLY_VALID = "PARTIALLY_VALID";

    const NOT_FOUND = "NOT_FOUND";

    const NOT_COVID_19 = "NOT_COVID_19";

    const NOT_RECOGNIZED = "NOT_RECOGNIZED";

    const NOT_VALID_YET = "NOT_VALID_YET";

    const NOT_VALID = "NOT_VALID";

    const EXPIRED = "EXPIRED";
    
    const NOT_EU_DCC = "NOT_EU_DCC";
}