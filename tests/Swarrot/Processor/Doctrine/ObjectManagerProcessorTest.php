<?php

namespace Swarrot\Tests\Processor\Doctrine;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Swarrot\Broker\Message;
use Swarrot\Processor\Doctrine\ObjectManagerProcessor;
use Swarrot\Processor\ProcessorInterface;

class ObjectManagerProcessorTest extends TestCase
{
    use ProphecyTrait;

    public function test()
    {
        $message = new Message();
        $options = [];

        $innerProcessorProphecy = $this->prophesize(ProcessorInterface::class);
        $innerProcessorProphecy->process($message, $options)->willReturn(true);

        $objectManagers = [];

        $objectManagerProphecy = $this->prophesize(ObjectManager::class);
        $objectManagerProphecy->clear()->shouldBeCalled();
        $objectManagers['default'] = $objectManagerProphecy->reveal();

        $objectManagerProphecy = $this->prophesize(__NAMESPACE__.'\\ObjectManagerWithIsOpen');
        $objectManagerProphecy->isOpen()->willReturn(true);
        $objectManagerProphecy->clear()->shouldBeCalled();
        $objectManagers['foo'] = $objectManagerProphecy->reveal();

        $objectManagerProphecy = $this->prophesize(__NAMESPACE__.'\\ObjectManagerWithIsOpen');
        $objectManagerProphecy->isOpen()->willReturn(false);
        $objectManagerProphecy->clear()->shouldNotBeCalled();
        $objectManagers['bar'] = $objectManagerProphecy->reveal();

        $managerRegistryProphecy = $this->prophesize(ManagerRegistry::class);
        $managerRegistryProphecy->getManagers()->willReturn($objectManagers);
        $managerRegistryProphecy->resetManager('bar')->shouldBeCalled();

        $processor = new ObjectManagerProcessor($innerProcessorProphecy->reveal(), $managerRegistryProphecy->reveal());

        $this->assertEquals($processor->process($message, $options), true);
    }

    public function testWithException()
    {
        $message = new Message();
        $options = [];

        $innerProcessorProphecy = $this->prophesize(ProcessorInterface::class);
        $innerProcessorProphecy->process($message, $options)->willThrow(new \Exception('my_fake_message'));

        $objectManagers = [];

        $objectManagerProphecy = $this->prophesize(ObjectManager::class);
        $objectManagerProphecy->clear()->shouldBeCalled();
        $objectManagers['default'] = $objectManagerProphecy->reveal();

        $objectManagerProphecy = $this->prophesize(__NAMESPACE__.'\\ObjectManagerWithIsOpen');
        $objectManagerProphecy->isOpen()->willReturn(true);
        $objectManagerProphecy->clear()->shouldBeCalled();
        $objectManagers['foo'] = $objectManagerProphecy->reveal();

        $objectManagerProphecy = $this->prophesize(__NAMESPACE__.'\\ObjectManagerWithIsOpen');
        $objectManagerProphecy->isOpen()->willReturn(false);
        $objectManagerProphecy->clear()->shouldNotBeCalled();
        $objectManagers['bar'] = $objectManagerProphecy->reveal();

        $managerRegistryProphecy = $this->prophesize(ManagerRegistry::class);
        $managerRegistryProphecy->getManagers()->willReturn($objectManagers);
        $managerRegistryProphecy->resetManager('bar')->shouldBeCalled();

        $processor = new ObjectManagerProcessor($innerProcessorProphecy->reveal(), $managerRegistryProphecy->reveal());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('my_fake_message');

        $processor->process($message, $options);
    }
}

interface ObjectManagerWithIsOpen extends ObjectManager
{
    public function isOpen();
}
