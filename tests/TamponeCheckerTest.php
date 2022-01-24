<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GPDataTest;
use Herald\GreenPass\GreenPass;

/**
 * TamponeCheckerTest test case.
 */
class TamponeCheckerTest extends GreenPassCovid19CheckerTest
{
    /**
     * Test tampone dopo 12 ore.
     */
    public function testVerifyCertTampone()
    {
        $testgp = GPDataTest::$testresult;

        $data_greenpass = $this->data_oggi->modify(self::DATE_12_HOURS_AGO);
        $testgp['t'][0]['sc'] = $data_greenpass->format(\DateTime::ATOM);
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, '3G');
        $this->assertEquals('VALID', $esito);
    }

    /**
     * Test scan mode GreenPass.
     */
    public function testScanModeTampone()
    {
        $testgp = GPDataTest::$testresult;

        $data_greenpass = $this->data_oggi->modify(self::DATE_12_HOURS_AGO);
        $testgp['t'][0]['sc'] = $data_greenpass->format(\DateTime::ATOM);
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, '3G');
        $this->assertEquals('VALID', $esito);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, '2G');
        $this->assertEquals('NOT_VALID', $esito);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, 'BOOSTED');
        $this->assertEquals('NOT_VALID', $esito);
    }

    /**
     * Test work scan mode GreenPass.
     */
    public function testWorkScanModeTampone()
    {
        $testgp = GPDataTest::$testresult;

        $data_greenpass = $this->data_oggi->modify(self::DATE_12_HOURS_AGO);
        $testgp['t'][0]['sc'] = $data_greenpass->format(\DateTime::ATOM);

        $today_50_birthday = $this->data_oggi->modify(self::DATE_50_YEARS);

        // birthday 50 years old
        $testgp['dob'] = $today_50_birthday->format(\DateTime::ATOM);
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, 'WORK');
        $this->assertEquals('NOT_VALID', $esito);

        // the day after 50 years old birthday
        $today_50_birthday_plus_one = $today_50_birthday->modify(self::DATE_A_DAY_AGO);
        $testgp['dob'] = $today_50_birthday_plus_one->format(\DateTime::ATOM);
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, 'WORK');
        $this->assertEquals('NOT_VALID', $esito);

        // the day before 50 years old birthday
        $today_49_years_old = $today_50_birthday->modify(self::DATE_TOMORROW);
        $testgp['dob'] = $today_49_years_old->format(\DateTime::ATOM);
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, 'WORK');
        $this->assertEquals('VALID', $esito);

        // fixed young date
        $testgp['dob'] = '2000-01-01';
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, 'WORK');
        $this->assertEquals('VALID', $esito);

        // fixed old date
        $testgp['dob'] = '1930-01-01';
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, 'WORK');
        $this->assertEquals('NOT_VALID', $esito);
    }

    /**
     * Test tampone dopo 120 ore.
     */
    public function testTamponeScaduto()
    {
        $testgp = GPDataTest::$testresult;

        $data_greenpass = $this->data_oggi->modify('-120 hour');
        $testgp['t'][0]['sc'] = $data_greenpass->format(\DateTime::ATOM);
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, '3G');
        $this->assertEquals('EXPIRED', $esito);
    }
}
