<?php
namespace Herald\GreenPass\Decoder;

use Herald\GreenPass\GPDataTest;

/**
 * Decoder test case.
 */
class DecoderTest extends \PHPUnit\Framework\TestCase
{

    public function testValidGreenPass()
    {
        $validQRCode = GPDataTest::$qrcode_certificate_valid_but_revoked;
        $greenPass = Decoder::qrcode($validQRCode);
        $this->assertEquals($greenPass->holder->forename, "ADOLF");
    }

    public function testInvalidHC1()
    {
        $notHC1 = GPDataTest::$qrcode_new_zeland_gp;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid HC1 Header');
        $greenPass = Decoder::qrcode($notHC1);
    }

    public function testInvalidKid()
    {
        $invalidKidCert = GPDataTest::$qrcode_de_test_kid_invalid;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Public key not found list');
        $greenPass = Decoder::qrcode($invalidKidCert);
    }
}

