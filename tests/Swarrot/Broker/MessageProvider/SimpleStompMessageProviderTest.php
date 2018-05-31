<?php

namespace Swarrot\Tests\Broker\MessageProvider;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Stomp\Client;
use Stomp\Protocol\Protocol;
use Stomp\Transport\Frame;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessageProvider\SimpleStompMessageProvider;

class SimpleStompMessageProviderTest extends TestCase
{
    /**
     * @var Protocol|ObjectProphecy
     */
    private $protocol;

    /**
     * @var Client|ObjectProphecy
     */
    private $client;

    /**
     * @var SimpleStompMessageProvider
     */
    private $provider;

    public function setUp()
    {
        $subscriptionFrame = $this->prophesize(Frame::class);
        $subscriptionFrame
            ->addHeaders(['fake_header'])
            ->willReturn($subscriptionFrame->reveal())
            ->shouldBeCalled();

        $this->protocol = $this->prophesize(Protocol::class);
        $this->protocol
            ->getSubscribeFrame('fake_destination', 'fake_subscription_id', 'fake_ack', 'fake_selector')
            ->willReturn($subscriptionFrame->reveal())
            ->shouldBeCalled();

        $this->client = $this->prophesize(Client::class);
        $this->client
            ->getProtocol()
            ->willReturn($this->protocol->reveal())
            ->shouldBeCalled();

        $this->client
            ->sendFrame($subscriptionFrame->reveal())
            ->shouldBeCalled();

        $this->provider = new SimpleStompMessageProvider(
            $this->client->reveal(),
            'fake_destination',
            'fake_subscription_id',
            'fake_ack',
            'fake_selector',
            ['fake_header']
        );
    }

    public function test_get_with_messages_in_queue_return_message()
    {
        $frame = $this->prophesize(Frame::class);
        $frame
            ->getHeaders()
            ->willReturn(['fake_headers'])
            ->shouldBeCalled();
        $frame
            ->getBody()
            ->willReturn('fake_body')
            ->shouldBeCalled();

        $this->client
            ->readFrame()
            ->willReturn($frame->reveal())
            ->shouldBeCalled();

        $message = $this->provider->get();

        $this->assertInstanceOf('Swarrot\Broker\Message', $message);
    }

    public function test_get_without_messages_in_queue_return_null()
    {
        $this->client
            ->readFrame()
            ->willReturn(null)
            ->shouldBeCalled();

        $message = $this->provider->get();

        $this->assertNull($message);
    }

    public function test_ack()
    {
        $frame = $this->prophesize(Frame::class);

        $this->protocol
            ->getAckFrame(Argument::that(function (Frame $frame) {
                return
                    'SEND' === $frame->getCommand() &&
                    'fake_body' === $frame->getBody() &&
                    ['fake_property'] === $frame->getHeaders();
            }))
            ->willReturn($frame->reveal())
            ->shouldBeCalled();

        $this->client
            ->sendFrame($frame->reveal(), false)
            ->shouldBeCalled();

        $this->provider->ack(new Message('fake_body', ['fake_property']));
    }

    public function test_nack()
    {
        $frame = $this->prophesize(Frame::class);

        $this->protocol
            ->getNackFrame(
                Argument::that(function (Frame $frame) {
                    return
                        'SEND' === $frame->getCommand() &&
                        'fake_body' === $frame->getBody() &&
                        ['fake_property'] === $frame->getHeaders();
                }),
                null,
                true
            )
            ->willReturn($frame->reveal())
            ->shouldBeCalled();

        $this->client
            ->sendFrame($frame->reveal(), false)
            ->shouldBeCalled();

        $this->provider->nack(new Message('fake_body', ['fake_property']), true);
    }

    /**
     * @expectedException \Stomp\Exception\StompException
     * @expectedExceptionMessage Stomp protocol is require to NACK Frames.
     */
    public function test_nack_without_protocol()
    {
        $this->client
            ->getProtocol()
            ->willReturn(null)
            ->shouldBeCalled();

        $this->provider->nack(new Message('fake_body', ['fake_property']), true);
    }

    public function test_get_name()
    {
        $this->assertEquals('fake_destination', $this->provider->getQueueName());
    }
}
