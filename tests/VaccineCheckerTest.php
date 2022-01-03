<?php
namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPass;
use Herald\GreenPass\GPDataTest;
use Herald\GreenPass\Decoder\Decoder;
use Herald\GreenPass\Validation\Covid19\GreenPassCovid19CheckerTest;

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
        $data_oggi = new \DateTimeImmutable();

        $testgp = GPDataTest::$vaccine;
        $testgp["v"][0]["mp"] = "FakeVaccine";
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("NOT_RECOGNIZED", $esito);
    }

    /*
     * Test vaccino dopo un mese
     */
    public function testVaccineAfterAMonth()
    {
        $testgp = GPDataTest::$vaccine;
        $data_greenpass = $this->data_oggi->modify(self::DATE_A_MONTH_AGO);
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("VALID", $esito);
    }

    /*
     * Test super GreenPass
     */
    public function testSuperGreenPass()
    {
        $testgp = GPDataTest::$vaccine;
        $data_greenpass = $this->data_oggi->modify(self::DATE_A_MONTH_AGO);
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");
        $greenpass = new GreenPass($testgp);
        
        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("VALID", $esito);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, "2G");
        $this->assertEquals("VALID", $esito);
    }
    

    /*
     * Test vaccino Sputnik-V dopo un mese non a San Marino
     */
    public function testSputnikNotSM()
    {
        $testgp = GPDataTest::$vaccine;
        $data_greenpass = $this->data_oggi->modify(self::DATE_A_MONTH_AGO);
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");
        $testgp["v"][0]["mp"] = "Sputnik-V";
        $testgp["v"][0]["co"] = "IT";
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("NOT_VALID", $esito);
    }

    /*
     * Test vaccino Sputnik-V dopo un mese a San Marino
     */
    public function testSputnikSM()
    {
        $testgp = GPDataTest::$vaccine;
        $data_greenpass = $this->data_oggi->modify(self::DATE_A_MONTH_AGO);
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");
        $testgp["v"][0]["mp"] = "Sputnik-V";
        $testgp["v"][0]["co"] = "SM";
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("VALID", $esito);
    }

    /*
     * Test prima dose dopo 5 giorni
     */
    public function testNotComplete5days()
    {
        $testgp = GPDataTest::$vaccine;
        $data_greenpass = $this->data_oggi->modify(self::DATE_5_DAYS_AGO);
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");
        $testgp["v"][0]["dn"] = 1;
        $testgp["v"][0]["sd"] = 2;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("NOT_VALID_YET", $esito);
    }

    /*
     * Test prima dose dopo 20 giorni
     */
    public function testNotComplete20days()
    {
        // TEST PRIMA DOSE DOPO 20 GIORNI
        $testgp = GPDataTest::$vaccine;
        $data_greenpass = $this->data_oggi->modify(self::DATE_20_DAYS_AGO);
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");
        $testgp["v"][0]["dn"] = 1;
        $testgp["v"][0]["sd"] = 2;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("PARTIALLY_VALID", $esito);
    }

    /*
     * Test seconda dose dopo 1 anno e 1 giorno
     */
    public function testCompleteMoreThanAYear()
    {
        $testgp = GPDataTest::$vaccine;
        $data_oggi = new \DateTimeImmutable();
        $data_greenpass = $this->data_oggi->modify(self::DATE_MORE_THAN_A_YEAR);
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("EXPIRED", $esito);
    }
}

