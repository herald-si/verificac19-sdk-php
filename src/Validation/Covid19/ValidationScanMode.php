<?php

namespace Herald\GreenPass\Validation\Covid19;

/*
 * https://github.com/ministero-salute/it-dgc-verificac19-sdk-android/commit/d0a3da2a0256a24ae4e32b039484436619e4a835
 */
class ValidationScanMode
{
    public const SUPER_DGP = '2G';

    public const CLASSIC_DGP = '3G';

    public const BOOSTER_DGP = 'BOOSTED';

    public const WORK_DGP = 'WORK';

    public const SCHOOL_DGP = 'SCHOOL';

}