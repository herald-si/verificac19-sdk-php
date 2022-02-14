<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPassEntities\Country;
use Herald\GreenPass\Utils\EndpointService;

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

    public const VACCINE_END_DAY_COMPLETE_EXTENDED_EMA = 'vaccine_end_day_complete_extended_EMA';

    public const EMA_VACCINES = 'EMA_vaccines';
    public const BLACK_LIST_UVCI = 'black_list_uvci';

    public const VACCINE_MANDATORY_AGE = 50;

    public const DEFAULT_DAYS_SCHOOL = 120;
    public const DEFAULT_DAYS_START = 0;
    public const DEFAULT_DAYS_START_JJ = 15;
    public const DEFAULT_DAYS_END_IT = 180;
    public const DEFAULT_DAYS_END_NOT_IT = 270;
    public const  CERT_RULE_START = 'START_DAY';
    public const  CERT_RULE_END = 'END_DAY';

    public const GENERIC_RULE = 'GENERIC';

    public static function convertRuleNameToConstant($ruleName)
    {
        return constant("self::$ruleName");
    }

    /**
     * Get validation rules from rule name and type.
     *
     * @param string $rule
     *                     rule name
     * @param string $type
     *                     rule type
     *
     * @return string
     *                rule value if set, ValidationStatus::NOT_FOUND otherwise
     */
    public static function getValues(string $rule, string $type)
    {
        $validity_rules = EndpointService::getValidationRules();
        $value = ValidationStatus::NOT_FOUND;
        foreach ($validity_rules as $item) {
            if (($item->name == $rule) && ($item->type == $type)) {
                $value = $item->value;
                break;
            }
        }

        return $value;
    }

    /**
     * Get default days check.
     */
    public static function getDefaultValidationDays(string $startEnd, string $country): int
    {
        $default = ValidationRules::DEFAULT_DAYS_START;

        if ($startEnd == self::CERT_RULE_END) {
            $default = ($country == Country::ITALY) ? ValidationRules::DEFAULT_DAYS_END_IT : ValidationRules::DEFAULT_DAYS_END_NOT_IT;
        }

        return $default;
    }

    public static function getEndDaySchool(string $rule, string $type)
    {
        $days = ValidationRules::getValues($rule, $type);
        if ($days == ValidationStatus::NOT_FOUND) {
            $days = ValidationRules::DEFAULT_DAYS_SCHOOL;
        }

        return $days;
    }
}