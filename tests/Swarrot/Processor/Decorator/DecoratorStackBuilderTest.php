<?php

namespace Swarrot\Processor\Decorator;

use Prophecy\PhpUnit\ProphecyTestCase;

class DecoratorStackBuilderTest extends ProphecyTestCase
{
    public function testCreateStack()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface')->reveal();
        $decorator1 = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')->reveal();
        $decorator2 = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')->reveal();

        $decoratorStack = DecoratorStackBuilder::createStack(
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

    public function testBuild()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface')->reveal();
        $decorator1 = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')->reveal();
        $decorator2 = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')->reveal();
        $decorator3 = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')->reveal();
        $decorator4 = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')->reveal();

        $builder = new DecoratorStackBuilder();
        $builder->addDecorator($decorator1);
        $builder->addDecorator($decorator2);
        $builder->addDecorator($decorator3, 10);
        $builder->addDecorator($decorator4, -10);

        $decoratorStack = $builder->build($processor);

        $this->assertInstanceOf('Swarrot\Processor\Decorator\DecoratorProcessor', $decoratorStack);
        $this->assertSame($decorator4, $decoratorStack->getDecorator());

        $this->assertInstanceOf('Swarrot\Processor\Decorator\DecoratorProcessor', $decoratorStack->getProcessor());
        $this->assertSame($decorator1, $decoratorStack->getProcessor()->getDecorator());

        $this->assertInstanceOf('Swarrot\Processor\Decorator\DecoratorProcessor', $decoratorStack->getProcessor()->getProcessor());
        $this->assertSame($decorator2, $decoratorStack->getProcessor()->getProcessor()->getDecorator());

        $this->assertInstanceOf('Swarrot\Processor\Decorator\DecoratorProcessor', $decoratorStack->getProcessor()->getProcessor()->getProcessor());
        $this->assertSame($decorator3, $decoratorStack->getProcessor()->getProcessor()->getProcessor()->getDecorator());

        $this->assertSame($processor, $decoratorStack->getProcessor()->getProcessor()->getProcessor()->getProcessor());
    }
}
