<?php

namespace ModbusTcpClient\Reader;


use ModbusTcpClient\Packet\ByteCountResponse;

class StringAddress extends Address
{
    /** @var int */
    private $byteLength;

    public function __construct(int $address, int $byteLength, string $name = null)
    {
        $type = Address::TYPE_STRING;
        parent::__construct($address, $type, $name ?: "{$type}_{$address}_{$byteLength}");
        $this->byteLength = $byteLength;
    }

    public function extract(ByteCountResponse $response)
    {
        return $response->getAsciiStringAt($this->address, $this->byteLength);
    }

    public function getSize(): int
    {
        return ceil($this->byteLength / 2); // 1 register contains 2 bytes/chars
    }

    protected function getAllowedTypes()
    {
        return [Address::TYPE_STRING];
    }
}