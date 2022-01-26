<?php

namespace Herald\GreenPass\Decoder;

use Herald\GreenPass\GPDataTest;
use Herald\GreenPass\Utils\FileUtils;

/**
 * Decoder test case.
 */
class DecoderTest extends \PHPUnit\Framework\TestCase
{
    public function testValidGreenPass()
    {
        $validQRCode = GPDataTest::$qrcode_certificate_valid_but_revoked;
        $greenPass = Decoder::qrcode($validQRCode);
        $this->assertEquals("ADOLF", $greenPass->holder->forename);
    }

    public function testInvalidHC1()
    {
        $notHC1 = GPDataTest::$qrcode_without_hc1;
        $greenPass = Decoder::qrcode($notHC1);
        $this->assertEquals("ADOLF", $greenPass->holder->forename);
    }

    public function testInvalidKid()
    {
        $invalidKidCert = GPDataTest::$qrcode_de_test_kid_invalid;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Public key not found list');
        Decoder::qrcode($invalidKidCert);
    }

    public function testChCert()
    {
        $current_dir[] = dirname(__FILE__);
        $current_dir[] = "..";
        $current_dir[] = "data";
        $cache_uri = join(DIRECTORY_SEPARATOR, $current_dir);
        FileUtils::overrideCacheFilePath($cache_uri);

        $ch_kid[] = "JLxre3vSwyg=";
        $chcert["JLxre3vSwyg="] = "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";

        $uri = FileUtils::getCacheFilePath(FileUtils::COUNTRY . "-gov-dgc-status.json");
        FileUtils::saveDataToFile($uri, json_encode($ch_kid));

        $uri = FileUtils::getCacheFilePath(FileUtils::COUNTRY . "-gov-dgc-certs.json");
        FileUtils::saveDataToFile($uri, json_encode($chcert));

        $validQRCode = GPDataTest::$qrcode_ch;
        $greenPass = Decoder::qrcode($validQRCode);
        $this->assertEquals("Martina", $greenPass->holder->forename);

        FileUtils::resetCacheFilePath();
    }
}
