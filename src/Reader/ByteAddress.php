<?php

namespace ModbusTcpClient\Reader;


use ModbusTcpClient\Packet\ByteCountResponse;

class ByteAddress extends Address
{
    /** @var bool */
    private $firstByte;

    public function __construct(int $address, bool $firstByte, string $name = null, callable $callback = null)
    {
        $type = Address::TYPE_BYTE;
        $fbInt = (int)$firstByte;
        parent::__construct($address, $type, $name ?: "{$type}_{$address}_{$fbInt}", $callback);
        $this->firstByte = $firstByte;
    }

    public function extract(ByteCountResponse $response)
    {
        $word = $response->getWordAt($this->address);
        $result = $this->firstByte ? $word->getLowByteAsInt() : $word->getHighByteAsInt();
        if ($this->callback !== null) {
            return ($this->callback)($result);
        }

        return $result;
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