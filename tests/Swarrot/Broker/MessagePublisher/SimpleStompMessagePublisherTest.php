<?php

namespace Swarrot\Tests\Broker\MessagePublisher;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Stomp\Client;
use Stomp\Transport\Message as StompMessage;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessagePublisher\SimpleStompMessagePublisher;

class SimpleStompMessagePublisherTest extends TestCase
{
    /**
     * @var Client|ObjectProphecy
     */
    private $client;

    /**
     * @var SimpleStompMessagePublisher
     */
    private $publisher;

    public function setUp(): void
    {
        $this->client = $this->prophesize(Client::class);

        $this->publisher = new SimpleStompMessagePublisher(
            $this->client->reveal()
        );
    }

    public function test_publish_message()
    {
        $this->client
            ->send(
                'fake_key',
                Argument::that(function (StompMessage $message) {
                    return
                        'fake_body' === $message->getBody() &&
                        ['fake_property'] === $message->getHeaders();
                })
            )
            ->shouldBeCalled();

        $this->publisher->publish(new Message('fake_body', ['fake_property'], 'fake_id'), 'fake_key');
    }
}
