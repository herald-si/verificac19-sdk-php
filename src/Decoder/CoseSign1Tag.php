<?php
namespace Herald\GreenPass\Decoder;

use CBOR\CBORObject;
use CBOR\TagObject;

final class CoseSign1Tag extends TagObject
{

    public static function getTagId(): int
    {
        return 18;
    }

    public static function createFromLoadedData(int $additionalInformation, ?string $data, CBORObject $object): TagObject
    {
        return new self($additionalInformation, $data, $object);
    }

    public function getNormalizedData(bool $ignoreTags = false)
    {
        return $this->getValue()->getNormalizedData($ignoreTags);
    }
}