<?php

namespace ModbusTcpClient\Reader;


use ModbusTcpClient\Packet\ByteCountResponse;

class ByteAddress extends Address
{
    /** @var bool */
    private $firstByte;

    public function __construct(int $address, bool $firstByte, string $name = null)
    {
        $type = Address::TYPE_BYTE;
        $fbInt = (int)$firstByte;
        parent::__construct($address, $type, $name ?: "{$type}_{$address}_{$fbInt}");
        $this->firstByte = $firstByte;
    }

    public function extract(ByteCountResponse $response)
    {
        $word = $response->getWordAt($this->address);
        return $this->firstByte ? $word->getLowByteAsInt() : $word->getHighByteAsInt();
    }

    /**
     * @return bool
     */
    public function isFirstByte(): bool
    {
        return $this->firstByte;
    }

    protected function getAllowedTypes()
    {
        return [Address::TYPE_BYTE];
    }
}