<?php

namespace Tests\unit\Reader;


use ModbusTcpClient\Packet\ModbusFunction\ReadHoldingRegistersResponse;
use ModbusTcpClient\Reader\Address;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    /**
     * @dataProvider sizeProvider
     */
    public function testGetSize($type, $expectedSize)
    {
        $address = new Address(1, $type);
        $this->assertEquals($expectedSize, $address->getSize());
    }

    public function sizeProvider()
    {
        return [
            'int16 size should be 1' => ['int16', 1],
            'uint16 size should be 1' => ['uint16', 1],
            'int32 size should be 2' => ['int32', 2],
            'uint32 size should be 2' => ['uint32', 2],
            'uint64 size should be 4' => ['uint64', 4],
        ];
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Invalid address type given! type: 'byte', address: 1
     */
    public function testInvalidType()
    {
        new Address(1, Address::TYPE_BYTE);
    }

    public function testGetName()
    {
        $address = new Address(1, Address::TYPE_INT16, 'temp1');
        $this->assertEquals('temp1', $address->getName());
    }

    public function testDefaultGetName()
    {
        $address = new Address(1, Address::TYPE_INT16);
        $this->assertEquals('int16_1', $address->getName());
    }

    public function testGetAddress()
    {
        $address = new Address(1, Address::TYPE_INT16);
        $this->assertEquals(1, $address->getAddress());
    }

    public function testExtractWithCallback()
    {
        $responsePacket = new ReadHoldingRegistersResponse("\x08\x00\x01\x80\x00\x00\x03\x00\x04", 3, 33152);
        $address = new Address(1, Address::TYPE_UINT16, null, function ($data) {
            return 'prefix_' . $data;
        });

        $value = $address->extract($responsePacket);

        $this->assertEquals('prefix_32768', $value);
    }

    public function testExtractInt16()
    {
        $responsePacket = new ReadHoldingRegistersResponse("\x08\x00\x01\x80\x00\x00\x03\x00\x04", 3, 33152);
        $address = new Address(1, Address::TYPE_INT16);

        $value = $address->extract($responsePacket);

        $this->assertEquals(-32768, $value);
    }

    public function testExtractUint16()
    {
        $responsePacket = new ReadHoldingRegistersResponse("\x08\x00\x01\x80\x00\x00\x03\x00\x04", 3, 33152);
        $address = new Address(1, Address::TYPE_UINT16);

        $value = $address->extract($responsePacket);

        $this->assertEquals(32768, $value);
    }

    public function testExtractUint32()
    {
        $responsePacket = new ReadHoldingRegistersResponse("\x08\x00\x01\xFF\xFF\x7F\xFF\x00\x04", 3, 33152);
        $address = new Address(1, Address::TYPE_UINT32);

        $value = $address->extract($responsePacket);

        $this->assertEquals(2147483647, $value);
    }

    public function testExtractInt32()
    {
        $responsePacket = new ReadHoldingRegistersResponse("\x08\x00\x01\x00\x00\x80\x00\x00\x04", 3, 33152);
        $address = new Address(1, Address::TYPE_INT32);

        $value = $address->extract($responsePacket);

        $this->assertEquals(-2147483648, $value);
    }

    public function testExtractFloat()
    {
        $responsePacket = new ReadHoldingRegistersResponse("\x08\x00\x01\xAA\xAB\x3F\x2A\x00\x04", 3, 33152);
        $address = new Address(1, Address::TYPE_FLOAT);

        $value = $address->extract($responsePacket);

        $this->assertEquals(0.6666666, $value, null, 0.0000001);
    }

    public function testExtractUInt64()
    {
        $responsePacket = new ReadHoldingRegistersResponse("\x08\xFF\xFF\x7F\xFF\x00\x00\x00\x00", 3, 33152);
        $address = new Address(0, Address::TYPE_UINT64);

        $value = $address->extract($responsePacket);

        $this->assertEquals(2147483647, $value);
    }

}