<?php
namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPass;
use Herald\GreenPass\GPDataTest;
use Herald\GreenPass\Decoder\Decoder;

/**
 * GreenPassCovid19Checker test case.
 */
class GreenPassCovid19CheckerTest extends \PHPUnit\Framework\TestCase
{

    const DATE_A_MONTH_AGO = "-1 month";

    const DATE_5_MONTHS_AGO = "-5 month";

    const DATE_7_MONTHS_AGO = "-7 month";

    const DATE_TOMORROW = "+1 day";

    const DATE_A_DAY_AGO = "-1 day";

    const DATE_5_DAYS_AGO = "-5 day";

    const DATE_20_DAYS_AGO = "-20 day";

    const DATE_MORE_THAN_A_YEAR = "-366 day";

    public function testVerifyC19Cert()
    {
        // TEST CODICE DIVERSO DA C19
        $testgp = GPDataTest::$vaccine;
        $testgp["v"][0]["tg"] = "00000000000000";
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("NOT_COVID_19", $esito);
    }

    public function testVerifyCertTampone()
    {
        // TEST GREEN PASS DOPO 12 ORE
        $testgp = GPDataTest::$testresult;
        $data_oggi = new \DateTimeImmutable();
        $data_greenpass = $data_oggi->modify("-12 hour");
        $testgp["t"][0]["sc"] = $data_greenpass->format(\DateTime::ATOM);
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, "3G");
        $this->assertEquals("VALID", $esito);

        // TEST SUPER GREEN PASS (CON I DATI PRECEDENTI)
        $esito = GreenPassCovid19Checker::verifyCert($greenpass, "2G");
        $this->assertEquals("NOT_VALID", $esito);

        // TEST GREEN PASS DOPO 120 ORE
        $data_oggi = new \DateTimeImmutable();
        $data_greenpass = $data_oggi->modify("-120 hour");
        $testgp["t"][0]["sc"] = $data_greenpass->format(\DateTime::ATOM);
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass, "3G");
        $this->assertEquals("EXPIRED", $esito);
    }

    public function testVerifyCertVaccine()
    {
        $data_oggi = new \DateTimeImmutable();

        // TEST VACCINO NON IN LISTA
        $testgp = GPDataTest::$vaccine;
        $testgp["v"][0]["mp"] = "FakeVaccine";
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("NOT_RECOGNIZED", $esito);

        // TEST GREEN PASS COMPLETO DOPO UN MESE
        $testgp = GPDataTest::$vaccine;
        $data_greenpass = $data_oggi->modify(self::DATE_A_MONTH_AGO);
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("VALID", $esito);

        // TEST SUPER GREEN PASS (CON I DATI PRECEDENTI)
        $esito = GreenPassCovid19Checker::verifyCert($greenpass, "2G");
        $this->assertEquals("VALID", $esito);

        // TEST GREEN PASS COMPLETO DOPO UN MESE Sputnik-V NOT SAN MARINO
        $testgp = GPDataTest::$vaccine;
        $data_greenpass = $data_oggi->modify(self::DATE_A_MONTH_AGO);
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");
        $testgp["v"][0]["mp"] = "Sputnik-V";
        $testgp["v"][0]["co"] = "IT";
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("NOT_VALID", $esito);

        // TEST GREEN PASS COMPLETO DOPO UN MESE Sputnik-V IN SAN MARINO
        $testgp = GPDataTest::$vaccine;
        $data_greenpass = $data_oggi->modify(self::DATE_A_MONTH_AGO);
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");
        $testgp["v"][0]["mp"] = "Sputnik-V";
        $testgp["v"][0]["co"] = "SM";
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("VALID", $esito);

        // TEST PRIMA DOSE DOPO 5 GIORNI
        $testgp = GPDataTest::$vaccine;
        $data_greenpass = $data_oggi->modify(self::DATE_5_DAYS_AGO);
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");
        $testgp["v"][0]["dn"] = 1;
        $testgp["v"][0]["sd"] = 2;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("NOT_VALID_YET", $esito);

        // TEST PRIMA DOSE DOPO 20 GIORNI
        $testgp = GPDataTest::$vaccine;
        $data_greenpass = $data_oggi->modify(self::DATE_20_DAYS_AGO);
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");
        $testgp["v"][0]["dn"] = 1;
        $testgp["v"][0]["sd"] = 2;
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("PARTIALLY_VALID", $esito);

        // TEST DOSI COMPLETE DOPO UN ANNO E UN GIORNO
        $testgp = GPDataTest::$vaccine;
        $data_oggi = new \DateTimeImmutable();
        $data_greenpass = $data_oggi->modify(self::DATE_MORE_THAN_A_YEAR);
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("EXPIRED", $esito);
    }

    public function testVerifyCertRecovery()
    {
        $data_oggi = new \DateTimeImmutable();

        // TEST RECOVERY DOPO UN MESE
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $data_oggi->modify(self::DATE_A_MONTH_AGO);
        $testgp["r"][0]["fr"] = $data_greenpass->format("Y-m-d");
        $testgp["r"][0]["df"] = $data_greenpass->format("Y-m-d");
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("VALID", $esito);

        // TEST SUPER GREEN PASS (CON I DATI PRECEDENTI)
        $esito = GreenPassCovid19Checker::verifyCert($greenpass, "2G");
        $this->assertEquals("VALID", $esito);

        // TEST RECOVERY DA DOMANI
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $data_oggi->modify(self::DATE_TOMORROW);
        $testgp["r"][0]["df"] = $data_greenpass->format("Y-m-d");
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("NOT_VALID_YET", $esito);

        // TEST RECOVERY DOPO 5 MESI CON DATE_UNTIL SCADUTO
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $data_oggi->modify(self::DATE_5_MONTHS_AGO);
        $testgp["r"][0]["fr"] = $data_greenpass->format("Y-m-d");
        $testgp["r"][0]["df"] = $data_greenpass->format("Y-m-d");
        $data_fine_validita = $data_oggi->modify(self::DATE_A_DAY_AGO);
        $testgp["r"][0]["du"] = $data_fine_validita->format("Y-m-d");
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("PARTIALLY_VALID", $esito);

        // TEST RECOVERY DOPO 7 MESI
        $testgp = GPDataTest::$recovery;
        $data_greenpass = $data_oggi->modify(self::DATE_7_MONTHS_AGO);
        $testgp["r"][0]["fr"] = $data_greenpass->format("Y-m-d");
        $testgp["r"][0]["df"] = $data_greenpass->format("Y-m-d");
        $greenpass = new GreenPass($testgp);

        $esito = GreenPassCovid19Checker::verifyCert($greenpass);
        $this->assertEquals("NOT_VALID", $esito);
    }

    public function testVerifyCertBlacklist()
    {
        $greenpass = Decoder::qrcode(GPDataTest::$qrcode_certificate_valid_but_revoked);
        $esito = GreenPassCovid19Checker::verifyCert($greenpass, "3G");
        $this->assertEquals("NOT_VALID", $esito);
    }
}

