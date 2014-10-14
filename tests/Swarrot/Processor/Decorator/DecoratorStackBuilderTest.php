<?php

namespace Swarrot\Processor\Decorator;

use Prophecy\PhpUnit\ProphecyTestCase;

class DecoratorStackBuilderTest extends ProphecyTestCase
{
    public function testBuild()
    {
        $processor = $this->prophesize('Swarrot\Processor\ProcessorInterface')->reveal();
        $decorator1 = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')->reveal();
        $decorator2 = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')->reveal();
        $decorator3 = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')->reveal();
        $decorator4 = $this->prophesize('Swarrot\Processor\Decorator\DecoratorInterface')->reveal();

        $decoratorStackFactoryProphecy = $this->prophesize('Swarrot\Processor\Decorator\DecoratorStackFactory');
        $decoratorStackFactoryProphecy
            ->create(
                $processor,
                [
                    $decorator4,
                    $decorator1,
                    $decorator2,
                    $decorator3
                ]
            )
            ->willReturn(
                $expectedDecoratorStack = $this->prophesize('Swarrot\Processor\ProcessorInterface')->reveal()
            )
        ;

        $builder = new DecoratorStackBuilder($decoratorStackFactoryProphecy->reveal());
        $builder->addDecorator($decorator1);
        $builder->addDecorator($decorator2);
        $builder->addDecorator($decorator3, 10);
        $builder->addDecorator($decorator4, -10);

        $decoratorStack = $builder->build($processor);

        $this->assertSame($expectedDecoratorStack, $decoratorStack);
    }
}
