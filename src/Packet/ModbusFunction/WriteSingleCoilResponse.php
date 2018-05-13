<?php
namespace ModbusTcpClient\Packet\ModbusFunction;

use ModbusTcpClient\Packet\ModbusPacket;
use ModbusTcpClient\Packet\StartAddressResponse;
use ModbusTcpClient\Utils\Types;

/**
 * Response for Write Single Coil (FC=05)
 *
 * Data part of packet is always 4 bytes - 2 byte for address and 2 byte for coil status (FF00 = on,  0000 = off).
 * For example: coil at address 1 is turned on '\x00\x01\xFF\x00'
 * For example: coil at address 10 is turned off '\x00\x0A\x00\x00'
 */
class WriteSingleCoilResponse extends StartAddressResponse
{
    const ON = 0xFF;
    const OFF = 0x0;

    /**
     * @var bool
     */
    private $coil;

    public function __construct($rawData, $unitId = 0, $transactionId = null)
    {
        parent::__construct($rawData, $unitId, $transactionId);
        $this->coil = ord($rawData[2]) === self::ON;
    }

    public function getFunctionCode()
    {
        return ModbusPacket::WRITE_SINGLE_COIL;
    }

    public function __toString()
    {
        return parent::__toString()
            . Types::toByte($this->isCoil() ? self::ON : self::OFF)
            . chr(self::OFF);
    }

    public function isCoil()
    {
        return $this->coil;
    }

    protected function getLengthInternal()
    {
        return parent::getLengthInternal() + 2; //coil 1 byte + 1 "unused 0x0 byte"
    }
}