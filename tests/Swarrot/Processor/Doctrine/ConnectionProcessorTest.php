<?php

namespace Swarrot\Tests\Processor\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\Persistence\ConnectionRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Swarrot\Broker\Message;
use Swarrot\Processor\Doctrine\ConnectionProcessor;
use Swarrot\Processor\ProcessorInterface;

class ConnectionProcessorTest extends TestCase
{
    use ProphecyTrait;

    public function test()
    {
        $message = new Message();
        $options = [
            'doctrine_ping' => false,
            'doctrine_close_master' => true,
        ];

        $innerProcessorProphecy = $this->prophesize(ProcessorInterface::class);
        $innerProcessorProphecy->process($message, $options)->willReturn(true);

        $createConnections = function (): array {
            $connections = [];

            $connectionProphecy = $this->prophesizeConnection();
            $connectionProphecy->close()->shouldNotBeCalled();
            $connections[] = $connectionProphecy->reveal();

            $connectionProphecy = $this->prophesizePrimaryReplicaConnection();
            $connectionProphecy->isConnectedToPrimary()->willReturn(false);
            $connectionProphecy->close()->shouldNotBeCalled();
            $connections[] = $connectionProphecy->reveal();

            $connectionProphecy = $this->prophesizePrimaryReplicaConnection();
            $connectionProphecy->isConnectedToPrimary()->willReturn(true);
            $connectionProphecy->close()->shouldBeCalled();
            $connections[] = $connectionProphecy->reveal();

            return $connections;
        };

        $processor = new ConnectionProcessor($innerProcessorProphecy->reveal(), $createConnections());
        $this->assertEquals($processor->process($message, $options), true);

        $connectionRegistry = $this->prophesize(ConnectionRegistry::class);
        $connectionRegistry->getConnections()->willReturn($createConnections);

        $processor = new ConnectionProcessor($innerProcessorProphecy->reveal(), $createConnections());
        $this->assertEquals($processor->process($message, $options), true);
    }

    public function testWithException()
    {
        $message = new Message();
        $options = [
            'doctrine_ping' => false,
            'doctrine_close_master' => true,
        ];

        $innerProcessorProphecy = $this->prophesize(ProcessorInterface::class);
        $innerProcessorProphecy->process($message, $options)->willThrow(new \Exception('my_fake_message'));

        $createConnections = function () {
            $connections = [];

            $connectionProphecy = $this->prophesizeConnection();
            $connectionProphecy->close()->shouldNotBeCalled();
            $connections[] = $connectionProphecy->reveal();

            $connectionProphecy = $this->prophesizePrimaryReplicaConnection();
            $connectionProphecy->isConnectedToPrimary()->willReturn(false);
            $connectionProphecy->close()->shouldNotBeCalled();
            $connections[] = $connectionProphecy->reveal();

            $connectionProphecy = $this->prophesizePrimaryReplicaConnection();
            $connectionProphecy->isConnectedToPrimary()->willReturn(true);
            $connectionProphecy->close()->shouldBeCalled();
            $connections[] = $connectionProphecy->reveal();

            return $connections;
        };

        $processor = new ConnectionProcessor($innerProcessorProphecy->reveal(), $createConnections());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('my_fake_message');

        $processor->process($message, $options);
    }

    public function testCloseTimedOutConnectionDbal()
    {
        $innerProcessorProphecy = $this->prophesize(ProcessorInterface::class);
        $innerProcessorProphecy->process(Argument::cetera())->willReturn(true);

        $dummySql = 'SELECT 1';

        $databasePlatformProphecy = $this->prophesize(class_exists(SQLitePlatform::class) ? SQLitePlatform::class : '\Doctrine\DBAL\Platforms\SqlitePlatform');
        $databasePlatformProphecy->getDummySelectSQL()->willReturn($dummySql);

        $connectionProphecy = $this->prophesizeConnection();
        $connectionProphecy->isConnected()->willReturn(true);
        $connectionProphecy->getDatabasePlatform()->willReturn($databasePlatformProphecy->reveal());
        $exception = interface_exists(DBALException::class) ? new class extends \Exception implements DBALException {} : new DBALException();
        $connectionProphecy->executeQuery($dummySql)->willThrow($exception);
        $connectionProphecy->close()->shouldBeCalled();

        $options = [
            'doctrine_ping' => true,
            'doctrine_close_master' => true,
        ];
        $processor = new ConnectionProcessor($innerProcessorProphecy->reveal(), [$connectionProphecy->reveal()]);
        $processor->process(new Message(), $options);
    }

    public function testRejectNonConnections()
    {
        $innerProcessorProphecy = $this->prophesize(ProcessorInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$connections must be an array of Connection, but one of the elements was stdClass');

        new ConnectionProcessor($innerProcessorProphecy->reveal(), [new \stdClass()]);
    }

    public function testAcceptEmptyConnections()
    {
        $innerProcessorProphecy = $this->prophesize(ProcessorInterface::class);
        $innerProcessorProphecy->process(Argument::cetera())->willReturn(true)->shouldBeCalledTimes(1);

        $options = [
            'doctrine_ping' => false,
            'doctrine_close_master' => true,
        ];
        $processor = new ConnectionProcessor($innerProcessorProphecy->reveal(), []);
        $processor->process(new Message(), $options);
    }

    private function prophesizeConnection()
    {
        return $this->prophesize(Connection::class);
    }

    private function prophesizePrimaryReplicaConnection()
    {
        return $this->prophesize(PrimaryReadReplicaConnection::class);
    }
}
