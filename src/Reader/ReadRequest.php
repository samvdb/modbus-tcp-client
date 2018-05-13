<?php

namespace ModbusTcpClient\Reader;


use ModbusTcpClient\Packet\ModbusFunction\ReadHoldingRegistersRequest;
use ModbusTcpClient\Packet\ResponseFactory;

class ReadRequest
{
    /**
     * @var string uri to modbus server. Example: 'tcp://192.168.100.1:502'
     */
    private $uri;

    /** @var Address[] */
    private $addresses;

    /** @var ReadHoldingRegistersRequest */
    private $request;


    public function __construct(string $uri, array $addresses, $request)
    {
        $this->addresses = $addresses;
        $this->request = $request;
        $this->uri = $uri;
    }

    /**
     * @return ReadHoldingRegistersRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return Address[]
     */
    public function getAddresses(): array
    {
        return $this->addresses;
    }

    public function __toString()
    {
        return $this->request->__toString();
    }

    public function extract(string $data): array
    {
        $response = ResponseFactory::parseResponse($data)->withStartAddress($this->request->getStartAddress());
        $result = [];
        foreach ($this->addresses as $address) {
            $result[$address->getName()] = $address->extract($response);
        }
        return $result;
    }
}