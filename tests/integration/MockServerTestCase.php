<?php

namespace Tests\integration;

use ModbusTcpClient\Network\BinaryStreamConnection;
use ModbusTcpClient\Packet\ProtocolDataUnitRequest;
use PHPUnit\Framework\TestCase;
use React\ChildProcess\Process;
use React\EventLoop\Factory;

abstract class MockServerTestCase extends TestCase
{
    public static function executeWithMockServer($packetToRespond, \Closure $closure, $protocol = 'TCP', $answerTimeout = 0, $port = 0)
    {
        $loop = Factory::create();

        $port = $port ?: random_int(10000, 50000);
        $process = new Process(PHP_BINARY . ' ' . __DIR__ . DIRECTORY_SEPARATOR . "MockResponseServer.php {$protocol} {$port} {$answerTimeout} {$packetToRespond}");

        $clientData = [];
        $loop->addTimer(0.001, function () use ($process, $closure, $port, &$clientData, $loop) {
            $process->start($loop);

            $process->stdout->on('data', function ($output) use (&$clientData) {
                $clientData[] = $output;
            });

            if (strpos(PHP_OS, 'WIN') === false || getenv('MOCKSERVER_TIMEOUT_USEC') !== false) {
                // wait to spin up. needed for linux. unnecessary on Windows 10.
                // Ugly but even with 150ms sleep tests total run time is faster on Linux
                usleep(getenv('MOCKSERVER_TIMEOUT_USEC') ?: 150000);
            }

            $closure($port);
        });

        $loop->run();
        return $clientData;
    }

    public static function executeWithMock($mockResponse, ProtocolDataUnitRequest $request)
    {
        $responseBinary = null;
        $clientData = static::executeWithMockServer($mockResponse, function ($port) use ($request, &$responseBinary) {

            $connection = BinaryStreamConnection::getBuilder()
                ->setPort($port)
                ->setHost(getenv('MOCKSERVER_BIND_ADDRESS') ?: '127.0.0.1')
                ->build();

            $responseBinary = $connection->connect()
                ->sendAndReceive($request);
        });

        return [$responseBinary, $clientData];
    }
}