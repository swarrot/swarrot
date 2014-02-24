<?php

namespace spec\Swarrot;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swarrot\AMQP\MessageProviderInterface;
use Swarrot\Processor\ProcessorInterface;

class ConsumerSpec extends ObjectBehavior
{
    function let(MessageProviderInterface $provider, ProcessorInterface $processor)
    {
        $this->beConstructedWith($provider, $processor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Swarrot\Consumer');
    }
}
