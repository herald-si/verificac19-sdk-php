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

        $dataGreenpass = $this->dataOggi->modify(self::DATE_12_HOURS_AGO);
        $testgp['t'][0]['sc'] = $dataGreenpass->format(\DateTime::ATOM);
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, '3G');
        $this->assertEquals('VALID', $esito);
    }

    /**
     * Test tampone dopo 120 ore.
     */
    public function testTamponeScaduto()
    {
        $testgp = GPDataTest::$testresult;

        $dataGreenpass = $this->dataOggi->modify('-120 hour');
        $testgp['t'][0]['sc'] = $dataGreenpass->format(\DateTime::ATOM);
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, '3G');
        $this->assertEquals('EXPIRED', $esito);
    }
}
