<?php

namespace Swarrot\Processor\Decorator;

use Prophecy\PhpUnit\ProphecyTestCase;
use Prophecy\Argument;
use Swarrot\Broker\Message;

class DecoratorProcessorTest extends ProphecyTestCase
{
    public function test_it_calls_decorator()
    {
        $decoratorProphecy = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface');
        $processorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface');

        $decoratorProcessor = new DecoratorProcessor($decoratorProphecy->reveal(), $processorProphecy->reveal());

        $message = new Message();
        $options = [];
        $decoratorResult = true;

        $decoratorProphecy
            ->decorate($processorProphecy->reveal(), $message, $options)
            ->willReturn($decoratorResult)
        ;

        $this->assertEquals($decoratorResult, $decoratorProcessor->process($message, $options));
    }

    public function test_it_calls_setDefaultOptions_if_configurable()
    {
        $decoratorProphecy = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')
            ->willImplement('Swarrot\Processor\ConfigurableInterface');
        $processorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface')
            ->willImplement('Swarrot\Processor\ConfigurableInterface');

        $decoratorProcessor = new DecoratorProcessor($decoratorProphecy->reveal(), $processorProphecy->reveal());

        $optionResolverProphecy = $this->prophesize('Symfony\Component\OptionsResolver\OptionsResolverInterface');

        $decoratorProphecy->setDefaultOptions($optionResolverProphecy->reveal())->shouldBeCalled();
        $processorProphecy->setDefaultOptions($optionResolverProphecy->reveal())->shouldBeCalled();

        $decoratorProcessor->setDefaultOptions($optionResolverProphecy->reveal());
    }

    public function test_it_calls_terminate_if_terminable()
    {
        $decoratorProphecy = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')
            ->willImplement('Swarrot\Processor\TerminableInterface');
        $processorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface')
            ->willImplement('Swarrot\Processor\TerminableInterface');

        $decoratorProcessor = new DecoratorProcessor($decoratorProphecy->reveal(), $processorProphecy->reveal());

        $options = ['foo' => 'bar'];

        $decoratorProphecy->terminate($options)->shouldBeCalled();
        $processorProphecy->terminate($options)->shouldBeCalled();

        $decoratorProcessor->terminate($options);
    }

    public function test_it_calls_initialize_if_initializable()
    {
        $decoratorProphecy = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')
            ->willImplement('Swarrot\Processor\InitializableInterface');
        $processorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface')
            ->willImplement('Swarrot\Processor\InitializableInterface');

        $decoratorProcessor = new DecoratorProcessor($decoratorProphecy->reveal(), $processorProphecy->reveal());

        $options = ['foo' => 'bar'];

        $decoratorProphecy->initialize($options)->shouldBeCalled();
        $processorProphecy->initialize($options)->shouldBeCalled();

        $decoratorProcessor->initialize($options);
    }

    public function test_it_calls_sleep_if_sleepy()
    {
        $decoratorProphecy = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')
            ->willImplement('Swarrot\Processor\SleepyInterface');
        $processorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface')
            ->willImplement('Swarrot\Processor\SleepyInterface');

        $decoratorProcessor = new DecoratorProcessor($decoratorProphecy->reveal(), $processorProphecy->reveal());

        $options = ['foo' => 'bar'];

        $decoratorProphecy->sleep($options)->willReturn(true)->shouldBeCalled();
        $processorProphecy->sleep($options)->willReturn(false)->shouldBeCalled();

        $this->assertEquals(true, $decoratorProcessor->sleep($options));
    }

    public function test_sleep_returns_false_if_no_sleepy()
    {
        $decoratorProphecy = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface');
        $processorProphecy = $this->prophesize('Swarrot\Processor\ProcessorInterface');

        $decoratorProcessor = new DecoratorProcessor($decoratorProphecy->reveal(), $processorProphecy->reveal());

        $this->assertEquals(false, $decoratorProcessor->sleep([]));
    }
}
