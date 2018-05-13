<?php

namespace ModbusTcpClient\Packet\ModbusFunction;


use ModbusTcpClient\ModbusException;
use ModbusTcpClient\Packet\ByteCountResponse;
use ModbusTcpClient\Packet\DoubleWord;
use ModbusTcpClient\Packet\ModbusPacket;
use ModbusTcpClient\Packet\QuadWord;
use ModbusTcpClient\Packet\Word;
use ModbusTcpClient\Utils\Types;
use Traversable;

/**
 * Response for Read Holding Registers (FC=03)
 */
class ReadHoldingRegistersResponse extends ByteCountResponse implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var string
     */
    private $data;

    /** @var int[] */
    private $dataBytes;

    public function __construct($rawData, $unitId = 0, $transactionId = null)
    {
        parent::__construct($rawData, $unitId, $transactionId);
        $this->data = substr($rawData, 1); //first byte is byteCount. remove it
        $this->dataBytes = Types::parseByteArray($this->data);
    }

    public function getFunctionCode()
    {
        return ModbusPacket::READ_HOLDING_REGISTERS;
    }

    /**
     * @return int[]
     */
    public function getData()
    {
        return $this->dataBytes;
    }

    /**
     * Iterator returning data by words. Each word contains 2 bytes
     *
     * @return Traversable
     * @throws \ModbusTcpClient\ModbusException
     */
    public function asWords()
    {
        if ($this->getByteCount() % 2 !== 0) {
            throw new ModbusException('getWords needs packet byte count to be multiple of 2');
        }
        $index = $this->getStartAddress();
        $addressStep = $this->getAddressStep();
        foreach (str_split($this->data, 2) as $str) {
            yield $index => new Word($str);
            $index += $addressStep;
        }
    }

    /**
     * Return data as splitted into words. Each word contains 2 bytes
     *
     * @return Word[] array of Words. each arrays cointains 2 bytes
     * @throws \ModbusTcpClient\ModbusException
     */
    public function getWords()
    {
        return iterator_to_array($this->asWords());
    }

    /**
     * Iterator returning data by double words. Each dword contains 4 bytes
     *
     * @return Traversable
     * @throws \ModbusTcpClient\ModbusException
     */
    public function asDoubleWords()
    {
        $byteCount = $this->getByteCount();
        if ($byteCount % 4 !== 0) {
            throw new ModbusException('getDoubleWords needs packet byte count to be multiple of 4');
        }

        $index = $this->getStartAddress();
        $addressStep = $this->getAddressStep() * 2; // double word is 2 words :)
        foreach (str_split($this->data, 4) as $str) {
            yield $index => new DoubleWord($str);
            $index += $addressStep;
        }
    }

    /**
     * Return data as splitted into double words. Each dword contains 4 bytes
     *
     * @return DoubleWord[] array of Double Words. each arrays cointains 4 bytes
     * @throws \ModbusTcpClient\ModbusException
     */
    public function getDoubleWords()
    {
        return iterator_to_array($this->asDoubleWords());
    }

    public function __toString()
    {
        return parent::__toString()
            . $this->data;
    }

    protected function getLengthInternal()
    {
        return parent::getLengthInternal() + $this->getByteCount();
    }

    public function offsetSet($offset, $value)
    {
        throw new \LogicException('setting value in response is not supported!');
    }

    public function offsetUnset($offset)
    {
        throw new \LogicException('unsetting value in response is not supported!');
    }

    public function offsetExists($offset)
    {
        $address = ($offset - $this->getStartAddress()) * 2;
        return isset($this->dataBytes[$address]);
    }

    public function offsetGet($offset)
    {
        $address = ($offset - $this->getStartAddress()) * 2;
        $byteCount = $this->getByteCount();
        if ($address < 0 || $address >= $byteCount) {
            throw new \OutOfBoundsException('offset out of bounds');
        }
        return new Word(substr($this->data, $address, 2));
    }

    public function getWordAt($wordAddress)
    {
        return $this->offsetGet($wordAddress);
    }

    public function getIterator()
    {
        return $this->asWords();
    }

    /**
     * @param $firstWordAddress
     * @return DoubleWord
     */
    public function getDoubleWordAt($firstWordAddress)
    {
        $address = ($firstWordAddress - $this->getStartAddress()) * 2;
        $byteCount = $this->getByteCount();
        if ($address < 0 || ($address + 4) > $byteCount) {
            throw new \OutOfBoundsException('address out of bounds');
        }
        return new DoubleWord(substr($this->data, $address, 4));
    }

    /**
     * @param $firstWordAddress
     * @return QuadWord
     */
    public function getQuadWordAt($firstWordAddress)
    {
        $address = ($firstWordAddress - $this->getStartAddress()) * 2;
        $byteCount = $this->getByteCount();
        if ($address < 0 || ($address + 8) > $byteCount) {
            throw new \OutOfBoundsException('address out of bounds');
        }
        return new QuadWord(substr($this->data, $address, 8));
    }

    /**
     * Parse ascii string from registers to utf-8 string
     *
     * @param $startFromWord int start parsing string from that word/register
     * @param $length int count of characters to parse
     * @param int $endianness byte and word order for modbus binary data
     * @return string
     */
    public function getAsciiStringAt($startFromWord, $length, $endianness = null)
    {
        $address = ($startFromWord - $this->getStartAddress()) * 2;

        $byteCount = $this->getByteCount();
        if ($address < 0 || $address >= $byteCount) {
            throw new \OutOfBoundsException('startFromWord out of bounds');
        }
        if ($length < 1) {
            // length can be bigger than bytes count - we will just parse less as there is nothing to parse
            throw new \OutOfBoundsException('length out of bounds');
        }

        $binaryData = substr($this->data, $address);
        return Types::parseAsciiStringFromRegister($binaryData, $length, $endianness);
    }
}
