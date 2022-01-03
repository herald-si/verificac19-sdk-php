<?php
namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPass;
use Herald\GreenPass\GPDataTest;
use Herald\GreenPass\Decoder\Decoder;
use Herald\GreenPass\Validation\Covid19\GreenPassCovid19CheckerTest;

/**
 * TamponeCheckerTest test case.
 */
class TamponeCheckerTest extends GreenPassCovid19CheckerTest
{

    /**
     * Test tampone dopo 12 ore
     */
    public function testVerifyCertTampone()
    {
        $testgp = GPDataTest::$testresult;

        $data_greenpass = $this->data_oggi->modify("-12 hour");
        $testgp["t"][0]["sc"] = $data_greenpass->format(\DateTime::ATOM);
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, "3G");
        $this->assertEquals("VALID", $esito);
    }

    /**
     * Test scan mode GreenPass
     */
    public function testScanModeTampone()
    {
        $testgp = GPDataTest::$testresult;

        $data_greenpass = $this->data_oggi->modify("-12 hour");
        $testgp["t"][0]["sc"] = $data_greenpass->format(\DateTime::ATOM);
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, "3G");
        $this->assertEquals("VALID", $esito);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, "2G");
        $this->assertEquals("NOT_VALID", $esito);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, "BOOSTED");
        $this->assertEquals("NOT_VALID", $esito);
    }

    /**
     * Test tampone dopo 120 ore
     */
    public function testTamponeScaduto()
    {
        $testgp = GPDataTest::$testresult;

        $data_greenpass = $this->data_oggi->modify("-120 hour");
        $testgp["t"][0]["sc"] = $data_greenpass->format(\DateTime::ATOM);
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, "3G");
        $this->assertEquals("EXPIRED", $esito);
    }
}

