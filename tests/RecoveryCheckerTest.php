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
        $dataGreenpass = $this->dataOggi->modify(self::DATE_A_MONTH_AGO);
        $dataScadenzaGp = $this->dataOggi->modify(self::DATE_IN_5_MONTHS);
        $testgp['r'][0]['fr'] = $dataGreenpass->format('Y-m-d');
        $testgp['r'][0]['df'] = $dataGreenpass->format('Y-m-d');
        $testgp['r'][0]['du'] = $dataScadenzaGp->format('Y-m-d');

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
        $dataGreenpass = $this->dataOggi->modify(self::DATE_A_MONTH_AGO);
        $dataScadenzaGp = $this->dataOggi->modify(self::DATE_IN_5_MONTHS);
        $testgp['r'][0]['fr'] = $dataGreenpass->format('Y-m-d');
        $testgp['r'][0]['df'] = $dataGreenpass->format('Y-m-d');
        $testgp['r'][0]['du'] = $dataScadenzaGp->format('Y-m-d');

        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, ValidationScanMode::CLASSIC_DGP);
        $this->assertEquals('VALID', $esito);
    }

    /**
     * Test recovery da domani.
     */
    public function testTomorrow()
    {
        $testgp = GPDataTest::$recovery;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_TOMORROW);
        $testgp['r'][0]['df'] = $dataGreenpass->format('Y-m-d');
        $testgp['r'][0]['fr'] = $dataGreenpass->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('NOT_VALID_YET', $esito);
    }

    /*
     * test recovery dopo 5 mesi con date_until scaduto
     */
    public function testDateUntil()
    {
        $testgp = GPDataTest::$recovery;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_5_MONTHS_AGO);
        $testgp['r'][0]['fr'] = $dataGreenpass->format('Y-m-d');
        $testgp['r'][0]['df'] = $dataGreenpass->format('Y-m-d');
        $dataFineValidita = $this->dataOggi->modify(self::DATE_A_DAY_AGO);
        $testgp['r'][0]['du'] = $dataFineValidita->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * test recovery dopo 121 giorni
     */
    public function testDueDate121()
    {
        $testgp = GPDataTest::$recovery;
        $dataGreenpass = $this->dataOggi->modify('-121 day');
        $testgp['r'][0]['fr'] = $dataGreenpass->format('Y-m-d');
        $testgp['r'][0]['df'] = $dataGreenpass->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('VALID', $esito);
    }

    /*
     * test recovery dopo 7 mesi
     */
    public function testDueDate()
    {
        $testgp = GPDataTest::$recovery;
        $dataGreenpass = $this->dataOggi->modify(self::DATE_7_MONTHS_AGO);
        $testgp['r'][0]['fr'] = $dataGreenpass->format('Y-m-d');
        $testgp['r'][0]['df'] = $dataGreenpass->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('EXPIRED', $esito);


        // test recovery dopo 7 mesi other country
        $testgp['r'][0]['co'] = 'GR';
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('EXPIRED', $esito);
    }
}
