<?php

namespace Swarrot\Processor\Ack;

use Doctrine\Common\Persistence\ObjectManager;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Swarrot\Broker\Message;
use Swarrot\Processor\Doctrine\ObjectManagerProcessor;

class ObjectManagerProcessorTest extends ProphecyTestCase
{
    public function test()
    {
        $message = new Message();
        $options = array();

        $innerProcessorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface');
        $innerProcessorProphecy->process($message, $options)->willReturn(true);

        $objectManagers = array();

        $objectManagerProphecy = $this->prophesize('Doctrine\Common\Persistence\ObjectManager');
        $objectManagerProphecy->clear()->shouldBeCalled();
        $objectManagers['default'] = $objectManagerProphecy->reveal();

        $objectManagerProphecy = $this->prophesize(__NAMESPACE__ . '\\ObjectManagerWithIsOpen');
        $objectManagerProphecy->isOpen()->willReturn(true);
        $objectManagerProphecy->clear()->shouldBeCalled();
        $objectManagers['foo'] = $objectManagerProphecy->reveal();

        $objectManagerProphecy = $this->prophesize(__NAMESPACE__ . '\\ObjectManagerWithIsOpen');
        $objectManagerProphecy->isOpen()->willReturn(false);
        $objectManagerProphecy->clear()->shouldNotBeCalled();
        $objectManagers['bar'] = $objectManagerProphecy->reveal();

        $managerRegistryProphecy = $this->prophesize('Doctrine\Common\Persistence\ManagerRegistry');
        $managerRegistryProphecy->getManagers()->willReturn($objectManagers);
        $managerRegistryProphecy->resetManager('bar')->shouldBeCalled();

        $processor = new ObjectManagerProcessor($innerProcessorProphecy->reveal(), $managerRegistryProphecy->reveal());

        $this->assertEquals($processor->process($message, $options), true);
    }
}

interface ObjectManagerWithIsOpen extends ObjectManager
{
    public function isOpen();
}
