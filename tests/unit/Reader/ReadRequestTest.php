<?php

namespace Tests\unit\Reader;


use ModbusTcpClient\Packet\ModbusFunction\ReadHoldingRegistersRequest;
use ModbusTcpClient\Reader\Address;
use ModbusTcpClient\Reader\BitAddress;
use ModbusTcpClient\Reader\ReadRequest;
use ModbusTcpClient\Reader\StringAddress;
use PHPUnit\Framework\TestCase;

class ReadRequestTest extends TestCase
{
    public function testCreate()
    {
        $uri = 'tcp://192.168.100.1:502';
        $addresses = [new BitAddress(1, 0, 'alarm1_do')];
        $request = new ReadHoldingRegistersRequest(1, 1);

        $rr = new ReadRequest($uri, $addresses, $request);

        $this->assertEquals($uri, $rr->getUri());
        $this->assertEquals($request, $rr->getRequest());
        $this->assertEquals($addresses, $rr->getAddresses());
    }

    public function testToString()
    {
        $uri = 'tcp://192.168.100.1:502';
        $addresses = [new BitAddress(1, 0, 'alarm1_do')];
        $request = new ReadHoldingRegistersRequest(1, 1);

        $rr = new ReadRequest($uri, $addresses, $request);

        $this->assertEquals($request->__toString(), $rr->__toString());
    }

    public function testExtract()
    {
        $uri = 'tcp://192.168.100.1:502';
        $addresses = [
            new Address(0, Address::TYPE_INT16, 'temp1_wo'),
            new StringAddress(1, 5, 'username')
        ];
        $request = new ReadHoldingRegistersRequest(0, 4);

        $rr = new ReadRequest($uri, $addresses, $request);

        $values = $rr->extract("\x81\x80\x00\x00\x00\x0B\x01\x03\x08\x01\x00\xF8\x53\x65\x72\x00\x6E");
        $this->assertEquals(
            ['username' => 'Søren', 'temp1_wo' => 256],
            $values
        );
    }

}