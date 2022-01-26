<?php

namespace Herald\GreenPass;

/**
 * GreenPass test case.
 */
class GreenPassTest extends \PHPUnit\Framework\TestCase
{
    /**
     *
     * @var GreenPass
     */
    private $greenPass;

    /**
     * Tests GreenPass->checkValid()
     */
    public function testCheckValidFunction()
    {
        $data_oggi = new \DateTimeImmutable();
        $testgp = GPDataTest::$vaccine;

        $data_greenpass = $data_oggi->modify("-1 month");
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");

        $this->greenPass = new GreenPass($testgp);
        $valid = $this->greenPass->checkValid("3G");

        $this->assertEquals("VALID", $valid);

        $testgp = GPDataTest::$vaccine;

        $data_greenpass = $data_oggi->modify("+1 day");
        $testgp["v"][0]["dt"] = $data_greenpass->format("Y-m-d");
        $testgp["v"][0]["dn"] = 1;

        $this->greenPass = new GreenPass($testgp);
        $valid = $this->greenPass->checkValid("3G");

        $this->assertEquals("NOT_VALID", $valid);
    }
}
