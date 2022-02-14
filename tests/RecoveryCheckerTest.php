<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GPDataTest;
use Herald\GreenPass\GreenPass;

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
        $testgp['r'][0]['fr'] = $data_greenpass->format('Y-m-d');
        $testgp['r'][0]['df'] = $data_greenpass->format('Y-m-d');
        $testgp['r'][0]['du'] = $data_scadenza_gp->format('Y-m-d');

        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * Test recovery super green pass
     */
    public function testSuperGreenPass()
    {
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $this->data_oggi->modify(self::DATE_A_MONTH_AGO);
        $data_scadenza_gp = $this->data_oggi->modify(self::DATE_IN_5_MONTHS);
        $testgp['r'][0]['fr'] = $data_greenpass->format('Y-m-d');
        $testgp['r'][0]['df'] = $data_greenpass->format('Y-m-d');
        $testgp['r'][0]['du'] = $data_scadenza_gp->format('Y-m-d');

        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, ValidationScanMode::CLASSIC_DGP);
        $this->assertEquals('VALID', $esito);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, ValidationScanMode::SUPER_DGP);
        $this->assertEquals('VALID', $esito);
    }

    /**
     * Test Booster Scan mode.
     */
    public function testBoosterScanMode()
    {
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $this->data_oggi->modify(self::DATE_A_MONTH_AGO);
        $data_scadenza_gp = $this->data_oggi->modify(self::DATE_IN_5_MONTHS);
        $testgp['r'][0]['fr'] = $data_greenpass->format('Y-m-d');
        $testgp['r'][0]['df'] = $data_greenpass->format('Y-m-d');
        $testgp['r'][0]['du'] = $data_scadenza_gp->format('Y-m-d');

        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, ValidationScanMode::CLASSIC_DGP);
        $this->assertEquals('VALID', $esito);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, ValidationScanMode::BOOSTER_DGP);
        $this->assertEquals('TEST_NEEDED', $esito);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, ValidationScanMode::SCHOOL_DGP);
        $this->assertEquals('VALID', $esito);
    }

    /**
     * Test recovery da domani.
     */
    public function testTomorrow()
    {
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $this->data_oggi->modify(self::DATE_TOMORROW);
        $testgp['r'][0]['df'] = $data_greenpass->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('NOT_VALID_YET', $esito);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, ValidationScanMode::SCHOOL_DGP);
        $this->assertEquals('NOT_VALID_YET', $esito);
    }

    /*
     * test recovery dopo 5 mesi con date_until scaduto
     */
    public function testDateUntil()
    {
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $this->data_oggi->modify(self::DATE_5_MONTHS_AGO);
        $testgp['r'][0]['fr'] = $data_greenpass->format('Y-m-d');
        $testgp['r'][0]['df'] = $data_greenpass->format('Y-m-d');
        $data_fine_validita = $this->data_oggi->modify(self::DATE_A_DAY_AGO);
        $testgp['r'][0]['du'] = $data_fine_validita->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, ValidationScanMode::BOOSTER_DGP);
        $this->assertEquals('TEST_NEEDED', $esito);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, ValidationScanMode::SCHOOL_DGP);
        $this->assertEquals('EXPIRED', $esito);
    }

    /*
     * test recovery dopo 121 giorni
     */
    public function testDueDate121()
    {
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $this->data_oggi->modify('-121 day');
        $testgp['r'][0]['fr'] = $data_greenpass->format('Y-m-d');
        $testgp['r'][0]['df'] = $data_greenpass->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, ValidationScanMode::SCHOOL_DGP);
        $this->assertEquals('EXPIRED', $esito);
    }

    /*
     * test recovery dopo 7 mesi
     */
    public function testDueDate()
    {
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $this->data_oggi->modify(self::DATE_7_MONTHS_AGO);
        $testgp['r'][0]['fr'] = $data_greenpass->format('Y-m-d');
        $testgp['r'][0]['df'] = $data_greenpass->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('EXPIRED', $esito);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, ValidationScanMode::SCHOOL_DGP);
        $this->assertEquals('EXPIRED', $esito);

        // test recovery dopo 7 mesi other country
        $testgp['r'][0]['co'] = 'GR';
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('EXPIRED', $esito);

        // other scandmode use Italy validation rules
        $esito = GreenPassCovid19Checker::verifyCert($greenpass, ValidationScanMode::SUPER_DGP);
        $this->assertEquals('EXPIRED', $esito);
        $esito = GreenPassCovid19Checker::verifyCert($greenpass, ValidationScanMode::BOOSTER_DGP);
        $this->assertEquals('EXPIRED', $esito);
    }
}