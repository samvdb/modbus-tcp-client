<?php

namespace ModbusTcpClient\Packet;

use ModbusTcpClient\Utils\Types;

/**
 * Double word - 4 bytes, 32bits of data
 */
class DoubleWord extends AbstractWord
{
    protected function getByteLength()
    {
        return 4;
    }

    /**
     * @param int $endianness byte and word order for modbus binary data
     * @return int
     * @throws \RuntimeException
     */
    public function getUInt32($endianness = null)
    {
        return Types::parseUInt32($this->getData(), $endianness);
    }

    /**
     * @param int $endianness byte and word order for modbus binary data
     * @return int
     * @throws \ModbusTcpClient\ModbusException
     */
    public function getInt32($endianness = null)
    {
        return Types::parseInt32($this->getData(), $endianness);
    }

    /**
     * @param int $endianness byte and word order for modbus binary data
     * @return float
     * @throws \RuntimeException
     */
    public function getFloat($endianness = null)
    {
        return Types::parseFloat($this->getData(), $endianness);
    }

    /**
     * @return Word
     * @throws \ModbusTcpClient\ModbusException
     */
    public function getLowBytesAsWord()
    {
        return new Word(substr($this->getData(), 2));
    }

    /**
     * @return Word
     * @throws \ModbusTcpClient\ModbusException
     */
    public function getHighBytesAsWord()
    {
        return new Word(substr($this->getData(), 0, 2));
    }

    /**
     * Combine DoubleWords (2x(2x2) bytes) into Quad Word (8 bytes). This Double Word is used as highest bytes and argument $lowDoubleWord as lowest bytes
     *
     * @param DoubleWord $lowDoubleWord
     * @return QuadWord
     * @throws \ModbusTcpClient\ModbusException
     */
    public function combine(DoubleWord $lowDoubleWord)
    {
        return new QuadWord($this->getData() . $lowDoubleWord->getData());
    }
}