<?php

namespace spec\Swarrot;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ParameterBagSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Swarrot\ParameterBag');
    }

    function it_store_attributes_and_give_them_back()
    {
        $this->set('key1', 'val1')->shouldReturn($this);
        $this->has('key1')->shouldBe(true);
        $this->get('key1')->shouldBe('val1');

        $this->has('key2')->shouldBe(false);
        $this->get('key2')->shouldBe(null);

        $this->has('key3')->shouldBe(false);
        $this->get('key3', 'foobar')->shouldBe('foobar');
    }
}
