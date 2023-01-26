<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GPDataTest;
use Herald\GreenPass\GreenPass;

/**
 * ExemptionCheckerTest test case.
 */
class GreenPassExemptionCheckerTest extends GreenPassCovid19CheckerTest
{
    /*
     * Test exemption dopo un mese
     */
    public function testVerifyCertExemption()
    {
        $testgp = GPDataTest::$exemption;
        $data_greenpass = $this->data_oggi->modify(self::DATE_A_MONTH_AGO);
        $data_scadenza_gp = $this->data_oggi->modify(self::DATE_IN_5_MONTHS);
        $testgp['e'][0]['df'] = $data_greenpass->format('Y-m-d');
        $testgp['e'][0]['du'] = $data_scadenza_gp->format('Y-m-d');

        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * Test exemption super green pass
     */
    public function testSuperGreenPass()
    {
        $testgp = GPDataTest::$exemption;
        $data_greenpass = $this->data_oggi->modify(self::DATE_A_MONTH_AGO);
        $data_scadenza_gp = $this->data_oggi->modify(self::DATE_IN_5_MONTHS);
        $testgp['e'][0]['df'] = $data_greenpass->format('Y-m-d');
        $testgp['e'][0]['du'] = $data_scadenza_gp->format('Y-m-d');

        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, '3G');
        $this->assertEquals('VALID', $esito);
    }

    /**
     * Test Booster Scan mode.
     */
    public function testBoosterScanMode()
    {
        $testgp = GPDataTest::$exemption;
        $data_greenpass = $this->data_oggi->modify(self::DATE_A_MONTH_AGO);
        $data_scadenza_gp = $this->data_oggi->modify(self::DATE_IN_5_MONTHS);
        $testgp['e'][0]['df'] = $data_greenpass->format('Y-m-d');
        $testgp['e'][0]['du'] = $data_scadenza_gp->format('Y-m-d');

        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, '3G');
        $this->assertEquals('VALID', $esito);
    }

    /**
     * Test exemption da domani.
     */
    public function testTomorrow()
    {
        $testgp = GPDataTest::$exemption;
        $data_greenpass = $this->data_oggi->modify(self::DATE_TOMORROW);
        $testgp['e'][0]['df'] = $data_greenpass->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('NOT_VALID_YET', $esito);
    }

    /*
     * test exemption dopo 5 mesi con date_until scaduto
     */
    public function testDateUntilScaduto()
    {
        $testgp = GPDataTest::$exemption;
        $data_greenpass = $this->data_oggi->modify(self::DATE_5_MONTHS_AGO);
        $testgp['e'][0]['df'] = $data_greenpass->format('Y-m-d');
        $data_fine_validita = $this->data_oggi->modify(self::DATE_A_DAY_AGO);
        $testgp['e'][0]['du'] = $data_fine_validita->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('EXPIRED', $esito);
    }

    /*
     * test exemption dopo 5 mesi con date_until valido
     */
    public function testDateUntilValid()
    {
        $testgp = GPDataTest::$exemption;
        $data_greenpass = $this->data_oggi->modify(self::DATE_5_MONTHS_AGO);
        $testgp['e'][0]['df'] = $data_greenpass->format('Y-m-d');
        $data_fine_validita = $this->data_oggi->modify(self::DATE_TOMORROW);
        $testgp['e'][0]['du'] = $data_fine_validita->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * test exemption dopo 7 mesi
     */
    public function testDueDate()
    {
        $testgp = GPDataTest::$exemption;
        $data_greenpass = $this->data_oggi->modify(self::DATE_7_MONTHS_AGO);
        $testgp['e'][0]['df'] = $data_greenpass->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('EXPIRED', $esito);
    }
}
