<?php

namespace Herald\GreenPass\Validation\Covid19;

use Herald\GreenPass\GreenPass;
use Herald\GreenPass\GPDataTest;
use Herald\GreenPass\Decoder\Decoder;

/**
 * BlacklistCheckerTest test case.
 */
class BlacklistCheckerTest extends \PHPUnit\Framework\TestCase
{
    public function testVerifyCertBlacklist()
    {
        $greenpass = Decoder::qrcode(GPDataTest::$qrcode_certificate_valid_but_revoked);
        $esito = GreenPassCovid19Checker::verifyCert($greenpass, "3G");
        $this->assertEquals("NOT_VALID", $esito);
    }
}
