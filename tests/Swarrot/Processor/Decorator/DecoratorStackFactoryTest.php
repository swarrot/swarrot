<?php

namespace Swarrot\Processor\Decorator;

use Prophecy\PhpUnit\ProphecyTestCase;

class DecoratorStackFactoryTest extends ProphecyTestCase
{
    public function test()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface')->reveal();
        $decorator1 = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')->reveal();
        $decorator2 = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')->reveal();

        $decoratorStack = DecoratorStackFactory::create(
            $processor,
            [
                $decorator1,
                $decorator2
            ]
        );

        $this->assertInstanceOf('Swarrot\Processor\Decorator\DecoratorProcessor', $decoratorStack);
        $this->assertSame($decorator1, $decoratorStack->getDecorator());

        $this->assertInstanceOf('Swarrot\Processor\Decorator\DecoratorProcessor', $decoratorStack->getProcessor());
        $this->assertSame($decorator2, $decoratorStack->getProcessor()->getDecorator());
        $this->assertSame($processor, $decoratorStack->getProcessor()->getProcessor());
    }
}
