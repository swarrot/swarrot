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
        $options = array(
            'doctrine_ping' => false,
            'doctrine_close_master' => true,
        );

        $innerProcessorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $innerProcessorProphecy->process($message, $options)->willReturn(true);

        $test = $this;

        $createConnections = function () use ($test) {
            $connections = array();

            $connectionProphecy = $test->prophesizeConnection();
            $connectionProphecy->close()->shouldNotBeCalled();
            $connections[] = $connectionProphecy->reveal();

            $connectionProphecy = $test->prophesizeMasterSlaveConnection();
            $connectionProphecy->isConnectedToMaster()->willReturn(false);
            $connectionProphecy->close()->shouldNotBeCalled();
            $connections[] = $connectionProphecy->reveal();

            $connectionProphecy = $test->prophesizeMasterSlaveConnection();
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

        $options = array(
            'doctrine_ping' => true,
            'doctrine_close_master' => true,
        );
        $processor = new ConnectionProcessor($innerProcessorProphecy->reveal(), array($connectionProphecy->reveal()), true);
        $processor->process(new Message(), $options);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $connections must be an array of Connection, but one of the elements was stdClass
     */
    public function testRejectNonConnections()
    {
        $innerProcessorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface');

        new ConnectionProcessor($innerProcessorProphecy->reveal(), array(new \StdClass));
    }

    public function testAcceptEmptyConnections()
    {
        $innerProcessorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $innerProcessorProphecy->process(Argument::cetera())->willReturn(true);

        $options = array(
            'doctrine_ping' => false,
            'doctrine_close_master' => true,
        );
        $processor = new ConnectionProcessor($innerProcessorProphecy->reveal(), array());
        $processor->process(new Message(), $options);
    }

    public function prophesizeConnection()
    {
        return $this->prophesize('Doctrine\DBAL\Connection');
    }

    public function prophesizeMasterSlaveConnection()
    {
        return $this->prophesize('Doctrine\DBAL\Connections\MasterSlaveConnection');
    }
}
