<?php

namespace Herald\GreenPass\Validation\Covid19;

/*
 * https://ministero-salute.github.io/it-dgc-verificac19-sdk-android/documentation/-verifica-c19%20-s-d-k/it.ministerodellasalute.verificaC19sdk.model/-certificate-status/index.html
 */
class ValidationStatus
{
    public const VALID = 'VALID';

    public const NOT_FOUND = 'NOT_FOUND';

    public const NOT_COVID_19 = 'NOT_COVID_19';

    public const NOT_RECOGNIZED = 'NOT_RECOGNIZED';

    public const NOT_VALID_YET = 'NOT_VALID_YET';

    public const NOT_VALID = 'NOT_VALID';

    public const EXPIRED = 'EXPIRED';

    public const NOT_EU_DCC = 'NOT_EU_DCC';

    // NEW STATUS: https://github.com/ministero-salute/it-dgc-verificac19-sdk-android/blob/cb669a952b6e5e33bbd45cead6c86ee8ba5827b7/sdk/src/main/java/it/ministerodellasalute/verificaC19sdk/model/CertificateStatus.kt

    public const REVOKED = 'REVOKED';

    public const TEST_NEEDED = 'TEST_NEEDED';

    // vedi it-dgc-verificac19-sdk-android/sdk/src/main/java/it/ministerodellasalute/verificaC19sdk/model/VerificationViewModel.kt fullModel
    public static function greenpassStatusAnonymizer($stato)
    {
        switch ($stato) {
            case ValidationStatus::NOT_VALID_YET:
                return 'NOT_VALID';
            default:
                return $stato;
        }
    }
}