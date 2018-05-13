<?php

namespace ModbusTcpClient\Reader;

use ModbusTcpClient\Packet\ModbusFunction\ReadHoldingRegistersRequest;
use ModbusTcpClient\Packet\ModbusFunction\ReadInputRegistersRequest;

class ReadRegistersBuilder
{
    const MAX_ADDRESSES_PER_MODBUS_REQUEST = 124;

    private $requestClass;
    private $addresses = [];
    private $currentUri;

    public function __construct(string $requestClass, string $uri = null)
    {
        $this->requestClass = $requestClass;

        if ($uri !== null) {
            $this->useUri($uri);
        }
    }

    public static function newReadHoldingRegisters(string $uri = null): ReadRegistersBuilder
    {
        return new ReadRegistersBuilder(ReadHoldingRegistersRequest::class, $uri);
    }

    public static function newReadInputRegisters(string $uri = null): ReadRegistersBuilder
    {
        return new ReadRegistersBuilder(ReadInputRegistersRequest::class, $uri);
    }

    public function useUri(string $uri): ReadRegistersBuilder
    {
        if (empty($uri)) {
            throw new \LogicException('uri can not be empty value');
        }
        $this->currentUri = $uri;
        return $this;
    }

    protected function addAddress(Address $address): ReadRegistersBuilder
    {
        if (empty($this->currentUri)) {
            throw new \LogicException('uri not set');
        }
        $this->addresses[$this->currentUri][$address->getName()] = $address;
        return $this;
    }

    public function allFromArray(array $registers): ReadRegistersBuilder
    {
        foreach ($registers as $register) {
            if (\is_array($register)) {
                $this->fromArray($register);
            } elseif ($register instanceof Address) {
                $this->addAddress($register);
            }
        }
        return $this;
    }

    public function fromArray(array $register): ReadRegistersBuilder
    {
        $uri = $register['uri'] ?? null;
        if ($uri !== null) {
            $this->useUri($uri);
        }

        $address = $register['address'] ?? null;
        if (empty($address)) {
            throw new \LogicException('empty address given');
        }

        $addressType = strtolower($register['type'] ?? null);
        if (empty($addressType) || !\in_array($addressType, Address::TYPES, true)) {
            throw new \LogicException('empty or unknown type for address given');
        }

        switch ($addressType) {
            case Address::TYPE_BIT:
                $this->bit($address, $register['bit'] ?? 0, $register['name'] ?? null);
                break;
            case Address::TYPE_BYTE:
                $this->byte($address, (bool)($register['firstByte'] ?? true), $register['name'] ?? null);
                break;
            case Address::TYPE_INT16:
                $this->int16($address, $register['name'] ?? null);
                break;
            case Address::TYPE_UINT16:
                $this->uint16($address, $register['name'] ?? null);
                break;
            case Address::TYPE_INT32:
                $this->int32($address, $register['name'] ?? null);
                break;
            case Address::TYPE_UINT32:
                $this->uint32($address, $register['name'] ?? null);
                break;
            case Address::TYPE_UINT64:
                $this->uint64($address, $register['name'] ?? null);
                break;
            case Address::TYPE_FLOAT:
                $this->float($address, $register['name'] ?? null);
                break;
            case Address::TYPE_STRING:
                $this->string($address, $register['length'] ?? null, $register['name'] ?? null);
                break;
        }
        return $this;
    }

    public function bit(int $address, int $nthBit, string $name = null): ReadRegistersBuilder
    {
        if ($nthBit < 0 || $nthBit > 15) {
            throw new \OutOfBoundsException("Invalid bit number in for register given! nthBit: '{$nthBit}', address: {$address}");
        }
        return $this->addAddress(new BitAddress($address, $nthBit, $name));
    }

    public function byte(int $address, bool $firstByte = true, string $name = null): ReadRegistersBuilder
    {
        return $this->addAddress(new ByteAddress($address, $firstByte, $name));
    }

    public function int16(int $address, string $name = null): ReadRegistersBuilder
    {
        return $this->addAddress(new Address($address, Address::TYPE_INT16, $name ?: $address));
    }

    public function uint16(int $address, string $name = null): ReadRegistersBuilder
    {
        return $this->addAddress(new Address($address, Address::TYPE_UINT16, $name ?: $address));
    }

    public function int32(int $address, string $name = null): ReadRegistersBuilder
    {
        return $this->addAddress(new Address($address, Address::TYPE_INT32, $name ?: $address));
    }

    public function uint32(int $address, string $name = null): ReadRegistersBuilder
    {
        return $this->addAddress(new Address($address, Address::TYPE_UINT32, $name ?: $address));
    }

    public function uint64(int $address, string $name = null): ReadRegistersBuilder
    {
        return $this->addAddress(new Address($address, Address::TYPE_UINT64, $name ?: $address));
    }

    public function float(int $address, string $name = null): ReadRegistersBuilder
    {
        return $this->addAddress(new Address($address, Address::TYPE_FLOAT, $name ?: $address));
    }

    public function string(int $address, int $byteLength, string $name = null): ReadRegistersBuilder
    {
        if ($byteLength < 1 || $byteLength > 228) {
            throw new \OutOfBoundsException("Out of range string length for given! length: '{$byteLength}', address: {$address}");
        }
        return $this->addAddress(new StringAddress($address, $byteLength, $name));
    }

    /**
     * @return ReadRequest[]
     */
    public function build(): array
    {
        $result = [];
        foreach ($this->addresses as $uri => $addrs) {
            // sort by address and size to help chunking
            usort($addrs, function (Address $a, Address $b) {
                $aAddr = $a->getAddress();
                $bAddr = $b->getAddress();
                if ($aAddr === $bAddr) {
                    return $a->getSize() <=> $b->getSize();
                }
                return $aAddr <=> $bAddr;

            });

            $startAddress = null;
            $quantity = null;
            $chunk = [];
            foreach ($addrs as $addr) {
                /** @var Address $addr */
                $a = $addr->getAddress();
                if (!$startAddress) {
                    $startAddress = $a;
                }

                $nextAvailableRegister = $a + $addr->getSize();
                $previousQuantity = $quantity;
                $quantity = $nextAvailableRegister - $startAddress;
                if ($quantity >= static::MAX_ADDRESSES_PER_MODBUS_REQUEST) {
                    $result[] = new ReadRequest($uri, $chunk, new $this->requestClass($startAddress, $previousQuantity));

                    $chunk = [];
                    $startAddress = $a;
                    $quantity = $addr->getSize();
                }
                $chunk[] = $addr;

            }
            if (!empty($chunk)) {
                $result[] = new ReadRequest($uri, $chunk, new $this->requestClass($startAddress, $quantity));
            }
        }
        return $result;
    }
}





