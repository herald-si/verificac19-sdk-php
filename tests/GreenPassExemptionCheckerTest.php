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
        $dataGreenpass = $this->dataOggi->modify(self::DATE_A_MONTH_AGO);
        $dataScadenzaGp = $this->dataOggi->modify(self::DATE_IN_5_MONTHS);
        $testgp['e'][0]['df'] = $dataGreenpass->format('Y-m-d');
        $testgp['e'][0]['du'] = $dataScadenzaGp->format('Y-m-d');

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
        $dataGreenpass = $this->dataOggi->modify(self::DATE_A_MONTH_AGO);
        $dataScadenzaGp = $this->dataOggi->modify(self::DATE_IN_5_MONTHS);
        $testgp['e'][0]['df'] = $dataGreenpass->format('Y-m-d');
        $testgp['e'][0]['du'] = $dataScadenzaGp->format('Y-m-d');

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
        $dataGreenpass = $this->dataOggi->modify(self::DATE_TOMORROW);
        $testgp['e'][0]['df'] = $dataGreenpass->format('Y-m-d');
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
        $dataGreenpass = $this->dataOggi->modify(self::DATE_5_MONTHS_AGO);
        $testgp['e'][0]['df'] = $dataGreenpass->format('Y-m-d');
        $dataFineValidita = $this->dataOggi->modify(self::DATE_A_DAY_AGO);
        $testgp['e'][0]['du'] = $dataFineValidita->format('Y-m-d');
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
        $dataGreenpass = $this->dataOggi->modify(self::DATE_5_MONTHS_AGO);
        $testgp['e'][0]['df'] = $dataGreenpass->format('Y-m-d');
        $dataFineValidita = $this->dataOggi->modify(self::DATE_TOMORROW);
        $testgp['e'][0]['du'] = $dataFineValidita->format('Y-m-d');
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
        $dataGreenpass = $this->dataOggi->modify(self::DATE_7_MONTHS_AGO);
        $testgp['e'][0]['df'] = $dataGreenpass->format('Y-m-d');
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals('EXPIRED', $esito);
    }
}
