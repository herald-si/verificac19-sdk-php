<?php
namespace Herald\GreenPass;

use Herald\GreenPass\Utils\CertificateValidator;
use Herald\GreenPass\Utils\EnvConfig;

/**
 * GreenPass test case.
 */
class CertificateValidatorTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Tests name
     */
    public function testCertificateValidatorDecoder()
    {
        $validQRCode = GPDataTest::$qrcode_certificate_valid_but_revoked;
        $decoder = new CertificateValidator($validQRCode);

        $this->assertEquals("ADOLF", $decoder->getCertificateSimple()->person->givenName);
    }

    /**
     * Tests debug mode on/off
     */
    public function testDebugModeDecoder()
    {
        $validQRCode = GPDataTest::$qrcode_certificate_valid_but_revoked;

        EnvConfig::enableDebugMode();
        $decoder = new CertificateValidator($validQRCode);

        $this->assertEquals("DISABLE-DEBUG-MODE-IN-PRODUCTION", $decoder->getCertificateSimple()->person->givenName);

        EnvConfig::disableDebugMode();
        $decoder = new CertificateValidator($validQRCode);

        $this->assertEquals("ADOLF", $decoder->getCertificateSimple()->person->givenName);
    }
}

