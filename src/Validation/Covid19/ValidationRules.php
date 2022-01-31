<?php

namespace Herald\GreenPass\Validation\Covid19;

class ValidationRules
{
    public const RECOVERY_CERT_START_DAY = 'recovery_cert_start_day';
    public const RECOVERY_CERT_END_DAY = 'recovery_cert_end_day';

    public const RECOVERY_CERT_PV_START_DAY = 'recovery_pv_cert_start_day';
    public const RECOVERY_CERT_PV_END_DAY = 'recovery_pv_cert_end_day';

    public const RECOVERY_CERT_END_DAY_SCHOOL = 'recovery_cert_end_day_school';

    public const MOLECULAR_TEST_START_HOUR = 'molecular_test_start_hours';
    public const MOLECULAR_TEST_END_HOUR = 'molecular_test_end_hours';

    public const RAPID_TEST_START_HOUR = 'rapid_test_start_hours';
    public const RAPID_TEST_END_HOUR = 'rapid_test_end_hours';

    public const VACCINE_START_DAY_NOT_COMPLETE = 'vaccine_start_day_not_complete';
    public const VACCINE_END_DAY_NOT_COMPLETE = 'vaccine_end_day_not_complete';
    public const VACCINE_START_DAY_COMPLETE = 'vaccine_start_day_complete';
    public const VACCINE_END_DAY_COMPLETE = 'vaccine_end_day_complete';

    public const VACCINE_END_DAY_SCHOOL = 'vaccine_end_day_school';

    public const VACCINE_START_DAY_COMPLETE_IT = 'vaccine_start_day_complete_IT';
    public const VACCINE_END_DAY_COMPLETE_IT = 'vaccine_end_day_complete_IT';
    public const VACCINE_START_DAY_BOOSTER_IT = 'vaccine_start_day_booster_IT';
    public const VACCINE_END_DAY_BOOSTER_IT = 'vaccine_end_day_booster_IT';

    public const VACCINE_START_DAY_COMPLETE_NOT_IT = 'vaccine_start_day_complete_NOT_IT';
    public const VACCINE_END_DAY_COMPLETE_NOT_IT = 'vaccine_end_day_complete_NOT_IT';
    public const VACCINE_START_DAY_BOOSTER_NOT_IT = 'vaccine_start_day_booster_NOT_IT';
    public const VACCINE_END_DAY_BOOSTER_NOT_IT = 'vaccine_end_day_booster_NOT_IT';

    public const RECOVERY_CERT_START_DAY_IT = 'recovery_cert_start_day_IT';
    public const RECOVERY_CERT_END_DAY_IT = 'recovery_cert_end_day_IT';
    public const RECOVERY_CERT_START_DAY_NOT_IT = 'recovery_cert_start_day_NOT_IT';
    public const RECOVERY_CERT_END_DAY_NOT_IT = 'recovery_cert_end_day_NOT_IT';

    public const BLACK_LIST_UVCI = 'black_list_uvci';

    public const VACCINE_MANDATORY_AGE = 50;

    public const DEFAULT_DAYS_SCHOOL = 120;
    public const DEFAULT_DAYS_START = 0;
    public const DEFAULT_DAYS_START_JJ = 15;
    public const DEFAULT_DAYS_END_IT = 180;
    public const DEFAULT_DAYS_END_NOT_IT = 270;

    public const GENERIC_RULE = 'GENERIC';
}