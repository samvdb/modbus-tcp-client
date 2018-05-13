<?php

namespace ModbusTcpClient\Reader;

use ModbusTcpClient\Packet\ByteCountResponse;

class BitAddress extends Address
{
    /** @var int */
    private $bit;

    public function __construct(int $address, int $bit, string $name = null)
    {
        $type = Address::TYPE_BIT;
        parent::__construct($address, $type, $name ?: "{$type}_{$address}_{$bit}");
        $this->bit = $bit;
    }

    public function extract(ByteCountResponse $response)
    {
        return $response->getWordAt($this->address)->isBitSet($this->bit);
    }

    public function getBit(): int
    {
        return $this->bit;
    }

    protected function getAllowedTypes()
    {
        return [Address::TYPE_BIT];
    }
}