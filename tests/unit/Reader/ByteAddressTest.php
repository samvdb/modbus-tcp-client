<?php

namespace Tests\unit\Reader;


use ModbusTcpClient\Packet\ModbusFunction\ReadHoldingRegistersResponse;
use ModbusTcpClient\Reader\ByteAddress;
use PHPUnit\Framework\TestCase;

class ByteAddressTest extends TestCase
{

    public function testGetSize()
    {
        $address = new ByteAddress(1, true);
        $this->assertEquals(1, $address->getSize());
    }

    public function testGetName()
    {
        $address = new ByteAddress(1, true, 'direction');
        $this->assertEquals('direction', $address->getName());
    }

    public function testDefaultGetName()
    {
        $address = new ByteAddress(1, true);
        $this->assertEquals('byte_1_1', $address->getName());

        $address = new ByteAddress(1, false);
        $this->assertEquals('byte_1_0', $address->getName());
    }

    public function testExtract()
    {
        $responsePacket = new ReadHoldingRegistersResponse("\x01\x00\x05", 3, 33152);

        $this->assertEquals(5, (new ByteAddress(0, true))->extract($responsePacket));
        $this->assertEquals(0, (new ByteAddress(0, false))->extract($responsePacket));
    }
}