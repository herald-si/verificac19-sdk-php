<?php
namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPass;
use Herald\GreenPass\GPDataTest;
use Herald\GreenPass\Decoder\Decoder;
use Herald\GreenPass\Validation\Covid19\GreenPassCovid19CheckerTest;

/**
 * RecoveryCheckerTest test case.
 */
class RecoveryCheckerTest extends GreenPassCovid19CheckerTest
{

    /*
     * Test recovery dopo un mese
     */
    public function testVerifyCertRecovery()
    {
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $this->data_oggi->modify(self::DATE_A_MONTH_AGO);
        $data_scadenza_gp = $this->data_oggi->modify(self::DATE_IN_5_MONTHS);
        $testgp["r"][0]["fr"] = $data_greenpass->format("Y-m-d");
        $testgp["r"][0]["df"] = $data_greenpass->format("Y-m-d");
        $testgp["r"][0]["du"] = $data_scadenza_gp->format("Y-m-d");

        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("VALID", $esito);
    }

    /*
     * Test recovery super green pass
     */
    public function testSuperGreenPass()
    {
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $this->data_oggi->modify(self::DATE_A_MONTH_AGO);
        $data_scadenza_gp = $this->data_oggi->modify(self::DATE_IN_5_MONTHS);
        $testgp["r"][0]["fr"] = $data_greenpass->format("Y-m-d");
        $testgp["r"][0]["df"] = $data_greenpass->format("Y-m-d");
        $testgp["r"][0]["du"] = $data_scadenza_gp->format("Y-m-d");

        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, "2G");
        $this->assertEquals("VALID", $esito);
    }

    /**
     * Test recovery da domani
     */
    public function testTomorrow()
    {
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $this->data_oggi->modify(self::DATE_TOMORROW);
        $testgp["r"][0]["df"] = $data_greenpass->format("Y-m-d");
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("NOT_VALID_YET", $esito);
    }

    /*
     * test recovery dopo 5 mesi con date_until scaduto
     */
    public function testDateUntil()
    {
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $this->data_oggi->modify(self::DATE_5_MONTHS_AGO);
        $testgp["r"][0]["fr"] = $data_greenpass->format("Y-m-d");
        $testgp["r"][0]["df"] = $data_greenpass->format("Y-m-d");
        $data_fine_validita = $this->data_oggi->modify(self::DATE_A_DAY_AGO);
        $testgp["r"][0]["du"] = $data_fine_validita->format("Y-m-d");
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("PARTIALLY_VALID", $esito);
    }

    /*
     * test recovery dopo 7 mesi
     */
    public function testDueDate()
    {
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $this->data_oggi->modify(self::DATE_7_MONTHS_AGO);
        $testgp["r"][0]["fr"] = $data_greenpass->format("Y-m-d");
        $testgp["r"][0]["df"] = $data_greenpass->format("Y-m-d");
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("NOT_VALID", $esito);
    }
}

