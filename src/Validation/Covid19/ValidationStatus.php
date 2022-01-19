<?php
namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\Validation\Covid19\ValidationScanMode;

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
    
    // NEW STATUS: https://github.com/ministero-salute/it-dgc-verificac19-sdk-android/blob/cb669a952b6e5e33bbd45cead6c86ee8ba5827b7/sdk/src/main/java/it/ministerodellasalute/verificaC19sdk/model/CertificateStatus.kt

    const REVOKED = "REVOKED";

    const TEST_NEEDED = "TEST_NEEDED";
    
    // vedi it-dgc-verificac19-sdk-android/sdk/src/main/java/it/ministerodellasalute/verificaC19sdk/model/VerificationViewModel.kt fullModel
    public static function greenpassStatusAnonymizer($stato, $scanMode)
    {
        switch ($stato) {
            
            case ValidationStatus::NOT_VALID_YET:
            case ValidationStatus::EXPIRED:
                return "NOT_VALID";
            case ValidationStatus::PARTIALLY_VALID && $scanMode != ValidationScanMode::BOOSTER_DGP:
                return "VALID";
            case ValidationStatus::PARTIALLY_VALID && $scanMode == ValidationScanMode::BOOSTER_DGP:
                return "TEST_NEEDED";
            default:
                return $stato;
        }
    }
}