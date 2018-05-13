<?php

namespace ModbusTcpClient\Reader;


use ModbusTcpClient\Packet\ByteCountResponse;

class Address
{
    const TYPE_BIT = 'bit';
    const TYPE_BYTE = 'byte';
    const TYPE_INT16 = 'int16';
    const TYPE_UINT16 = 'uint16';
    const TYPE_INT32 = 'int32';
    const TYPE_UINT32 = 'uint32';
    const TYPE_UINT64 = 'uint64';
    const TYPE_FLOAT = 'float';
    const TYPE_STRING = 'string';

    const TYPES = [
        Address::TYPE_BIT,
        Address::TYPE_BYTE,
        Address::TYPE_INT16,
        Address::TYPE_UINT16,
        Address::TYPE_INT32,
        Address::TYPE_UINT32,
        Address::TYPE_UINT64,
        Address::TYPE_FLOAT,
        Address::TYPE_STRING,
    ];

    /** @var int */
    protected $address;

    /** @var string */
    protected $type;

    /** @var string */
    private $name;

    public function __construct(int $address, string $type, string $name = null)
    {
        $this->address = $address;
        $this->type = $type;
        $this->name = $name ?: "{$type}_{$address}";

        if (!in_array($type, $this->getAllowedTypes(), true)) {
            throw new \LogicException("Invalid address type given! type: '{$type}', address: {$address}");
        }
    }

    protected function getAllowedTypes()
    {
        return [
            Address::TYPE_INT16,
            Address::TYPE_UINT16,
            Address::TYPE_INT32,
            Address::TYPE_UINT32,
            Address::TYPE_UINT64,
            Address::TYPE_FLOAT,
        ];
    }

    public function extract(ByteCountResponse $response)
    {
        switch ($this->type) {
            case Address::TYPE_INT16:
                return $response->getWordAt($this->address)->getInt16();
            case Address::TYPE_UINT16:
                return $response->getWordAt($this->address)->getUInt16();
            case Address::TYPE_INT32:
                return $response->getDoubleWordAt($this->address)->getInt32();
            case Address::TYPE_UINT32:
                return $response->getDoubleWordAt($this->address)->getUInt32();
            case Address::TYPE_FLOAT:
                return $response->getDoubleWordAt($this->address)->getFloat();
            case Address::TYPE_UINT64:
                return $response->getQuadWordAt($this->address)->getUInt64();
        }
        throw new \RuntimeException('Invalid type given for address extraction!');
    }

    public function getSize(): int
    {
        $size = 1;
        switch ($this->type) {
            case Address::TYPE_INT32:
            case Address::TYPE_UINT32:
            case Address::TYPE_FLOAT:
                $size = 2;
                break;
            case Address::TYPE_UINT64:
                $size = 4;
                break;
        }
        return $size;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddress(): int
    {
        return $this->address;
    }

    public function getType(): string
    {
        return $this->type;
    }
}