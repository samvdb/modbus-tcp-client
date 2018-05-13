<?php

namespace Tests\unit\Reader;


use ModbusTcpClient\Packet\ModbusFunction\ReadHoldingRegistersResponse;
use ModbusTcpClient\Reader\BitAddress;
use PHPUnit\Framework\TestCase;

class BitAddressTest extends TestCase
{
    public function testGetSize()
    {
        $address = new BitAddress(1, 0);
        $this->assertEquals(1, $address->getSize());
    }

    public function testGetName()
    {
        $address = new BitAddress(1, 0, 'alarm1_do');
        $this->assertEquals('alarm1_do', $address->getName());
    }

    public function testDefaultGetName()
    {
        $address = new BitAddress(1, 1);
        $this->assertEquals('bit_1_1', $address->getName());
    }

    public function testExtract()
    {
        $responsePacket = new ReadHoldingRegistersResponse("\x01\x00\x05", 3, 33152);

        $this->assertTrue((new BitAddress(0, 0))->extract($responsePacket));
        $this->assertFalse((new BitAddress(0, 1))->extract($responsePacket));
        $this->assertTrue((new BitAddress(0, 2))->extract($responsePacket));
    }
}