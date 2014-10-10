<?php

namespace Swarrot\Processor\Ack;

use Doctrine\DBAL\DBALException;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Swarrot\Broker\Message;
use Swarrot\Processor\Doctrine\ConnectionProcessor;

class ConnectionProcessorTest extends ProphecyTestCase
{
    public function test()
    {
        $message = new Message();
        $options = [
            'doctrine_ping' => false,
            'doctrine_close_master' => true,
        ];

        $innerProcessorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $innerProcessorProphecy->process($message, $options)->willReturn(true);

        $createConnections = function () {
            $connections = [];

            $connectionProphecy = $this->prophesizeConnection();
            $connectionProphecy->close()->shouldNotBeCalled();
            $connections[] = $connectionProphecy->reveal();

            $connectionProphecy = $this->prophesizeMasterSlaveConnection();
            $connectionProphecy->isConnectedToMaster()->willReturn(false);
            $connectionProphecy->close()->shouldNotBeCalled();
            $connections[] = $connectionProphecy->reveal();

            $connectionProphecy = $this->prophesizeMasterSlaveConnection();
            $connectionProphecy->isConnectedToMaster()->willReturn(true);
            $connectionProphecy->close()->shouldBeCalled();
            $connections[] = $connectionProphecy->reveal();

            return $connections;
        };

        $processor = new ConnectionProcessor($innerProcessorProphecy->reveal(), $createConnections());
        $this->assertEquals($processor->process($message, $options), true);

        $connectionRegistry = $this->prophesize('Doctrine\Common\Persistence\ConnectionRegistry');
        $connectionRegistry->getConnections()->willReturn($createConnections);

        $processor = new ConnectionProcessor($innerProcessorProphecy->reveal(), $createConnections());
        $this->assertEquals($processor->process($message, $options), true);
    }

    public function testCloseTimedOutConnection()
    {
        $innerProcessorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $innerProcessorProphecy->process(Argument::cetera())->willReturn(true);

        $dummySql = 'SELECT 1';

        $databasePlatformProphecy = $this->prophesize('Doctrine\DBAL\Platforms\SqlitePlatform');
        $databasePlatformProphecy->getDummySelectSQL()->willReturn($dummySql);

        $connectionProphecy = $this->prophesizeConnection();
        $connectionProphecy->isConnected()->willReturn(true);
        $connectionProphecy->getDatabasePlatform()->willReturn($databasePlatformProphecy->reveal());
        $connectionProphecy->query($dummySql)->willThrow(new DBALException());
        $connectionProphecy->close()->shouldBeCalled();

        $options = [
            'doctrine_ping' => true,
            'doctrine_close_master' => true,
        ];
        $processor = new ConnectionProcessor($innerProcessorProphecy->reveal(), [$connectionProphecy->reveal()], true);
        $processor->process(new Message(), $options);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $connections must be an array of Connection, but one of the elements was stdClass
     */
    public function testRejectNonConnections()
    {
        $innerProcessorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface');

        new ConnectionProcessor($innerProcessorProphecy->reveal(), [new \StdClass]);
    }

    public function testAcceptEmptyConnections()
    {
        $innerProcessorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $innerProcessorProphecy->process(Argument::cetera())->willReturn(true);

        $options = [
            'doctrine_ping' => false,
            'doctrine_close_master' => true,
        ];
        $processor = new ConnectionProcessor($innerProcessorProphecy->reveal(), []);
        $processor->process(new Message(), $options);
    }

    private function prophesizeConnection()
    {
        return $this->prophesize('Doctrine\DBAL\Connection');
    }

    private function prophesizeMasterSlaveConnection()
    {
        return $this->prophesize('Doctrine\DBAL\Connections\MasterSlaveConnection');
    }
}
