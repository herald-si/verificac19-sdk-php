<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GPDataTest;
use Herald\GreenPass\GreenPass;

/**
 * GreenPassCovid19Checker test case.
 */
class GreenPassCovid19CheckerTest extends \PHPUnit\Framework\TestCase
{
    public const DATE_IN_5_MONTHS = '+5 month';

    public const DATE_A_MONTH_AGO = '-1 month';

    public const DATE_5_MONTHS_AGO = '-5 month';

    public const DATE_7_MONTHS_AGO = '-7 month';

    public const DATE_TOMORROW = '+1 day';

    public const DATE_A_DAY_AGO = '-1 day';

    public const DATE_5_DAYS_AGO = '-5 day';

    public const DATE_20_DAYS_AGO = '-20 day';

    public const DATE_MORE_THAN_A_YEAR = '-366 day';

    public const DATE_50_YEARS = '-50 year';

    public const DATE_12_HOURS_AGO = '-12 hour';

    protected $data_oggi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->data_oggi = new \DateTimeImmutable();
    }

    public function testVerifyC19Cert()
    {
        // TEST CODICE DIVERSO DA C19
        $testgp = GPDataTest::$vaccine;
        $testgp['v'][0]['tg'] = '00000000000000';
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('NOT_COVID_19', $esito);
    }

    protected function tearDown(): void
    {
        $this->data_oggi = null;
        parent::tearDown();
    }
}
