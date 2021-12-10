<?php
declare(strict_types = 1);
namespace Herald\GreenPass\Decoder;

/**
 * Base45 test case
 */
class Base45Test extends \PHPUnit\Framework\TestCase
{

    /**
     *
     * @param
     *            $data
     *            
     * @dataProvider dataProvider
     */
    public function testEncoding($input, $output)
    {
        $result = (new Base45())->encode($input);
        $this->assertEquals($output, $result);
    }

    /**
     *
     * @param
     *            $data
     *            
     * @dataProvider dataProvider
     */
    public function testDecoding($input, $output)
    {
        $result = (new Base45())->decode($output);
        $this->assertEquals($input, $result);
    }

    public function testRandomBytes()
    {
        $base45 = new Base45();
        $bytes = random_bytes(128);
        $encoded = $base45->encode($bytes);
        $decoded = $base45->decode($encoded);
        $this->assertSame($decoded, $bytes);
    }

    public function testException()
    {
        $this->expectException(\Exception::class);
        (new Base45())->decode('x');
    }

    public function testInvalidTryplet()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Base45())->decode("GGWFGW");
    }

    public function dataProvider(): array
    {
        return [
            [
                'AB',
                'BB8'
            ],
            [
                'Hello!!',
                '%69 VD92EX0'
            ],
            [
                'base-45',
                'UJCLQE7W581'
            ]
        ];
    }
}