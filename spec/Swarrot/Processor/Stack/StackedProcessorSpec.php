<?php

namespace spec\Swarrot\Processor\Stack;

use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\TerminableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\Message;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StackedProcessorSpec extends ObjectBehavior
{
    function it_is_initializable(ProcessorInterface $p1, InitializableProcessor $p2, TerminableProcessor $p3)
    {
        $this->beConstructedWith($p1, array($p2, $p3));
        $this->shouldHaveType('Swarrot\Processor\Stack\StackedProcessor');
    }
}

class InitializableProcessor implements InitializableInterface {
    public function initialize(array $options) {}
}

class TerminableProcessor implements TerminableInterface {
    public function terminate(array $options) {}
}
