<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GPDataTest;
use Herald\GreenPass\GreenPass;

/**
 * VaccineCheckerTest test case.
 */
class VaccineCheckerTest extends GreenPassCovid19CheckerTest
{
    /*
     * Test vaccino non in lista
     */
    public function testUnknownVaccine()
    {
        $testgp = GPDataTest::$vaccine;
        $testgp['v'][0]['mp'] = 'FakeVaccine';
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('EXPIRED', $esito);
    }

    /*
     * Test super GreenPass
     */
    public function testSuperGreenPass()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_A_MONTH_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * Test vaccino Sputnik-V dopo un mese non a San Marino
     */
    public function testSputnikNotSM()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_A_MONTH_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['mp'] = MedicinalProduct::SPUTNIK;
        $testgp['v'][0]['co'] = 'IT';
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('NOT_VALID', $esito);
    }

    /*
     * Test vaccino Sputnik-V dopo un mese a San Marino
     */
    public function testSputnikSM()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_A_MONTH_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['mp'] = MedicinalProduct::SPUTNIK;
        $testgp['v'][0]['co'] = 'SM';
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * Test prima dose dopo 5 giorni
     */
    public function testNotComplete5days()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_5_DAYS_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['dn'] = 1;
        $testgp['v'][0]['sd'] = 2;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('NOT_VALID_YET', $esito);
    }

    /*
     * Test completo dopo 1 anno e 1 giorno
     */
    public function testCompleteMoreThanAYear()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_MORE_THAN_A_YEAR);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('EXPIRED', $esito);
    }

    /*
     * Test JOHNSON Completo
     */
    public function testJohnsonCompleto()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_A_MONTH_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['mp'] = MedicinalProduct::JOHNSON;
        $testgp['v'][0]['dn'] = 1;
        $testgp['v'][0]['sd'] = 1;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * Test Other Parziale
     */
    public function testNotComplete()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_20_DAYS_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['dn'] = 1;
        $testgp['v'][0]['sd'] = 2;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * Test all Booster
     */
    public function testBoosterComplete()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_A_MONTH_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['dn'] = 2;
        $testgp['v'][0]['sd'] = 1;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * Test JOHNSON Booster
     */
    public function testBoosterJohnsonCompleto()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_A_MONTH_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['mp'] = MedicinalProduct::JOHNSON;
        $testgp['v'][0]['dn'] = 2;
        $testgp['v'][0]['sd'] = 2;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * Test Other Completo
     */
    public function testComplete()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_A_MONTH_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['dn'] = 2;
        $testgp['v'][0]['sd'] = 2;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
    * Test Other Completo
    */
    public function testCompleteAfter150Days()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_5_MONTHS_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['dn'] = 2;
        $testgp['v'][0]['sd'] = 2;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
        * Test Other Completo 7 mesi
        */
    public function testCompleteAfter7Month()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_7_MONTHS_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['dn'] = 2;
        $testgp['v'][0]['sd'] = 2;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('EXPIRED', $esito);

        $testgp['v'][0]['co'] = 'FR';
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('EXPIRED', $esito);
    }

    /*
     * Test Booster
     */
    public function testBoosterDose()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_A_MONTH_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['dn'] = 3;
        $testgp['v'][0]['sd'] = 2;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * Test Booster 5 month
     */
    public function testBoosterDoseAfter150Days()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_5_MONTHS_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['dn'] = 3;
        $testgp['v'][0]['sd'] = 2;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * Test Booster alt
     */
    public function testBoosterAltDose()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_A_MONTH_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['dn'] = 3;
        $testgp['v'][0]['sd'] = 3;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * Test Booster alt
     */
    public function testBoosterAltDose150Days()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_5_MONTHS_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['dn'] = 3;
        $testgp['v'][0]['sd'] = 3;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * Test Booster alt 7 mesi
     */
    public function testBoosterAltDose7Months()
    {
        $testgp = GPDataTest::$vaccine;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_7_MONTHS_AGO);
        $testgp['v'][0]['dt'] = $dataGreenpass->format('Y-m-d');
        $testgp['v'][0]['dn'] = 3;
        $testgp['v'][0]['sd'] = 3;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);

        $testgp['v'][0]['co'] = 'DE';
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }
}
