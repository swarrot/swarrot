<?php

namespace Swarrot\Tests\Broker\MessageProvider;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessageProvider\CallbackMessageProvider;

class CallbackMessageProviderTest extends TestCase
{
    use ProphecyTrait;

    public static function callable_with_message_provider()
    {
        return [
            [
                function () { return new Message(); },
            ],
            [
                \Closure::fromCallable(function () { return new Message(); }),
            ],
            [
                [
                    new class {
                        public function get()
                        {
                            return new Message();
                        }
                    },
                    'get',
                ],
            ],
            [
                new class {
                    public function __invoke()
                    {
                        return new Message();
                    }
                },
            ],
        ];
    }

    /**
     * @dataProvider callable_with_message_provider
     */
    public function test_get_with_messages_in_queue_return_message($callableProvider)
    {
        $provider = new CallbackMessageProvider($callableProvider);
        $message = $provider->get();

        $this->assertInstanceOf(Message::class, $message);
    }

    public static function callable_without_message_provider()
    {
        return [
            [
                function () { return null; },
            ],
            [
                \Closure::fromCallable(function () { return null; }),
            ],
            [
                [
                    new class {
                        public function get()
                        {
                            return null;
                        }
                    },
                    'get',
                ],
            ],
            [
                new class {
                    public function __invoke()
                    {
                        return null;
                    }
                },
            ],
        ];
    }

    /**
     * @dataProvider callable_without_message_provider
     */
    public function test_get_without_messages_in_queue_return_null($callableProvider)
    {
        $provider = new CallbackMessageProvider($callableProvider);
        $message = $provider->get();

        $this->assertNull($message);
    }

    public function test_ack()
    {
        $message = new Message();
        $ackMock = $this->prophesize(AckNackProvider::class);
        $ackMock->ack($message)->shouldBeCalled();

        $provider = new CallbackMessageProvider(function () {}, [$ackMock->reveal(), 'ack']);
        $provider->ack($message);
    }

    public function test_nack()
    {
        $message = new Message();
        $nackMock = $this->prophesize(AckNackProvider::class);
        $nackMock->nack($message, false)->shouldBeCalled();

        $provider = new CallbackMessageProvider(function () {}, function () {}, [$nackMock->reveal(), 'nack']);
        $provider->nack($message);
    }

    public function test_get_name()
    {
        $provider = new CallbackMessageProvider(function () {});

        $this->assertEquals('', $provider->getQueueName());
    }
}
class AckNackProvider
{
    public function ack(Message $message)
    {
    }

    public function nack(Message $message)
    {
    }
}
