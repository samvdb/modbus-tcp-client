<?php

namespace ModbusTcpClient\Reader;

use ModbusTcpClient\Packet\ByteCountResponse;

class BitAddress extends Address
{
    /** @var int */
    private $bit;

    public function __construct(int $address, int $bit, string $name = null, callable $callback = null)
    {
        $type = Address::TYPE_BIT;
        parent::__construct($address, $type, $name ?: "{$type}_{$address}_{$bit}", $callback);
        $this->bit = $bit;
    }

    public function extract(ByteCountResponse $response)
    {
        $isBitSet = $response->getWordAt($this->address)->isBitSet($this->bit);
        if ($this->callback !== null) {
            return ($this->callback)($isBitSet);
        }
        return $isBitSet;
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