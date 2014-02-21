<?php

namespace spec\Swarrot;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swarrot\AMQP\MessageProviderInterface;

class ConsumerSpec extends ObjectBehavior
{
    function let(MessageProviderInterface $provider)
    {
        $this->beConstructedWith($provider);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Swarrot\Consumer');
    }
}
