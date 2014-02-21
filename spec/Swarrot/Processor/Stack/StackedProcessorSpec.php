<?php

namespace spec\Swarrot\Processor\Stack;

use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\TerminableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\ParameterBag;
use Swarrot\AMQP\Message;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StackedProcessorSpec extends ObjectBehavior
{
    function it_is_initializable(InitializableProcessor $p1, TerminableProcessor $p2)
    {
        $this->beConstructedWith(array($p1, $p2));
        $this->shouldHaveType('Swarrot\Processor\Stack\StackedProcessor');
    }
}

class InitializableProcessor implements InitializableInterface {
    public function initialize(ParameterBag $bag) {}
}

class TerminableProcessor implements TerminableInterface {
    public function terminate(ParameterBag $bag) {}
}
