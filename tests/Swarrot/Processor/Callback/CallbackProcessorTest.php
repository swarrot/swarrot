<?php

namespace Swarrot\Tests\Processor\Callback;

use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;
use Swarrot\Processor\Callback\CallbackProcessor;

class CallbackProcessorTest extends TestCase
{
    public function callable_provider()
    {
        return [
            [
                function () { return true; },
            ],
            [
                \Closure::fromCallable(function () { return true; }),
            ],
            [
                [
                    new class() {
                        public function get()
                        {
                            return true;
                        }
                    },
                    'get',
                ],
            ],
            [
                new class() {
                    public function __invoke()
                    {
                        return true;
                    }
                },
            ],
        ];
    }

    /**
     * @dataProvider callable_provider
     */
    public function test_process(callable $callable)
    {
        $callbackProcessor = new CallbackProcessor($callable);

        $this->assertTrue($callbackProcessor->process(new Message(), []));
    }

    public function test_process_args()
    {
        $message = new Message();
        $options = ['fake_options_value'];

        $fakeCallable = $this->prophesize(FakeCallable::class);
        $fakeCallable->process($message, $options)->willReturn(true)->shouldBeCalled();

        $callbackProcessor = new CallbackProcessor([$fakeCallable->reveal(), 'process']);

        $this->assertTrue($callbackProcessor->process($message, $options));
    }
}

class FakeCallable
{
    public function process($message, $options)
    {
    }
}
