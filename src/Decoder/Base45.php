<?php
declare(strict_types = 1);

/*
 * Forked from Mhauri\Base45
 * @author Marcel Hauri <marcel@hauri.dev>
 */
namespace Herald\GreenPass\Decoder;

/**
 * Class Base45
 */
class Base45
{

    const CHARSET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:';

    /**
     *
     * @param string $data
     *
     * @return string
     */
    public function encode(string $data): string
    {
        $buffer = $this->stringToBuffer($data);
        $charset = self::CHARSET;
        $result = '';
        for ($i = 0; $i < count($buffer); $i += 2) {
            if (count($buffer) - $i > 1) {
                $x = ($buffer[$i] << 8) + $buffer[$i + 1];
                list ($e, $rest) = $this->divmod($x, 45 * 45);
                list ($d, $c) = $this->divmod($rest, 45);
                $result .= sprintf('%s%s%s', @$charset[$c], @$charset[$d], @$charset[$e]);
            } else {
                list ($d, $c) = $this->divmod($buffer[$i], 45);
                $result .= sprintf('%s%s', @$charset[$c], @$charset[$d]);
            }
        }

        return $result;
    }

    /**
     *
     * @param string $data
     *
     * @return string
     * @throws \Exception
     */
    public function decode(string $data): string
    {
        $buffer = $this->base45StringToBuffer($data);
        $result = '';
        for ($i = 0; $i < count($buffer); $i += 3) {
            if (count($buffer) - $i >= 3) {
                $x = $buffer[$i] + $buffer[$i + 1] * 45 + $buffer[$i + 2] * 45 * 45;
                if ($x > 0xFFFF) {
                    throw new \InvalidArgumentException('Invalid base45 string');
                }
                list ($a, $b) = $this->divmod($x, 256);
                $result .= sprintf('%s%s', chr((int) $a), chr((int) $b));
            } else {
                $x = $buffer[$i] + $buffer[$i + 1] * 45;
                if ($x > 0xFF) {
                    throw new \InvalidArgumentException('Invalid base45 string');
                }
                $result .= chr((int) $x);
            }
        }

        return $result;
    }

    /**
     *
     * @param int $x
     * @param int $y
     *
     * @return array
     */
    private function divmod(int $x, int $y): array
    {
        $resX = floor($x / $y);
        $resY = $x % $y;

        return [
            $resX,
            $resY
        ];
    }

    /**
     *
     * @param string $input
     *
     * @return array
     */
    private function stringToBuffer(string $input): array
    {
        $result = [];
        for ($i = 0; $i < strlen($input); $i ++) {
            $result[] = ord($input[$i]);
        }

        return $result;
    }

    /**
     *
     * @param string $input
     *
     * @return array
     * @throws \Exception
     */
    private function base45StringToBuffer(string $input): array
    {
        $result = [];
        for ($i = 0; $i < strlen($input); $i ++) {
            $position = strpos(self::CHARSET, $input[$i]);
            if ($position === false) {
                throw new \InvalidArgumentException('Invalid base45 value');
            } else {
                $result[] = $position;
            }
        }

        return $result;
    }
}
