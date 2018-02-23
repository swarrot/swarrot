<?php

namespace Swarrot\Tests\Processor\Ack;

use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;
use Swarrot\Processor\Callback\CallbackProcessor;

class CallbackProcessorTest extends TestCase
{
    public function callable_provider()
    {
        return [
            [
                function () { return 'fake_callable_return'; },
            ],
            [
                \Closure::fromCallable(function () { return 'fake_callable_return'; }),
            ],
            [
                [
                    new class() {
                        public function get()
                        {
                            return 'fake_callable_return';
                        }
                    },
                    'get',
                ],
            ],
            [
                new class() {
                    public function __invoke()
                    {
                        return 'fake_callable_return';
                    }
                },
            ],
        ];
    }

    /**
     * @dataProvider callable_provider
     *
     * @param $callable
     */
    public function test_process($callable)
    {
        $callbackProcessor = new CallbackProcessor($callable);

        $this->assertEquals('fake_callable_return', $callbackProcessor->process(new Message(), []));
    }

    public function test_process_args()
    {
        $message = new Message();
        $options = ['fake_options_value'];

        $fakeCallable = $this->prophesize(FakeCallable::class);
        $fakeCallable->process($message, $options)->willReturn('fake_callable_return')->shouldBeCalled();

        $callbackProcessor = new CallbackProcessor([$fakeCallable->reveal(), 'process']);

        $this->assertEquals('fake_callable_return', $callbackProcessor->process($message, $options));
    }
}

class FakeCallable
{
    public function process($message, $options)
    {
    }
}
