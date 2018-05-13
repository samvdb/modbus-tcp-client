<?php

namespace Tests\unit\Reader;


use ModbusTcpClient\Packet\ModbusFunction\ReadHoldingRegistersResponse;
use ModbusTcpClient\Reader\StringAddress;
use PHPUnit\Framework\TestCase;

class StringAddressTest extends TestCase
{
    public function testGetSize()
    {
        $address = new StringAddress(1, 5);
        $this->assertEquals(3, $address->getSize());
    }

    public function testGetName()
    {
        $address = new StringAddress(1, 5, 'username');
        $this->assertEquals('username', $address->getName());
    }

    public function testDefaultGetName()
    {
        $address = new StringAddress(1, 5);
        $this->assertEquals('string_1_5', $address->getName());
    }

    public function testExtract()
    {
        $responsePacket = new ReadHoldingRegistersResponse("\x08\x01\x00\xF8\x53\x65\x72\x00\x6E", 3, 33152);
        $address = new StringAddress(1, 5, 'username');

        $value = $address->extract($responsePacket);

        $this->assertEquals('Søren', $value);
    }

}